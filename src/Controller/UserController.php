<?php

namespace App\Controller;

use App\Entity\User;
<<<<<<< HEAD
use App\Entity\Player;
=======
>>>>>>> module-rewards
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

<<<<<<< HEAD
        // Determine the user class based on selection
        $selectedRole = $this->getSelectedRole($request);
        $user = ($selectedRole === 'ROLE_ADMIN' || !$selectedRole) ? new User() : new Player();
        
=======
        $user = new User();
>>>>>>> module-rewards
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
<<<<<<< HEAD
=======
                // Additional server-side validation
                $email = $user->getEmail();
                $username = $user->getUsername();
                $password = $form->get('password')->getData();
                
                // Check for duplicate email
                $existingEmail = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($existingEmail) {
                    $this->addFlash('danger', 'This email is already registered. Please use a different email or log in.');
                    return $this->render('security/register.html.twig', [
                        'registrationForm' => $form,
                    ]);
                }
                
                // Check for duplicate username
                $existingUsername = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
                if ($existingUsername) {
                    $this->addFlash('danger', 'This username is already taken. Please choose a different username.');
                    return $this->render('security/register.html.twig', [
                        'registrationForm' => $form,
                    ]);
                }
                
>>>>>>> module-rewards
                // Encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
<<<<<<< HEAD
                        $form->get('password')->getData()
=======
                        $password
>>>>>>> module-rewards
                    )
                );

                // Set role based on user selection
                $selectedRole = $form->get('userRole')->getData();
                $user->setRoles([$selectedRole]);
                
                // Set typeuser based on role
                if ($selectedRole === 'ROLE_ADMIN') {
                    $user->setTypeuser('admin');
                    // Admins are auto-approved
                    $user->setApprovalStatus('approved');
                } else {
                    $user->setTypeuser('user');
                    // Players need approval
                    $user->setApprovalStatus('pending');
                    
<<<<<<< HEAD
                    // Set required Player fields
                    if ($user instanceof Player) {
                        $user->setNickname($user->getUsername());
                    }
                    
=======
>>>>>>> module-rewards
                    // Handle file upload for players
                    $verificationFile = $form->get('verificationFile')->getData();
                    if ($verificationFile) {
                        $originalFilename = pathinfo($verificationFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $verificationFile->guessExtension();
                        
                        try {
                            // Create directory if it doesn't exist
                            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/verification';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            
                            $verificationFile->move(
                                $uploadDir,
                                $newFilename
                            );
                            $user->setConfirmationFile('/uploads/verification/' . $newFilename);
                        } catch (\Exception $e) {
                            $this->addFlash('danger', 'File upload failed: ' . $e->getMessage());
                            return $this->render('security/register.html.twig', [
                                'registrationForm' => $form,
                            ]);
                        }
                    }
                }

                $entityManager->persist($user);
                $entityManager->flush();

                // Different success messages based on role
                if ($selectedRole === 'ROLE_ADMIN') {
<<<<<<< HEAD
                    $this->addFlash('success', 'Admin account created! Please log in.');
=======
                    $this->addFlash('success', 'Admin account created successfully! You can now log in.');
>>>>>>> module-rewards
                } else {
                    $this->addFlash('success', 'Registration successful! Your account is pending admin approval. Please check back soon.');
                }
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Registration failed: ' . $e->getMessage());
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            // Show form errors
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
<<<<<<< HEAD
    
    private function getSelectedRole(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            return $data['registration_form']['userRole'] ?? null;
        }
        return null;
    }
=======
>>>>>>> module-rewards
}

