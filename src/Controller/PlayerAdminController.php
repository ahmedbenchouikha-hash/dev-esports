<?php

namespace App\Controller;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/players', name: 'admin_player_')]
class PlayerAdminController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(PlayerRepository $playerRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'nickname');

        if ($search) {
            $players = $playerRepository->findBySearchTerm($search);
        } else {
            $players = $playerRepository->findAllOrdered($sort);
        }

        return $this->render('admin/player/index.html.twig', [
            'players' => $players,
            'search' => $search,
            'sort' => $sort,
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
