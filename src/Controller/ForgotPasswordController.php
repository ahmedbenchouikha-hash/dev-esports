<?php

namespace App\Controller;

use App\Form\ForgotPasswordRequestType;
use App\Repository\UserRepository;
use App\Service\PasswordResetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ForgotPasswordController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly UserRepository $userRepo,
        private readonly EntityManagerInterface $em,
        private readonly PasswordResetService $passwordResetService
    ) {
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
                    if (!isset($dataResp['success']) || $dataResp['success'] !== true) {
                        $this->addFlash('error', 'reCAPTCHA verification failed.');
                        return $this->render('security/forgot_password_request.html.twig', ['requestForm' => $form->createView()]);
                    }
                }
            }

            $user = $this->userRepo->findOneBy(['email' => $email]);

            // Generate and send reset token
            if ($user) {
                $this->passwordResetService->generateAndSendResetToken($user);
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
    }
}
