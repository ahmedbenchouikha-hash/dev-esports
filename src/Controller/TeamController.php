<?php

namespace App\Controller;

use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teams', name: 'team_')]
class TeamController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');

        if ($search) {
            $teams = $teamRepository->findBySearchTerm($search);
        } else {
            $teams = $teamRepository->findAllOrdered('name');
        }

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, TeamRepository $teamRepository): Response
    {
        $team = $teamRepository->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Team not found');
        }

        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }
}
