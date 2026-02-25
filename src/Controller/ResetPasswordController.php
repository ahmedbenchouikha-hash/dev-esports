<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        PasswordResetTokenRepository $tokenRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response
    {
        // Find the token
        $resetToken = $tokenRepository->findOneBy(['token' => $token]);

        if (!$resetToken || $resetToken->isExpired()) {
            $this->addFlash('danger', 'Invalid or expired password reset link. Please request a new one.');
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');
            $confirmPassword = $request->request->get('password_confirm');

            if (!$newPassword || strlen($newPassword) < 8) {
                $this->addFlash('danger', 'Password must be at least 8 characters long.');
            } elseif ($newPassword !== $confirmPassword) {
                $this->addFlash('danger', 'Passwords do not match.');
            } else {
                // Update the password
                $user = $resetToken->getUser();
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                // Delete the token
                $em->remove($resetToken);
                $em->flush();

                $this->addFlash('success', 'Your password has been reset successfully! You can now log in with your new password.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}
