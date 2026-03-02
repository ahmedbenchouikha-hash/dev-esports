<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $plainPassword = $form->get('plainPassword')->getData();
                    $hashed = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashed);

                    // Set user type from form
                    $typeuser = $form->get('typeuser')->getData();
                    if ($typeuser) {
                        $user->setTypeuser($typeuser);
                    } else {
                        $user->setTypeuser('USER');
                    }

                    // Handle profile upload
                    $profileFile = $form->get('confirmationFile')->getData();
                    if ($profileFile) {
                        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
                        if (!is_dir($uploadsDir)) {
                            mkdir($uploadsDir, 0755, true);
                        }
                        $newName = uniqid() . '-' . preg_replace('/[^a-z0-9.\-_]/i', '', $profileFile->getClientOriginalName());
                        $profileFile->move($uploadsDir, $newName);
                        $user->setConfirmationFile($newName);
                    }

                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Registration successful! You can now sign in.');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'An error occurred during registration: ' . $e->getMessage());
                }
            } else {
                // Form has errors - they will be displayed in the template
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error);
                    }
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Create a named form with an empty name so HTML input names are `email` and `password`
        $form = $this->container->get('form.factory')->createNamed('', LoginFormType::class, ['email' => $lastUsername], ['csrf_token_id' => 'authenticate', 'csrf_field_name' => '_csrf_token']);
        $form->handleRequest($request);

        return $this->render('security/login.html.twig', [
            'loginForm' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method will be intercepted by the logout listener
        // No code is needed here
    }
}
