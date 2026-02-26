<?php

namespace App\Controller;

<<<<<<< HEAD
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
=======
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
>>>>>>> module-rewards
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
<<<<<<< HEAD
    public function login(AuthenticationUtils $authenticationUtils): Response
=======
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        UserRepository $userRepository
    ): Response
>>>>>>> module-rewards
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
<<<<<<< HEAD
=======
        
        // Validation errors array
        $validationErrors = [];
        
        // Validate login form submission
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email', '');
            $password = $request->request->get('password', '');
            
            // Validate email field
            if (empty($email)) {
                $validationErrors['email'] = 'Email address is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationErrors['email'] = 'Please provide a valid email address.';
            } elseif (!$userRepository->findOneBy(['email' => $email])) {
                $validationErrors['email'] = 'No account found with this email address.';
            }
            
            // Validate password field
            if (empty($password)) {
                $validationErrors['password'] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $validationErrors['password'] = 'Password must be at least 8 characters long.';
            }
        }
>>>>>>> module-rewards

        return $this->render('security/login.html.twig', [
            'last_email' => $lastUsername,
            'error' => $error,
<<<<<<< HEAD
=======
            'validationErrors' => $validationErrors,
>>>>>>> module-rewards
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
