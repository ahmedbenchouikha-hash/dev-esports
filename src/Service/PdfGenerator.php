<?php

namespace App\Service;

use Knp\Snappy\Pdf;

class PdfGenerator
{
    private Pdf $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    public function generatePdf(string $html): string
    {
        return $this->pdf->getOutputFromHtml($html);
    }
}
