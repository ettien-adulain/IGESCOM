<?php
namespace App\Controllers\Accounting;

use App\Controllers\Controller;
use App\Repositories\HRRepository;
use App\Utils\Logger;

class HRController extends Controller {

    public function index() {
        $this->middleware(['RH', 'COMPTABLE', 'ADMIN', 'SUPERADMIN']);
        
        $repo = new HRRepository();
        $employees = $repo->getAllEmployees($_SESSION['agence_id']);

        $this->render('accounting/hr_management', [
            'title'      => 'Gestion RH | WinTech',
            'page_title' => 'Ressources Humaines & Paie',
            'active'     => 'management',
            'employees'  => $employees
        ]);
    }

    /**
     * Alias route `/rh/salaires` : même écran que l'index (paie / masse salariale).
     */
    public function payroll(): void
    {
        $this->index();
    }

    public function pay() {
        $this->middleware(['COMPTABLE', 'ADMIN']);
        $repo = new HRRepository();
        
        if ($repo->recordPayment($this->request->all())) {
            \App\Utils\Logger::log("SALAIRE_PAIEMENT", "Paiement validé pour employé ID: " . $this->request->input('id_employe'));
            $this->redirect('/rh/salaires?success=paiement_effectue');
        }
    }
}