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
        $options->set([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Générer un PDF à partir de contenu HTML et le retourner en tant que fichier
     * 
     * @param string $html Le contenu HTML à convertir en PDF
     * @param string $filename Le nom du fichier PDF
     * @return string Le contenu PDF
     */
    public function generatePdf(string $html, string $filename = 'document.pdf'): string
    {
        // Charger le HTML
        $this->dompdf->loadHtml($html, 'UTF-8');

        // Définir le format du papier et l'orientation
        $this->dompdf->setPaper('A4', 'portrait');

        // Générer le PDF
        $this->dompdf->render();

        return $this->dompdf->output();
    }

    /**
     * Obtenir l'instance Dompdf
     */
    public function getDompdf(): Dompdf
    {
        return $this->dompdf;
    }
}
