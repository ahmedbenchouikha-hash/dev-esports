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
        $pendingPlayers = $userRepository->findBy([
            'roles' => '["ROLE_USER"]',
            'approvalStatus' => 'pending',
        ]);

        // Workaround for Doctrine JSON filtering
        $pendingPlayers = $userRepository->createQueryBuilder('u')
            ->where("JSON_CONTAINS(u.roles, '\"ROLE_USER\"') = 1")
            ->andWhere("u.approvalStatus = 'pending'")
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult();

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
