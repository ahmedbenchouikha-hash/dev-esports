<?php

namespace App\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class QrCodeService
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->projectDir = $parameterBag->get('kernel.project_dir');
    }

    /**
     * Generate a QR code for a ticket
     * 
     * @param string $ticketNumber The ticket number to encode
     * @param string $ticketType The type of ticket (regular, vip, student)
     * @return string Base64 encoded PNG image
     */
    public function generateTicketQrCode(string $ticketNumber, string $ticketType = 'regular'): string
    {
        // Create data string to encode (ticketNumber:ticketType)
        $data = sprintf('%s:%s', $ticketNumber, $ticketType);

        // Create QR Code and write to PNG
        $qrCode = new QrCode($data);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Return as base64 for embedding in HTML
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    /**
     * Generate and save QR code for payment (saves to file system and returns URL)
     * 
     * @param string $paymentIntentId The payment intent ID
     * @param string $ticketNumber The ticket number
     * @return string URL to access the QR code
     */
    public function generateAndSavePaymentQrCode(string $paymentIntentId, string $ticketNumber): string
    {
        // Create unique filename from payment intent ID and ticket
        $filename = str_replace('_', '', substr($paymentIntentId, 0, 15)) . '_' . $ticketNumber;
        
        // Ensure uploads directory exists
        $uploadsDir = $this->projectDir . '/public/uploads/qrcodes';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Data to encode: PaymentIntentID:TicketNumber
        $data = sprintf('%s:%s', $paymentIntentId, $ticketNumber);
        
        // Create QR Code and write to PNG
        $qrCode = new QrCode($data);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Save to file
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename) . '.png';
        $filepath = $uploadsDir . '/' . $filename;
        file_put_contents($filepath, $result->getString());

        // Return URL path for email
        return '/uploads/qrcodes/' . $filename;
    }
}

