<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'admin_')]
class DashboardAdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function index(
        DepenseRepository $depenseRepository,
        EntityManagerInterface $em
    ): Response
    {
        $pendingExpenses = $depenseRepository->findBy(
            ['statut' => ['en_attente', 'en attente']],
            ['date_creation' => 'DESC']
        );
        
        $totalPendingExpenses = count($pendingExpenses);
        $totalPendingAmount = array_sum(array_map(fn($d) => $d->getMontant(), $pendingExpenses));
        
        // Get repositories from entity manager
        $gameRepository = $em->getRepository(\App\Entity\Game::class);
        $tournamentRepository = $em->getRepository(\App\Entity\Tournament::class);
        $teamRepository = $em->getRepository(\App\Entity\Team::class);
        $playerRepository = $em->getRepository(\App\Entity\Player::class);
        
        // Fetch data for dashboard
        $totalMatches = $gameRepository->count([]);
        $totalTournaments = $tournamentRepository->count([]);
        $totalTeams = $teamRepository->count([]);
        $totalPlayers = $playerRepository->count([]);
        
        // Get recent matches (last 6)
        $matches = $gameRepository->findBy([], ['matchdate' => 'DESC'], 6);
        
        // Get recent tournaments (last 6)
        $tournaments = $tournamentRepository->findBy([], ['startDate' => 'DESC'], 6);

        return $this->render('dashboard/admin.html.twig', [
            'totalMatches' => $totalMatches,
            'totalTournaments' => $totalTournaments,
            'totalTeams' => $totalTeams,
            'totalPlayers' => $totalPlayers,
            'matches' => $matches,
            'tournaments' => $tournaments,
            'total_pending_expenses' => $totalPendingExpenses,
            'total_pending_amount' => $totalPendingAmount,
            'pending_expenses' => $pendingExpenses,
        ]);
    }

    #[Route('/depense/{id}/approuver', name: 'approve_expense', methods: ['POST'])]
    public function approveExpense(Depense $depense, EntityManagerInterface $em): Response
    {
        $team = $depense->getTeam();
        if ($team && $team->getBudget()) {
            $budget = $team->getBudget();
            $montantUtilise = $budget->getMontantUtilise() + $depense->getMontant();
            $budget->setMontantUtilise($montantUtilise);
            
            $em->persist($budget);
        }

        $depense->setStatut('validée');
        $em->persist($depense);
        $em->flush();

        $this->addFlash('success', '✅ Expense #' . $depense->getId() . ' approved successfully!');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/depense/{id}/refuser', name: 'refuse_expense', methods: ['POST'])]
    public function refuseExpense(Depense $depense, EntityManagerInterface $em): Response
    {
        $depense->setStatut('refusée');
        $em->persist($depense);
        $em->flush();

        $this->addFlash('warning', '⚠️ Expense #' . $depense->getId() . ' has been refused.');
        return $this->redirectToRoute('admin_dashboard');
    }
}
