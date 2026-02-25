<?php

namespace App\Controller;

use App\Entity\AdminResponse;
use App\Form\AdminResponseType;
use App\Repository\AdminResponseRepository;
use App\Service\EmailService;
use App\Service\MistralAssistantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/response')]
final class AdminResponseController extends AbstractController
{
    private EmailService $emailService;
    private MessageBusInterface $bus;

    public function __construct(EmailService $emailService, MessageBusInterface $bus)
    {
        $this->emailService = $emailService;
        $this->bus = $bus;
    }

    #[Route('', name: 'app_admin_response_index', methods: ['GET'])]
    public function index(AdminResponseRepository $adminResponseRepository): Response
    {
        return $this->render('admin_response/index.html.twig', [
            'admin_responses' => $adminResponseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_response_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adminResponse = new AdminResponse();
        $form = $this->createForm(AdminResponseType::class, $adminResponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation = $adminResponse->getReclamation();
            // Validate that reclamation is selected
            if (!$reclamation) {
                $this->addFlash('error', 'Veuillez sélectionner une réclamation.');
                return $this->redirectToRoute('app_admin_response_new');
            }

            $entityManager->persist($adminResponse);
            $entityManager->flush();

            // Dispatch Messenger message for Mercure
            $this->bus->dispatch(new \App\DTO\NewResponseNotification(
                $reclamation->getId(),
                $reclamation->getTitre(),
                $this->getUser()?->getUserIdentifier() ?? 'Admin'
            ));

            $this->addFlash('success', 'Réponse d\'administration créée avec succès.');
            return $this->redirectToRoute('app_admin_response_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_response/new.html.twig', [
            'admin_response' => $adminResponse,
            'form' => $form,
        ]);
    }

    #[Route('/chatbot', name: 'app_admin_response_chatbot', methods: ['POST'])]
    public function chatbot(Request $request, MistralAssistantService $assistant): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $message = trim((string) ($payload['message'] ?? ''));

        $prompt = "Tu es un assistant e-sport pour les administrateurs et joueurs. "
            . "Tu peux discuter librement des jeux e-sport populaires, des joueurs connus, des scènes compétitives et des performances. "
            . "Tu réponds en français, de manière claire, utile et naturelle. "
            . "Si la question concerne une sanction, recommande uniquement parmi: ban from this match, ban from this game, ban from this tournament. "
            . "Donne une justification courte selon la gravité, la répétition et l'impact compétitif. "
            . "Tu peux aussi aider à rédiger des réponses admin professionnelles et équilibrées.";

        $result = $assistant->askWithPrompt($message, $prompt);
        $status = $result['status'];
        unset($result['status']);

        return $this->json($result, $status);
    }

    #[Route('/{id}', name: 'app_admin_response_show', methods: ['GET'])]
    public function show(AdminResponse $adminResponse): Response
    {
        return $this->render('admin_response/show.html.twig', [
            'admin_response' => $adminResponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_response_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdminResponse $adminResponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminResponseType::class, $adminResponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation = $adminResponse->getReclamation();
            
            // Validate that reclamation is selected
            if (!$reclamation) {
                $this->addFlash('error', 'Veuillez sélectionner une réclamation.');
                return $this->redirectToRoute('app_admin_response_edit', ['id' => $adminResponse->getId()]);
            }

            $entityManager->flush();

            // Dispatch Messenger message for Mercure notification (edit)
            $this->bus->dispatch(new \App\DTO\NewResponseNotification(
                $reclamation->getId(),
                $reclamation->getTitre(),
                $this->getUser()?->getUserIdentifier() ?? 'Admin'
            ));

            $this->addFlash('success', 'Réponse d\'administration modifiée avec succès.');
            return $this->redirectToRoute('app_admin_response_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_response/edit.html.twig', [
            'admin_response' => $adminResponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_response_delete', methods: ['POST'])]
    public function delete(Request $request, AdminResponse $adminResponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$adminResponse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($adminResponse);
            $entityManager->flush();
            $this->addFlash('success', 'Réponse supprimée avec succès.');
        }

        return $this->redirectToRoute('app_admin_response_index', [], Response::HTTP_SEE_OTHER);
    }
}
