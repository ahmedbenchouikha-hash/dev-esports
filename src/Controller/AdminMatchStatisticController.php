<?php

namespace App\Controller;

use App\Entity\MatchStatistic;
use App\Form\MatchStatisticType;
use App\Repository\MatchStatisticRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/match-statistics')]
#[IsGranted('ROLE_ADMIN')]
class AdminMatchStatisticController extends AbstractController
{
    #[Route('', name: 'admin_match_statistic_index', methods: ['GET'])]
    public function index(MatchStatisticRepository $repository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 15;

        // Get filter parameters and convert empty strings to null
        $search = $request->query->get('search') ?: null;
        $role = $request->query->get('role') ?: null;
        $kdaMin = $request->query->get('kdaMin') ? (float) $request->query->get('kdaMin') : null;
        $kdaMax = $request->query->get('kdaMax') ? (float) $request->query->get('kdaMax') : null;

        // Build query with filters
        $query = $repository->searchAndFilter($search, $role, $kdaMin, $kdaMax)->getQuery();
        $allStatistics = $query->getResult();
        $total = count($allStatistics);
        $maxPages = ceil($total / $limit);

        if ($page > $maxPages && $maxPages > 0) {
            $page = $maxPages;
        }

        $offset = ($page - 1) * $limit;
        $statistics = array_slice($allStatistics, $offset, $limit);

        // Get available roles for filter dropdown
        $availableRoles = $repository->getAvailableRoles();
        $roles = array_map(function($item) {
            return $item['role'];
        }, $availableRoles);
        $roles = array_filter(array_unique($roles));

        return $this->render('admin/match_statistic/index.html.twig', [
            'statistics' => $statistics,
            'current_page' => $page,
            'max_pages' => $maxPages,
            'total' => $total,
            'search' => $search,
            'role' => $role,
            'kdaMin' => $kdaMin,
            'kdaMax' => $kdaMax,
            'availableRoles' => $roles,
        ]);
    }

    #[Route('/create', name: 'admin_match_statistic_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $statistic = new MatchStatistic();
        $form = $this->createForm(MatchStatisticType::class, $statistic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statistic->setUpdatedAt(new \DateTime());
            $em->persist($statistic);
            $em->flush();

            $this->addFlash('success', 'Match statistic created successfully!');

            return $this->redirectToRoute('admin_match_statistic_index');
        }

        return $this->render('admin/match_statistic/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_match_statistic_show', methods: ['GET'])]
    public function show(MatchStatistic $statistic): Response
    {
        return $this->render('admin/match_statistic/show.html.twig', [
            'statistic' => $statistic,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_match_statistic_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MatchStatistic $statistic, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MatchStatisticType::class, $statistic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statistic->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Match statistic updated successfully!');

            return $this->redirectToRoute('admin_match_statistic_index');
        }

        return $this->render('admin/match_statistic/edit.html.twig', [
            'form' => $form->createView(),
            'statistic' => $statistic,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_match_statistic_delete', methods: ['POST'])]
    public function delete(Request $request, MatchStatistic $statistic, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $statistic->getId(), $request->request->get('_token'))) {
            $em->remove($statistic);
            $em->flush();

            $this->addFlash('success', 'Match statistic deleted successfully!');
        }

        return $this->redirectToRoute('admin_match_statistic_index');
    }
}
