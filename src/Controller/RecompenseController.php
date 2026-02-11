<?php

namespace App\Controller;

use App\Entity\Recompense;
use App\Entity\DemandeRecompense;
use App\Form\RecompenseType;
use App\Form\DemandeRecompenseType;
use App\Repository\RecompenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/recompense')]
class RecompenseController extends AbstractController
{
    #[Route('', name: 'recompense_index', methods: ['GET'])]
    public function index(Request $request, RecompenseRepository $recompenseRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        // Récupérer les récompenses avec recherche et tri
        $recompenses = $recompenseRepository->searchAndSort($search, $sort);

        // Calculer les statistiques
        $minClassement = null;
        $maxClassement = null;
        
        if (count($recompenses) > 0) {
            $classements = array_map(fn($r) => $r->getClassement(), $recompenses);
            $minClassement = min($classements);
            $maxClassement = max($classements);
        }

        return $this->render('recompense/list.html.twig', [
            'recompenses' => $recompenses,
            'search' => $search,
            'sort' => $sort,
            'minClassement' => $minClassement,
            'maxClassement' => $maxClassement,
        ]);
    }

    #[Route('/new', name: 'recompense_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $recompense = new Recompense();
        $form = $this->createForm(RecompenseType::class, $recompense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation PHP côté serveur
            $errors = $validator->validate($recompense);

            if (count($errors) === 0) {
                $entityManager->persist($recompense);
                $entityManager->flush();
                $this->addFlash('success', 'Récompense ajoutée avec succès.');
                return $this->redirectToRoute('recompense_index');
            } else {
                // Ajouter les erreurs de validation au formulaire
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('recompense/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/export/pdf', name: 'recompense_export_pdf', methods: ['GET'])]
    public function exportPdf(RecompenseRepository $recompenseRepository): Response
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
        
        // Générer le contenu HTML
        $html = $this->renderView('recompense/export_pdf.html.twig', [
            'recompenses' => $recompenses,
            'minClassement' => $minClassement,
            'maxClassement' => $maxClassement,
        ]);
        
        // Retourner en tant que HTML pour téléchargement
        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="Rapport-Recompenses-' . date('Y-m-d-H-i-s') . '.html"',
        ]);
    }

    #[Route('/{id}/edit', name: 'recompense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recompense $recompense, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(RecompenseType::class, $recompense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation PHP côté serveur
            $errors = $validator->validate($recompense);

            if (count($errors) === 0) {
                $entityManager->flush();
                $this->addFlash('success', 'Récompense modifiée avec succès.');
                return $this->redirectToRoute('recompense_index');
            } else {
                // Ajouter les erreurs de validation au flash
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('recompense/edit.html.twig', [
            'form' => $form->createView(),
            'recompense' => $recompense,
        ]);
    }

    #[Route('/{id}', name: 'recompense_show', methods: ['GET'])]
    public function show(Recompense $recompense): Response
    {
        return $this->render('recompense/show.html.twig', [
            'recompense' => $recompense,
        ]);
    }

    #[Route('/{id}/delete', name: 'recompense_delete', methods: ['POST'])]
    public function delete(Request $request, Recompense $recompense, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$recompense->getId(), $request->request->get('_token'))) {
            $entityManager->remove($recompense);
            $entityManager->flush();
            $this->addFlash('success', 'Récompense supprimée.');
        }

        return $this->redirectToRoute('recompense_index');
    }

    #[Route('/{id}/demande', name: 'demande_new', methods: ['GET', 'POST'])]
    public function demandeNew(Request $request, Recompense $recompense, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $demande = new DemandeRecompense();
        $demande->setRecompense($recompense);

        $form = $this->createForm(DemandeRecompenseType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $errors = $validator->validate($demande);

            if (count($errors) === 0) {
                $entityManager->persist($demande);
                $entityManager->flush();
                $this->addFlash('success', 'Demande envoyée avec succès.');
                return $this->redirectToRoute('recompense_index');
            }
        }

        return $this->render('recompense/demande_new.html.twig', [
            'form' => $form->createView(),
            'recompense' => $recompense,
        ]);
    }
}
