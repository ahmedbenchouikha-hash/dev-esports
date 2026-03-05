<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

/**
 * Smart Pricing Service - Adjusts ticket prices based on:
 * - Demand (tickets sold vs. available)
 * - Team popularity (historical sales)
 * - Time until match (scarcity)
 * - Historical pricing trends
 */
class SmartPricingService
{
    private array $teamSalesCache = [];

    public function __construct(
        private PaymentRepository $paymentRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Calculate smart price for a ticket
     * 
     * @param Ticket $ticket
     * @return float Recommended price
     */
    public function calculateSmartPrice(Ticket $ticket): float
    {
        $basePrice = $ticket->getPrice();
        $multiplier = 1.0;

        // 1. DEMAND MULTIPLIER - Higher demand = higher price
        $demandMultiplier = $this->calculateDemandMultiplier($ticket);
        $multiplier *= $demandMultiplier;

        // 2. TEAM POPULARITY MULTIPLIER - Popular teams can charge more
        $popularityMultiplier = $this->calculatePopularityMultiplier($ticket);
        $multiplier *= $popularityMultiplier;

        // 3. SCARCITY MULTIPLIER - Less time = higher price (urgency)
        $scarcityMultiplier = $this->calculateScarcityMultiplier($ticket);
        $multiplier *= $scarcityMultiplier;

        // 4. TICKET TYPE MULTIPLIER - VIP more expensive than regular
        $typeMultiplier = $this->calculateTypeMultiplier($ticket);
        $multiplier *= $typeMultiplier;

        // Calculate final price with 20% cap (don't go too crazy)
        $maxMultiplier = 1.20; // 20% increase max
        $minMultiplier = 0.85; // 15% decrease min
        $multiplier = min(max($multiplier, $minMultiplier), $maxMultiplier);

        $smartPrice = $basePrice * $multiplier;

        // Round to nearest 0.5
        return round($smartPrice * 2) / 2;
    }

    /**
     * Calculate demand multiplier (0.8 - 1.3)
     * Sold percentage determines price adjustment
     */
    private function calculateDemandMultiplier(Ticket $ticket): float
    {
        $quantity = $ticket->getQuantity();
        $sold = $ticket->getSold();
        
        if ($quantity <= 0) {
            return 1.0;
        }

        $soldPercentage = $sold / $quantity;

        // 0-25% sold = 0.85x (discount to move stock)
        // 25-50% sold = 1.0x (normal)
        // 50-75% sold = 1.1x (premium, limited)
        // 75-90% sold = 1.2x (very limited)
        // 90%+ sold = 1.3x (almost gone)

        if ($soldPercentage < 0.25) {
            return 0.85;
        } elseif ($soldPercentage < 0.50) {
            return 0.95;
        } elseif ($soldPercentage < 0.75) {
            return 1.05;
        } elseif ($soldPercentage < 0.90) {
            return 1.15;
        } else {
            return 1.30;
        }
    }

    /**
     * Calculate team popularity multiplier (0.9 - 1.2)
     * Based on historical ticket sales for these teams
     */
    private function calculatePopularityMultiplier(Ticket $ticket): float
    {
        if (!$ticket->getGame() || !$ticket->getGame()->getTeam1() || !$ticket->getGame()->getTeam2()) {
            return 1.0;
        }

        $team1 = $ticket->getGame()->getTeam1();
        $team2 = $ticket->getGame()->getTeam2();

        // Get historical sales for both teams
        $team1Sales = $this->getHistoricalTeamSales($team1->getId());
        $team2Sales = $this->getHistoricalTeamSales($team2->getId());

        // Average popularity score
        $avgPopularity = ($team1Sales + $team2Sales) / 2;

        // 0-50 sales: 0.90x (low popularity)
        // 50-150 sales: 1.0x (normal)
        // 150-300 sales: 1.1x (popular)
        // 300+ sales: 1.2x (very popular)

        if ($avgPopularity < 50) {
            return 0.90;
        } elseif ($avgPopularity < 150) {
            return 1.00;
        } elseif ($avgPopularity < 300) {
            return 1.10;
        } else {
            return 1.20;
        }
    }

    /**
     * Calculate scarcity multiplier (0.9 - 1.25)
     * Time until match determines urgency pricing
     */
    private function calculateScarcityMultiplier(Ticket $ticket): float
    {
        if (!$ticket->getGame() || !$ticket->getGame()->getMatchdate()) {
            return 1.0;
        }

        $matchDate = $ticket->getGame()->getMatchdate();
        $today = new DateTime();
        $daysUntil = $matchDate->diff($today)->days;

        // More than 60 days: 0.90x (early bird discount)
        // 30-60 days: 1.0x (normal)
        // 14-30 days: 1.10x (approaching)
        // 7-14 days: 1.20x (soon)
        // Less than 7 days: 1.25x (last minute)

        if ($daysUntil > 60) {
            return 0.90;
        } elseif ($daysUntil > 30) {
            return 1.00;
        } elseif ($daysUntil > 14) {
            return 1.10;
        } elseif ($daysUntil > 7) {
            return 1.20;
        } else {
            return 1.25;
        }
    }

    /**
     * Calculate ticket type multiplier
     * VIP and Student tickets have different price adjustments
     */
    private function calculateTypeMultiplier(Ticket $ticket): float
    {
        $type = $ticket->getType();

        return match ($type) {
            'vip' => 1.0,      // VIP already has premium price
            'student' => 0.95, // Student gets small discount
            'regular' => 1.0,  // Regular is baseline
            default => 1.0,
        };
    }

    /**
     * Get historical sales count for a team
     */
    private function getHistoricalTeamSales(int $teamId): int
    {
        if (isset($this->teamSalesCache[$teamId])) {
            return $this->teamSalesCache[$teamId];
        }

        $qb = $this->entityManager->createQueryBuilder();
        
        $result = $qb->select('COUNT(p.id)')
            ->from(Payment::class, 'p')
            ->leftJoin('p.ticket', 't')
            ->leftJoin('t.game', 'g')
            ->where('(g.team1 = :teamId OR g.team2 = :teamId)')
            ->andWhere('p.status = :status')
            ->setParameter('teamId', $teamId)
            ->setParameter('status', 'succeeded')
            ->getQuery()
            ->getSingleScalarResult();

        $this->teamSalesCache[$teamId] = (int) $result;

        return $this->teamSalesCache[$teamId];
    }

    /**
     * Get pricing recommendation with breakdown
     * Returns array with suggested price and multiplier breakdown
     */
    public function getPricingRecommendation(Ticket $ticket): array
    {
        $basePrice = $ticket->getPrice();
        $smartPrice = $this->calculateSmartPrice($ticket);
        $priceDifference = $smartPrice - $basePrice;
        $percentChange = ($priceDifference / $basePrice) * 100;

        return [
            'basePrice' => $basePrice,
            'smartPrice' => $smartPrice,
            'difference' => $priceDifference,
            'percentChange' => round($percentChange, 2),
            'demand' => $this->calculateDemandMultiplier($ticket),
            'popularity' => $this->calculatePopularityMultiplier($ticket),
            'scarcity' => $this->calculateScarcityMultiplier($ticket),
            'type' => $this->calculateTypeMultiplier($ticket),
            'recommendedAction' => $this->getRecommendedAction($smartPrice, $basePrice),
        ];
    }

    /**
     * Get action recommendation
     */
    private function getRecommendedAction(float $smartPrice, float $basePrice): string
    {
        $diff = $smartPrice - $basePrice;
        $percentChange = ($diff / $basePrice) * 100;

        if ($percentChange > 10) {
            return 'INCREASE_PRICE';
        } elseif ($percentChange < -10) {
            return 'DECREASE_PRICE';
        } else {
            return 'KEEP_CURRENT';
        }
    }
}
