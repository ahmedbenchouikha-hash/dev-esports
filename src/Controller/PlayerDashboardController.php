<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Player;
use App\Entity\Payment;
use App\Entity\Budget;
use App\Entity\Depense;
use App\Repository\TeamInvitationRepository;
use App\Repository\PaymentRepository;
use App\Repository\DepenseRepository;
use App\Repository\BudgetRepository;
use App\Service\PlayerScoreService;
use App\Service\BudgetAlertService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/player')]
#[IsGranted('ROLE_USER')]
class PlayerDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'player_dashboard', methods: ['GET'])]
    public function dashboard(
        EntityManagerInterface $em,
        TeamInvitationRepository $invitationRepo,
        PlayerScoreService $scoreService,
        BudgetAlertService $budgetAlertService,
        DepenseRepository $depenseRepo,
        BudgetRepository $budgetRepo
    ): Response
    {
        $user = $this->getUser();
        
        // Check if user is a Player
        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to access this page.');
            return $this->redirectToRoute('home');
        }

        $currentTeams = $user->getTeams();
        
        // OPTIMIZATION: Get only teams the player is NOT in
        // Instead of loading all teams and filtering, query for available teams directly
        $playerTeamIds = array_map(fn($t) => $t->getId(), $currentTeams->toArray());
        $availableTeams = empty($playerTeamIds) 
            ? $em->getRepository(Team::class)->findAll()
            : $em->getRepository(Team::class)->createQueryBuilder('t')
                ->where('t.id NOT IN (:ids)')
                ->setParameter('ids', $playerTeamIds)
                ->getQuery()
                ->getResult();
        
        // No need to reindex - query builder returns array

        // Get pending invitations for this player
        $pendingInvitations = $invitationRepo->findPendingInvitationForPlayer($user);

        // Get player statistics
        $playerStats = $scoreService->getPlayerStats($user);

        $budgets = [];
        $depenses = [];
        $managedTeams = [];

        if ($this->isGranted('ROLE_MANAGER') || $this->isGranted('ROLE_ADMIN')) {
            // OPTIMIZATION: Query for teams where user is manager directly instead of looping
            if ($this->isGranted('ROLE_MANAGER')) {
                $managedTeams = $em->getRepository(Team::class)->createQueryBuilder('t')
                    ->where('t.creator = :manager')
                    ->setParameter('manager', $user)
                    ->getQuery()
                    ->getResult();
                
                // Check budgets and alerts for teams where user is a manager
                foreach ($managedTeams as $team) {
                    $budgetAlertService->checkBudgetAndAlert($team);
                }
            }

            // OPTIMIZATION: Only fetch budgets and expenses for the user's managed teams
            // or all if admin (avoid loading thousands of records)
            if ($this->isGranted('ROLE_ADMIN')) {
                $budgets = $budgetRepo->findAll();
                $depenses = $depenseRepo->findAll();
            } elseif (!empty($managedTeams)) {
                // Managers only see their team budgets
                $teamIds = array_map(fn($t) => $t->getId(), $managedTeams);
                $budgets = $budgetRepo->createQueryBuilder('b')
                    ->leftJoin('b.team', 't')
                    ->andWhere('t.id IN (:teams)')
                    ->setParameter('teams', $teamIds)
                    ->getQuery()
                    ->getResult();
                
                $depenses = $depenseRepo->createQueryBuilder('d')
                    ->leftJoin('d.budget', 'b')
                    ->leftJoin('b.team', 't')
                    ->andWhere('t.id IN (:teams)')
                    ->setParameter('teams', $teamIds)
                    ->getQuery()
                    ->getResult();
            }
        }

        // Manager financial data (aggregate from their teams)
        $managerFinancialSummary = null;
        $managerDepenseLineLabels = [];
        $managerDepenseLineDepenses = [];
        $managerDepenseLineBudgets = [];
        $managerDepensePieLabels = [];
        $managerDepensePieValues = [];
        $managerDepenseBudgetLineLabels = [];
        $managerDepenseBudgetLineDepenses = [];
        $managerDepenseBudgetLineBudgets = [];

        return $this->render('player/dashboard.html.twig', [
            'player' => $user,
            'currentTeam' => $currentTeams->first() ?: null,
            'currentTeams' => $currentTeams,
            'availableTeams' => $availableTeams,
            'pendingInvitations' => $pendingInvitations,
            'playerStats' => $playerStats,
            'budgets' => $budgets,
            'depenses' => $depenses,
            'managerFinancialSummary' => $managerFinancialSummary,
            'managerDepenseLineLabels' => $managerDepenseLineLabels,
            'managerDepenseLineDepenses' => $managerDepenseLineDepenses,
            'managerDepenseLineBudgets' => $managerDepenseLineBudgets,
            'managerDepensePieLabels' => $managerDepensePieLabels,
            'managerDepensePieValues' => $managerDepensePieValues,
            'managerDepenseBudgetLineLabels' => $managerDepenseBudgetLineLabels,
            'managerDepenseBudgetLineDepenses' => $managerDepenseBudgetLineDepenses,
            'managerDepenseBudgetLineBudgets' => $managerDepenseBudgetLineBudgets,
        ]);
    }

    #[Route('/join-team/{id}', name: 'player_join_team', methods: ['POST'])]
    public function joinTeam(Team $team, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to join a team.');
            return $this->redirectToRoute('home');
        }

        // Verify CSRF token
        if (!$this->isCsrfTokenValid('join_team_' . $team->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('player_dashboard');
        }

        try {
            // Check if player is already in the team
            if ($team->getPlayers()->contains($user)) {
                $this->addFlash('warning', 'You are already a member of this team.');
                return $this->redirectToRoute('player_dashboard');
            }

            // Check if team is full
            if ($team->getPlayers()->count() >= 5) {
                $this->addFlash('error', '❌ This team is full (maximum 5 players). You cannot join.');
                return $this->redirectToRoute('player_dashboard');
            }

            $user->addTeam($team);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', '✅ You have successfully joined ' . $team->getName() . '!');
        } catch (\Exception $e) {
            $this->addFlash('error', '❌ Error joining team: ' . $e->getMessage());
            \error_log('Join team error: ' . $e->getTraceAsString());
        }
        
        return $this->redirectToRoute('player_dashboard');
    }

    #[Route('/leave-team', name: 'player_leave_team', methods: ['POST'])]
    public function leaveTeam(EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to leave a team.');
            return $this->redirectToRoute('home');
        }

        if (!$this->isCsrfTokenValid('leave_team', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('player_dashboard');
        }

        $teamId = $request->request->get('team_id');
        if ($teamId) {
            // Leave specific team
            $team = $em->getRepository(Team::class)->find($teamId);
            if ($team && $user->getTeams()->contains($team)) {
                $teamName = $team->getName();
                $user->removeTeam($team);
                $em->flush();
                $this->addFlash('success', 'You have left ' . $teamName . '.');
            }
        } else {
            // For backward compatibility: leave first team
            $teams = $user->getTeams();
            if ($teams->count() > 0) {
                $team = $teams->first();
                $teamName = $team->getName();
                $user->removeTeam($team);
                $em->flush();
                $this->addFlash('success', 'You have left ' . $teamName . '.');
            } else {
                $this->addFlash('warning', 'You are not part of any team.');
            }
        }
        
        return $this->redirectToRoute('player_dashboard');
    }

    #[Route('/my-tickets', name: 'player_tickets', methods: ['GET'])]
    public function myTickets(PaymentRepository $paymentRepo): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to access this page.');
            return $this->redirectToRoute('home');
        }

        $payments = $paymentRepo->findBy(
            ['player' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('player/my_tickets.html.twig', [
            'payments' => $payments,
            'player' => $user,
        ]);
    }

    #[Route('/qr-code/{id}', name: 'player_qr_detail', methods: ['GET'])]
    public function viewQrCode(Payment $payment): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to access this page.');
            return $this->redirectToRoute('home');
        }

        if ($payment->getPlayer() !== $user) {
            $this->addFlash('error', 'You don\'t have access to this payment.');
            return $this->redirectToRoute('player_tickets');
        }

        return $this->render('player/qr_detail.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/invitations', name: 'player_invitations', methods: ['GET'])]
    public function invitations(TeamInvitationRepository $invitationRepo): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to access this page.');
            return $this->redirectToRoute('home');
        }

        $pendingInvitations = $invitationRepo->findPendingInvitationForPlayer($user);

        return $this->render('player/invitations.html.twig', [
            'pendingInvitations' => $pendingInvitations,
        ]);
    }
}


