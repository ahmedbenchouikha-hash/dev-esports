<?php

namespace App\Controller;

use App\Entity\DemandeRecompense;
use App\Entity\Recompense;
use App\Entity\User;
use App\Form\DemandeRecompenseType;
use App\Repository\DemandeRecompenseRepository;
use App\Repository\RecompenseRepository;
use App\Service\EmailService;
use App\Service\AIRewardAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/demande-recompense')]
class DemandeRecompenseController extends AbstractController
{
    public function __construct(
        private EmailService $emailService,
        private AIRewardAnalysisService $aiService
    ) {}

    /** @return User */
    private function getAuthenticatedUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();
        return $user;
    }

    #[Route('', name: 'demande_recompense_index', methods: ['GET'])]
    public function index(
        Request $request,
        DemandeRecompenseRepository $demandeRepository,
        RecompenseRepository $recompenseRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $userEmail = null;
        if (!$isAdmin) {
            $userEmail = $this->getUser()?->getUserIdentifier();
            if (!$userEmail) {
                throw new AccessDeniedException('Utilisateur non authentifie.');
            }
        }

        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'date_desc');
        $statut = $request->query->get('statut');
        $page = $request->query->getInt('page', 1);

        // Récupérer les demandes avec recherche et tri (Query Builder)
        $demandesQuery = $demandeRepository->createSearchAndSortQuery($search, $sort, $statut, $userEmail);
        
        // Paginer les résultats (10 par page)
        $demandes = $paginator->paginate(
            $demandesQuery,
            $page,
            10 // Items par page
        );
        
        // Récupérer les récompenses pour affichage
        $recompenses = $recompenseRepository->findAll();

        $isPlayer = !$isAdmin;

        // Use different template for players vs admins
        $template = $isPlayer ? 'demande_recompense/player_list.html.twig' : 'demande_recompense/list.html.twig';

        return $this->render($template, [
            'demandes' => $demandes,
            'search' => $search,
            'sort' => $sort,
            'statut' => $statut,
            'recompenses' => $recompenses,
            'isAdmin' => $isAdmin,
            'isPlayer' => $isPlayer,
        ]);
    }

    #[Route('/modal/form', name: 'demande_recompense_modal_form', methods: ['GET', 'POST'])]
    public function getModalForm(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        DemandeRecompenseRepository $demandeRepository
    ): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Acces reserve aux joueurs'], 403);
        }

        if (!$this->isGranted('ROLE_USER')) {
            return new JsonResponse(['error' => 'Acces refuse'], 403);
        }

        $user = $this->getAuthenticatedUser();
        $userEmail = $user->getUserIdentifier();
        if (!$userEmail) {
            return new JsonResponse(['error' => 'Utilisateur non authentifie'], 403);
        }

        // Get user's full name
        $firstName = $user->getFirstName() ?? '';
        $lastName = $user->getLastName() ?? '';
        $nomDemandeur = trim($firstName . ' ' . $lastName) ?: $user->getUsername();

        // Récupérer action depuis GET (chargement formulaire) ou POST (soumission)
        $action = $request->query->get('action') ?? $request->request->get('action', 'create');
        $id = $request->query->get('id') ?? $request->request->get('id');

        $demande = $action === 'edit' 
            ? $entityManager->getRepository(DemandeRecompense::class)->find($id) 
            : new DemandeRecompense();

        if ($action === 'edit' && !$demande) {
            return new JsonResponse(['error' => 'Demande non trouvée'], 404);
        }

        if ($action === 'edit' && $demande->getEmail() !== $userEmail) {
            return new JsonResponse(['error' => 'Acces refuse'], 403);
        }

        // Auto-populate for new demands
        if ($action === 'create') {
            $demande->setEmail($userEmail);
            $demande->setNomDemandeur($nomDemandeur);
        }

        $form = $this->createForm(DemandeRecompenseType::class, $demande);

        if ($request->isMethod('POST')) {
            error_log("📬 POST REQUEST RECEIVED for modal form");
            $form->handleRequest($request);
            error_log("📝 Form handled");

            if ($form->isSubmitted()) {
                // Ensure email and nomDemandeur are never changed
                $demande->setEmail($userEmail);
                $demande->setNomDemandeur($nomDemandeur);
                if ($action === 'create' && trim((string) $demande->getMotif()) === '') {
                    try {
                        $demande->setMotif($this->aiService->generateMotifSuggestion($demande));
                    } catch (\Throwable $e) {
                        error_log('Erreur IA (motif suggestion): ' . $e->getMessage());
                    }
                }
                error_log("✓ Form is submitted");
                if (!$form->isValid()) {
                    error_log("❌ Form is INVALID");
                    // Retourner les erreurs du formulaire
                    $errors = [];
                    foreach ($form->getErrors(true) as $error) {
                        $errors[] = $error->getMessage();
                    }
                    
                    if (empty($errors)) {
                        $errors[] = 'Le formulaire contient des erreurs de validation.';
                    }
                    
                    return new JsonResponse(['errors' => $errors], 400);
                }
                
                error_log("✓ Form is VALID");

                // Vérification de la limite de 3 demandes pour la création
                if ($action === 'create') {
                    error_log("🔍 Checking demand limit for email: " . $demande->getEmail());
                    $email = $demande->getEmail();
                    $existingDemands = $demandeRepository->countByEmail($email);
                    
                    error_log("📊 Existing demands for $email: $existingDemands");
                    
                    if ($existingDemands >= 3) {
                        return new JsonResponse([
                            'error' => 'Vous avez atteint le nombre maximum de demandes (3). Vous ne pouvez pas créer d\'autres demandes pour le moment.'
                        ], 400);
                    }
                }

                $errors = $validator->validate($demande);
                error_log("🔎 Validator errors count: " . count($errors));

                if (count($errors) === 0) {
                    try {
                        error_log("====== DEBUT CREATION DEMANDE ======");
                        error_log("Action: " . $action);
                        error_log("Email demandeur: " . $demande->getEmail());
                        
                        // === ANALYSE IA ===
                        if ($action === 'create') {
                            try {
                                // Analyser la demande avec l'IA
                                $aiAnalysis = $this->aiService->analyzeDemand($demande);
                                
                                // Hydrater l'entité avec les résultats de l'IA
                                $demande->applyAIAnalysis($aiAnalysis);
                                error_log("IA Analysis complete");
                            } catch (\Exception $e) {
                                // Log l'erreur mais continue (l'IA n'est pas bloquante)
                                error_log('Erreur lors de l\'analyse IA: ' . $e->getMessage());
                            }
                        }

                        if ($action === 'create') {
                            $entityManager->persist($demande);
                            error_log("Demande persisted");
                        }
                        $entityManager->flush();
                        error_log("Demande flushed. ID: " . $demande->getId());

                        if ($action === 'create') {
                            error_log("Sending emails...");
                            $verificationSent = $this->emailService->sendVerificationEmail($demande);
                            error_log("Verification email sent: " . ($verificationSent ? 'YES' : 'NO'));
                            
                            $confirmationSent = $this->emailService->sendConfirmationEmail($demande);
                            error_log("Confirmation email sent: " . ($confirmationSent ? 'YES' : 'NO'));
                        }
                        
                        error_log("====== FIN CREATION DEMANDE ======");

                        return new JsonResponse([
                            'success' => true,
                            'message' => 'Demande ' . ($action === 'edit' ? 'modifiée' : 'ajoutée') . ' avec succès'
                        ]);
                    } catch (\Exception $e) {
                        error_log('Erreur lors de la sauvegarde: ' . $e->getMessage());
                        error_log('Stack trace: ' . $e->getTraceAsString());
                        return new JsonResponse([
                            'error' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
                        ], 500);
                    }
                } else {
                    error_log("❌ Validator found errors:");
                    $errorMessages = [];
                    foreach ($errors as $error) {
                        error_log("  - " . $error->getMessage());
                        $errorMessages[] = $error->getMessage();
                    }
                    return new JsonResponse(['errors' => $errorMessages], 400);
                }
            }
        }

        $formView = $form->createView();
        $formHtml = $this->renderView('demande_recompense/_form_modal.html.twig', [
            'form' => $formView,
            'action' => $action,
        ]);

        return new JsonResponse(['html' => $formHtml]);
    }

    #[Route('/motif-suggest', name: 'demande_recompense_motif_suggest', methods: ['POST'])]
    public function suggestMotif(Request $request, RecompenseRepository $recompenseRepository): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Acces reserve aux joueurs'], 403);
        }

        if (!$this->isGranted('ROLE_USER')) {
            return new JsonResponse(['error' => 'Acces refuse'], 403);
        }

        $user = $this->getUser();
        $userEmail = $user?->getUserIdentifier();
        if (!$userEmail) {
            return new JsonResponse(['error' => 'Utilisateur non authentifie'], 403);
        }

        $recompenseId = $request->request->get('recompenseId');
        $nomDemandeur = $request->request->get('nomDemandeur');
        $existingMotif = $request->request->get('motif', ''); // Récupérer le motif existant

        $demande = new DemandeRecompense();
        $demande->setEmail($userEmail);
        if ($nomDemandeur) {
            $demande->setNomDemandeur($nomDemandeur);
        }
        if ($existingMotif) {
            $demande->setMotif($existingMotif); // Définir le motif existant
        }

        if ($recompenseId) {
            $recompense = $recompenseRepository->find($recompenseId);
            if ($recompense) {
                $demande->setRecompense($recompense);
            }
        }

        try {
            $suggestion = $this->aiService->generateMotifSuggestion($demande);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Erreur IA'], 500);
        }

        return new JsonResponse(['suggestion' => $suggestion]);
    }

    #[Route('/modal/delete', name: 'demande_recompense_modal_delete', methods: ['POST'])]
    public function deleteViaModal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Acces reserve aux joueurs'], 403);
        }

        if (!$this->isGranted('ROLE_USER')) {
            return new JsonResponse(['error' => 'Acces refuse'], 403);
        }

        $userEmail = $this->getUser()?->getUserIdentifier();
        if (!$userEmail) {
            return new JsonResponse(['error' => 'Utilisateur non authentifie'], 403);
        }

        $id = $request->request->get('id');
        $token = $request->request->get('_token');

        if (!$id || !$token) {
            return new JsonResponse(['error' => 'Paramètres manquants'], 400);
        }

        if (!$this->isCsrfTokenValid('demande_recompense_delete', $token)) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $demande = $entityManager->getRepository(DemandeRecompense::class)->find($id);

        if (!$demande) {
            return new JsonResponse(['error' => 'Demande non trouvée'], 404);
        }

        if ($demande->getEmail() !== $userEmail) {
            return new JsonResponse(['error' => 'Acces refuse'], 403);
        }

        try {
            $entityManager->remove($demande);
            $entityManager->flush();
            return new JsonResponse(['success' => true, 'message' => 'Demande supprimée avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/statut', name: 'demande_recompense_change_statut', methods: ['POST'])]
    public function changeStatut(
        Request $request,
        DemandeRecompense $demande,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Acces refuse'], 403);
        }

        $newStatut = $request->request->get('statut');
        $token = $request->request->get('_token');
        $validStatuts = ['en_attente', 'approuvee', 'rejetee'];

        if (!$token || !$this->isCsrfTokenValid('statut' . $demande->getId(), $token)) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        if (!in_array($newStatut, $validStatuts)) {
            return new JsonResponse(['error' => 'Statut invalide'], 400);
        }

        $demande->setStatut($newStatut);
        $entityManager->flush();

        // Envoyer un email de notification du changement de statut
        try {
            $this->emailService->sendStatusChangeEmail($demande, $newStatut);
        } catch (\Exception $e) {
            // Email failure is not critical, continue anyway
        }

        return new JsonResponse(['success' => true, 'message' => 'Status updated successfully']);
    }

    #[Route('/{id}/verify/{token}', name: 'demande_recompense_verify_email', methods: ['GET'])]
    public function verifyEmail(
        int $id,
        string $token,
        DemandeRecompenseRepository $demandeRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $demande = $demandeRepository->find($id);

        if (!$demande) {
            $this->addFlash('error', 'Demande non trouvée');
            return $this->redirectToRoute('demande_recompense_index');
        }

        // Vérifier le token
        if ($demande->getVerificationToken() !== $token) {
            $this->addFlash('error', 'Token de vérification invalide');
            return $this->redirectToRoute('demande_recompense_index');
        }

        // Vérifier si déjà vérifié
        if ($demande->getEmailVerifiedAt()) {
            $this->addFlash('warning', 'Cet email a déjà été vérifié');
            return $this->redirectToRoute('demande_recompense_index');
        }

        // Marquer comme vérifié et prioritaire
        $demande->setEmailVerifiedAt(new \DateTime());
        $demande->setIsPrioritaire(true); // Mettre is_prioritaire à true
        
        $entityManager->flush();

        $this->addFlash('success', '✅ Email vérifié avec succès ! Votre demande est maintenant marquée comme prioritaire.');
        return $this->redirectToRoute('demande_recompense_show', ['id' => $demande->getId()]);
    }

    #[Route('/new', name: 'demande_recompense_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        RecompenseRepository $recompenseRepository
    ): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Acces reserve aux joueurs.');
        }

        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getAuthenticatedUser();
        $userEmail = $user->getUserIdentifier();
        if (!$userEmail) {
            throw new AccessDeniedException('Utilisateur non authentifie.');
        }

        $demande = new DemandeRecompense();
        
        // Auto-populate with current user's information
        $demande->setEmail($userEmail);
        $firstName = $user->getFirstName() ?? '';
        $lastName = $user->getLastName() ?? '';
        $nomDemandeur = trim($firstName . ' ' . $lastName) ?: $user->getUsername();
        $demande->setNomDemandeur($nomDemandeur);
        
        // Pre-select recompense if recompenseId is provided
        $recompenseId = $request->query->get('recompenseId');
        if ($recompenseId) {
            $recompense = $recompenseRepository->find($recompenseId);
            if ($recompense) {
                $demande->setRecompense($recompense);
            }
        }
        
        $form = $this->createForm(DemandeRecompenseType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Ensure email and nomDemandeur are never changed by the form
            $demande->setEmail($userEmail);
            $demande->setNomDemandeur($nomDemandeur);
            
            if (trim((string) $demande->getMotif()) === '') {
                try {
                    $demande->setMotif($this->aiService->generateMotifSuggestion($demande));
                } catch (\Throwable $e) {
                    error_log('Erreur IA (motif suggestion): ' . $e->getMessage());
                }
            }

            if ($form->isValid()) {
                // Validation PHP côté serveur
                $errors = $validator->validate($demande);

                if (count($errors) === 0) {
                    // === ANALYSE IA ===
                    try {
                        $aiAnalysis = $this->aiService->analyzeDemand($demande);
                        $demande->applyAIAnalysis($aiAnalysis);
                        error_log("IA Analysis complete");
                    } catch (\Exception $e) {
                        // Log l'erreur mais continue (l'IA n'est pas bloquante)
                        error_log('Erreur lors de l\'analyse IA: ' . $e->getMessage());
                    }

                    $entityManager->persist($demande);
                    $entityManager->flush();

                    // Envoyer un email de verification et un email de confirmation
                    $verificationSent = $this->emailService->sendVerificationEmail($demande);
                    $this->emailService->sendConfirmationEmail($demande);

                    if ($verificationSent) {
                        $this->addFlash('success', 'Demande creee avec succes ! Veuillez verifier votre email pour confirmer votre adresse.');
                    } else {
                        $this->addFlash('warning', 'Demande creee mais l\'email de verification n\'a pas pu etre envoye.');
                    }
                    return $this->redirectToRoute('demande_recompense_index');
                } else {
                    // Ajouter les erreurs de validation au formulaire
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                }
            }
        }

        return $this->render('demande_recompense/new.html.twig', [
            'form' => $form->createView(),
            'recompense' => $demande->getRecompense(),
        ]);
    }

    #[Route('/{id}', name: 'demande_recompense_show', methods: ['GET'])]
    public function show(DemandeRecompense $demande): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isGranted('ROLE_ADMIN')) {
            $userEmail = $this->getUser()?->getUserIdentifier();
            if (!$userEmail || $demande->getEmail() !== $userEmail) {
                throw new AccessDeniedException('Acces refuse');
            }
        }

        return $this->render('demande_recompense/show.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/export/pdf', name: 'demande_recompense_export_pdf', methods: ['GET'])]
    public function exportPdf(DemandeRecompenseRepository $demandeRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Acces reserve aux joueurs.');
        }

        $userEmail = $this->getUser()?->getUserIdentifier();
        if (!$userEmail) {
            throw new AccessDeniedException('Utilisateur non authentifie.');
        }

        // Récupérer toutes les demandes de l'utilisateur
        $demandes = $demandeRepository->searchAndSort(null, 'date_desc', null, $userEmail);

        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);

        // Générer le HTML pour le PDF
        $html = $this->renderView('demande_recompense/pdf_export.html.twig', [
            'demandes' => $demandes,
            'userEmail' => $userEmail,
            'exportDate' => new \DateTime(),
        ]);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Configurer la taille du papier et l'orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render le PDF
        $dompdf->render();

        // Envoyer le PDF au navigateur
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="my_reward_requests_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }
}
