<?php
namespace App\Models;

class User {
    public ?int $id;
    public string $matricule;
    public string $nom_complet;
    public string $role; // SUPERADMIN, ADMIN, COMMERCIAL, COMPTABLE, etc.
    public string $photo_path;
    public bool $active;

    public function __construct(array $data = []) {
        $this->id = $data['id'] ?? null;
        $this->matricule = $data['matricule'] ?? '';
        $this->nom_complet = $data['nom_complet'] ?? '';
        $this->role = $data['role'] ?? 'COMMERCIAL';
        $this->photo_path = $data['photo_path'] ?? 'default_user.png';
        $this->active = (bool)($data['active'] ?? true);
    }

    /** Vérifie si l'utilisateur possède les droits d'administration */
    public function isAdmin(): bool {
        return in_array($this->role, ['SUPERADMIN', 'ADMIN']);
    }

    /** Formate l'affichage pour la Navbar */
    public function getShortName(): string {
        $parts = explode(' ', $this->nom_complet);
        return $parts[0];
    }
}