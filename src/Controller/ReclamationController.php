<?php

namespace App\Controller;

use App\Entity\Reclamation;
<<<<<<< HEAD
=======
use App\Entity\Notification;
>>>>>>> module-rewards
use App\Enum\ReclamationStatus;
use App\Enum\ReclamationType;
use App\Form\ReclamationType as ReclamationTypeForm;
use App\Service\EmailService;
<<<<<<< HEAD
use App\Service\MistralAssistantService;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
=======
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
>>>>>>> module-rewards
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reclamation')]
final class ReclamationController extends AbstractController
{
    private EmailService $emailService;
<<<<<<< HEAD
    private MessageBusInterface $bus;

    public function __construct(EmailService $emailService, MessageBusInterface $bus)
    {
        $this->emailService = $emailService;
        $this->bus = $bus;
=======

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
>>>>>>> module-rewards
    }

    #[Route('/home', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $reclamation = new Reclamation();
        $reclamation->setType(ReclamationType::JOUEUR);
        $reclamation->setEtat(ReclamationStatus::EN_COURS);

        $form = $this->createForm(ReclamationTypeForm::class, $reclamation, [
            'action' => $this->generateUrl('app_reclamation_new_player'),
            'method' => 'POST'
        ]);

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findBy([], ['createdAt' => 'DESC']),
            'form' => $form->createView(),
        ]);
    }

<<<<<<< HEAD
    #[Route('/leaderboard/stats', name: 'app_reclamation_leaderboard_stats', methods: ['GET'])]
    public function leaderboardStats(ReclamationRepository $reclamationRepository): JsonResponse
    {
        return $this->json([
            'total' => $reclamationRepository->count([]),
            'en_cours' => $reclamationRepository->count(['etat' => ReclamationStatus::EN_COURS]),
            'resolu' => $reclamationRepository->count(['etat' => ReclamationStatus::RESOLU]),
            'rejete' => $reclamationRepository->count(['etat' => ReclamationStatus::REJETE]),
        ]);
    }

=======
>>>>>>> module-rewards
    #[Route('/new/simple', name: 'app_reclamation_new_simple', methods: ['POST'])]
    public function newSimple(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {

        try {

            $reclamation = new Reclamation();

            $reclamation->setTitre($request->request->get('titre'));
            $reclamation->setDescription($request->request->get('description'));
            $reclamation->setEtat(ReclamationStatus::EN_COURS);

            $simpleType = $request->request->get('type_simple');

            if ($simpleType === 'TECHNIQUE') {
                $reclamation->setType(ReclamationType::TECHNIQUE);
            } elseif ($simpleType === 'ORGANISATIONNELLE') {
                $reclamation->setType(ReclamationType::ORGANISATIONNELLE);
            }

            if (method_exists($reclamation, 'setCreatedAt')) {
                $reclamation->setCreatedAt(new \DateTime());
            }

            $errors = $validator->validate($reclamation);
            if (count($errors) > 0) {
                $errs = [];
                foreach ($errors as $error) {
                    $prop = $error->getPropertyPath();
                    $field = $prop === 'type' ? 'type_simple' : $prop;
                    $errs[$field][] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'errors' => $errs
                ], 400);
            }

            $entityManager->persist($reclamation);
            $entityManager->flush();

<<<<<<< HEAD
=======
            $notif = new Notification();
            $notif->setTitle('Nouvelle réclamation #' . $reclamation->getId());
            $notif->setMessage($reclamation->getTitre());
            $notif->setReclamation($reclamation);
            $entityManager->persist($notif);
            $entityManager->flush();

>>>>>>> module-rewards
            return $this->json([
                'success' => true,
                'message' => 'Réclamation ajoutée',
                'id' => $reclamation->getId()
            ]);

        } catch (\Throwable $e) {

            return $this->json([
                'success' => false,
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/new/player', name: 'app_reclamation_new_player', methods: ['GET','POST'])]
    public function newPlayer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $reclamation->setType(ReclamationType::JOUEUR);
        $reclamation->setEtat(ReclamationStatus::EN_COURS);

        $form = $this->createForm(ReclamationTypeForm::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (method_exists($reclamation, 'setCreatedAt')) {
                $reclamation->setCreatedAt(new \DateTime());
            }

            $uploadedFile = $form->get('attachment')->getData();
            if ($uploadedFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/reclamations';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0775, true);
                }
                $originalExt = $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension();
                $newFilename = uniqid('rec_', true) . '.' . $originalExt;
                $uploadedFile->move($uploadsDir, $newFilename);
                $reclamation->setAttachmentFilename($newFilename);
            }

<<<<<<< HEAD

            $entityManager->persist($reclamation); 
            $entityManager->flush(); 
 
            // Dispatch Messenger/Mercure notification (no entity)
            $this->bus->dispatch(new \App\DTO\NewResponseNotification(
                $reclamation->getId(),
                $reclamation->getTitre(),
                $this->getUser()?->getUsername() ?? 'User'
            ));
=======
            $entityManager->persist($reclamation);
            $entityManager->flush();

            $notif = new Notification();
            $notif->setTitle('Nouvelle réclamation #' . $reclamation->getId());
            $notif->setMessage($reclamation->getTitre());
            $notif->setReclamation($reclamation);
            $entityManager->persist($notif);
            $entityManager->flush();
>>>>>>> module-rewards

            $this->addFlash('success', 'Réclamation ajoutée avec succès');

            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationTypeForm::class, $reclamation);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            if ($form->isSubmitted() && $form->isValid()) {
                if (method_exists($reclamation, 'setUpdatedAt')) {
                    $reclamation->setUpdatedAt(new \DateTime());
                }

                $entityManager->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Réclamation modifiée avec succès',
                    'id' => $reclamation->getId(),
                    'titre' => $reclamation->getTitre(),
                    'etat'  => $reclamation->getEtat()->value,
                    'adminResponse' => $reclamation->getAdminResponse() ?? '',
                    'updatedAt' => $reclamation->getUpdatedAt()?->format('d M Y H:i'),
                ]);
            }

            if ($form->isSubmitted()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'errors' => $errors
                ], 400);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'titre' => $reclamation->getTitre(),
                    'description' => $reclamation->getDescription(),
                    'type' => $reclamation->getType()->value,
                    'etat' => $reclamation->getEtat()->value,
                    'createdAt' => $reclamation->getCreatedAt()?->format('d M Y H:i'),
                    'updatedAt' => $reclamation->getUpdatedAt()?->format('d M Y H:i'),
                    'playerId' => $reclamation->getPlayer()?->getPlayerId() ?? '',
                    'adminResponse' => $reclamation->getAdminResponse() ?? '',
                ]
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (method_exists($reclamation, 'setUpdatedAt')) {
                $reclamation->setUpdatedAt(new \DateTime());
            }
            $entityManager->flush();
            $this->addFlash('success', 'Réclamation modifiée');
            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

<<<<<<< HEAD
    #[Route('/{id}/change-state', name: 'app_reclamation_change_state', methods: ['POST'])]
    public function changeState(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Validate CSRF token
        $token = $request->request->get('_token') ?? $request->getPayload()->get('_token');
        if (!$this->isCsrfTokenValid('change_state', $token)) {
            return $this->json([
                'success' => false,
                'error' => 'Token CSRF invalide'
            ], 403);
        }

        // Only allow state change if currently EN_COURS
        if ($reclamation->getEtat()->value !== ReclamationStatus::EN_COURS->value) {
            return $this->json([
                'success' => false,
                'error' => 'La réclamation ne peut être modifiée que si elle est en cours'
            ], 400);
        }

        $newState = $request->request->get('state') ?? $request->getPayload()->get('state');
        
        // Validate new state
        $validStates = [ReclamationStatus::RESOLU->value, ReclamationStatus::REJETE->value];
        if (!in_array($newState, $validStates)) {
            return $this->json([
                'success' => false,
                'error' => 'État invalide'
            ], 400);
        }

        // Update state
        $reclamation->setEtat(ReclamationStatus::from($newState));
        $entityManager->flush();

        // Dispatch notification
        $this->bus->dispatch(new \App\DTO\NewResponseNotification(
            $reclamation->getId(),
            'État modifié: ' . $newState,
            'Admin'
        ));

        return $this->json([
            'success' => true,
            'message' => 'État modifié avec succès',
            'newState' => $newState,
            'badge_class' => $newState === ReclamationStatus::RESOLU->value ? 'bg-success' : 'bg-danger'
        ]);
    }

    #[Route('/{id}/analyze-emotion', name: 'app_reclamation_analyze_emotion', methods: ['POST'])]
    public function analyzeEmotion(Reclamation $reclamation, MistralAssistantService $assistant): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $prompt = <<<'PROMPT'
Tu es un analyste de modération e-sport.
Analyse UNIQUEMENT l'état émotionnel du joueur et le niveau d'urgence à partir d'une réclamation.

Retourne STRICTEMENT un JSON valide, sans markdown ni texte additionnel:
{
  "emotion_state": "urgent matter|angry user|frustrated user|confused user|neutral user",
  "urgency": "high|medium|low",
  "short_reason": "phrase courte en français"
}

Règles:
- "urgent matter" si risque immédiat, menace, harcèlement fort, sécurité/intégrité compétitive critique.
- "angry user" si ton agressif/colérique explicite.
- "frustrated user" si plainte forte mais sans agressivité majeure.
- "confused user" si incompréhension dominante.
- "neutral user" sinon.
PROMPT;

        $textToAnalyze = sprintf(
            "Titre: %s\nType: %s\nDescription: %s",
            $reclamation->getTitre() ?? '',
            $reclamation->getType()->value,
            $reclamation->getDescription() ?? ''
        );

        $result = $assistant->askWithPrompt($textToAnalyze, $prompt);
        if (!($result['success'] ?? false)) {
            return $this->json([
                'success' => false,
                'error' => $result['error'] ?? 'Analyse impossible.'
            ], $result['status'] ?? 502);
        }

        $raw = trim((string) ($result['answer'] ?? ''));
        $clean = preg_replace('/^```json\s*|^```|```$/m', '', $raw) ?? $raw;
        $decoded = json_decode(trim($clean), true);

        if (!is_array($decoded)) {
            $fallbackState = 'neutral user';
            $rawLower = mb_strtolower($raw);

            if (str_contains($rawLower, 'urgent')) {
                $fallbackState = 'urgent matter';
            } elseif (str_contains($rawLower, 'angry')) {
                $fallbackState = 'angry user';
            } elseif (str_contains($rawLower, 'frustr')) {
                $fallbackState = 'frustrated user';
            } elseif (str_contains($rawLower, 'confus')) {
                $fallbackState = 'confused user';
            }

            return $this->json([
                'success' => true,
                'emotion_state' => $fallbackState,
                'urgency' => 'medium',
                'short_reason' => 'Analyse IA générée (format simplifié).',
            ]);
        }

        return $this->json([
            'success' => true,
            'emotion_state' => (string) ($decoded['emotion_state'] ?? 'neutral user'),
            'urgency' => (string) ($decoded['urgency'] ?? 'medium'),
            'short_reason' => (string) ($decoded['short_reason'] ?? 'Analyse effectuée.'),
        ]);
    }

=======
>>>>>>> module-rewards
    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete' . $reclamation->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_reclamation_index');
        }

        $entityManager->remove($reclamation);
        $entityManager->flush();

        $this->addFlash('success', 'Réclamation supprimée avec succès');

        return $this->redirectToRoute('app_reclamation_index');
    }
}
