<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\TournamentRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/matches', name: 'match_')]
final class MatchController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(GameRepository $gameRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');

        if ($search) {
            $games = $gameRepository->findBySearchTerm($search);
        } elseif ($status) {
            $games = $gameRepository->findByStatus($status);
        } else {
            $games = $gameRepository->findAllOrdered('matchdate');
        }

        return $this->render('match/index.html.twig', [
            'games' => $games,
            'search' => $search,
            'status' => $status,
        ]);
    }

    #[Route('/upcoming', name: 'upcoming', methods: ['GET'])]
    public function upcoming(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findUpcoming();

        return $this->render('match/upcoming.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, GameRepository $gameRepository): Response
    {
        $game = $gameRepository->find($id);

        if (!$game) {
            throw $this->createNotFoundException('Game not found');
        }

        return $this->render('match/show.html.twig', [
            'game' => $game
        ]);
    }
}
