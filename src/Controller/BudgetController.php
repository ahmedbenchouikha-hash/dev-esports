<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Depense;
use App\Entity\Player;
use App\Entity\Team;
use App\Form\BudgetType;
use App\Repository\BudgetRepository;
use App\Repository\DepenseRepository;
use App\Service\AuthorizationService;
use App\Service\BudgetAlertService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;

#[Route('/budget', name: 'budget_')]
class BudgetController extends AbstractController
{
    // Specific routes first (more specific must be before generic)
    
    #[Route('/historique', name: 'historique', methods: ['GET'])]
    public function historique(BudgetRepository $budgetRepository): Response
    {
        $budgets = $budgetRepository->findBy([], ['dateModification' => 'DESC']);
        
        return $this->render('budget/historique.html.twig', [
            'budgets' => $budgets,
        ]);
    }

    #[Route('/tracking', name: 'tracking', methods: ['GET'])]
    public function tracking(BudgetRepository $budgetRepository, DepenseRepository $depenseRepository): Response
    {
        $budgets = $budgetRepository->findBy(['statut' => 'actif']);
        
        $data = [];
        $totalAlloue = 0;
        $totalUtilise = 0;
        $alertes = [];
        
        foreach ($budgets as $budget) {
            $depenses = $depenseRepository->findBy([
                'team' => $budget->getTeam(),
                'statut' => 'validée'
            ]);
            
            $montantUtilise = 0;
            foreach ($depenses as $d) {
                $montantUtilise += $d->getMontant();
            }
            
            $budget->setMontantUtilise($montantUtilise);
            $totalAlloue += $budget->getMontantAlloue();
            $totalUtilise += $montantUtilise;
            $percentageUtilisation = $budget->getPourcentageUtilisation();
            
            $data[] = [
                'budget' => $budget,
                'pourcentage' => $percentageUtilisation,
                'restant' => $budget->getMontantRestant(),
                'depassement' => $budget->isDepassement(),
            ];
            
            if ($budget->isDepassement()) {
                $alertes[] = [
                    'team' => $budget->getTeam()->getName(),
                    'montant_depasse' => $montantUtilise - $budget->getMontantAlloue(),
                    'budget' => $budget,
                    'type' => 'depassement',
                    'pourcentage' => $percentageUtilisation,
                ];
            } elseif ($percentageUtilisation > 80) {
                $alertes[] = [
                    'team' => $budget->getTeam()->getName(),
                    'pourcentage' => $percentageUtilisation,
                    'budget' => $budget,
                    'type' => 'alerte',
                ];
            }
        }
        
        return $this->render('budget/tracking.html.twig', [
            'data' => $data,
            'totalAlloue' => $totalAlloue,
            'totalUtilise' => $totalUtilise,
            'alertes' => $alertes,
        ]);
    }

    #[Route('/alerte', name: 'alerte', methods: ['GET'])]
    public function alerte(BudgetRepository $budgetRepository, DepenseRepository $depenseRepository): Response
    {
        $budgets = $budgetRepository->findAll();
        $alertes = [];
        
        foreach ($budgets as $budget) {
            $depenses = $depenseRepository->findBy([
                'team' => $budget->getTeam(),
                'statut' => 'validée'
            ]);
            
            $montantUtilise = 0;
            foreach ($depenses as $d) {
                $montantUtilise += $d->getMontant();
            }
            
            $budget->setMontantUtilise($montantUtilise);
            
            if ($budget->isDepassement()) {
                $alertes[] = [
                    'type' => 'depassement',
                    'budget' => $budget,
                    'montant_depasse' => $montantUtilise - $budget->getMontantAlloue(),
                    'pourcentage' => $budget->getPourcentageUtilisation(),
                    'severite' => 'critique',
                ];
            } elseif ($budget->getPourcentageUtilisation() >= 90) {
                $alertes[] = [
                    'type' => 'proche_limite',
                    'budget' => $budget,
                    'pourcentage' => $budget->getPourcentageUtilisation(),
                    'severite' => 'haute',
                ];
            } elseif ($budget->getPourcentageUtilisation() >= 75) {
                $alertes[] = [
                    'type' => 'attention',
                    'budget' => $budget,
                    'pourcentage' => $budget->getPourcentageUtilisation(),
                    'severite' => 'moyenne',
                ];
            }
        }
        
        return $this->render('budget/alerte.html.twig', [
            'alertes' => $alertes,
        ]);
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(Request $request, BudgetRepository $budgetRepository, DepenseRepository $depenseRepository, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        
        // Check if user is a manager
        if (!in_array('ROLE_MANAGER', $user->getRoles())) {
            $this->addFlash('warning', 'Only managers can access the budget dashboard');
            return $this->redirectToRoute('player_dashboard');
        }
        
        // Get manager's teams
        $managerTeams = $user->getTeams();
        
        if ($managerTeams->isEmpty()) {
            $this->addFlash('warning', 'You must manage a team to access the budget dashboard');
            return $this->redirectToRoute('player_dashboard');
        }
        
        // Get budgets for manager's teams only
        $budgets = [];
        foreach ($managerTeams as $team) {
            $budget = $budgetRepository->findOneBy(['team' => $team]);
            if ($budget) {
                $budgets[] = $budget;
            }
        }
        
        // Get depenses for manager's teams only
        $allDepenses = $depenseRepository->findBy(
            ['team' => $managerTeams->toArray()],
            ['date_creation' => 'DESC']
        );
        
        // Calculate totals for manager's teams only
        $budgets_total = 0;
        $depenses_total = 0;
        
        foreach ($budgets as $budget) {
            $budgets_total += $budget->getMontantAlloue();
        }
        
        foreach ($allDepenses as $depense) {
            if ($depense->getStatut() === 'validée') {
                $depenses_total += $depense->getMontant();
            }
        }
        
        // Convert depenses to array for JSON encoding
        $depensesArray = [];
        foreach ($allDepenses as $depense) {
            $depensesArray[] = [
                'id' => $depense->getId(),
                'titre' => $depense->getTitre(),
                'description' => $depense->getDescription(),
                'montant' => floatval($depense->getMontant()),
                'categorie' => $depense->getCategorie(),
                'statut' => $depense->getStatut(),
                'date_creation' => $depense->getDateCreation() ? $depense->getDateCreation()->format('Y-m-d') : null,
                'team_id' => $depense->getTeam() ? $depense->getTeam()->getId() : null,
            ];
        }
        
        // Convert budgets to array
        $budgetsArray = [];
        foreach ($budgets as $budget) {
            $budgetsArray[] = [
                'id' => $budget->getId(),
                'montant_alloue' => floatval($budget->getMontantAlloue()),
                'montant_utilise' => floatval($budget->getMontantUtilise()),
                'team_name' => $budget->getTeam() ? $budget->getTeam()->getName() : 'Unknown',
                'team_id' => $budget->getTeam() ? $budget->getTeam()->getId() : null,
            ];
        }
        
        return $this->render('budget/dashboard_simple.html.twig', [
            'budgets' => $budgetsArray,
            'depenses' => $depensesArray,
            'budgets_total' => $budgets_total,
            'depenses_total' => $depenses_total,
            'managerTeams' => $managerTeams,
        ]);
    }

    #[Route('/team/{teamId}', name: 'by_team', methods: ['GET'])]
    public function byTeam(int $teamId, BudgetRepository $budgetRepository, DepenseRepository $depenseRepository): Response
    {
        $budget = $budgetRepository->findOneBy(['team' => $teamId]);
        
        if (!$budget) {
            throw $this->createNotFoundException('Budget not found for this team');
        }
        
        $depenses = $depenseRepository->findBy(['team' => $teamId, 'statut' => 'validée']);
        $montantUtilise = 0;
        foreach ($depenses as $d) {
            $montantUtilise += $d->getMontant();
        }
        $budget->setMontantUtilise($montantUtilise);
        
        return $this->render('budget/by_team.html.twig', [
            'budget' => $budget,
            'teamId' => $teamId,
            'depenses' => $depenses,
        ]);
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, BudgetRepository $budgetRepository, DepenseRepository $depenseRepository, AuthorizationService $authService, PaginatorInterface $paginator, BudgetAlertService $budgetAlertService, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'dateAllocation');
        $order = $request->query->get('order', 'DESC');
        $filter = $request->query->get('filter', 'all');
        $page = $request->query->getInt('page', 1);

        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $validSorts = ['dateAllocation', 'montantAlloue', 'montantUtilise', 'team'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'dateAllocation';
        }

        $allBudgets = $budgetRepository->findBy([], [$sort => $order]);
        
        // First pass: recalculate all budgets
        foreach ($allBudgets as $budget) {
            // Managers can only see budgets for teams they manage
            if (!$this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_MANAGER')) {
                if (!$authService->canManageTeam($budget->getTeam())) {
                    continue;
                }
            }
            
            // Recalculate budget usage
            $budgetAlertService->checkBudgetAndAlert($budget->getTeam());
        }
        
        // Reload budgets from database to get updated values
        $allBudgets = $budgetRepository->findBy([], [$sort => $order]);
        
        $budgets = [];
        foreach ($allBudgets as $budget) {
            // Managers can only see budgets for teams they manage
            if (!$this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_MANAGER')) {
                if (!$authService->canManageTeam($budget->getTeam())) {
                    continue; // Skip this budget, user is not a manager of this team
                }
            }
            
            if ($filter === 'all') {
                $budgets[] = $budget;
            } elseif ($filter === 'actif' && $budget->getStatut() === 'actif') {
                $budgets[] = $budget;
            } elseif ($filter === 'depassement' && $budget->isDepassement()) {
                $budgets[] = $budget;
            }
        }

        if (!empty($search)) {
            $search = strtolower(trim($search));
            $budgets = array_filter($budgets, function($budget) use ($search) {
                return strpos(strtolower($budget->getTeam()->getName()), $search) !== false;
            });
        }

        // Pagination
        $pagination = $paginator->paginate(
            $budgets,
            $page,
            3  // 3 items per page
        );

        // Get expenses data for charts
        $allExpenses = $depenseRepository->findBy([], ['date_creation' => 'DESC']);
        $expenses = [];
        foreach ($allExpenses as $exp) {
            // Managers can only see expenses for teams they manage
            if (!$this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_MANAGER')) {
                if (!$authService->canManageTeam($exp->getTeam())) {
                    continue;
                }
            }
            $expenses[] = $exp;
        }

        $stats = [
            'total_alloue' => 0,
            'total_utilise' => 0,
            'nombre_budgets' => count($budgets),
            'budgets_depassement' => 0,
        ];
        
        foreach ($budgets as $budget) {
            $stats['total_alloue'] += $budget->getMontantAlloue();
            $stats['total_utilise'] += $budget->getMontantUtilise();
            if ($budget->isDepassement()) {
                $stats['budgets_depassement']++;
            }
        }
        
        return $this->render('budget/index.html.twig', [
            'pagination' => $pagination,
            'budgets' => $pagination->getItems(),
            'stats' => $stats,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'filter' => $filter,
            'expenses' => $expenses,
            'all_budgets' => $budgets,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, BudgetRepository $budgetRepository, ValidatorInterface $validator, AuthorizationService $authService): Response
    {
        $budget = new Budget();
        
        // Get current manager and their teams
        $currentUser = $this->getUser();
        $managerTeams = [];
        
        if ($this->isGranted('ROLE_ADMIN')) {
            // Admins can create budgets for any team
            $managerTeams = $entityManager->getRepository(Team::class)->findAll();
        } elseif ($currentUser instanceof Player) {
            // Managers can only create budgets for teams they are members of
            $managerTeams = $currentUser->getTeams()->toArray();
        }
        
        // Create form with filtered teams
        $form = $this->createForm(BudgetType::class, $budget, [
            'teams' => $managerTeams
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', '❌ ' . $error->getMessage());
                }
                return $this->render('budget/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $validationErrors = [];

            $existingBudget = $budgetRepository->findOneBy([
                'team' => $budget->getTeam(),
                'statut' => 'actif'
            ]);
            
            if ($existingBudget) {
                $validationErrors[] = '❌ This team already has an active budget. Modify it or delete it before creating a new one.';
            }

            $montant = $budget->getMontantAlloue();
            if ($montant === null || $montant <= 0) {
                $validationErrors[] = '❌ Amount must be greater than 0 €';
            } elseif ($montant > 9999999.99) {
                $validationErrors[] = '❌ Amount is too high (max 9,999,999.99 €)';
            }

            if ($budget->getTeam() === null) {
                $validationErrors[] = '❌ You must select a team';
            } else {
                // Check if user has access to manage this team
                if (!$authService->canManageTeam($budget->getTeam())) {
                    $validationErrors[] = '❌ You don\'t have access to manage this team';
                }
            }

            if (count($validationErrors) > 0) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('budget/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $errors = $validator->validate($budget);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', '❌ ' . $error->getPropertyPath() . ': ' . $error->getMessage());
                }
                return $this->render('budget/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            try {
                $budget->setDateAllocation(new DateTime());
                $budget->setStatut('actif');
                $entityManager->persist($budget);
                $entityManager->flush();

                $this->addFlash('success', '✅ Budget of ' . number_format($budget->getMontantAlloue(), 2, ',', ' ') . '€ allocated to ' . $budget->getTeam()->getName() . ' successfully!');
                return $this->redirectToRoute('budget_index');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
                return $this->render('budget/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->render('budget/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Budget $budget, DepenseRepository $depenseRepository, AuthorizationService $authService): Response
    {
        // Managers can only view budgets for their teams
        if (!$this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_MANAGER')) {
            if (!$authService->canManageTeam($budget->getTeam())) {
                throw $this->createAccessDeniedException('You can only view budgets for teams you manage');
            }
        }

        $depenses = $depenseRepository->findBy([
            'team' => $budget->getTeam(),
            'statut' => 'validée'
        ]);
        
        $montantUtilise = 0;
        foreach ($depenses as $d) {
            $montantUtilise += $d->getMontant();
        }
        $budget->setMontantUtilise($montantUtilise);
        
        return $this->render('budget/show.html.twig', [
            'budget' => $budget,
            'depenses' => $depenses,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Budget $budget, EntityManagerInterface $entityManager, ValidatorInterface $validator, AuthorizationService $authService): Response
    {
        // Check if user has access to this budget's team
        if (!$this->isGranted('ROLE_ADMIN')) {
            $authService->ensureCanManageTeam($budget->getTeam());
        }

        $form = $this->createForm(BudgetType::class, $budget);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', '❌ ' . $error->getMessage());
                }
                return $this->render('budget/edit.html.twig', [
                    'form' => $form->createView(),
                    'budget' => $budget,
                ]);
            }

            $validationErrors = [];

            if ($budget->getMontantAlloue() === null || $budget->getMontantAlloue() <= 0) {
                $validationErrors[] = '❌ Amount must be greater than 0 €';
            }

            if ($budget->getTeam() === null) {
                $validationErrors[] = '❌ You must select a team';
            }

            if (count($validationErrors) > 0) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('budget/edit.html.twig', [
                    'form' => $form->createView(),
                    'budget' => $budget,
                ]);
            }

            $errors = $validator->validate($budget);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', '❌ ' . $error->getPropertyPath() . ': ' . $error->getMessage());
                }
                return $this->render('budget/edit.html.twig', [
                    'form' => $form->createView(),
                    'budget' => $budget,
                ]);
            }

            try {
                $budget->setDateModification(new DateTime());
                
                if ($budget->isDepassement()) {
                    $budget->setStatut('dépassé');
                } else {
                    $budget->setStatut('actif');
                }
                
                $entityManager->flush();
                $this->addFlash('success', '✅ Budget updated successfully!');
                return $this->redirectToRoute('budget_show', ['id' => $budget->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
                return $this->render('budget/edit.html.twig', [
                    'form' => $form->createView(),
                    'budget' => $budget,
                ]);
            }
        }

        return $this->render('budget/edit.html.twig', [
            'budget' => $budget,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Budget $budget, EntityManagerInterface $entityManager, AuthorizationService $authService): Response
    {
        // Check if user has access to this budget's team
        if (!$this->isGranted('ROLE_ADMIN')) {
            $authService->ensureCanManageTeam($budget->getTeam());
        }

        if ($this->isCsrfTokenValid('delete'.$budget->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($budget);
                $entityManager->flush();
                $this->addFlash('success', '✅ Budget deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('budget_index');
    }
}
