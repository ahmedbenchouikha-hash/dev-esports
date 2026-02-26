<?php

namespace App\Controller;

use App\Entity\Recompense;
use App\Entity\DemandeRecompense;
use App\Entity\Tournament;
use App\Entity\User;
use App\Form\RecompenseType;
use App\Form\DemandeRecompenseType;
use App\Repository\RecompenseRepository;
use App\Repository\TournamentRepository;
use App\Service\PdfGenerator;
use App\Service\AIRewardAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/recompense')]
class RecompenseController extends AbstractController
{
    #[Route('', name: 'recompense_index', methods: ['GET'])]
    public function index(
        Request $request,
        RecompenseRepository $recompenseRepository,
        TournamentRepository $tournamentRepository
    ): Response
    {
        $searchName = $request->query->get('searchName');
        $searchType = $request->query->get('searchType');
        $searchTournament = $request->query->get('searchTournament');
        $sort = $request->query->get('sort');

        // Récupérer toutes les récompenses filtrées
        $recompenses = $recompenseRepository->searchByNameAndType($searchName, $searchType, $searchTournament, $sort);
        
        // Récupérer tous les tournois pour le filtre
        $tournaments = $tournamentRepository->findAll();

        // Calculer les statistiques
        $minClassement = null;
        $maxClassement = null;
        
        if (count($recompenses) > 0) {
            $classements = array_map(fn($r) => $r->getClassement(), $recompenses);
            $minClassement = min($classements);
            $maxClassement = max($classements);
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');

        return $this->render('recompense/list.html.twig', [
            'recompenses' => $recompenses,
            'tournaments' => $tournaments,
            'searchName' => $searchName,
            'searchType' => $searchType,
            'searchTournament' => $searchTournament,
            'sort' => $sort,
            'minClassement' => $minClassement,
            'maxClassement' => $maxClassement,
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/modal/form', name: 'recompense_modal_form', methods: ['GET', 'POST'])]
    public function getModalForm(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        TournamentRepository $tournamentRepository
    ): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Acces refuse'], 403);
        }
        $action = $request->query->get('action'); // 'create' ou 'edit'
        $id = $request->query->get('id');
        $tournamentId = $request->query->get('tournamentId');

        $recompense = $action === 'edit' 
            ? $entityManager->getRepository(Recompense::class)->find($id) 
            : new Recompense();

        if ($action === 'edit' && !$recompense) {
            return new JsonResponse(['error' => 'Récompense non trouvée'], 404);
        }

        // Si création, définir le tournoi
        if ($action === 'create' && $tournamentId) {
            $tournament = $tournamentRepository->find($tournamentId);
            if ($tournament) {
                $recompense->setTournament($tournament);
            }
        }

        $form = $this->createForm(RecompenseType::class, $recompense);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $errors = $validator->validate($recompense);

                if (count($errors) === 0) {
                    $entityManager->persist($recompense);
                    $entityManager->flush();
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Récompense ' . ($action === 'edit' ? 'modifiée' : 'ajoutée') . ' avec succès'
                    ]);
                } else {
                    $errorMessages = [];
                    foreach ($errors as $error) {
                        $errorMessages[] = $error->getMessage();
                    }
                    return new JsonResponse(['errors' => $errorMessages], 400);
                }
            }
        }

        $formView = $form->createView();
        $formHtml = $this->renderView('recompense/_form_modal.html.twig', [
            'form' => $formView,
            'action' => $action,
            'recompense' => $recompense,
        ]);

        return new JsonResponse(['html' => $formHtml]);
    }

    #[Route('/modal/delete', name: 'recompense_modal_delete', methods: ['POST'])]
    public function deleteViaModal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Acces refuse'], 403);
        }
        $id = $request->request->get('id');
        $token = $request->request->get('_token');

        if (!$id || !$token) {
            return new JsonResponse(['error' => 'Paramètres manquants'], 400);
        }

        if (!$this->isCsrfTokenValid('recompense_delete', $token)) {
            return new JsonResponse(['error' => 'Token CSRF invalide'], 403);
        }

        $recompense = $entityManager->getRepository(Recompense::class)->find($id);

        if (!$recompense) {
            return new JsonResponse(['error' => 'Récompense non trouvée'], 404);
        }

        try {
            $entityManager->remove($recompense);
            $entityManager->flush();
            return new JsonResponse(['success' => true, 'message' => 'Récompense supprimée avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/export/pdf', name: 'recompense_export_pdf', methods: ['GET'])]
    public function exportPdf(
        RecompenseRepository $recompenseRepository,
        PdfGenerator $pdfGenerator
    ): Response
    {
        $recompenses = $recompenseRepository->findAllOrderedByClassement();
        
        // Calculer les statistiques
        $minClassement = null;
        $maxClassement = null;
        
        if (count($recompenses) > 0) {
            $classements = array_map(fn($r) => $r->getClassement(), $recompenses);
            $minClassement = min($classements);
            $maxClassement = max($classements);
        }
        
        // Générer le contenu HTML du PDF
        $html = $this->renderView('recompense/export_pdf.html.twig', [
            'recompenses' => $recompenses,
            'minClassement' => $minClassement,
            'maxClassement' => $maxClassement,
        ]);
        
        // Générer le PDF
        $pdfContent = $pdfGenerator->generatePdf($html);
        
        // Retourner le PDF téléchargeable
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Rapport-Recompenses-' . date('Y-m-d-H-i-s') . '.pdf"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    #[Route('/new', name: 'recompense_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $recompense = new Recompense();
        $form = $this->createForm(RecompenseType::class, $recompense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($recompense);
            if (count($errors) === 0) {
                $entityManager->persist($recompense);
                $entityManager->flush();
                $this->addFlash('success', 'Récompense ajoutée avec succès.');
                return $this->redirectToRoute('recompense_index');
            }
        }

        return $this->render('recompense/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'recompense_show', methods: ['GET'])]
    public function show(Recompense $recompense): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        return $this->render('recompense/show.html.twig', [
            'recompense' => $recompense,
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/{id}/edit', name: 'recompense_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Recompense $recompense, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(RecompenseType::class, $recompense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($recompense);
            if (count($errors) === 0) {
                $entityManager->flush();
                $this->addFlash('success', 'Récompense modifiée avec succès.');
                return $this->redirectToRoute('recompense_show', ['id' => $recompense->getId()]);
            }
        }

        return $this->render('recompense/edit.html.twig', [
            'form' => $form->createView(),
            'recompense' => $recompense,
        ]);
    }

    #[Route('/{id}', name: 'recompense_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Recompense $recompense, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recompense->getId(), $request->request->get('_token'))) {
            $entityManager->remove($recompense);
            $entityManager->flush();
            $this->addFlash('success', 'Récompense supprimée.');
        }
        return $this->redirectToRoute('recompense_index');
    }

}
