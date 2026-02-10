<?php

namespace App\Controller;

use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tournaments', name: 'tournament_')]
class TournamentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TournamentRepository $tournamentRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');

        if ($search) {
            $tournaments = $tournamentRepository->findBySearchTerm($search);
        } elseif ($status) {
            $tournaments = $tournamentRepository->findByStatus($status);
        } else {
            $tournaments = $tournamentRepository->findAllOrdered('startDate');
        }

        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournaments,
            'search' => $search,
            'status' => $status,
        ]);
    }

    #[Route('/upcoming', name: 'upcoming', methods: ['GET'])]
    public function upcoming(TournamentRepository $tournamentRepository): Response
    {
        $tournaments = $tournamentRepository->findUpcoming();

        return $this->render('tournament/upcoming.html.twig', [
            'tournaments' => $tournaments,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, TournamentRepository $tournamentRepository): Response
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            throw $this->createNotFoundException('Tournament not found');
        }

        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
        ]);
    }
}
