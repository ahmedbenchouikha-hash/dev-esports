<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PlayerPendingApprovalController extends AbstractController
{
    #[Route('/player/pending-approval', name: 'player_pending_approval')]
    public function pending(): Response
    {
        $user = $this->getUser();
        
        // Redirect if already approved
        if ($user->isApproved()) {
            return $this->redirectToRoute('player_dashboard');
        }
        
        // Redirect if rejected
        if ($user->isRejected()) {
            return $this->redirectToRoute('player_rejected');
        }

        return $this->render('player/pending_approval.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/player/rejected', name: 'player_rejected')]
    public function rejected(): Response
    {
        $user = $this->getUser();
        
        // Redirect if somehow not rejected
        if (!$user->isRejected()) {
            return $this->redirectToRoute('player_dashboard');
        }

        return $this->render('player/rejected.html.twig', [
            'user' => $user,
        ]);
    }
}
