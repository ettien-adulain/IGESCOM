<?php
namespace App\Controllers\Accounting; // <--- Namespace exact

use App\Controllers\Controller;
use App\Repositories\AccountingRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\HRRepository;
use App\Utils\Logger;
use Exception;

class FinanceController extends Controller {

    private AccountingRepository $comptaRepo;

    public function __construct() {
        parent::__construct();
        // Seuls les rôles financiers accèdent à ce module
        $this->middleware(['COMPTABLE', 'ADMIN', 'SUPERADMIN']);
        $this->comptaRepo = new AccountingRepository();
    }

    /**
     * VUE : Tableau de Bord Financier (Module 6)
     */
    public function stats() {
        try {
            $agId = $_SESSION['agence_id'];
            $docRepo = new DocumentRepository();
            $hrRepo = new HRRepository();

            $ca = $docRepo->getTurnover($agId, 'month');
            $payroll = $hrRepo->getMonthlyPayroll($agId);
            $solde = $this->comptaRepo->getGlobalCashBalance($agId);

            $this->render('accounting/analytics', [
                'title'      => 'Finance Expert | WinTech',
                'page_title' => 'Pilotage Analytique',
                'active'     => 'accounting',
                'data' => [
                    'ca' => $ca,
                    'payroll' => $payroll,
                    'expenses' => 450000, // À dynamiser ultérieurement
                    'net_result' => $ca - ($payroll + 450000),
                    'solde_global' => $solde,
                    'unpaid_docs' => $docRepo->getUnpaidCount($agId)
                ]
            ]);
        } catch (Exception $e) {
            Logger::log("FINANCE_CTRL_ERROR", $e->getMessage());
            $this->redirect('/management?error=donnees_indisponibles');
        }
    }
}