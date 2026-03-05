<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use App\Service\AIPricingService;
use App\Service\SmartPricingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

/**
 * AI-based Smart Pricing Controller
 * Uses Google Gemini API for intelligent ticket pricing recommendations
 */
#[Route('/admin/ai-pricing')]
#[IsGranted('ROLE_ADMIN')]
class AIPricingController extends AbstractController
{
    #[Route('', name: 'ai_pricing_index', methods: ['GET'])]
    public function index(
        TicketRepository $ticketRepo,
        AIPricingService $aiPricingService,
        SmartPricingService $smartPricingService
    ): Response {
        $geminiAvailable = $aiPricingService->isGeminiAvailable();
        $availableModels = $aiPricingService->getAvailableModels();
        
        // Get all tickets with game+teams eagerly loaded (avoids N+1 queries)
        $tickets = $ticketRepo->findAllWithGameAndTeams();
        
        return $this->render('admin/ai_pricing/index.html.twig', [
            'geminiAvailable' => $geminiAvailable,
            'availableModels' => $availableModels,
            'tickets' => $tickets,
            'totalTickets' => count($tickets),
        ]);
    }

    /**
     * Get AI pricing recommendation for a single ticket (AJAX)
     */
    #[Route('/ticket/{id}/ai-analysis', name: 'ai_pricing_analyze', methods: ['GET'])]
    public function analyzeTicket(
        Ticket $ticket,
        AIPricingService $aiPricingService,
        SmartPricingService $smartPricingService
    ): JsonResponse {
        $geminiAvailable = $aiPricingService->isGeminiAvailable();
        
        if (!$geminiAvailable) {
            return $this->json([
                'error' => 'Google Gemini API key not configured. Add GEMINI_API_KEY to .env file.',
                'url' => 'https://ai.google.dev/'
            ], 503);
        }

        try {
            // Get both rule-based and AI recommendations
            $ruleBased = $smartPricingService->getPricingRecommendation($ticket);
            $aiRecommendation = $aiPricingService->getAIPricingRecommendation($ticket);

            return $this->json([
                'success' => true,
                'ticket' => [
                    'id' => $ticket->getId(),
                    'name' => $ticket->getType() . ' - ' . (string)$ticket->getGame(),
                    'currentPrice' => $ticket->getPrice(),
                ],
                'ruleBased' => $ruleBased,
                'aiPowered' => $aiRecommendation,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Analysis failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get AI analysis for all tickets (AJAX)
     */
    #[Route('/analyze-all', name: 'ai_pricing_analyze_all', methods: ['POST'])]
    public function analyzeAll(
        TicketRepository $ticketRepo,
        AIPricingService $aiPricingService,
        SmartPricingService $smartPricingService
    ): JsonResponse {
        $geminiAvailable = $aiPricingService->isGeminiAvailable();
        
        if (!$geminiAvailable) {
            return $this->json([
                'error' => 'Google Gemini API key not configured. Add GEMINI_API_KEY to .env file.'
            ], 503);
        }

        $tickets = $ticketRepo->findAllWithGameAndTeams();
        $analyses = [];
        $processedCount = 0;

        foreach ($tickets as $ticket) {
            try {
                $analysis = $aiPricingService->getAIPricingRecommendation($ticket);
                $analyses[] = [
                    'ticketId' => $ticket->getId(),
                    'ticketName' => $ticket->getType(),
                    'analysis' => $analysis,
                ];
                $processedCount++;
            } catch (\Exception $e) {
                $analyses[] = [
                    'ticketId' => $ticket->getId(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->json([
            'success' => true,
            'totalTickets' => count($tickets),
            'processedCount' => $processedCount,
            'analyses' => $analyses,
        ]);
    }

    /**
     * Apply AI recommendation to a ticket
     */
    #[Route('/apply/{id}', name: 'ai_pricing_apply', methods: ['POST'])]
    public function applyPricing(
        Ticket $ticket,
        AIPricingService $aiPricingService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $aiRecommendation = $aiPricingService->getAIPricingRecommendation($ticket);
            
            $oldPrice = $ticket->getPrice();
            $newPrice = $aiRecommendation['price'];
            
            // Apply the AI-recommended price
            $ticket->setPrice($newPrice);
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Price updated successfully',
                'oldPrice' => $oldPrice,
                'newPrice' => $newPrice,
                'change' => $aiRecommendation['change_percentage'],
                'reason' => $aiRecommendation['reason'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check Gemini API status
     */
    #[Route('/status', name: 'ai_pricing_status', methods: ['GET'])]
    public function checkStatus(AIPricingService $aiPricingService): JsonResponse
    {
        $available = $aiPricingService->isGeminiAvailable();
        $models = $available ? $aiPricingService->getAvailableModels() : [];

        return $this->json([
            'status' => $available ? 'configured' : 'not_configured',
            'url' => 'https://ai.google.dev/',
            'models' => $models,
            'instructions' => !$available ? 'Get free API key at https://ai.google.dev/' : 'Gemini API is configured',
        ]);
    }

    /**
     * Debug endpoint - test Gemini API connection
     */
    #[Route('/debug', name: 'ai_pricing_debug', methods: ['GET'])]
    public function debug(AIPricingService $aiPricingService): JsonResponse
    {
        try {
            $available = $aiPricingService->isGeminiAvailable();
            $statusMsg = $aiPricingService->getDebugApiKeyStatus();
            
            // Extract actual API key length from the status message
            $keyLength = 0;
            if (strpos($statusMsg, 'KEY_LENGTH:') !== false) {
                $keyLength = (int) str_replace('KEY_LENGTH:', '', $statusMsg);
            }
            
            return $this->json([
                'api_key_loaded' => $available,
                'api_key_status' => $statusMsg,
                'api_key_actual_length' => $keyLength,
                'expected_key_length' => 40, // Google Gemini keys are ~40 chars
                'issue' => $keyLength !== 0 && $keyLength < 30 ? 'WARNING: API key seems truncated or invalid' : 'OK',
                'timestamp' => new \DateTime(),
                'instructions' => 'If API key is loaded correctly, test the pricing analysis on a ticket. If you still get 404:
                1. Make sure your Google API key has "Generative Language API" enabled
                2. Check the API key is active at https://ai.google.dev/
                3. Try generating a fresh API key'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'debug_info' => 'Failed to check API key status'
            ], 500);
        }
    }

    /**
     * Debug Groq API - test request/response details
     */
    #[Route('/debug-groq', name: 'ai_pricing_debug_groq', methods: ['GET'])]
    public function debugGroq(AIPricingService $aiPricingService): JsonResponse
    {
        try {
            $debugInfo = $aiPricingService->debugGroqRequest();
            return $this->json($debugInfo);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
