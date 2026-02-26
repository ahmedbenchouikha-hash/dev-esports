<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
<<<<<<< HEAD
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/all-players', name: 'admin_player_')]
#[IsGranted('ROLE_ADMIN')]
=======

#[Route('/admin/all-players', name: 'admin_player_')]
>>>>>>> module-rewards
class PlayerAdminController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(PlayerRepository $playerRepository, TeamRepository $teamRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $team = $request->query->get('team', '');
        $role = $request->query->get('role', '');
        $sort = $request->query->get('sort', 'nickname');

        // Get all teams for filter dropdown
        $allTeams = $teamRepository->findAll();
        
        // Get all unique roles
        $allRoles = ['Mid', 'Support', 'Carry', 'Top', 'Jungler'];

        // Build query based on filters
        if ($search || $team || $role) {
            $players = $playerRepository->findByFilters(
                $search,
                $team ? (int)$team : null,
                $role ?: null,
                $sort
            );
        } else {
            $players = $playerRepository->findAllOrdered($sort);
        }

        return $this->render('admin/player/index.html.twig', [
            'players' => $players,
            'search' => $search,
            'team' => $team,
            'role' => $role,
            'sort' => $sort,
            'allTeams' => $allTeams,
            'allRoles' => $allRoles,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($player);
            $entityManager->flush();

            $this->addFlash('success', 'Player created successfully!');
            return $this->redirectToRoute('admin_player_show', ['id' => $player->getId()]);
        }

        return $this->render('admin/player/form.html.twig', [
            'form' => $form,
            'title' => 'Create Player',
            'player' => $player,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Player $player, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlayerType::class, $player);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $player->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Player updated successfully!');
            return $this->redirectToRoute('admin_player_show', ['id' => $player->getId()]);
        }

        return $this->render('admin/player/form.html.twig', [
            'form' => $form,
            'title' => 'Edit Player',
            'player' => $player,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Player $player): Response
    {
        return $this->render('admin/player/show.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Player $player, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $player->getId(), $request->request->get('_token'))) {
            $entityManager->remove($player);
            $entityManager->flush();
            $this->addFlash('success', 'Player deleted successfully!');
        }

        return $this->redirectToRoute('admin_player_index');
    }
}
