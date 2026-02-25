<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Player;
use App\Entity\Budget;
use App\Entity\Depense;
use App\Service\PlayerScoreService;
use App\Repository\TeamInvitationRepository;
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
        PlayerScoreService $scoreService
    ): Response
    {
        $user = $this->getUser();
        
        // Check if user is a Player
        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to access this page.');
            return $this->redirectToRoute('home');
        }

        $currentTeams = $user->getTeams();
        $availableTeams = $em->getRepository(Team::class)->findAll();
        
        // Remove teams the player is already in from available teams
        $availableTeams = array_filter($availableTeams, function($team) use ($currentTeams) {
            return !$currentTeams->contains($team);
        });
        
        // Reindex array for Twig
        $availableTeams = array_values($availableTeams);

        // Get pending invitations for this player
        $pendingInvitations = $invitationRepo->findPendingInvitationForPlayer($user);

        // Get budgets and depenses data for charts
        $budgets = $em->getRepository(Budget::class)->findAll();
        $depenses = $em->getRepository(Depense::class)->findAll();

        // Get player score and stats
        $playerStats = $scoreService->getPlayerStats($user);

        return $this->render('player/dashboard.html.twig', [
            'player' => $user,
            'currentTeam' => $currentTeams->first() ?: null,  // For backward compatibility with template
            'currentTeams' => $currentTeams,
            'availableTeams' => $availableTeams,
            'pendingInvitations' => $pendingInvitations,
            'budgets' => $budgets,
            'depenses' => $depenses,
            'playerStats' => $playerStats,
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
}
