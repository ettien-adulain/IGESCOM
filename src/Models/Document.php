<?php
namespace App\Models;

class Document {
    public ?int $id;
    public string $numero_officiel;
    public string $type_doc; // PROFORMA, FACTURE
    public string $statut;   // ATTENTE_VAL_CLIENT, CLIENT_OK, FACTURE_VALIDEE
    public float $total_ht;
    public float $total_tva;
    public float $net_a_payer;
    public ?string $pdf_path;

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) $this->$key = $value;
        }
    }

    /** Vérifie si le document est modifiable (Module 4.2) */
    public function isEditable(): bool {
        return in_array($this->statut, ['ATTENTE_VAL_CLIENT', 'MODIFIE']);
    }

    /** Indique si le document a atteint le statut final */
    public function isFinalized(): bool {
        return $this->statut === 'FACTURE_VALIDEE';
    }
}