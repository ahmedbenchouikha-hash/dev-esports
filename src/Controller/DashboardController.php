<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(EntityManagerInterface $em): Response
    {
        $userCount = $em->getRepository(User::class)->count([]);

        return $this->render('dashboard/admin.html.twig', [
            'userCount' => $userCount,
        ]);
    }

    #[Route('/user/dashboard', name: 'user_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function user(): Response
    {
        $user = $this->getUser();

        return $this->render('dashboard/user.html.twig', [
            'user' => $user,
        ]);
    }
}
