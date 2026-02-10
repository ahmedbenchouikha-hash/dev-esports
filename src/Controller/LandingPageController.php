<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\TournamentRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LandingPageController extends AbstractController
{
    #[Route('/landing', name: 'landing_page')]
    public function index(
        GameRepository $gameRepository,
        TournamentRepository $tournamentRepository,
        TeamRepository $teamRepository
    ): Response {
        // Get all matches
        $allMatches = $gameRepository->findAll();
        
        // Separate matches by status
        $upcomingMatches = $gameRepository->findByStatus('pending');
        $ongoingMatches = $gameRepository->findByStatus('ongoing');
        $finishedMatches = $gameRepository->findByStatus('finished');
        
        // Get recent finished matches (limit to 6)
        $recentFinishedMatches = array_slice($finishedMatches, 0, 6);
        
        // Get upcoming matches (limit to 6)
        $upcomingMatchesLimited = array_slice($upcomingMatches, 0, 6);
        
        // Get ongoing matches
        $ongoingMatchesLimited = array_slice($ongoingMatches, 0, 3);
        
        // Get all tournaments
        $allTournaments = $tournamentRepository->findAll();
        $featuredTournaments = array_slice($allTournaments, 0, 3);
        
        // Get all teams
        $allTeams = $teamRepository->findAll();
        
        // Calculate stats
        $totalMatches = count($allMatches);
        $totalTournaments = count($allTournaments);
        $totalTeams = count($allTeams);

        return $this->render('landing.html.twig', [
            'upcoming_matches' => $upcomingMatchesLimited,
            'ongoing_matches' => $ongoingMatchesLimited,
            'recent_finished_matches' => $recentFinishedMatches,
            'featured_tournaments' => $featuredTournaments,
            'total_matches' => $totalMatches,
            'total_tournaments' => $totalTournaments,
            'total_teams' => $totalTeams,
            'all_matches' => $allMatches,
        ]);
    }
}
