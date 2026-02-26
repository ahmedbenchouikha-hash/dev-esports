<?php

namespace App\Controller;

use App\Repository\BudgetRepository;
use App\Repository\DepenseRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/charts', name: 'api_charts_')]
class ChartApiController extends AbstractController
{
    #[Route('/depenses-by-category', name: 'depenses_by_category', methods: ['GET'])]
    public function depensesByCategory(
        DepenseRepository $depenseRepo,
        SerializerInterface $serializer
    ): JsonResponse {
        $depenses = $depenseRepo->findAll();
        
        // Grouper par catégorie (validées seulement)
        $categoryMap = [];
        $total = 0;
        
        foreach ($depenses as $depense) {
            if ($depense->getStatut() !== 'validée') {
                continue;
            }
            
            $category = $depense->getCategorie() ?? 'Non catégorisée';
            $amount = $depense->getMontant() ?? 0;
            
            if (!isset($categoryMap[$category])) {
                $categoryMap[$category] = 0;
            }
            
            $categoryMap[$category] += $amount;
            $total += $amount;
        }
        
        // Formater réponse
        $result = [
            'labels' => array_keys($categoryMap),
            'data' => array_values($categoryMap),
            'total' => $total,
            'count' => count($categoryMap),
            'percentages' => array_map(
                fn($val) => $total > 0 ? round(($val / $total) * 100, 2) : 0,
                array_values($categoryMap)
            )
        ];
        
        return $this->json($result);
    }

    #[Route('/teams-budget-comparison', name: 'teams_budget_comparison', methods: ['GET'])]
    public function teamsBudgetComparison(BudgetRepository $budgetRepo): JsonResponse
    {
        $budgets = $budgetRepo->findAll();
        
        $result = [
            'teams' => [],
            'allocated' => [],
            'used' => [],
            'remaining' => []
        ];
        
        foreach ($budgets as $budget) {
            $teamName = $budget->getTeam()?->getName() ?? 'Unknown';
            $allocated = $budget->getMontantAlloue() ?? 0;
            
            // Calculer utilisé à partir des dépenses validées
            $used = 0;
            foreach ($budget->getTeam()?->getDepenses() ?? [] as $depense) {
                if ($depense->getStatut() === 'validée') {
                    $used += $depense->getMontant() ?? 0;
                }
            }
            
            $result['teams'][] = $teamName;
            $result['allocated'][] = $allocated;
            $result['used'][] = $used;
            $result['remaining'][] = max(0, $allocated - $used);
        }
        
        return $this->json($result);
    }

    #[Route('/budget-evolution/{teamId?}', name: 'budget_evolution', methods: ['GET'])]
    public function budgetEvolution(
        ?int $teamId,
        DepenseRepository $depenseRepo,
        BudgetRepository $budgetRepo,
        TeamRepository $teamRepo
    ): JsonResponse {
        // Récupérer les dépenses
        if ($teamId) {
            $team = $teamRepo->find($teamId);
            $depenses = $depenseRepo->findBy(['team' => $team]);
        } else {
            $depenses = $depenseRepo->findAll();
        }
        
        // Trier par date
        usort($depenses, fn($a, $b) => 
            $a->getDateCreation() <=> $b->getDateCreation()
        );
        
        // Construire série chronologique
        $dates = [];
        $cumulativeAmounts = [];
        $runningTotal = 0;
        
        foreach ($depenses as $depense) {
            if ($depense->getStatut() !== 'validée') {
                continue;
            }
            
            $date = $depense->getDateCreation()->format('Y-m-d');
            $runningTotal += $depense->getMontant() ?? 0;
            
            $dates[] = $date;
            $cumulativeAmounts[] = round($runningTotal, 2);
        }
        
        // Récupérer budget alloué
        $budgetAllocated = 0;
        if ($teamId && $team = $teamRepo->find($teamId)) {
            $budget = $team->getBudget();
            $budgetAllocated = $budget?->getMontantAlloue() ?? 0;
        }
        
        $result = [
            'dates' => $dates,
            'cumulative' => $cumulativeAmounts,
            'remaining' => array_map(
                fn($cum) => max(0, $budgetAllocated - $cum),
                $cumulativeAmounts
            ),
            'budget_allocated' => $budgetAllocated,
            'total_spent' => $runningTotal ?? 0
        ];
        
        return $this->json($result);
    }

    #[Route('/expense-stats', name: 'expense_stats', methods: ['GET'])]
    public function expenseStatistics(DepenseRepository $depenseRepo): JsonResponse
    {
        $depenses = $depenseRepo->findAll();
        
        $categoryStats = [];
        
        foreach ($depenses as $depense) {
            $category = $depense->getCategorie() ?? 'Non catégorisée';
            
            if (!isset($categoryStats[$category])) {
                $categoryStats[$category] = [
                    'count' => 0,
                    'validated' => 0,
                    'pending' => 0,
                    'rejected' => 0,
                    'total_amount' => 0
                ];
            }
            
            $categoryStats[$category]['count']++;
            $categoryStats[$category]['total_amount'] += $depense->getMontant() ?? 0;
            
            match($depense->getStatut()) {
                'validée' => $categoryStats[$category]['validated']++,
                'brouillon' => $categoryStats[$category]['pending']++,
                'rejetée' => $categoryStats[$category]['rejected']++,
                default => null
            };
        }
        
        $result = [
            'categories' => array_keys($categoryStats),
            'stats' => array_map(fn($stat) => [
                'count' => $stat['count'],
                'validated' => $stat['validated'],
                'pending' => $stat['pending'],
                'rejected' => $stat['rejected'],
                'total_amount' => round($stat['total_amount'], 2)
            ], $categoryStats)
        ];
        
        return $this->json($result);
    }

    #[Route('/summary', name: 'summary', methods: ['GET'])]
    public function summary(
        BudgetRepository $budgetRepo,
        DepenseRepository $depenseRepo
    ): JsonResponse {
        $budgets = $budgetRepo->findAll();
        $depenses = $depenseRepo->findAll();
        
        $totalBudget = 0;
        $totalSpent = 0;
        $teamCount = count($budgets);
        
        foreach ($budgets as $budget) {
            $totalBudget += $budget->getMontantAlloue() ?? 0;
        }
        
        foreach ($depenses as $depense) {
            if ($depense->getStatut() === 'validée') {
                $totalSpent += $depense->getMontant() ?? 0;
            }
        }
        
        $result = [
            'total_budget' => round($totalBudget, 2),
            'total_spent' => round($totalSpent, 2),
            'total_remaining' => round(max(0, $totalBudget - $totalSpent), 2),
            'utilization_percent' => $totalBudget > 0 ? 
                round(($totalSpent / $totalBudget) * 100, 2) : 0,
            'team_count' => $teamCount,
            'expense_count' => count($depenses),
            'expense_validated' => count(array_filter($depenses, 
                fn($d) => $d->getStatut() === 'validée'))
        ];
        
        return $this->json($result);
    }
}
