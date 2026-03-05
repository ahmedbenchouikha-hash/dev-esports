<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Entity\Team;
use App\Entity\Player;
use App\Form\DepenseType;
use App\Repository\DepenseRepository;
use App\Service\BudgetAlertService;
use App\Service\AuthorizationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/depense', name: 'depense_')]
class DepenseController extends AbstractController
{
    // Specific routes first (more specific must be before generic)
    
    #[Route('/historique', name: 'historique', methods: ['GET'])]
    public function historique(DepenseRepository $depenseRepository): Response
    {
        $depenses = $depenseRepository->findBy([], ['date_creation' => 'DESC']);
        
        return $this->render('depense/historique.html.twig', [
            'depenses' => $depenses,
        ]);
    }

    #[Route('/pending', name: 'pending', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function pending(DepenseRepository $depenseRepository): Response
    {
        $depenses = $depenseRepository->findBy(['statut' => 'en_attente']);
        
        return $this->render('depense/pending.html.twig', [
            'depenses' => $depenses,
        ]);
    }

    #[Route('/finance/stats', name: 'finance_stats', methods: ['GET'])]
    public function financeStats(DepenseRepository $depenseRepository, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only managers/admins can access finance statistics.');
        }

        // Get all budgets and expenses
        $budgetRepo = $em->getRepository(\App\Entity\Budget::class);
        $teamRepo = $em->getRepository(Team::class);
        
        $budgets = $budgetRepo->findAll();
        $depenses = $depenseRepository->findAll();
        $teams = $teamRepo->findAll();
        
        // Prepare data for charts
        $budgetsByTeam = [];
        $expensesByTeam = [];
        $expensesByCategory = [];
        $totalStats = [
            'total_budget' => 0,
            'total_expenses' => 0,
            'total_approved' => 0,
            'total_pending' => 0,
            'total_rejected' => 0,
        ];
        
        // Process budgets
        foreach ($budgets as $budget) {
            $teamName = $budget->getTeam() ? $budget->getTeam()->getName() : 'Unknown';
            $budgetsByTeam[$teamName] = [
                'alloue' => (float)$budget->getMontantAlloue(),
                'utilise' => (float)$budget->getMontantUtilise(),
            ];
            $totalStats['total_budget'] += (float)$budget->getMontantAlloue();
        }
        
        // Process expenses
        foreach ($depenses as $depense) {
            $teamName = $depense->getTeam() ? $depense->getTeam()->getName() : 'Unknown';
            $category = $depense->getCategorie();
            $montant = (float)$depense->getMontant();
            
            // Group by team
            if (!isset($expensesByTeam[$teamName])) {
                $expensesByTeam[$teamName] = 0;
            }
            $expensesByTeam[$teamName] += $montant;
            
            // Group by category
            if (!isset($expensesByCategory[$category])) {
                $expensesByCategory[$category] = 0;
            }
            $expensesByCategory[$category] += $montant;
            
            // Count by status
            $statut = $depense->getStatut();
            if ($statut === 'validée') {
                $totalStats['total_approved'] += $montant;
            } elseif ($statut === 'en_attente' || $statut === 'en attente') {
                $totalStats['total_pending'] += $montant;
            } elseif ($statut === 'refusée') {
                $totalStats['total_rejected'] += $montant;
            }
            
            $totalStats['total_expenses'] += $montant;
        }
        
        return $this->render('depense/finance_stats.html.twig', [
            'budgetsByTeam' => $budgetsByTeam,
            'expensesByTeam' => $expensesByTeam,
            'expensesByCategory' => $expensesByCategory,
            'totalStats' => $totalStats,
            'budgets' => $budgets,
            'depenses' => $depenses,
        ]);
    }

    #[Route('/team/{teamId}', name: 'by_team', methods: ['GET'])]
    public function byTeam(int $teamId, DepenseRepository $depenseRepository, AuthorizationService $authService, EntityManagerInterface $em): Response
    {
        // Load team from repository to check access
        $team = $em->getRepository(Team::class)->find($teamId);

        if (!$team) {
            throw $this->createNotFoundException('Team not found');
        }

        // Check if user has access to this team
        $authService->ensureCanAccessTeam($team);

        $depenses = $depenseRepository->findBy(['team' => $teamId]);
        
        return $this->render('depense/by_team.html.twig', [
            'depenses' => $depenses,
            'teamId' => $teamId,
        ]);
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, DepenseRepository $depenseRepository, AuthorizationService $authService, PaginatorInterface $paginator): Response
    {
        // Paramètres de recherche et tri
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_creation');
        $order = $request->query->get('order', 'DESC');
        $statut = $request->query->get('statut', 'all');
        $categorie = $request->query->get('categorie', 'all');
        $minAmount = $request->query->get('min_amount', '');
        $maxAmount = $request->query->get('max_amount', '');
        $page = $request->query->getInt('page', 1);

        // Validations
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $validSorts = ['date_creation', 'montant', 'titre', 'statut', 'categorie'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'date_creation';
        }

        // Récupérer toutes les dépenses
        $allDepenses = $depenseRepository->findBy([], [$sort => $order]);

        // Filter by team access for managers (admins see everything)
        $currentPlayer = $authService->getCurrentPlayer();

        // Filtrer
        $depenses = [];
        foreach ($allDepenses as $depense) {
            // Managers can only see expenses for teams they belong to
            if (!$this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_MANAGER')) {
                if (!$depense->getTeam() || !$depense->getTeam()->getPlayers()->contains($currentPlayer)) {
                    continue;
                }
            }

            $pass = true;

            // Filtre statut
            if ($statut !== 'all' && $depense->getStatut() !== $statut) {
                $pass = false;
            }

            // Filtre catégorie
            if ($categorie !== 'all' && $depense->getCategorie() !== $categorie) {
                $pass = false;
            }

            // Filtre montant min
            if (!empty($minAmount) && is_numeric($minAmount) && $depense->getMontant() < (float)$minAmount) {
                $pass = false;
            }

            // Filtre montant max
            if (!empty($maxAmount) && is_numeric($maxAmount) && $depense->getMontant() > (float)$maxAmount) {
                $pass = false;
            }

            if ($pass) {
                $depenses[] = $depense;
            }
        }

        // Recherche par titre ou équipe
        if (!empty($search)) {
            $search = strtolower(trim($search));
            $depenses = array_filter($depenses, function($depense) use ($search) {
                $titleMatch = strpos(strtolower($depense->getTitre()), $search) !== false;
                $teamMatch = $depense->getTeam() && strpos(strtolower($depense->getTeam()->getName()), $search) !== false;
                return $titleMatch || $teamMatch;
            });
        }

        // Pagination
        $pagination = $paginator->paginate(
            $depenses,
            $page,
            3  // 3 items per page
        );

        // Stats
        $stats = [
            'total_montant' => 0,
            'nombre_depenses' => count($depenses),
            'en_attente' => 0,
            'validees' => 0,
            'refusees' => 0,
        ];

        foreach ($depenses as $d) {
            $stats['total_montant'] += $d->getMontant();
            if ($d->getStatut() === 'en_attente') {
                $stats['en_attente']++;
            } elseif ($d->getStatut() === 'validée') {
                $stats['validees']++;
            } elseif ($d->getStatut() === 'refusée') {
                $stats['refusees']++;
            }
        }

        return $this->render('depense/index.html.twig', [
            'pagination' => $pagination,
            'depenses' => $pagination->getItems(),
            'stats' => $stats,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'statut' => $statut,
            'categorie' => $categorie,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, AuthorizationService $authService): Response
    {
        $depense = new Depense();
        
        // Get manager's teams (only teams where manager is a member with ROLE_MANAGER)
        $managerTeams = [];
        $currentPlayer = $authService->getCurrentPlayer();
        if ($currentPlayer && in_array('ROLE_MANAGER', $currentPlayer->getRoles())) {
            $managerTeams = $currentPlayer->getTeams()->getValues();
        }
        
        $form = $this->createForm(DepenseType::class, $depense, [
            'manager_teams' => $managerTeams,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // STEP 1: Form validation
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', '❌ ' . $error->getMessage());
                }
                return $this->render('depense/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // STEP 2: Custom server-side validation
            $validationErrors = [];

            // REQUIRED: Titre
            $titre = $depense->getTitre();
            if ($titre === null || $titre === '') {
                $validationErrors[] = '❌ Le titre est OBLIGATOIRE et ne peut pas être vide';
            } else {
                $titre = trim($titre);
                if ($titre === '') {
                    $validationErrors[] = '❌ Le titre est OBLIGATOIRE - Cannot be only spaces';
                } elseif (strlen($titre) < 3) {
                    $validationErrors[] = '❌ Le titre doit faire au minimum 3 caractères';
                } elseif (strlen($titre) > 255) {
                    $validationErrors[] = '❌ Le titre ne peut pas dépasser 255 caractères';
                }
            }

            // REQUIRED: Montant
            $montant = $depense->getMontant();
            if ($montant === null) {
                $validationErrors[] = '❌ Le montant est OBLIGATOIRE';
            } elseif ($montant <= 0) {
                $validationErrors[] = '❌ Le montant doit être supérieur à 0 €';
            } elseif ($montant > 999999.99) {
                $validationErrors[] = '❌ Le montant ne peut pas dépasser 999 999,99 €';
            }

            // REQUIRED: Category
            if ($depense->getCategorie() === null || $depense->getCategorie() === '') {
                $validationErrors[] = '❌ La catégorie est OBLIGATOIRE';
            } else {
                $validCategories = ['materiel', 'transport', 'logistique', 'nourriture', 'autre'];
                if (!in_array($depense->getCategorie(), $validCategories)) {
                    $validationErrors[] = '❌ Catégorie invalide sélectionnée';
                }
            }

            // REQUIRED: Team
            if ($depense->getTeam() === null) {
                $validationErrors[] = '❌ Vous devez sélectionner une équipe';
            } else {
                // Check if user can manage this team
                if (!$authService->canManageTeam($depense->getTeam())) {
                    $validationErrors[] = '❌ Vous n\'avez pas accès à cette équipe';
                }
            }

            // OPTIONAL: Description
            if (!empty($depense->getDescription())) {
                $desc = trim($depense->getDescription());
                if (strlen($desc) > 255) {
                    $validationErrors[] = '❌ La description ne peut pas dépasser 255 caractères';
                }
            }

            if (count($validationErrors) > 0) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('depense/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // STEP 3: Final validation with Symfony Validator
            $errors = $validator->validate($depense);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', '❌ ' . $error->getPropertyPath() . ': ' . $error->getMessage());
                }
                return $this->render('depense/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            try {
                // STEP 4: Save to database
                $entityManager->persist($depense);
                $entityManager->flush();

                $this->addFlash('success', '✅ Dépense de ' . number_format($depense->getMontant(), 2, ',', ' ') . '€ créée avec succès!');
                return $this->redirectToRoute('depense_index');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Une erreur est survenue lors de la création: ' . $e->getMessage());
                return $this->render('depense/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->render('depense/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Parameterized routes (must be last)
    
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Depense $depense, EntityManagerInterface $entityManager, ValidatorInterface $validator, AuthorizationService $authService): Response
    {
        // Check if user has access to this expense's team
        if (!$this->isGranted('ROLE_ADMIN') && $depense->getTeam()) {
            $authService->ensureCanManageTeam($depense->getTeam());
        }

        // Get manager's teams (only teams where manager is a member with ROLE_MANAGER)
        $managerTeams = [];
        $currentPlayer = $authService->getCurrentPlayer();
        if ($currentPlayer && in_array('ROLE_MANAGER', $currentPlayer->getRoles())) {
            $managerTeams = $currentPlayer->getTeams()->getValues();
        }

        $form = $this->createForm(DepenseType::class, $depense, [
            'manager_teams' => $managerTeams,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // STEP 1: Form validation
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', '❌ ' . $error->getMessage());
                }
                return $this->render('depense/edit.html.twig', [
                    'form' => $form->createView(),
                    'depense' => $depense,
                ]);
            }

            // STEP 2: Custom server-side validation
            $validationErrors = [];

            $titre = $depense->getTitre();
            if ($titre === null || trim($titre) === '') {
                $validationErrors[] = '❌ Le titre est OBLIGATOIRE';
            } elseif (strlen(trim($titre)) < 3) {
                $validationErrors[] = '❌ Le titre doit faire au minimum 3 caractères';
            }

            $montant = $depense->getMontant();
            if ($montant === null || $montant <= 0) {
                $validationErrors[] = '❌ Le montant doit être supérieur à 0 €';
            }

            if ($depense->getCategorie() === null) {
                $validationErrors[] = '❌ La catégorie est OBLIGATOIRE';
            }

            if ($depense->getTeam() === null) {
                $validationErrors[] = '❌ Vous devez sélectionner une équipe';
            }

            if (count($validationErrors) > 0) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('depense/edit.html.twig', [
                    'form' => $form->createView(),
                    'depense' => $depense,
                ]);
            }

            // STEP 3: Final validation
            $errors = $validator->validate($depense);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', '❌ ' . $error->getPropertyPath() . ': ' . $error->getMessage());
                }
                return $this->render('depense/edit.html.twig', [
                    'form' => $form->createView(),
                    'depense' => $depense,
                ]);
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', '✅ Dépense modifiée avec succès!');
                return $this->redirectToRoute('depense_index');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Une erreur est survenue: ' . $e->getMessage());
                return $this->render('depense/edit.html.twig', [
                    'form' => $form->createView(),
                    'depense' => $depense,
                ]);
            }
        }

        return $this->render('depense/edit.html.twig', [
            'form' => $form->createView(),
            'depense' => $depense,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Depense $depense, EntityManagerInterface $entityManager, AuthorizationService $authService): Response
    {
        // Check if user has access to this expense's team
        if (!$this->isGranted('ROLE_ADMIN') && $depense->getTeam()) {
            $authService->ensureCanManageTeam($depense->getTeam());
        }

        if ($this->isCsrfTokenValid('delete'.$depense->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($depense);
                $entityManager->flush();
                $this->addFlash('success', '✅ Dépense supprimée avec succès!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Une erreur est survenue: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('depense_index');
    }

    #[Route('/{id}/valider', name: 'valider', methods: ['POST'])]
    public function valider(Request $request, Depense $depense, EntityManagerInterface $entityManager, BudgetAlertService $budgetAlertService, AuthorizationService $authService): Response
    {
        // Allow only admin to validate expenses
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only admins can approve expenses');
        }

        if ($this->isCsrfTokenValid('valider'.$depense->getId(), $request->request->get('_token'))) {
            try {
                $depense->setStatut('validée');
                $entityManager->flush();
                
                // Check budget and send alerts if needed
                if ($depense->getTeam()) {
                    $budgetAlertService->checkBudgetAndAlert($depense->getTeam());
                }
                
                $this->addFlash('success', '✅ Dépense validée!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Une erreur est survenue: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('depense_index');
    }

    #[Route('/{id}/refuser', name: 'refuser', methods: ['POST'])]
    public function refuser(Request $request, Depense $depense, EntityManagerInterface $entityManager, AuthorizationService $authService): Response
    {
        // Allow only admin to refuse expenses
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Only admins can refuse expenses');
        }

        if ($this->isCsrfTokenValid('refuser'.$depense->getId(), $request->request->get('_token'))) {
            try {
                $depense->setStatut('refusée');
                $entityManager->flush();
                $this->addFlash('success', '✅ Dépense refusée!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ Une erreur est survenue: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('depense_index');
    }

    #[Route('/{id}/download-pdf', name: 'download_pdf', methods: ['GET'])]
    public function downloadPdf(Depense $depense): Response
    {
        // Generate PDF for a single expense
        $html = $this->renderView('depense/single_pdf.html.twig', [
            'depense' => $depense,
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'expense_' . $depense->getId() . '_' . date('Y-m-d') . '.pdf';

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    #[Route('/export-pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request, DepenseRepository $depenseRepository, AuthorizationService $authService): Response
    {
        // Paramètres de recherche et tri (mêmes que la méthode index)
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'date_creation');
        $order = $request->query->get('order', 'DESC');
        $statut = $request->query->get('statut', 'all');
        $categorie = $request->query->get('categorie', 'all');
        $minAmount = $request->query->get('min_amount', '');
        $maxAmount = $request->query->get('max_amount', '');

        // Validations
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $validSorts = ['date_creation', 'montant', 'titre', 'statut', 'categorie'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'date_creation';
        }

        // Récupérer toutes les dépenses
        $allDepenses = $depenseRepository->findBy([], [$sort => $order]);

        // Filter by team access for managers (admins see everything)
        $currentPlayer = $authService->getCurrentPlayer();

        // Filtrer (même logique que index)
        $depenses = [];
        foreach ($allDepenses as $depense) {
            // Managers can only see expenses for teams they belong to
            if (!$this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_MANAGER')) {
                if (!$depense->getTeam() || !$depense->getTeam()->getPlayers()->contains($currentPlayer)) {
                    continue;
                }
            }

            $pass = true;

            // Filtre statut
            if ($statut !== 'all' && $depense->getStatut() !== $statut) {
                $pass = false;
            }

            // Filtre catégorie
            if ($categorie !== 'all' && $depense->getCategorie() !== $categorie) {
                $pass = false;
            }

            // Filtre montant min
            if (!empty($minAmount) && is_numeric($minAmount) && $depense->getMontant() < (float)$minAmount) {
                $pass = false;
            }

            // Filtre montant max
            if (!empty($maxAmount) && is_numeric($maxAmount) && $depense->getMontant() > (float)$maxAmount) {
                $pass = false;
            }

            if ($pass) {
                $depenses[] = $depense;
            }
        }

        // Recherche par titre ou équipe
        if (!empty($search)) {
            $search = strtolower(trim($search));
            $depenses = array_filter($depenses, function($depense) use ($search) {
                $titleMatch = strpos(strtolower($depense->getTitre()), $search) !== false;
                $teamMatch = $depense->getTeam() && strpos(strtolower($depense->getTeam()->getName()), $search) !== false;
                return $titleMatch || $teamMatch;
            });
        }

        // Générer le PDF
        $html = $this->renderView('depense/export_pdf.html.twig', [
            'depenses' => $depenses,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'statut' => $statut,
            'categorie' => $categorie,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'export_date' => new \DateTime(),
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'expenses_export_' . date('Y-m-d_H-i-s') . '.pdf';

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Depense $depense, AuthorizationService $authService): Response
    {
        // Check if user has access to this expense's team
        if ($depense->getTeam()) {
            $authService->ensureCanAccessTeam($depense->getTeam());
        }

        return $this->render('depense/show.html.twig', [
            'depense' => $depense,
        ]);
    }
}
