<?php

namespace App\Controller;

<<<<<<< HEAD
use App\Entity\User;
use App\Entity\PasswordResetToken;
use App\Repository\UserRepository;
=======
use App\Form\ForgotPasswordRequestType;
use App\Repository\UserRepository;
use App\Entity\PasswordResetToken;
>>>>>>> module-user
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
<<<<<<< HEAD
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
=======
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mime\Address;

class ForgotPasswordController extends AbstractController
{
    public function __construct(private readonly HttpClientInterface $httpClient, private readonly UserRepository $userRepo, private readonly EntityManagerInterface $em, private readonly MailerInterface $mailer, private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ForgotPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];

            // Recaptcha server-side verification (optional, requires RECAPTCHA_SECRET)
            $recaptcha = $request->request->get('g-recaptcha-response');
            if ($recaptcha) {
                $secret = getenv('RECAPTCHA_SECRET') ?: ($_ENV['RECAPTCHA_SECRET'] ?? null);
                if ($secret) {
                    $resp = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                        'body' => [
                            'secret' => $secret,
                            'response' => $recaptcha,
                            'remoteip' => $request->getClientIp(),
                        ],
                    ]);
                    $dataResp = $resp->toArray(false);
                    if (! isset($dataResp['success']) || $dataResp['success'] !== true) {
                        $this->addFlash('error', 'reCAPTCHA verification failed.');
                        return $this->render('security/forgot_password_request.html.twig', ['requestForm' => $form->createView()]);
                    }
                }
            }

            $user = $this->userRepo->findOneBy(['email' => $email]);

            // Generate token and persist it linked to the user. In production also send an email with the token link.
            if ($user) {
                $token = bin2hex(random_bytes(32));

                $tokenEntity = new PasswordResetToken();
                $tokenEntity->setUser($user)
                    ->setToken($token)
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setExpiresAt(new \DateTimeImmutable('+1 hour'));

                $this->em->persist($tokenEntity);
                $this->em->flush();

                // Build reset URL
                $resetUrl = $this->urlGenerator->generate('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // Send email
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@example.com', 'Esports Dev'))
                    ->to($user->getEmail())
                    ->subject('Password reset request')
                    ->htmlTemplate('emails/password_reset.html.twig')
                    ->context([
                        'resetUrl' => $resetUrl,
                        'user' => $user,
                    ]);

                $this->mailer->send($email);
            }

            // Regardless of whether user exists, show the same message for security
            return $this->redirectToRoute('app_forgot_password_check_email');
        }

        return $this->render('security/forgot_password_request.html.twig', ['requestForm' => $form->createView()]);
    }

    #[Route('/forgot-password/check-email', name: 'app_forgot_password_check_email')]
    public function checkEmail(Request $request): Response
    {
        // Generic page that tells the user an email was sent if the address exists
        return $this->render('security/check_email.html.twig');
>>>>>>> module-user
    }
}
