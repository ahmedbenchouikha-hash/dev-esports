<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/qrcode')]
class QrCodeController extends AbstractController
{
    /**
     * Serve QR code image
     */
    #[Route('/{filename}', name: 'serve_qr_code', requirements: ['filename' => '^[a-zA-Z0-9._-]+\.png$'])]
    public function serveQrCode(string $filename): Response
    {
        $filepath = $this->getParameter('kernel.project_dir') . '/public/uploads/qrcodes/' . $filename;
        
        // Security check: ensure the file is in the right directory
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return $this->json(['error' => 'QR code not found'], 404);
        }
        
        // Verify the file is actually in the uploads directory
        $realPath = realpath($filepath);
        $uploadsDir = realpath($this->getParameter('kernel.project_dir') . '/public/uploads/qrcodes');
        
        if (strpos($realPath, $uploadsDir) !== 0) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }
        
        // Serve the image
        $response = new Response(file_get_contents($filepath));
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Cache-Control', 'public, max-age=31536000');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        
        return $response;
    }
}
