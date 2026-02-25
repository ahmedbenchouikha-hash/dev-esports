<?php

namespace App\Controller;

use App\Entity\Punition;
use App\Form\PunitionType;
use App\Repository\PunitionRepository;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/punition')]
final class PunitionController extends AbstractController
{
    #[Route(name: 'app_punition_index', methods: ['GET'])]
    public function index(PunitionRepository $punitionRepository): Response
    {
        return $this->render('punition/index.html.twig', [
            'punitions' => $punitionRepository->findAll(),
        ]);
    }

    #[Route('/ajax-ban', name: 'app_punition_ajax_ban', methods: ['POST'])]
    public function ajaxBan(
        Request $request,
        EntityManagerInterface $em,
        ReclamationRepository $reclamationRepo
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        $rec = $reclamationRepo->find($data['reclamationId'] ?? 0);

        if (!$rec || $rec->getType()->value !== 'JOUEUR') {
            return new JsonResponse(['error' => 'Reclamation invalide'], 400);
        }

        if ($rec->getPunition()) {
            return new JsonResponse(['error' => 'Déjà banni'], 400);
        }

        $days = max(1, (int)$data['days']);

        $start = new \DateTimeImmutable();
        $end = $start->modify("+$days days");

        $punition = new Punition();
        $punition->setStartAt($start);
        $punition->setEndAt($end);
        $punition->setReclamation($rec);

        $player = $rec->getPlayer();
        if ($player) {
            $player->setPlayerStatus('BANNED');
            $em->persist($player);
        }

        $em->persist($punition);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ]);
    }

    #[Route('/new', name: 'app_punition_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $punition = new Punition();
        $form = $this->createForm(PunitionType::class, $punition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rec = $punition->getReclamation();
            
            // Validate that reclamation is selected
            if (!$rec) {
                $this->addFlash('error', 'Veuillez sélectionner une réclamation.');
                return $this->redirectToRoute('app_punition_new');
            }

            if ($rec->getPlayer()) {
                $player = $rec->getPlayer();
                $player->setPlayerStatus('BANNED');
                $entityManager->persist($player);
            }

            $entityManager->persist($punition);
            $entityManager->flush();

            $this->addFlash('success', 'Punition créée avec succès.');
            return $this->redirectToRoute('app_punition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('punition/new.html.twig', [
            'punition' => $punition,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_punition_show', methods: ['GET'])]
    public function show(Punition $punition): Response
    {
        return $this->render('punition/show.html.twig', [
            'punition' => $punition,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_punition_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Punition $punition, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PunitionType::class, $punition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rec = $punition->getReclamation();
            
            // Validate that reclamation is selected
            if (!$rec) {
                $this->addFlash('error', 'Veuillez sélectionner une réclamation.');
                return $this->redirectToRoute('app_punition_edit', ['id' => $punition->getId()]);
            }

            if ($rec->getPlayer()) {
                $player = $rec->getPlayer();
                $player->setPlayerStatus('BANNED');
                $entityManager->persist($player);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Punition modifiée avec succès.');
            return $this->redirectToRoute('app_punition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('punition/edit.html.twig', [
            'punition' => $punition,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_punition_delete', methods: ['POST'])]
    public function delete(Request $request, Punition $punition, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$punition->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($punition);
            $entityManager->flush();
            $this->addFlash('success', 'Punition supprimée avec succès.');
        }

        return $this->redirectToRoute('app_punition_index', [], Response::HTTP_SEE_OTHER);
    }
}
