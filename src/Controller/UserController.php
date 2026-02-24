<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Player;
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

        // Determine the user class based on selection
        $selectedRole = $this->getSelectedRole($request);
        $user = ($selectedRole === 'ROLE_ADMIN' || !$selectedRole) ? new User() : new Player();
        
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
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
                    
                    // Set required Player fields
                    if ($user instanceof Player) {
                        $user->setNickname($user->getUsername());
                    }
                    
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
                    $this->addFlash('success', 'Admin account created! Please log in.');
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
    
    private function getSelectedRole(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            return $data['registration_form']['userRole'] ?? null;
        }
        return null;
    }
}

