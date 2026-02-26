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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\FormError;

#[Route('/admin/tournaments', name: 'admin_tournament_')]
class TournamentAdminController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TournamentRepository $tournamentRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $sort = $request->query->get('sort', 'startDate');
        $direction = $request->query->get('direction', 'ASC');

        if ($search) {
            $tournaments = $tournamentRepository->findBySearchTerm($search, $sort, $direction);
        } elseif ($status) {
            $tournaments = $tournamentRepository->findByStatus($status, $sort, $direction);
        } else {
            $tournaments = $tournamentRepository->findAllOrdered($sort, $direction);
        }

        return $this->render('admin/tournament/index.html.twig', [
            'tournaments' => $tournaments,
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validate the tournament entity
            $errors = $validator->validate($tournament);
            
            if ($form->isValid() && count($errors) === 0) {
                $entityManager->persist($tournament);
                $entityManager->flush();

                $this->addFlash('success', 'Tournament created successfully!');
                return $this->redirectToRoute('admin_tournament_show', ['id' => $tournament->getId()]);
            }
            
            // Add validation errors to form
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError(new \Symfony\Component\Form\FormError($error->getMessage()));
                }
            }
        }

        return $this->render('admin/tournament/form.html.twig', [
            'form' => $form,
            'title' => 'Create Tournament',
            'tournament' => $tournament,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Tournament $tournament, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validate the tournament entity
            $errors = $validator->validate($tournament);
            
            if ($form->isValid() && count($errors) === 0) {
                $tournament->setUpdatedAt(new \DateTime());
                $entityManager->flush();

                $this->addFlash('success', 'Tournament updated successfully!');
                return $this->redirectToRoute('admin_tournament_show', ['id' => $tournament->getId()]);
            }
            
            // Add validation errors to form
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $form->addError(new \Symfony\Component\Form\FormError($error->getMessage()));
                }
            }
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
