<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    private Dompdf $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate PDF from HTML content
     */
    public function generatePdf(string $html): string
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        
        return $this->dompdf->output();
    }

    /**
     * Stream PDF output directly to browser
     */
    public function streamPdf(string $html, string $filename = 'document.pdf'): void
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        
        $this->dompdf->stream($filename);
    }

    /**
     * Get Dompdf instance for advanced usage
     */
    public function getDompdf(): Dompdf
    {
        return $this->dompdf;
    }
}
