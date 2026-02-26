<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/equipe')]
class EquipeController extends AbstractController
{
    #[Route('/', name: 'equipe_index')]
    public function index(Request $request, EquipeRepository $repo): Response
    {
        $search = $request->query->get('search');
        
        // 1. On récupère le total réel en base (pour ton widget RankUp)
        $totalTeams = $repo->count([]); 

        // 2. Gestion de la recherche ou affichage simple
        if ($search) {
            $equipes = $repo->createQueryBuilder('e')
                ->where('e.nom LIKE :search OR e.jeu LIKE :search')
                ->setParameter('search', '%'.$search.'%')
                ->getQuery()
                ->getResult();
        } else {
            $equipes = $repo->findAll();
        }

        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipes,
            'search' => $search,
            'totalTeams' => $totalTeams // Variable envoyée au widget dans base.html.twig
        ]);
    }

    #[Route('/new', name: 'equipe_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload logo
            $file = $form->get('logo')->getData();
            if ($file) {
                $filename = uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('logos_directory'), $filename);
                $equipe->setLogo($filename);
            }

            // Gestion des membres (Conversion String -> Array)
            $membersString = $form->get('membres')->getData();
            if ($membersString) {
                $membersArray = array_filter(array_map('trim', explode(',', $membersString)));
                $equipe->setMembres($membersArray);
            }

            $em->persist($equipe);
            $em->flush();

            $this->addFlash('success', 'Équipe créée avec succès !');
            return $this->redirectToRoute('equipe_index');
        }

        return $this->render('equipe/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'equipe_edit')]
    public function edit(Request $request, Equipe $equipe, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe);
        
        // Pré-remplir le champ "membres" (Array -> String pour le formulaire)
        if ($equipe->getMembres()) {
            $form->get('membres')->setData(implode(', ', $equipe->getMembres()));
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('logo')->getData();
            if ($file) {
                $filename = uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('logos_directory'), $filename);
                $equipe->setLogo($filename);
            }

            // Mises à jour membres (String -> Array)
            $membersString = $form->get('membres')->getData();
            if ($membersString) {
                $membersArray = array_filter(array_map('trim', explode(',', $membersString)));
                $equipe->setMembres($membersArray);
            }

            $em->flush();
            return $this->redirectToRoute('equipe_index');
        }

        return $this->render('equipe/edit.html.twig', [
            'form' => $form->createView(),
            'equipe' => $equipe
        ]);
    }

    #[Route('/{id}/delete', name: 'equipe_delete', methods:['POST'])]
    public function delete(Request $request, Equipe $equipe, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipe->getId(), $request->request->get('_token'))) {
            $em->remove($equipe);
            $em->flush();
        }
        return $this->redirectToRoute('equipe_index');
    }

    #[Route('/{id}/approve', name:'equipe_approve')]
    public function approve(Equipe $equipe, EntityManagerInterface $em): Response
    {
        $equipe->setStatut('approuvé');
        $equipe->setDateValidation(new \DateTime());
        $em->flush();
        return $this->redirectToRoute('equipe_index');
    }

    #[Route('/{id}/refuse', name:'equipe_refuse')]
    public function refuse(Equipe $equipe, EntityManagerInterface $em): Response
    {
        $equipe->setStatut('refusé');
        $equipe->setDateValidation(new \DateTime());
        $em->flush();
        return $this->redirectToRoute('equipe_index');
    }
}
