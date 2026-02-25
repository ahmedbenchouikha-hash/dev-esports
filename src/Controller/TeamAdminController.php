<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/teams', name: 'admin_team_')]
class TeamAdminController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'name');

        if ($search) {
            $teams = $teamRepository->findBySearchTerm($search);
        } else {
            $teams = $teamRepository->findAllOrdered($sort);
        }

        return $this->render('admin/team/index.html.twig', [
            'teams' => $teams,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();

            $this->addFlash('success', 'Team created successfully!');
            return $this->redirectToRoute('admin_team_show', ['id' => $team->getId()]);
        }

        return $this->render('admin/team/form.html.twig', [
            'form' => $form,
            'title' => 'Create Team',
            'team' => $team,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Team $team, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Team updated successfully!');
            return $this->redirectToRoute('admin_team_show', ['id' => $team->getId()]);
        }

        return $this->render('admin/team/form.html.twig', [
            'form' => $form,
            'title' => 'Edit Team',
            'team' => $team,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('admin/team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Team $team, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $team->getId(), $request->request->get('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();
            $this->addFlash('success', 'Team deleted successfully!');
        }

        return $this->redirectToRoute('admin_team_index');
    }
}
