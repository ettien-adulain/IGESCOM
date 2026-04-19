<?php
namespace App\Controllers\Logistics;

use App\Controllers\Controller;
use App\Repositories\LogisticsRepository;

class TrackingController extends Controller {

    public function index() {
        $this->middleware(['MAGASINIER', 'ADMIN', 'SUPERADMIN']);
        
        $repo = new LogisticsRepository();
        $orders = $repo->getOrdersToPrepare($_SESSION['agence_id']);

        $this->render('logistics/orders_tracking', [
            'page_title' => 'Logistique & Expédition',
            'active' => 'management',
            'orders' => $orders
        ]);
    }
}