<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public function generatePdf(string $html): string
    {
        $options = new Options();
        $options->set([
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'Arial',
            'dpi' => 150,
        ]);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate PDF and save to file
     */
    public function generatePdfFile(string $html, string $filename): void
    {
        $pdf = $this->generatePdf($html);
        file_put_contents($filename, $pdf);
    }
}
