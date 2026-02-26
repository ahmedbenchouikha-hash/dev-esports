<?php

namespace App\Service;

use App\Repository\DemandeRecompenseRepository;
use App\Repository\RecompenseRepository;
use App\Repository\DepenseRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Service d'Analytics pour les Récompenses
 * Calcule les statistiques avancées pour le dashboard
 */
class RewardAnalyticsService
{
    public function __construct(
        private DemandeRecompenseRepository $demandeRepo,
        private RecompenseRepository $recompenseRepo,
        private DepenseRepository $depenseRepo
    ) {}

    /**
     * Récupère les statistiques globales des récompenses
     */
    public function getOverallStats(): array
    {
        return [
            'totalApprobations' => $this->demandeRepo->count(['statut' => 'approuvee']),
            'totalRefus' => $this->demandeRepo->count(['statut' => 'rejetee']),
            'totalEnAttente' => $this->demandeRepo->count(['statut' => 'en_attente']),
            'totalDemandes' => $this->demandeRepo->count([]),
            'tauxApprobation' => $this->getTaxeApprobation(),
            'depenseTotale' => $this->getDepenseTotale(),
            'budgetRestant' => $this->getBudgetRestant(),
        ];
    }

    /**
     * Taux d'approbation en pourcentage
     */
    public function getTaxeApprobation(): float
    {
        $total = $this->demandeRepo->count([]);
        if ($total === 0) {
            return 0;
        }
        $approved = $this->demandeRepo->count(['statut' => 'approuvee']);
        return round(($approved / $total) * 100, 2);
    }

    /**
     * Dépense totale des récompenses accordées
     * Note: À implémenter avec structure de coûts si disponible
     */
    public function getDepenseTotale(): float
    {
        // TODO: Implémenter si colonne coût disponible sur Recompense ou Depense entity
        return 0;
    }

    /**
     * Budget restant si budget défini
     */
    public function getBudgetRestant(): ?float
    {
        // À implémenter selon votre structure Budget
        return null;
    }

    /**
     * Répartition par type de récompense
     * @return array [type => count]
     */
    public function getRecompensesByType(): array
    {
        $data = [];
        $demands = $this->demandeRepo->findAll();
        
        foreach ($demands as $demand) {
            $type = $demand->getTypeRecompense() ?? 'Non spécifié';
            $data[$type] = ($data[$type] ?? 0) + 1;
        }
        
        return $data;
    }

    /**
     * Évolution des demandes par mois (derniers 6 mois)
     * @return array [month => count]
     */
    public function getDemandsTrendSixMonths(): array
    {
        $data = [];
        $now = new \DateTime();
        
        // Initialiser les 6 derniers mois
        for ($i = 5; $i >= 0; $i--) {
            $date = (clone $now)->modify("-$i months");
            $month = $date->format('Y-m');
            $data[$month] = 0;
        }
        
        // Compter les demandes par mois
        $demands = $this->demandeRepo->findAll();
        foreach ($demands as $demand) {
            if ($demand->getCreatedAt()) {
                $month = $demand->getCreatedAt()->format('Y-m');
                if (isset($data[$month])) {
                    $data[$month]++;
                }
            }
        }
        
        return $data;
    }

    /**
     * Statut des demandes (en attente, approuvée, refusée)
     */
    public function getDemandStatusDistribution(): array
    {
        return [
            'en_attente' => $this->demandeRepo->count(['statut' => 'en_attente']),
            'approuvee' => $this->demandeRepo->count(['statut' => 'approuvee']),
            'rejetee' => $this->demandeRepo->count(['statut' => 'rejetee']),
        ];
    }

    /**
     * Top 10 jours avec plus de demandes
     */
    public function getTopDemandDays(int $limit = 10): array
    {
        $data = [];
        $demands = $this->demandeRepo->findAll();
        
        foreach ($demands as $demand) {
            if ($demand->getCreatedAt()) {
                $day = $demand->getCreatedAt()->format('Y-m-d');
                $data[$day] = ($data[$day] ?? 0) + 1;
            }
        }
        
        // Trier par nombre de demandes (décroissant)
        arsort($data);
        
        return array_slice($data, 0, $limit);
    }

    /**
     * Dépense moyenne par récompense approuvée
     */
    public function getAverageCostPerReward(): float
    {
        // TODO: Implémenter si données de coût disponibles
        return 0;
    }

    /**
     * Motifs les plus courants
     */
    public function getTopMotifs(int $limit = 5): array
    {
        $data = [];
        $demands = $this->demandeRepo->findAll();
        
        foreach ($demands as $demand) {
            $motif = $demand->getMotif() ?? 'Non spécifié';
            // Limiter à 50 caractères pour catégoriser
            $motif = substr($motif, 0, 50);
            $data[$motif] = ($data[$motif] ?? 0) + 1;
        }
        
        // Trier par nombre d'occurrences
        arsort($data);
        
        return array_slice($data, 0, $limit);
    }

    /**
     * Données pour le graphique de dépenses par mois
     * Note: À implémenter avec structure de coûts si disponible
     */
    public function getExpensesTrendSixMonths(): array
    {
        $data = [];
        $now = new \DateTime();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = (clone $now)->modify("-$i months");
            $month = $date->format('Y-m');
            $data[$month] = 0; // TODO: Implémenter avec calcul de coûts réels
        }
        
        return $data;
    }

    /**
     * Score de performance des récompenses (AI si disponible)
     */
    public function getPerformanceScore(): array
    {
        $approved = $this->demandeRepo->count(['statut' => 'approuvee']);
        $refused = $this->demandeRepo->count(['statut' => 'rejetee']);
        $total = $approved + $refused;
        
        if ($total === 0) {
            return [
                'efficiency' => 0,
                'quality' => 0,
                'trustScore' => 0,
            ];
        }
        
        return [
            'efficiency' => round(($approved / max($total, 1)) * 100, 2),
            'quality' => min(100, round(($approved / max($total, 1)) * 100, 2) + 20),
            'trustScore' => round(($approved / max($total, 1)) * 100, 2),
        ];
    }

    /**
     * Comparaison mois actuel vs mois précédent
     */
    public function getMonthComparison(): array
    {
        $now = new \DateTime();
        $currentMonth = $now->format('Y-m');
        $previousMonth = (clone $now)->modify('-1 month')->format('Y-m');
        
        $demands = $this->demandeRepo->findAll();
        
        $currentCount = 0;
        $previousCount = 0;
        
        foreach ($demands as $demand) {
            if ($demand->getCreatedAt()) {
                $month = $demand->getCreatedAt()->format('Y-m');
                if ($month === $currentMonth) {
                    $currentCount++;
                } elseif ($month === $previousMonth) {
                    $previousCount++;
                }
            }
        }
        
        $evolution = $previousCount > 0 
            ? round((($currentCount - $previousCount) / $previousCount) * 100, 2)
            : 0;
        
        return [
            'currentMonth' => $currentCount,
            'previousMonth' => $previousCount,
            'evolution' => $evolution,
            'evolutionPercentage' => $evolution . '%',
        ];
    }
}
