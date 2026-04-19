<?php
namespace App\Controllers\Sales;

use App\Controllers\Controller;
use App\Services\CalculationService;
use App\Services\DocumentService;

class ProformaController extends Controller {
    public function create() {
        $this->middleware(['COMMERCIAL', 'ADMIN']);
        $this->render('sales/proforma_form', ['active' => 'management']);
    }

    public function store() {
        $this->middleware();
        $data = $this->request->getJson();
        
        $this->db->beginTransaction();
        try {
            // Logique de sauvegarde complexe via Service
            $totals = CalculationService::finalizeDocument($data['items'], $data['remise'] ?? 0);
            // ... Insertion BDD via Repository ...
            
            $docService = new DocumentService();
            $pdf = $docService->generate($docId, 'PROFORMA');
            
            $this->db->commit();
            $this->response->json(['status' => 'success', 'pdf' => $pdf]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->response->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}