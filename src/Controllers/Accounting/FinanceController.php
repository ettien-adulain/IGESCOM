<?php
namespace App\Controllers\Accounting;

use App\Controllers\Controller;
use App\Repositories\DocumentRepository;
use App\Repositories\HRRepository;
use App\Utils\Logger;
use Exception;

class FinanceController extends Controller {

    private DocumentRepository $docRepo;
    private HRRepository $hrRepo;

    public function __construct() {
        parent::__construct();
        // Sécurité de niveau 3 : Comptabilité
        $this->middleware(['COMPTABLE', 'ADMIN', 'SUPERADMIN']);
        $this->docRepo = new DocumentRepository();
        $this->hrRepo = new HRRepository();
    }

    /**
     * VUE ANALYTICS : Le tableau de bord décisionnel
     */
    public function stats() {
        try {
            $agId = $_SESSION['agence_id'];

            // 1. Collecte des flux
            $ca_mensuel = $this->docRepo->getTurnover($agId, 'month');
            $payroll    = $this->hrRepo->getMonthlyPayroll($agId);
            
            // 2. Récupération des charges fixes (Simulation table depenses)
            $stmt = $this->db->prepare("SELECT SUM(montant) FROM depenses_entreprise WHERE id_agence = ? AND MONTH(date_depense) = MONTH(CURRENT_DATE())");
            $stmt->execute([$agId]);
            $expenses = (float)$stmt->fetchColumn() ?: 450000.00;

            // 3. Calcul de la performance (Intelligence Financière)
            $netResult = $ca_mensuel - ($payroll + $expenses);
            $margin    = ($ca_mensuel > 0) ? ($netResult / $ca_mensuel) * 100 : 0;

            // 4. Transmission sécurisée à la vue
            $this->render('accounting/analytics', [
                'page_title' => 'Analytique Financière',
                'active'     => 'accounting',
                'data'       => [
                    'ca'           => $ca_mensuel,
                    'payroll'      => $payroll,
                    'expenses'     => $expenses,
                    'net_result'   => $netResult,
                    'margin_rate'  => round($margin, 2),
                    'unpaid_docs'  => $this->docRepo->getUnpaidCount($agId),
                    'agence_nom'   => $_SESSION['agence_nom'] ?? 'Agence Locale'
                ]
            ]);

        } catch (Exception $e) {
            Logger::log("FINANCE_CTRL_ERROR", $e->getMessage());
            $this->redirect('/management?error=chargement_analytics_impossible');
        }
    }
}