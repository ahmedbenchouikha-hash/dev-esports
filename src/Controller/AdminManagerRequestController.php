<?php

namespace App\Controller;

use App\Entity\ManagerRequest;
use App\Form\ManagerRequestReviewType;
use App\Repository\ManagerRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/manager-requests', name: 'admin_manager_')]
#[IsGranted('ROLE_ADMIN')]
class AdminManagerRequestController extends AbstractController
{
    #[Route('', name: 'requests_list', methods: ['GET'])]
    public function list(ManagerRequestRepository $repository): Response
    {
        $pendingRequests = $repository->findBy(['status' => 'pending'], ['createdAt' => 'DESC']);
        $reviewedRequests = $repository->findBy(
            ['status' => ['approved', 'rejected']],
            ['reviewedAt' => 'DESC'],
            20
        );

        return $this->render('admin_manager/list.html.twig', [
            'pendingRequests' => $pendingRequests,
            'reviewedRequests' => $reviewedRequests,
        ]);
    }

    #[Route('/{id}/review', name: 'review', methods: ['GET', 'POST'])]
    public function review(
        ManagerRequest $managerRequest,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Check if already reviewed
        if ($managerRequest->getStatus() !== 'pending') {
            $this->addFlash('warning', 'This request has already been reviewed.');
            return $this->redirectToRoute('admin_manager_requests_list');
        }

        $form = $this->createForm(ManagerRequestReviewType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->get('status')->getData();
            $adminComment = $form->get('adminComment')->getData();

            $managerRequest->setStatus($status);
            $managerRequest->setAdminComment($adminComment);
            /** @var \App\Entity\User $reviewer */
            $reviewer = $this->getUser();
            $managerRequest->setReviewedBy($reviewer);
            $managerRequest->setReviewedAt(new \DateTime());

            // If approved, add ROLE_MANAGER to the player
            if ($status === 'approved') {
                $player = $managerRequest->getPlayer();
                $roles = $player->getRoles();
                if (!in_array('ROLE_MANAGER', $roles)) {
                    $roles[] = 'ROLE_MANAGER';
                    $player->setRoles($roles);
                }
            }

            $em->flush();

            $resultMessage = $status === 'approved'
                ? 'Manager request approved! The player can now manage teams and budgets.'
                : 'Manager request rejected. The admin comment has been saved.';

            $this->addFlash('success', $resultMessage);
            return $this->redirectToRoute('admin_manager_requests_list');
        }

        return $this->render('admin_manager/review.html.twig', [
            'managerRequest' => $managerRequest,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/revoke', name: 'revoke', methods: ['POST'])]
    public function revoke(
        ManagerRequest $managerRequest,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('revoke_' . $managerRequest->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_manager_requests_list');
        }

        // Only revoke if previously approved
        if ($managerRequest->getStatus() !== 'approved') {
            $this->addFlash('error', 'Can only revoke approved requests.');
            return $this->redirectToRoute('admin_manager_requests_list');
        }

        $player = $managerRequest->getPlayer();
        $roles = $player->getRoles();

        // Remove ROLE_MANAGER
        $roles = array_filter($roles, function($role) {
            return $role !== 'ROLE_MANAGER';
        });
        $player->setRoles(array_values($roles));

        $em->flush();

        $this->addFlash('success', 'Manager role has been revoked from the player.');
        return $this->redirectToRoute('admin_manager_requests_list');
    }
}
