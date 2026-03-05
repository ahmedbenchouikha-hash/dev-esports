<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use App\Service\SmartPricingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/smart-pricing')]
#[IsGranted('ROLE_ADMIN')]
class SmartPricingController extends AbstractController
{
    public function __construct(
        private SmartPricingService $pricingService,
        private TicketRepository $ticketRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Show all tickets with smart pricing recommendations
     */
    #[Route('', name: 'smart_pricing_index', methods: ['GET'])]
    public function index(): Response
    {
        $tickets = $this->ticketRepository->findAllWithGameAndTeams();

        $recommendations = [];
        foreach ($tickets as $ticket) {
            $recommendations[] = [
                'ticket' => $ticket,
                'pricing' => $this->pricingService->getPricingRecommendation($ticket),
            ];
        }

        // Sort by potential revenue gain
        usort($recommendations, function ($a, $b) {
            return abs($b['pricing']['difference']) <=> abs($a['pricing']['difference']);
        });

        return $this->render('admin/smart_pricing/index.html.twig', [
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Get pricing details for a specific ticket (AJAX)
     */
    #[Route('/ticket/{id}', name: 'smart_pricing_detail', methods: ['GET'])]
    public function getTicketPricing(Ticket $ticket): JsonResponse
    {
        $recommendation = $this->pricingService->getPricingRecommendation($ticket);

        return $this->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->getId(),
                'name' => $ticket->getTicketNumber(),
                'type' => $ticket->getType(),
            ],
            'recommendation' => $recommendation,
        ]);
    }

    /**
     * Apply smart price to a ticket
     */
    #[Route('/apply/{id}', name: 'smart_pricing_apply', methods: ['POST'])]
    public function applySmartPrice(Ticket $ticket): JsonResponse
    {
        try {
            $oldPrice = $ticket->getPrice();
            $newPrice = $this->pricingService->calculateSmartPrice($ticket);

            $ticket->setPrice($newPrice);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "Price updated from €{$oldPrice} to €{$newPrice}",
                'oldPrice' => $oldPrice,
                'newPrice' => $newPrice,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error updating price: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Apply smart prices to all tickets
     */
    #[Route('/apply-all', name: 'smart_pricing_apply_all', methods: ['POST'])]
    public function applyAllSmartPrices(): JsonResponse
    {
        try {
            $tickets = $this->ticketRepository->findAllWithGameAndTeams();
            $updated = 0;
            $totalRevenueDifference = 0;

            foreach ($tickets as $ticket) {
                $oldPrice = $ticket->getPrice();
                $newPrice = $this->pricingService->calculateSmartPrice($ticket);

                $ticket->setPrice($newPrice);
                $sold = $ticket->getSold();
                $totalRevenueDifference += ($newPrice - $oldPrice) * $sold;
                $updated++;
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "Applied smart pricing to {$updated} tickets",
                'ticketsUpdated' => $updated,
                'projectedRevenueImpact' => round($totalRevenueDifference, 2),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error applying prices: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get pricing statistics dashboard
     */
    #[Route('/stats', name: 'smart_pricing_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $tickets = $this->ticketRepository->findAll();

        $stats = [
            'totalTickets' => count($tickets),
            'shouldIncrease' => 0,
            'shouldDecrease' => 0,
            'keepCurrent' => 0,
            'totalPotentialIncrease' => 0,
            'totalPotentialDecrease' => 0,
            'averageDemand' => 0,
            'averagePopularity' => 0,
        ];

        $demandSum = 0;
        $popularitySum = 0;

        foreach ($tickets as $ticket) {
            $rec = $this->pricingService->getPricingRecommendation($ticket);

            match ($rec['recommendedAction']) {
                'INCREASE_PRICE' => $stats['shouldIncrease']++,
                'DECREASE_PRICE' => $stats['shouldDecrease']++,
                'KEEP_CURRENT' => $stats['keepCurrent']++,
                default => null,
            };

            if ($rec['recommendedAction'] === 'INCREASE_PRICE') {
                $stats['totalPotentialIncrease'] += $rec['difference'] * $ticket->getSold();
            } else {
                $stats['totalPotentialDecrease'] += abs($rec['difference']) * $ticket->getSold();
            }

            $demandSum += $rec['demand'];
            $popularitySum += $rec['popularity'];
        }

        $stats['averageDemand'] = round($demandSum / count($tickets), 2);
        $stats['averagePopularity'] = round($popularitySum / count($tickets), 2);
        $stats['totalPotentialIncrease'] = round($stats['totalPotentialIncrease'], 2);
        $stats['totalPotentialDecrease'] = round($stats['totalPotentialDecrease'], 2);

        return $this->json($stats);
    }
}
