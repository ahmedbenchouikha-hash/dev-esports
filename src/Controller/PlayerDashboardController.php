<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Player;
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
    public function dashboard(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Check if user is a Player
        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to access this page.');
            return $this->redirectToRoute('home');
        }

        $currentTeam = $user->getTeam();
        $availableTeams = $em->getRepository(Team::class)->findAll();
        
        // Remove current team from available teams
        $availableTeams = array_filter($availableTeams, function($team) use ($currentTeam) {
            return $team !== $currentTeam;
        });

        return $this->render('player/dashboard.html.twig', [
            'player' => $user,
            'currentTeam' => $currentTeam,
            'availableTeams' => $availableTeams,
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

        $user->setTeam($team);
        $em->flush();

        $this->addFlash('success', 'You have successfully joined ' . $team->getName() . '!');
        
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

        $teamName = $user->getTeam()?->getName() ?? 'Unknown Team';
        $user->setTeam(null);
        $em->flush();

        $this->addFlash('success', 'You have left ' . $teamName . '.');
        
        return $this->redirectToRoute('player_dashboard');
    }
}
