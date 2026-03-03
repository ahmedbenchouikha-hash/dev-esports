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

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Encode the plain password
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $plainPassword)
                );

                // Set typeuser to USER (regular user account, no admin selection)
                $user->setTypeuser('USER');
                
                // Set default approval status
                $user->setApprovalStatus('pending');

                // Handle file upload
                $confirmationFile = $form->get('confirmationFile')->getData();
                if ($confirmationFile) {
                    $originalFilename = pathinfo($confirmationFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $confirmationFile->guessExtension();
                    
                    try {
                        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        $confirmationFile->move($uploadDir, $newFilename);
                        $user->setConfirmationFile($newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'File upload failed: ' . $e->getMessage());
                        return $this->render('security/register.html.twig', [
                            'registrationForm' => $form->createView(),
                        ]);
                    }
                }

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Registration successful! You can now log in.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Registration failed: ' . $e->getMessage());
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            // Show form errors
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}

