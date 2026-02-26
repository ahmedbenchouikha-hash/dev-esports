<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Repository\TournamentRepository;
use App\Repository\DepenseRepository;
use App\Entity\Depense;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardAdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        GameRepository $gameRepository,
        TournamentRepository $tournamentRepository,
        TeamRepository $teamRepository,
        PlayerRepository $playerRepository,
        DepenseRepository $depenseRepository
    ): Response {
        // Get pending expenses (not validated yet) - with correct status values
        $pendingExpenses = $depenseRepository->findBy(
            [],
            ['date_creation' => 'DESC']
        );
        
        // Filter for pending status (en_attente or en attente)
        $filteredPending = array_filter($pendingExpenses, function($expense) {
            $statut = $expense->getStatut();
            return $statut === 'en_attente' || $statut === 'en attente';
        });
        
        return $this->render('admin/dashboard.html.twig', [
            'totalMatches' => $gameRepository->count([]),
            'totalTournaments' => $tournamentRepository->count([]),
            'totalTeams' => $teamRepository->count([]),
            'totalPlayers' => $playerRepository->count([]),
            'matches' => $gameRepository->findBy([], ['matchdate' => 'DESC'], 5),
            'tournaments' => $tournamentRepository->findBy([], ['startDate' => 'DESC'], 5),
            'pendingExpenses' => $filteredPending,
            'totalPendingExpenses' => count($filteredPending),
        ]);
    }

    #[Route('/admin/depense/{id}/approuver', name: 'admin_depense_approve', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function approveExpense(Depense $depense, EntityManagerInterface $em): Response
    {
        $depense->setStatut('validée');
        
        // Update budget usage
        $team = $depense->getTeam();
        if ($team) {
            $budget = $team->getBudget();
            if ($budget) {
                $currentUsed = $budget->getMontantUtilise();
                $budget->setMontantUtilise($currentUsed + $depense->getMontant());
                $em->persist($budget);
            }
        }
        
        $em->persist($depense);
        $em->flush();
        
        $this->addFlash('success', '✅ Dépense "' . $depense->getTitre() . '" approuvée avec succès!');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/depense/{id}/refuser', name: 'admin_depense_refuse', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function refuseExpense(Depense $depense, EntityManagerInterface $em): Response
    {
        $depense->setStatut('refusée');
        $em->persist($depense);
        $em->flush();
        
        $this->addFlash('warning', '❌ Dépense "' . $depense->getTitre() . '" refusée!');
        return $this->redirectToRoute('admin_dashboard');
    }
}
