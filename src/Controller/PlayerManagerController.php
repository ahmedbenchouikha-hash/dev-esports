<?php

namespace App\Controller;

use App\Entity\ManagerRequest;
use App\Entity\Player;
use App\Form\ManagerRequestType;
use App\Repository\ManagerRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/player/manager')]
#[IsGranted('ROLE_USER')]
class PlayerManagerController extends AbstractController
{
    #[Route('/request', name: 'player_manager_request', methods: ['GET', 'POST'])]
    public function requestManager(
        Request $request,
        EntityManagerInterface $em,
        ManagerRequestRepository $repository
    ): Response {
        $user = $this->getUser();

        // Check if user is already a manager
        if (in_array('ROLE_MANAGER', $user->getRoles())) {
            $this->addFlash('info', 'You are already a manager!');
            return $this->redirectToRoute('player_dashboard');
        }

        // Only allow Players to submit manager requests
        if (!$user instanceof Player) {
            $this->addFlash('error', 'Only players can submit manager requests. Please contact an administrator.');
            return $this->redirectToRoute('home');
        }

        // Check if user already has a pending request
        $existingRequest = $repository->findPendingByPlayer($user);
        if ($existingRequest) {
            $this->addFlash('warning', 'You already have a pending manager request. Please wait for admin approval.');
            return $this->redirectToRoute('player_manager_status');
        }

        // Create a new manager request with the player
        $managerRequest = new ManagerRequest();
        $managerRequest->setPlayer($user);

        $form = $this->createForm(ManagerRequestType::class, $managerRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($managerRequest);
            $em->flush();

            $this->addFlash('success', 'Your manager request has been submitted! An admin will review it soon.');
            return $this->redirectToRoute('player_manager_status');
        }

        return $this->render('player_manager/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/status', name: 'player_manager_status', methods: ['GET'])]
    public function status(
        ManagerRequestRepository $repository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        // Only allow Players to view status
        if (!$user instanceof Player) {
            $this->addFlash('error', 'Only players can view manager request status.');
            return $this->redirectToRoute('home');
        }

        $requests = $repository->findByPlayer($user);

        return $this->render('player_manager/status.html.twig', [
            'requests' => $requests,
            'isManager' => in_array('ROLE_MANAGER', $user->getRoles()),
        ]);
    }
}
