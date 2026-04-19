<?php
namespace App\Controllers\Profile;

use App\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Utils\Uploader;
use App\Utils\Logger;

class ProfileController extends Controller {

    public function index() {
        $this->middleware();
        $userRepo = new UserRepository();
        $user = $userRepo->findById($_SESSION['user_id']);

        $this->render('profile/index', [
            'page_title' => 'Mon Profil',
            'active' => 'dashboard',
            'user' => $user
        ]);
    }

    public function update() {
        $this->middleware();
        $data = $this->request->all();
        $userRepo = new UserRepository();
        
        // Gestion de l'upload photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $photoPath = Uploader::upload($_FILES['photo'], 'users');
            if ($photoPath) {
                $data['photo_path'] = $photoPath;
            }
        }

        if ($userRepo->updateProfile($_SESSION['user_id'], $data)) {
            $_SESSION['user_nom'] = $data['nom_complet']; // Update session
            Logger::log("PROFILE_UPDATE", "L'utilisateur a mis à jour ses informations.");
            $this->response->redirect('/profile?success=1');
        } else {
            $this->response->redirect('/profile?error=1');
        }
    }
}