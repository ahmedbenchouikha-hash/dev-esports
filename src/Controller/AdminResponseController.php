<?php

namespace App\Controller;

use App\Entity\AdminResponse;
use App\Entity\Notification;
use App\Form\AdminResponseType;
use App\Repository\AdminResponseRepository;
use App\Service\EmailService;
use App\Service\MistralAssistantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/response')]
#[IsGranted('ROLE_ADMIN')]
final class AdminResponseController extends AbstractController
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route(name: 'app_admin_response_index', methods: ['GET'])]
    public function index(AdminResponseRepository $adminResponseRepository): Response
    {
        return $this->render('admin_response/index.html.twig', [
            'admin_responses' => $adminResponseRepository->findAll(),
        ]);
    }

    #[Route('/chatbot', name: 'app_admin_response_chatbot', methods: ['GET'])]
    public function chatbotPage(): Response
    {
        return $this->render('admin_response/chatbot.html.twig');
    }

    #[Route('/chatbot/ask', name: 'app_admin_response_chatbot_ask', methods: ['POST'])]
    public function chatbotAsk(Request $request, MistralAssistantService $assistant): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $message = trim((string) ($payload['message'] ?? ''));

        $prompt = "Tu es un assistant e-sport pour les administrateurs. "
            . "Réponds en français, clairement et brièvement. "
            . "Si la question concerne une sanction, recommande une action proportionnée. "
            . "Tu peux aussi aider à rédiger des réponses admin professionnelles.";

        $result = $assistant->askWithPrompt($message, $prompt);
        $status = $result['status'] ?? 200;
        unset($result['status']);

        return $this->json($result, $status);
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

            // Auto-create notification for new admin response
            $notif = new Notification();
            $notif->setTitle('Réponse à réclamation #' . $reclamation->getId());
            $notif->setMessage(substr($adminResponse->getMessage(), 0, 200));
            $notif->setReclamation($reclamation);
            $entityManager->persist($notif);
            $entityManager->flush();

            $this->addFlash('success', 'Réponse d\'administration créée avec succès.');
            return $this->redirectToRoute('app_admin_response_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_response/new.html.twig', [
            'admin_response' => $adminResponse,
            'form' => $form,
        ]);
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
