<?php

namespace App\Controller;

<<<<<<< HEAD
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
=======
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
>>>>>>> module-user
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
<<<<<<< HEAD
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_email' => $lastUsername,
=======
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashed = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashed);

            // map form field name 'typeUser' to entity 'typeuser'
            $user->setTypeuser($form->get('typeUser')->getData());

            // handle profile upload
            $profileFile = $form->get('profileFile')->getData();
            if ($profileFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $newName = uniqid() . '-' . preg_replace('/[^a-z0-9.\-_]/i', '', $profileFile->getClientOriginalName());
                $profileFile->move($uploadsDir, $newName);
                // Store the filename in the user entity
                $user->setConfirmationFile($newName);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Registration successful. You can now sign in.');

            return $this->redirectToRoute('app_login');
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
>>>>>>> module-user
            'error' => $error,
        ]);
    }

<<<<<<< HEAD
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
=======
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // controller can be blank: handled by Symfony security
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
>>>>>>> module-user
    }
}
