<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
<<<<<<< HEAD
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/matches', name: 'admin_match_')]
#[IsGranted('ROLE_ADMIN')]
=======

#[Route('/admin/matches', name: 'admin_match_')]
>>>>>>> module-rewards
class MatchAdminController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(GameRepository $gameRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $sort = $request->query->get('sort', 'matchdate');

        if ($search) {
            $games = $gameRepository->findBySearchTerm($search);
        } elseif ($status) {
            $games = $gameRepository->findByStatus($status);
        } else {
            $games = $gameRepository->findAllOrdered($sort);
        }

        // Get counts for summary cards
        $total = $gameRepository->count([]);
        $pending_count = $gameRepository->count(['status' => 'pending']);
        $ongoing_count = $gameRepository->count(['status' => 'ongoing']);
        $finished_count = $gameRepository->count(['status' => 'finished']);

        return $this->render('admin/match/index.html.twig', [
            'games' => $games,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'total' => $total,
            'pending_count' => $pending_count,
            'ongoing_count' => $ongoing_count,
            'finished_count' => $finished_count,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash('success', 'Match created successfully!');
            return $this->redirectToRoute('admin_match_show', ['id' => $game->getId()]);
        }

        return $this->render('admin/match/form.html.twig', [
            'form' => $form,
            'title' => 'Create Match',
            'game' => $game,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Game $game, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $game->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Match updated successfully!');
            return $this->redirectToRoute('admin_match_show', ['id' => $game->getId()]);
        }

        return $this->render('admin/match/form.html.twig', [
            'form' => $form,
            'title' => 'Edit Match',
            'game' => $game,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Game $game): Response
    {
        return $this->render('admin/match/show.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Game $game, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $game->getId(), $request->request->get('_token'))) {
            $entityManager->remove($game);
            $entityManager->flush();
            $this->addFlash('success', 'Match deleted successfully!');
        }

        return $this->redirectToRoute('admin_match_index');
    }
}
