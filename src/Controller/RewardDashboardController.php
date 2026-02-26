<?php

namespace App\Controller;

use App\Service\RewardAnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Dashboard pour l'analyse avancée des récompenses
 * Affiche graphiques, statistiques et KPIs
 */
#[Route('/admin/dashboard/rewards', name: 'reward_dashboard_')]
class RewardDashboardController extends AbstractController
{
    public function __construct(private RewardAnalyticsService $analyticsService) {}

    /**
     * Page principale du dashboard des récompenses
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $stats = $this->analyticsService->getOverallStats();
        $statusDistribution = $this->analyticsService->getDemandStatusDistribution();
        $recompensesByType = $this->analyticsService->getRecompensesByType();
        $demandsTrend = $this->analyticsService->getDemandsTrendSixMonths();
        $monthComparison = $this->analyticsService->getMonthComparison();
        $performanceScore = $this->analyticsService->getPerformanceScore();
        $expensesTrend = $this->analyticsService->getExpensesTrendSixMonths();
        $topMotifs = $this->analyticsService->getTopMotifs();
        $avgCost = $this->analyticsService->getAverageCostPerReward();

        return $this->render('reward/dashboard.html.twig', [
            'stats' => $stats,
            'statusDistribution' => $statusDistribution,
            'recompensesByType' => $recompensesByType,
            'demandsTrend' => $demandsTrend,
            'monthComparison' => $monthComparison,
            'performanceScore' => $performanceScore,
            'expensesTrend' => $expensesTrend,
            'topMotifs' => $topMotifs,
            'avgCost' => $avgCost,
        ]);
    }

    /**
     * API: Données pour graphique statut des demandes
     */
    #[Route('/api/status-distribution', name: 'api_status', methods: ['GET'])]
    public function apiStatusDistribution(): JsonResponse
    {
        $data = $this->analyticsService->getDemandStatusDistribution();
        
        return $this->json([
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => 'Nombre de demandes',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#FFC107',  // en_attente - yellow
                        '#28A745',  // approuvee - green
                        '#DC3545',  // refusee - red
                        '#17A2B8',  // revisee - cyan
                    ],
                    'borderColor' => [
                        '#FFA500',
                        '#1E7E34',
                        '#BB2D3B',
                        '#0A6477',
                    ],
                    'borderWidth' => 2,
                ]
            ]
        ]);
    }

    /**
     * API: Récompenses par type
     */
    #[Route('/api/by-type', name: 'api_by_type', methods: ['GET'])]
    public function apiByType(): JsonResponse
    {
        $data = $this->analyticsService->getRecompensesByType();
        
        return $this->json([
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => 'Nombre de demandes par type',
                    'data' => array_values($data),
                    'backgroundColor' => $this->generateColors(count($data)),
                    'borderColor' => $this->generateDarkColors(count($data)),
                    'borderWidth' => 2,
                ]
            ]
        ]);
    }

    /**
     * API: Tendance des demandes (6 mois)
     */
    #[Route('/api/demands-trend', name: 'api_demands_trend', methods: ['GET'])]
    public function apiDemandsTrend(): JsonResponse
    {
        $data = $this->analyticsService->getDemandsTrendSixMonths();
        
        return $this->json([
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => 'Demandes par mois',
                    'data' => array_values($data),
                    'borderColor' => '#007BFF',
                    'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true,
                ]
            ]
        ]);
    }

    /**
     * API: Dépenses par mois (6 mois)
     */
    #[Route('/api/expenses-trend', name: 'api_expenses_trend', methods: ['GET'])]
    public function apiExpensesTrend(): JsonResponse
    {
        $data = $this->analyticsService->getExpensesTrendSixMonths();
        
        return $this->json([
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => 'Dépenses (€)',
                    'data' => array_values($data),
                    'borderColor' => '#28A745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true,
                ]
            ]
        ]);
    }

    /**
     * API: Stats globales
     */
    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function apiStats(): JsonResponse
    {
        $stats = $this->analyticsService->getOverallStats();
        
        return $this->json($stats);
    }

    /**
     * Génère une palette de couleurs pour graphiques
     */
    private function generateColors(int $count): array
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
            '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B88B', '#ABEBC6',
        ];
        
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }
        
        return $result;
    }

    /**
     * Génère des couleurs sombres pour les bordures
     */
    private function generateDarkColors(int $count): array
    {
        $colors = [
            '#C41E3A', '#00A3A3', '#0099CC', '#FF6347', '#4A8C6B',
            '#B8860B', '#9932CC', '#004E89', '#CD853F', '#2F8659',
        ];
        
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }
        
        return $result;
    }
}
