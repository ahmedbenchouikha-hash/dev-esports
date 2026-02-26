<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\PasswordResetToken;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        RouterInterface $router
    ): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $resetToken = new PasswordResetToken();
                $resetToken->setUser($user);
                $resetToken->setToken($token);
                $resetToken->setCreatedAt(new \DateTime());
                $resetToken->setExpiresAt(new \DateTime('+1 hour'));

                $em->persist($resetToken);
                $em->flush();

                // Send reset email
                $resetLink = $router->generate('app_reset_password', ['token' => $token], RouterInterface::ABSOLUTE_URL);
                
                $emailMessage = (new Email())
                    ->from('noreply@esports-dev.local')
                    ->to($user->getEmail())
                    ->subject('Password Reset Request')
                    ->html($this->renderView('security/email/password_reset.html.twig', [
                        'user' => $user,
                        'resetLink' => $resetLink,
                    ]));

                $mailer->send($emailMessage);

                $this->addFlash('success', 'If an account exists with this email, a password reset link has been sent.');
            } else {
                // Still show success message for security (don't reveal if email exists)
                $this->addFlash('success', 'If an account exists with this email, a password reset link has been sent.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }
}
