<?php

namespace App\Controller;

use App\Entity\Punition;
use App\Form\PunitionType;
use App\Repository\PunitionRepository;
use App\Repository\ReclamationRepository;
use App\Service\MistralAssistantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/punition')]
#[IsGranted('ROLE_USER')]
final class PunitionController extends AbstractController
{
    private function denyUnlessModeratorOrAdmin(): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MANAGER')) {
            throw $this->createAccessDeniedException('Access denied. Moderator role required.');
        }
    }

    #[Route(name: 'app_punition_index', methods: ['GET'])]
    public function index(PunitionRepository $punitionRepository): Response
    {
        $this->denyUnlessModeratorOrAdmin();

        return $this->render('punition/index.html.twig', [
            'punitions' => $punitionRepository->findAll(),
        ]);
    }

    #[Route('/leaderboard/stats', name: 'app_punition_leaderboard_stats', methods: ['GET'])]
    public function leaderboardStats(PunitionRepository $punitionRepository): JsonResponse
    {
        $this->denyUnlessModeratorOrAdmin();

        $punitions = $punitionRepository->findAll();

        return $this->json([
            'banned_players' => count($punitions),
            'banned_from_game' => count($punitions),
            'banned_from_match' => count($punitions),
            'banned_from_tournament' => count($punitions),
        ]);
    }

    #[Route('/assistant-vocal', name: 'app_punition_voice_assistant_page', methods: ['GET', 'POST'])]
    public function voiceAssistantPage(Request $request, MistralAssistantService $assistant): Response
    {
        $this->denyUnlessModeratorOrAdmin();

        $message = '';
        $answer = null;
        $error = null;

        if ($request->isMethod('POST')) {
            $message = trim((string) $request->request->get('message', ''));
            $result = $assistant->ask($message);

            if (($result['success'] ?? false) === true) {
                $answer = (string) ($result['answer'] ?? '');
            } else {
                $error = (string) ($result['error'] ?? 'Assistant indisponible.');
            }
        }

        return $this->render('punition/voice_assistant.html.twig', [
            'chat_message' => $message,
            'chat_answer' => $answer,
            'chat_error' => $error,
        ]);
    }

    #[Route('/ajax-ban', name: 'app_punition_ajax_ban', methods: ['POST'])]
    public function ajaxBan(
        Request $request,
        EntityManagerInterface $em,
        ReclamationRepository $reclamationRepo
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

    #[Route('/voice-assistant', name: 'app_punition_voice_assistant', methods: ['POST'])]
    public function voiceAssistant(Request $request, MistralAssistantService $assistant): JsonResponse
    {
        $this->denyUnlessModeratorOrAdmin();

        $payload = json_decode($request->getContent(), true);
        $message = trim((string) ($payload['message'] ?? ''));

        $result = $assistant->ask($message);
        $status = $result['status'] ?? 200;
        unset($result['status']);

        return $this->json($result, $status);
    }

    #[Route('/my', name: 'app_punition_my', methods: ['GET'])]
    public function myPunition(PunitionRepository $punitionRepository): Response
    {
        $user = $this->getUser();
        if (!$user || !method_exists($user, 'getId') || $user->getId() === null) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $punitions = $punitionRepository->createQueryBuilder('p')
            ->innerJoin('p.reclamation', 'r')
            ->addSelect('r')
            ->where('IDENTITY(r.player) = :playerId')
            ->setParameter('playerId', $user->getId())
            ->orderBy('p.startAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('punition/my_punition.html.twig', [
            'punitions' => $punitions,
        ]);
    }

    #[Route('/new', name: 'app_punition_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

    #[Route('/{id<\d+>}', name: 'app_punition_show', methods: ['GET'])]
    public function show(Punition $punition): Response
    {
        $this->denyUnlessModeratorOrAdmin();

        return $this->render('punition/show.html.twig', [
            'punition' => $punition,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_punition_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Punition $punition, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(PunitionType::class, $punition, [
            'edit_mode' => true,
        ]);
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

    #[Route('/{id<\d+>}', name: 'app_punition_delete', methods: ['POST'])]
    public function delete(Request $request, Punition $punition, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$punition->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($punition);
            $entityManager->flush();
            $this->addFlash('success', 'Punition supprimée avec succès.');
        }

        return $this->redirectToRoute('app_punition_index', [], Response::HTTP_SEE_OTHER);
    }
}
