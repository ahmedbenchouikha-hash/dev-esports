<?php

namespace App\Controller;

use App\Service\DepenseChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/depense/chatbot', name: 'depense_chatbot_')]
class DepenseChatbotController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(DepenseChatbotService $chatbotService): Response
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only managers/admins can access this assistant.');
        }

        $snapshot = $chatbotService->getFinancialSnapshot();

        return $this->render('depense/chatbot.html.twig', [
            'snapshot' => $snapshot,
        ]);
    }

    #[Route('/ask', name: 'ask', methods: ['POST'])]
    public function ask(Request $request, DepenseChatbotService $chatbotService): JsonResponse
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $message = trim((string) $request->request->get('message', ''));
        if ($message === '') {
            return $this->json(['error' => 'Message cannot be empty'], 422);
        }

        $user = $this->getUser();
        $answer = $chatbotService->ask($message, $user);

        return $this->json([
            'reply' => $answer,
        ]);
    }

    #[Route('/history', name: 'history', methods: ['GET'])]
    public function getHistory(DepenseChatbotService $chatbotService): JsonResponse
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $user = $this->getUser();
        $history = $chatbotService->getConversationHistory($user);

        $formattedHistory = array_map(function ($conversation) {
            return [
                'id' => $conversation->getId(),
                'userMessage' => $conversation->getUserMessage(),
                'aiResponse' => $conversation->getAiResponse(),
                'createdAt' => $conversation->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $history);

        return $this->json([
            'history' => array_reverse($formattedHistory), // Plus récent en dernier
        ]);
    }
}
