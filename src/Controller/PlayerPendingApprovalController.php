<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[IsGranted('ROLE_USER')]
class PlayerPendingApprovalController extends AbstractController
{
    #[Route('/player/pending-approval', name: 'player_pending_approval')]
    public function pending(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Redirect if already approved
        if ($user->isApproved()) {
            return $this->redirectToRoute('player_dashboard');
        }
        
        // Redirect if rejected
        if ($user->isRejected()) {
            return $this->redirectToRoute('player_rejected');
        }

        return $this->render('player/pending_approval.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/player/upload-verification', name: 'player_upload_verification', methods: ['POST'])]
    public function uploadVerification(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getApprovalStatus() || $user->getApprovalStatus() !== 'pending') {
            $this->addFlash('warning', 'You cannot upload a document at this time.');
            return $this->redirectToRoute('player_pending_approval');
        }
        
        $uploadedFile = $request->files->get('verification_file');
        
        if (!$uploadedFile) {
            $this->addFlash('danger', 'Please select a file to upload.');
            return $this->redirectToRoute('player_pending_approval');
        }
        
        try {
            // Validate file
            $fileValidator = new File([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/jpeg',
                    'image/png',
                    'application/pdf',
                ],
            ]);
            
            // Check file size
            if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
                $this->addFlash('danger', 'File is too large. Maximum size is 5MB.');
                return $this->redirectToRoute('player_pending_approval');
            }
            
            // Check MIME type
            $mimeType = $uploadedFile->getMimeType();
            if (!in_array($mimeType, ['image/jpeg', 'image/png', 'application/pdf'])) {
                $this->addFlash('danger', 'Invalid file type. Please upload PDF, JPG, or PNG.');
                return $this->redirectToRoute('player_pending_approval');
            }
            
            // Generate safe filename
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            
            // Create directory if it doesn't exist
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/verification';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Move file
            $uploadedFile->move($uploadDir, $newFilename);
            
            // Update user entity
            $user->setConfirmationFile('/uploads/verification/' . $newFilename);
            $entityManager->flush();
            
            $this->addFlash('success', 'Verification document uploaded successfully! The admin team will review it shortly.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'File upload failed: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('player_pending_approval');
    }

    #[Route('/player/rejected', name: 'player_rejected')]
    public function rejected(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Redirect if somehow not rejected
        if (!$user->isRejected()) {
            return $this->redirectToRoute('player_dashboard');
        }

        return $this->render('player/rejected.html.twig', [
            'user' => $user,
        ]);
    }
}
