<?php
namespace App\Services;

require_once dirname(__DIR__, 2) . '/vendor/fpdf/fpdf.php';

use App\Repositories\AccountingRepository;
use App\Utils\Formatter;

class BilanService extends \FPDF {
    
    private array $balance;

    public function generate(int $agenceId, string $dateFin): string {
        $repo = new AccountingRepository();
        // On récupère la balance à la date T
        $this->balance = $repo->getTrialBalance($agenceId, "2000-01-01", $dateFin);

        $this->AddPage('L'); // Format Paysage pour le Bilan
        $this->SetFont('Arial', 'B', 16);
        
        // Header
        $this->Cell(0, 10, utf8_decode("BILAN COMPTABLE ACTIF / PASSIF"), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 7, "Arrete au : " . $dateFin, 0, 1, 'C');
        $this->Ln(10);

        // Colonnes : ACTIF | MONTANT || PASSIF | MONTANT
        $this->SetFillColor(15, 23, 42); $this->SetTextColor(255);
        $this->Cell(100, 10, "ACTIF (EMPLOIS)", 1, 0, 'C', true);
        $this->Cell(35, 10, "MONTANT", 1, 0, 'C', true);
        $this->Cell(5, 10, "", 0, 0); // Séparateur
        $this->Cell(100, 10, "PASSIF (RESSOURCES)", 1, 0, 'C', true);
        $this->Cell(35, 10, "MONTANT", 1, 1, 'C', true);

        $this->SetTextColor(0);
        
        // Séparation des données
        $actif = array_filter($this->balance, fn($item) => in_array(substr($item['numero'],0,1), [2,3,4,5]) && $item['solde_net'] > 0);
        $passif = array_filter($this->balance, fn($item) => in_array(substr($item['numero'],0,1), [1,4,5]) && $item['solde_net'] < 0);

        // Rendu des lignes
        $this->SetFont('Arial', '', 9);
        $maxRows = max(count($actif), count($passif));
        $actif = array_values($actif);
        $passif = array_values($passif);

        for ($i = 0; $i < $maxRows; $i++) {
            // Côté Actif
            if(isset($actif[$i])) {
                $this->Cell(100, 8, utf8_decode($actif[$i]['libelle']), 1);
                $this->Cell(35, 8, number_format($actif[$i]['solde_net'], 0, '.', ' '), 1, 0, 'R');
            } else {
                $this->Cell(135, 8, "", 1);
            }
            
            $this->Cell(5, 8, "", 0, 0); // Espace central

            // Côté Passif
            if(isset($passif[$i])) {
                $this->Cell(100, 8, utf8_decode($passif[$i]['libelle']), 1);
                $this->Cell(35, 8, number_format(abs($passif[$i]['solde_net']), 0, '.', ' '), 1, 1, 'R');
            } else {
                $this->Cell(135, 8, "", 1, 1);
            }
        }

        $fileName = 'BILAN_' . $agenceId . '_' . date('Ymd') . '.pdf';
        $path = 'uploads/pdf_archives/' . $fileName;
        $this->Output('F', dirname(__DIR__, 2) . '/public/' . $path);
        
        return $path;
    }
}