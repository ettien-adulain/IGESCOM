<?php
namespace App\Models;

class Article {
    public ?int $id;
    public string $reference_atic;
    public string $designation;
    public string $type_article; // INFO, BIOTIQUE, LOGICIEL, SERVICE
    public float $prix_achat;
    public float $marge_pourcentage = 30.00; // Constante Module 6.1
    public int $stock_alerte;

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) $this->$key = (is_numeric($value)) ? (float)$value : $value;
        }
    }

    /** Calcul intelligent du prix de vente (Module 6.1) */
    public function getPrixVente(): float {
        return round($this->prix_achat * (1 + ($this->marge_pourcentage / 100)), 0);
    }

    /** Calcul du bénéfice par unité */
    public function getBenefice(): float {
        return $this->getPrixVente() - $this->prix_achat;
    }

    /** État de santé du stock */
    public function isLowStock(int $currentQty): bool {
        return $currentQty <= $this->stock_alerte;
    }
}