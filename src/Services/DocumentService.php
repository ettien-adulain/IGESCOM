<?php
namespace App\Services;

// On s'assure que FPDF est chargé depuis le dossier vendor
require_once dirname(__DIR__, 2) . '/vendor/fpdf/fpdf.php';

use App\Repositories\DocumentRepository;
use Exception;

/**
 * DocumentService V22.0 - Moteur de Certification DGI & SAE
 * Conforme aux exigences fiscales de Côte d'Ivoire (RNI/RSI)
 */
class DocumentService extends \FPDF {
    
    private array $d; // Data
    private string $type;
    private float $y_table_header;

    /**
     * Point d'entrée principal pour la génération
     */
    public function generate(int $docId, string $type = 'PROFORMA'): string {
        $repo = new DocumentRepository();
        $this->d = $repo->findWithDetails($docId);
        $this->type = strtoupper($type);

        if (!$this->d) {
            throw new Exception("Erreur Critique : Données du document #$docId introuvables.");
        }

        // Configuration du document
        $this->AddPage();
        $this->SetMargins(10, 10, 10);
        $this->AliasNbPages();

        // --- CONSTRUCTION DU MODÈLE DGI ---
        $this->drawBrandingSection();
        $this->drawClientAndQRSection();
        $this->drawLogisticsBar();
        $this->drawItemsTable();
        $this->drawFinancialSummary();
        $this->drawLegalFooter();

        // --- ARCHIVAGE ÉLECTRONIQUE (SAE) ---
        $fileName = $this->type . '_' . $this->d['numero_officiel'] . '_' . time() . '.pdf';
        $directory = 'uploads/pdf_archives/';
        $fullPath = dirname(__DIR__, 2) . '/public/' . $directory . $fileName;

        // Création du dossier si inexistant (CHMOD 775)
        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0775, true);
        }

        $this->Output('F', $fullPath);
        return $directory . $fileName;
    }

    /**
     * Encode le texte pour FPDF (ISO-8859-1)
     */
    private function encode($text) {
        return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    /**
     * 1. Bloc Émetteur (Haut Gauche) et Logo (Haut Droite)
     */
    private function drawBrandingSection() {
        // Infos Entreprise (YAOCOM'S)
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(15, 23, 42);
        $this->Cell(100, 7, "YAOCOM'S", 0, 1, 'L');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(100, 5, "NCC : 2036080K", 0, 1, 'L');
        $this->Cell(100, 5, $this->encode("Régime d'imposition : RSI"), 0, 1, 'L');
        $this->Cell(100, 5, $this->encode("Centre des impôts : 807 Impôts de Cocody"), 0, 1, 'L');
        $this->Cell(100, 5, "RCCM : CI-ABJ-2020-B-11930", 0, 1, 'L');
        
        // Logo en haut à droite
        $logo = dirname(__DIR__, 2) . '/public/assets/img/logo_ycs.png';
        if(file_exists($logo)) {
            $this->Image($logo, 150, 10, 45);
        }

        // Titre Document (Ex: Facture de vente N°)
        $this->SetXY(10, 45);
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(225, 29, 72); // Rouge Crimson
        $this->SetTextColor(255);
        $title = ($this->type == 'FACTURE' ? "FACTURE DE VENTE N " : "FACTURE PROFORMA N ") . $this->d['numero_officiel'];
        $this->Cell(0, 12, $this->encode($title), 0, 1, 'C', true);
        $this->Ln(5);
    }

    /**
     * 2. Bloc Client (Droite) et QR Code (Gauche)
     */
    private function drawClientAndQRSection() {
        $y_start = $this->GetY();

        // QR Code simulé (Cercle avec G au milieu comme sur capture)
        $this->SetFillColor(240);
        $this->Rect(10, $y_start, 35, 35, 'F');
        $this->SetXY(10, $y_start + 10);
        $this->SetFont('Arial', 'B', 25);
        $this->SetTextColor(225, 29, 72);
        $this->Cell(35, 15, "QR", 0, 0, 'C');

        // Bloc Client
        $this->SetXY(110, $y_start);
        $this->SetTextColor(0);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(248, 250, 252);
        $this->Cell(90, 7, "CLIENT", 'B', 1, 'L', true);
        
        $this->SetX(110);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(90, 7, $this->encode(strtoupper($this->d['client_nom'])), 0, 1, 'L');
        
        $this->SetX(110);
        $this->SetFont('Arial', '', 9);
        $this->MultiCell(90, 5, $this->encode("Adresse : " . ($this->d['client_adr'] ?? 'N/A')), 0, 'L');
        
        $this->SetX(110);
        $this->Cell(90, 5, "NCC : " . ($this->d['client_ncc'] ?? 'N/A'), 0, 1, 'L');
        
        $this->SetX(110);
        $this->Cell(90, 5, $this->encode("Régime : " . ($this->d['regime_imposition'] ?? 'RNI')), 0, 1, 'L');
        
        $this->Ln(10);
    }

    /**
     * 3. Barre Logistique (Date, Vendeur, PDV)
     */
    private function drawLogisticsBar() {
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(241, 245, 249);
        $this->Cell(45, 6, "DATE ET HEURE", 1, 0, 'C', true);
        $this->Cell(55, 6, "VENDEUR", 1, 0, 'C', true);
        $this->Cell(45, 6, "MODE PAIEMENT", 1, 0, 'C', true);
        $this->Cell(45, 6, "LIEU DE LIVRAISON", 1, 1, 'C', true);

        $this->SetFont('Arial', '', 9);
        $this->Cell(45, 8, date('d/m/Y H:i', strtotime($this->d['date_creation'])), 1, 0, 'C');
        $this->Cell(55, 8, $this->encode($this->d['auteur_nom']), 1, 0, 'C');
        $this->Cell(45, 8, $this->encode($this->d['conditions_paiement']), 1, 0, 'C');
        $this->Cell(45, 8, $this->encode($this->d['methode_expedition']), 1, 1, 'C');
        $this->Ln(5);
    }

    /**
     * 4. Tableau des Articles (Marge 30% intégrée)
     */
    private function drawItemsTable() {
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(30, 41, 59); // Noir Slate
        $this->SetTextColor(255);

        $this->Cell(15, 10, "REF", 1, 0, 'C', true);
        $this->Cell(85, 10, "DESIGNATION", 1, 0, 'C', true);
        $this->Cell(25, 10, "P.U HT", 1, 0, 'C', true);
        $this->Cell(15, 10, "QTE", 1, 0, 'C', true);
        $this->Cell(15, 10, "REM %", 1, 0, 'C', true);
        $this->Cell(35, 10, "MONTANT HT", 1, 1, 'C', true);

        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 9);
        
        $fill = false;
        foreach ($this->d['items'] as $item) {
            $this->SetFillColor(248, 250, 252);
            
            // Hauteur dynamique pour les désignations longues
            $nb_lines = $this->NbLines(85, $item['designation']);
            $h = 7 * $nb_lines;

            $this->Cell(15, $h, $item['id_article'] ?? '-', 1, 0, 'C', $fill);
            
            $curr_x = $this->GetX();
            $curr_y = $this->GetY();
            $this->MultiCell(85, 7, $this->encode($item['designation']), 1, 'L', $fill);
            
            $this->SetXY($curr_x + 85, $curr_y);
            $this->Cell(25, $h, number_format($item['prix_unitaire_applique'], 0, ',', ' '), 1, 0, 'R', $fill);
            $this->Cell(15, $h, $item['quantite'], 1, 0, 'C', $fill);
            $this->Cell(15, $h, $item['remise'] ?? '0', 1, 0, 'C', $fill);
            $this->Cell(35, $h, number_format($item['total_ligne_ht'], 0, ',', ' '), 1, 1, 'R', $fill);
            
            $fill = !$fill;
        }
    }

    /**
     * 5. Résumé Financier (HT, TVA 18%, Net à payer)
     */
    private function drawFinancialSummary() {
        $this->Ln(5);
        $x_pos = 130;

        $this->SetFont('Arial', 'B', 9);
        $this->SetX($x_pos);
        $this->Cell(40, 7, "TOTAL HT", 1, 0, 'L');
        $this->Cell(30, 7, number_format($this->d['total_ht'], 0, ',', ' '), 1, 1, 'R');

        $this->SetX($x_pos);
        $this->Cell(40, 7, "TVA (18%)", 1, 0, 'L');
        $this->Cell(30, 7, number_format($this->d['total_tva'], 0, ',', ' '), 1, 1, 'R');

        if($this->d['frais_port'] > 0) {
            $this->SetX($x_pos);
            $this->Cell(40, 7, "FRAIS DE PORT", 1, 0, 'L');
            $this->Cell(30, 7, number_format($this->d['frais_port'], 0, ',', ' '), 1, 1, 'R');
        }

        $this->Ln(2);
        $this->SetX($x_pos);
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(225, 29, 72);
        $this->SetTextColor(255);
        $this->Cell(40, 10, "NET A PAYER", 1, 0, 'L', true);
        $this->Cell(30, 10, number_format($this->d['net_a_payer'], 0, ',', ' ') . " F", 1, 1, 'R', true);
    }

    /**
     * 6. Pied de page légal et Signatures
     */
    private function drawLegalFooter() {
        $this->SetY(-55);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(0);
        $this->Cell(95, 5, "SIGNATURE ET CACHET", 'T', 0, 'L');
        $this->Cell(95, 5, "LE COMMERCIAL", 'T', 1, 'R');
        
        $this->Ln(10);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100);
        $msg = "Conditions : Paiement comptant. Garantie 12 mois sur materiel neuf.\n";
        $msg .= "YAOCOM'S GROUPE vous remercie pour votre confiance.";
        $this->MultiCell(0, 4, $this->encode($msg), 0, 'C');

        $this->SetY(-15);
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(180);
        $this->Cell(0, 5, $this->encode("WinTech GIA ERP - Système d'Archivage Électronique Certifié v2.5"), 0, 0, 'C');
    }

    /**
     * Helper : Calcule le nombre de lignes nécessaires pour un texte
     */
    private function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if($nb > 0 and $s[$nb-1] == "\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while($i < $nb) {
            $c = $s[$i];
            if($c == "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if($c == ' ') $sep = $i;
            $l += $cw[$c];
            if($l > $wmax) {
                if($sep == -1) { if($i == $j) $i++; }
                else $i = $sep + 1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else $i++;
        }
        return $nl;
    }
}