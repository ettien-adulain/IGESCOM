<?php
namespace App\Controllers\Inventory;

use App\Controllers\Controller;
use App\Repositories\StockRepository;
use App\Repositories\ArticleRepository;
use App\Utils\Logger;

class InventoryController extends Controller {

    public function index() {
        // Seuls les rôles d'encadrement et magasinier
        $this->middleware(['MAGASINIER', 'ADMIN', 'SUPERADMIN', 'GERANT']);

        $stockRepo = new StockRepository();
        $inventory = $stockRepo->getAgencyInventory($_SESSION['agence_id']);

        // Calcul du nombre d'alertes pour le badge
        $alertCount = count(array_filter($inventory, fn($i) => $i['quantite'] <= $i['stock_alerte']));

        $this->render('inventory/index', [
            'page_title' => 'Gestion des Stocks',
            'active' => 'management',
            'inventory' => $inventory,
            'alert_count' => $alertCount
        ]);
    }

    /**
     * Traitement d'une entrée en stock (Réapprovisionnement)
     */
    public function storeEntry() {
        $this->middleware(['MAGASINIER', 'ADMIN']);
        $data = $this->request->all();

        $repo = new StockRepository();
        $success = $repo->logMovement(
            (int)$data['id_article'], 
            (int)$_SESSION['agence_id'], 
            (int)$data['qty'], 
            'ENTREE', 
            $data['motif']
        );

        if($success) {
            Logger::log("STOCK_ENTRY", "Entrée de {$data['qty']} unités pour l'article ID {$data['id_article']}");
            $this->redirect('/logistics?success=stock_mis_a_jour');
        }
    }
}