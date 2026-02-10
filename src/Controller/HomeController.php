<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use App\Repository\TournamentRepository;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        GameRepository $gameRepository,
        TeamRepository $teamRepository,
        TournamentRepository $tournamentRepository,
        PlayerRepository $playerRepository
    ): Response {
        $allGames = $gameRepository->findAll();
        $allTeams = $teamRepository->findAll();
        
        // Get recent matches (limit to 6)
        $recentMatches = array_slice($allGames, 0, 6);
        
        // Get all tournaments
        $allTournaments = $tournamentRepository->findAll();
        $recentTournaments = array_slice($allTournaments, 0, 6);
        
        // Count games by status
        $completedGames = count($gameRepository->findByStatus('finished'));
        $scheduledGames = count($gameRepository->findByStatus('pending'));
        $inProgressGames = count($gameRepository->findByStatus('ongoing'));
        $cancelledGames = count($gameRepository->findByStatus('cancelled'));
        
        // Get top teams by wins (from finished games)
        $teamWins = [];
        foreach ($allTeams as $team) {
            $wins = 0;
            foreach ($allGames as $game) {
                if ($game->getStatus() === 'finished') {
                    // Determine winner based on scores
                    $winnerTeam = null;
                    if ($game->getScore1() > $game->getScore2()) {
                        $winnerTeam = $game->getTeam1();
                    } elseif ($game->getScore2() > $game->getScore1()) {
                        $winnerTeam = $game->getTeam2();
                    }
                    
                    // Check if this team is the winner
                    if ($winnerTeam && $winnerTeam->getId() === $team->getId()) {
                        $wins++;
                    }
                }
            }
            // Add all teams, even if they have 0 wins
            $teamWins[$team->getName()] = $wins;
        }
        arsort($teamWins);
        $topTeams = array_slice($teamWins, 0, 5);
        
        // Get matches by tournament
        $matchesByTournament = [];
        $tournaments = $tournamentRepository->findAll();
        foreach ($tournaments as $tournament) {
            $matches = $gameRepository->findByTournament($tournament);
            if (count($matches) > 0) {
                $matchesByTournament[$tournament->getName()] = count($matches);
            }
        }
        
        // Get team wins data for chart - show top 8 teams
        $topTeamsForChart = array_slice($teamWins, 0, 8);
        $teamNamesChart = array_keys($topTeamsForChart);
        $winsChart = array_values($topTeamsForChart);
        
        // Match status data for pie chart
        $statusData = [
            'completed' => $completedGames,
            'scheduled' => $scheduledGames,
            'in_progress' => $inProgressGames,
            'cancelled' => $cancelledGames,
        ];
        
        return $this->render('home.html.twig', [
            'games_count' => count($allGames),
            'teams_count' => count($allTeams),
            'tournaments_count' => count($tournaments),
            'players_count' => count($playerRepository->findAll()),
            'completed_games' => $completedGames,
            'scheduled_games' => $scheduledGames,
            'in_progress_games' => $inProgressGames,
            'top_teams' => $topTeams,
            'all_teams' => $allTeams,
            'recent_matches' => $recentMatches,
            'recent_tournaments' => $recentTournaments,
            'matches_by_tournament' => $matchesByTournament,
            'team_names_chart' => json_encode($teamNamesChart),
            'wins_chart' => json_encode($winsChart),
            'status_data' => $statusData,
        ]);
    }
}
