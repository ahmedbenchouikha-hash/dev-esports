<?php

/**
 * Example Controller: Comment intégrer les 3 Bundles ensemble
 * 
 * Ce fichier montre l'intégration complète:
 * - VichUploaderBundle (Upload documents)
 * - KnpPaginatorBundle (Pagination dépenses)
 * - ChartJS (Graphiques - Twig templates)
 */

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\Depense;
use App\Entity\Team;
use App\Form\BudgetType;
use App\Form\DepenseType;
use App\Repository\BudgetRepository;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BudgetDashboardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private BudgetRepository $budgetRepo,
        private DepenseRepository $depenseRepo,
        private PaginatorInterface $paginator
    ) {}

    /**
     * 🎯 DASHBOARD COMPLET
     * - Affiche tous les graphiques Chart.js
     * - Liste paginée des dépenses (KnpPaginator)
     * - KPI cards
     */
    #[Route('/budget/dashboard', name: 'budget_dashboard')]
    public function dashboard(Request $request): Response
    {
        // Récupérer les budgets
        $budgets = $this->budgetRepo->findAll();
        
        // Récupérer TOUTES les dépenses (pour graphiques)
        $depenses = $this->depenseRepo->findAll();
        
        // ✅ KnpPaginator: Paginer les dépenses pour le tableau
        $query = $this->em->getRepository(Depense::class)
            ->createQueryBuilder('d')
            ->orderBy('d.date_creation', 'DESC');
        
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10  // 10 dépenses par page
        );

        // Calculer les KPI
        $budgetsTotal = array_reduce($budgets, fn($sum, $b) => $sum + $b->getMontantAlloue(), 0);
        $depensesTotal = array_reduce($depenses, fn($sum, $d) => $sum + $d->getMontant(), 0);

        return $this->render('budget/dashboard_complete.html.twig', [
            // Données pour graphiques
            'budgets' => $budgets,
            'depenses' => $depenses,
            
            // Données paginées pour tableau
            'pagination' => $pagination,
            
            // KPI cards
            'budgets_total' => $budgetsTotal,
            'depenses_total' => $depensesTotal,
        ]);
    }

    /**
     * 💾 CRÉER BUDGET avec upload (VichUploader)
     */
    #[Route('/budget/new', name: 'budget_new', methods: ['GET', 'POST'])]
    public function newBudget(Request $request): Response
    {
        $budget = new Budget();
        $form = $this->createForm(BudgetType::class, $budget);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ VichUploader gère automatiquement le fichier!
            // Si un fichier justificatif a été uploadé, VichUploader l'a déjà déplacé
            
            $this->em->persist($budget);
            $this->em->flush();

            $this->addFlash('success', 'Budget créé avec succès!');
            return $this->redirectToRoute('budget_show', ['id' => $budget->getId()]);
        }

        return $this->render('budget/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * 📊 AFFICHER UN BUDGET avec graphique individual
     */
    #[Route('/budget/{id}', name: 'budget_show')]
    public function show(Budget $budget): Response
    {
        // Récupérer les dépenses associées
        $depenses = $this->em->getRepository(Depense::class)
            ->findBy(['team' => $budget->getTeam()]);

        return $this->render('budget/show.html.twig', [
            'budget' => $budget,
            'depenses' => $depenses,
            // Les templates Twig chargeront les graphiques automatiquement
        ]);
    }

    /**
     * 📋 LISTE DES DÉPENSES avec pagination
     * ✅ KnpPaginator
     */
    #[Route('/depenses', name: 'depense_list')]
    public function listDepenses(Request $request): Response
    {
        // Créer la query
        $query = $this->em->getRepository(Depense::class)
            ->createQueryBuilder('d')
            ->orderBy('d.date_creation', 'DESC')
            ->getQuery();

        // ✅ Paginer avec KnpPaginator
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15  // 15 items par page
        );

        return $this->render('depense/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * ✏️ CRÉER DÉPENSE avec upload facture (VichUploader)
     */
    #[Route('/depense/new', name: 'depense_new', methods: ['GET', 'POST'])]
    public function newDepense(Request $request): Response
    {
        $depense = new Depense();
        $form = $this->createForm(DepenseType::class, $depense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ VichUploader gère automatiquement l'upload de facture
            $this->em->persist($depense);
            $this->em->flush();

            $this->addFlash('success', 'Dépense créée avec succès!');
            return $this->redirectToRoute('depense_show', ['id' => $depense->getId()]);
        }

        return $this->render('depense/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * 📊 GRAPHIQUE: Budget vs Dépenses par Équipe
     * Retourne JSON pour un graphique personnalisé
     */
    #[Route('/budget/chart/teams', name: 'budget_chart_teams', methods: ['GET'])]
    public function chartTeams(): Response
    {
        $budgets = $this->budgetRepo->findAll();

        $labels = [];
        $allocated = [];
        $used = [];

        foreach ($budgets as $budget) {
            $labels[] = $budget->getTeam()->getName();
            $allocated[] = $budget->getMontantAlloue();
            $used[] = $budget->getMontantUtilise();
        }

        return $this->json([
            'labels' => $labels,
            'allocated' => $allocated,
            'used' => $used,
        ]);
    }

    /**
     * 📊 GRAPHIQUE: Dépenses par Catégorie
     * Retourne JSON pour un graphique personnalisé
     */
    #[Route('/budget/chart/categories', name: 'budget_chart_categories', methods: ['GET'])]
    public function chartCategories(): Response
    {
        $depenses = $this->depenseRepo->findAll();

        $categories = [];
        foreach ($depenses as $depense) {
            $cat = $depense->getCategorie() ?? 'Sans catégorie';
            if (!isset($categories[$cat])) {
                $categories[$cat] = 0;
            }
            $categories[$cat] += $depense->getMontant();
        }

        return $this->json([
            'labels' => array_keys($categories),
            'data' => array_values($categories),
        ]);
    }

    /**
     * 📊 STATISTIQUES: Pour API/AJAX
     */
    #[Route('/budget/stats', name: 'budget_stats', methods: ['GET'])]
    public function getStats(): Response
    {
        $budgets = $this->budgetRepo->findAll();
        $depenses = $this->depenseRepo->findAll();

        $budgetsTotal = array_reduce($budgets, fn($sum, $b) => $sum + $b->getMontantAlloue(), 0);
        $depensesTotal = array_reduce($depenses, fn($sum, $d) => $sum + $d->getMontant(), 0);
        $remaining = $budgetsTotal - $depensesTotal;

        return $this->json([
            'total_allocated' => $budgetsTotal,
            'total_used' => $depensesTotal,
            'remaining' => $remaining,
            'percentage_used' => $budgetsTotal > 0 ? round(($depensesTotal / $budgetsTotal) * 100, 2) : 0,
        ]);
    }
}

/**
 * ═══════════════════════════════════════════════════════════════════
 * 
 * 🎯 RÉSUMÉ: INTÉGRATION DES 3 BUNDLES DANS CE CONTROLLER
 * 
 * 1️⃣ VichUploaderBundle
 *    - newBudget(): Upload justificatif géré automatiquement
 *    - newDepense(): Upload facture géré automatiquement
 *    - N'apparaît pas directement dans le code!
 *    - Fonctionne via Form Type Configuration
 * 
 * 2️⃣ KnpPaginatorBundle
 *    - dashboard(): Pagine la liste des dépenses (10 par page)
 *    - listDepenses(): Affiche tous les dépenses avec pagination (15 par page)
 *    - $this->paginator->paginate() fait tout le travail
 * 
 * 3️⃣ ChartJS
 *    - Les graphiques sont chargés dans les templates Twig
 *    - `templates/budget/dashboard_complete.html.twig`
 *    - ChartJS fonctionne automatiquement via CDN
 * 
 * ═══════════════════════════════════════════════════════════════════
 * 
 * 📝 HOW TO USE THIS CONTROLLER:
 * 
 * 1. Créer les routes dans config/routes.yaml:
 *    BudgetDashboardController: 
 *        resource: BudgetDashboardController
 *        type: attribute
 * 
 * 2. Créer les templates:
 *    - templates/budget/dashboard_complete.html.twig
 *    - templates/budget/show.html.twig
 *    - templates/depense/list.html.twig
 *    - templates/depense/new.html.twig
 * 
 * 3. Tester:
 *    - http://localhost:8000/budget/dashboard
 *    - http://localhost:8000/depenses
 *    - http://localhost:8000/budget/new
 * 
 */
