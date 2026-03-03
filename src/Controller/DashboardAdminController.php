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
        $userRepository = $em->getRepository(\App\Entity\User::class);
        
        // Fetch current data for dashboard
        $totalMatches = $gameRepository->count([]);
        $totalTournaments = $tournamentRepository->count([]);
        $totalTeams = $teamRepository->count([]);
        $totalPlayers = $playerRepository->count([]);
        $totalUsers = $userRepository->count([]);
        
        // Calculate month-over-month statistics
        $thisMonthStart = new \DateTime('first day of this month');
        $thisMonthEnd = new \DateTime('last day of this month');
        $lastMonthStart = (clone $thisMonthStart)->modify('-1 month');
        $lastMonthEnd = (clone $thisMonthStart)->modify('-1 day');
        
        // Get matches this month vs last month
        $matchesThisMonth = $gameRepository->createQueryBuilder('g')
            ->where('g.matchdate >= :start')
            ->andWhere('g.matchdate <= :end')
            ->setParameter('start', $thisMonthStart)
            ->setParameter('end', $thisMonthEnd)
            ->getQuery()
            ->getResult();
        $matchesThisMonthCount = count($matchesThisMonth);
        
        $matchesLastMonth = $gameRepository->createQueryBuilder('g')
            ->where('g.matchdate >= :start')
            ->andWhere('g.matchdate <= :end')
            ->setParameter('start', $lastMonthStart)
            ->setParameter('end', $lastMonthEnd)
            ->getQuery()
            ->getResult();
        $matchesLastMonthCount = count($matchesLastMonth);
        $matchesChange = $matchesLastMonthCount > 0 ? round((($matchesThisMonthCount - $matchesLastMonthCount) / $matchesLastMonthCount) * 100) : 0;
        
        // Get tournaments this month vs last month
        $tournamentsThisMonth = $tournamentRepository->createQueryBuilder('t')
            ->where('t.startDate >= :start')
            ->andWhere('t.startDate <= :end')
            ->setParameter('start', $thisMonthStart)
            ->setParameter('end', $thisMonthEnd)
            ->getQuery()
            ->getResult();
        $tournamentsThisMonthCount = count($tournamentsThisMonth);
        
        $tournamentsLastMonth = $tournamentRepository->createQueryBuilder('t')
            ->where('t.startDate >= :start')
            ->andWhere('t.startDate <= :end')
            ->setParameter('start', $lastMonthStart)
            ->setParameter('end', $lastMonthEnd)
            ->getQuery()
            ->getResult();
        $tournamentsLastMonthCount = count($tournamentsLastMonth);
        $tournamentsChange = $tournamentsLastMonthCount > 0 ? round((($tournamentsThisMonthCount - $tournamentsLastMonthCount) / $tournamentsLastMonthCount) * 100) : 0;
        
        // Get teams this month vs last month
        $teamsThisMonth = $teamRepository->createQueryBuilder('t')
            ->where('t.createdAt >= :start')
            ->andWhere('t.createdAt <= :end')
            ->setParameter('start', $thisMonthStart)
            ->setParameter('end', $thisMonthEnd)
            ->getQuery()
            ->getResult();
        $teamsThisMonthCount = count($teamsThisMonth);
        
        $teamsLastMonth = $teamRepository->createQueryBuilder('t')
            ->where('t.createdAt >= :start')
            ->andWhere('t.createdAt <= :end')
            ->setParameter('start', $lastMonthStart)
            ->setParameter('end', $lastMonthEnd)
            ->getQuery()
            ->getResult();
        $teamsLastMonthCount = count($teamsLastMonth);
        $teamsChange = $teamsLastMonthCount > 0 ? round((($teamsThisMonthCount - $teamsLastMonthCount) / $teamsLastMonthCount) * 100) : 0;
        
        // Get players this month (new registrations)
        $playersThisMonth = $playerRepository->createQueryBuilder('p')
            ->where('p.createdAt >= :start')
            ->andWhere('p.createdAt <= :end')
            ->setParameter('start', $thisMonthStart)
            ->setParameter('end', $thisMonthEnd)
            ->getQuery()
            ->getResult();
        $playersThisMonthCount = count($playersThisMonth);
        
        $playersLastMonth = $playerRepository->createQueryBuilder('p')
            ->where('p.createdAt >= :start')
            ->andWhere('p.createdAt <= :end')
            ->setParameter('start', $lastMonthStart)
            ->setParameter('end', $lastMonthEnd)
            ->getQuery()
            ->getResult();
        $playersLastMonthCount = count($playersLastMonth);
        $playersChange = $playersLastMonthCount > 0 ? round((($playersThisMonthCount - $playersLastMonthCount) / $playersLastMonthCount) * 100) : 0;
        
        // Get recent matches (last 6)
        $matches = $gameRepository->findBy([], ['matchdate' => 'DESC'], 6);
        
        // Get recent tournaments (last 6)
        $tournaments = $tournamentRepository->findBy([], ['startDate' => 'DESC'], 6);
        
        // Get recent users (last 5 registrations)
        $recentUsers = $userRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('dashboard/admin.html.twig', [
            'totalMatches' => $totalMatches,
            'totalTournaments' => $totalTournaments,
            'totalTeams' => $totalTeams,
            'totalPlayers' => $totalPlayers,
            'totalUsers' => $totalUsers,
            'matches' => $matches,
            'tournaments' => $tournaments,
            'recentUsers' => $recentUsers,
            'total_pending_expenses' => $totalPendingExpenses,
            'total_pending_amount' => $totalPendingAmount,
            'pending_expenses' => $pendingExpenses,
            'matchesChange' => $matchesChange,
            'tournamentsChange' => $tournamentsChange,
            'teamsChange' => $teamsChange,
            'playersChange' => $playersChange,
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
