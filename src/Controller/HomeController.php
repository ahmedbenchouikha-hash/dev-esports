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
        // Use COUNT queries instead of loading all entities
        $gamesCount = $gameRepository->countAll();
        $teamsCount = $teamRepository->countAll();
        $tournamentsCount = $tournamentRepository->countAll();
        $playersCount = $playerRepository->countAll();

        // Get recent matches with JOIN FETCH (limited)
        $recentMatches = $gameRepository->findRecentWithTeams(6);

        // Get recent tournaments (limited)
        $recentTournaments = $tournamentRepository->findRecentTournaments(6);

        // Count games by status in a single GROUP BY query
        $statusCounts = $gameRepository->countAllByStatus();
        $statusMap = [];
        foreach ($statusCounts as $dto) {
            $statusMap[$dto->status] = $dto->count;
        }
        $completedGames = $statusMap['finished'] ?? 0;
        $scheduledGames = $statusMap['pending'] ?? 0;
        $inProgressGames = $statusMap['ongoing'] ?? 0;
        $cancelledGames = $statusMap['cancelled'] ?? 0;

        // Get top teams by wins using DTO hydration
        $topTeamsDTOs = $gameRepository->getTopTeamsByWins(5);
        $topTeams = [];
        foreach ($topTeamsDTOs as $dto) {
            $topTeams[$dto->name] = $dto->wins;
        }

        // Get top teams for chart
        $topTeamsForChartDTOs = $gameRepository->getTopTeamsByWinsForChart(8);
        $teamNamesChart = array_map(fn($dto) => $dto->name, $topTeamsForChartDTOs);
        $winsChart = array_map(fn($dto) => $dto->wins, $topTeamsForChartDTOs);

        // Get matches by tournament using grouped COUNT query with DTO
        $matchesByTournamentDTOs = $gameRepository->countMatchesByTournament();
        $matchesByTournament = [];
        foreach ($matchesByTournamentDTOs as $dto) {
            $matchesByTournament[$dto->tournamentName] = $dto->matchCount;
        }

        // Match status data for pie chart
        $statusData = [
            'completed' => $completedGames,
            'scheduled' => $scheduledGames,
            'in_progress' => $inProgressGames,
            'cancelled' => $cancelledGames,
        ];

        return $this->render('home.html.twig', [
            'games_count' => $gamesCount,
            'teams_count' => $teamsCount,
            'tournaments_count' => $tournamentsCount,
            'players_count' => $playersCount,
            'completed_games' => $completedGames,
            'scheduled_games' => $scheduledGames,
            'in_progress_games' => $inProgressGames,
            'top_teams' => $topTeams,
            'all_teams' => [],
            'recent_matches' => $recentMatches,
            'recent_tournaments' => $recentTournaments,
            'matches_by_tournament' => $matchesByTournament,
            'team_names_chart' => json_encode($teamNamesChart),
            'wins_chart' => json_encode($winsChart),
            'status_data' => $statusData,
        ]);
    }
}
