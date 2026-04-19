<?php
namespace App\Controllers\Service;

use App\Controllers\Controller;

class ServiceController extends Controller {
    public function index() {
        $this->middleware();
        // Simulation de données basées sur l'Image 8
        $services = [
            ['code' => 'SRV-77', 'nom' => 'MAINTENANCE SERVEUR', 'tarif' => '75 000 CFA / h', 'statut' => 'ACTIF'],
            ['code' => 'SRV-92', 'nom' => 'INSTALLATION RÉSEAU', 'tarif' => 'Sur Devis', 'statut' => 'ACTIF']
        ];

        $this->render('services/index', [
            'page_title' => 'Prestations de Services',
            'active' => 'management',
            'services' => $services
        ]);
    }
}