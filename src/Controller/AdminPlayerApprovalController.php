<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/players', name: 'admin_players_')]
#[IsGranted('ROLE_ADMIN')]
class AdminPlayerApprovalController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(UserRepository $userRepository): Response
    {
        // Get all pending users
        $pendingUsers = $userRepository->findBy([
            'approvalStatus' => 'pending',
        ], ['id' => 'DESC']);

        // Filter for ROLE_USER only (players)
        $pendingPlayers = array_filter($pendingUsers, function($user) {
            return in_array('ROLE_USER', $user->getRoles());
        });

        return $this->render('admin/player_approval.html.twig', [
            'pendingPlayers' => $pendingPlayers,
        ]);
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(int $id, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Player not found');
        }

        $user->setApprovalStatus('approved');
        $em->flush();

        $this->addFlash('success', 'Player ' . $user->getUsername() . ' has been approved!');
        return $this->redirectToRoute('admin_players_list');
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(int $id, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Player not found');
        }

        $user->setApprovalStatus('rejected');
        $em->flush();

        $this->addFlash('warning', 'Player ' . $user->getUsername() . ' has been rejected.');
        return $this->redirectToRoute('admin_players_list');
    }
}
