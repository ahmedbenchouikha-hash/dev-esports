<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Repository\TournamentRepository;
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
        PlayerRepository $playerRepository
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'totalMatches' => $gameRepository->count([]),
            'totalTournaments' => $tournamentRepository->count([]),
            'totalTeams' => $teamRepository->count([]),
            'totalPlayers' => $playerRepository->count([]),
            'matches' => $gameRepository->findBy([], ['matchdate' => 'DESC'], 5),
            'tournaments' => $tournamentRepository->findBy([], ['startDate' => 'DESC'], 5),
        ]);
    }
}
