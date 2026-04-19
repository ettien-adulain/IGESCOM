<?php
namespace App\Controllers\Agency;

use App\Controllers\Controller;

class AgencyController extends Controller {
    public function index() {
        $this->middleware(['ADMIN', 'SUPERADMIN']);
        // Récupération des agences en BDD
        $agences = $this->db->query("SELECT * FROM agences ORDER BY nom ASC")->fetchAll();

        $this->render('agencies/index', [
            'page_title' => 'Liste des Agences',
            'active' => 'management',
            'agences' => $agences
        ]);
    }
}