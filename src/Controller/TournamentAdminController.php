<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Form\TournamentType;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tournaments', name: 'admin_tournament_')]
class TournamentAdminController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TournamentRepository $tournamentRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $sort = $request->query->get('sort', 'startDate');

        if ($search) {
            $tournaments = $tournamentRepository->findBySearchTerm($search);
        } elseif ($status) {
            $tournaments = $tournamentRepository->findByStatus($status);
        } else {
            $tournaments = $tournamentRepository->findAllOrdered($sort);
        }

        return $this->render('admin/tournament/index.html.twig', [
            'tournaments' => $tournaments,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tournament);
            $entityManager->flush();

            $this->addFlash('success', 'Tournament created successfully!');
            return $this->redirectToRoute('admin_tournament_show', ['id' => $tournament->getId()]);
        }

        return $this->render('admin/tournament/form.html.twig', [
            'form' => $form,
            'title' => 'Create Tournament',
            'tournament' => $tournament,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Tournament $tournament, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tournament->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Tournament updated successfully!');
            return $this->redirectToRoute('admin_tournament_show', ['id' => $tournament->getId()]);
        }

        return $this->render('admin/tournament/form.html.twig', [
            'form' => $form,
            'title' => 'Edit Tournament',
            'tournament' => $tournament,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Tournament $tournament): Response
    {
        return $this->render('admin/tournament/show.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Tournament $tournament, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tournament->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tournament);
            $entityManager->flush();
            $this->addFlash('success', 'Tournament deleted successfully!');
        }

        return $this->redirectToRoute('admin_tournament_index');
    }
}