<?php
namespace App\Models;

class Employee {
    public ?int $id;
    public string $matricule_hr;
    public string $nom;
    public string $prenom;
    public string $poste;
    public float $salaire_base;
    public float $avantages;
    public float $charges_patronales;

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) $this->$key = (is_numeric($value)) ? (float)$value : $value;
        }
    }

    /** Calcul du coût total réel pour l'entreprise (Module 6) */
    public function getCoutGlobal(): float {
        return $this->salaire_base + $this->avantages + $this->charges_patronales;
    }

    public function getFullName(): string {
        return strtoupper($this->nom) . ' ' . $this->prenom;
    }
}