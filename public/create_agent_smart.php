<?php
/**
 * WINTECH ERP V2.5 - GÉNÉRATEUR D'AGENTS (SÉCURITÉ SERVEUR N0C)
 */

// 1. DÉBOGAGE : On force l'affichage pour voir si la BDD répond
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. CHARGEMENT DU MOTEUR (Chemin corrigé vers /core)
require_once dirname(__DIR__) . '/core/autoload_custom.php';

use Core\Config;
use Core\Database\Connection;

try {
    Config::load();
} catch (Exception $e) {
    die("Erreur chargement config : " . $e->getMessage());
}

$message = "";

// 3. LOGIQUE D'ENREGISTREMENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = strip_tags($_POST['nom_complet']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $naissance = (int)$_POST['annee_naissance'];
    $pass_clair = $_POST['password'];
    $role = $_POST['role'];
    $id_agence = (int)$_POST['id_agence'];
    $photo = $_FILES['photo'] ?? null;

    try {
        $db = Connection::getInstance();
        $db->beginTransaction();

        // A. GÉNÉRATION MATRICULE INTELLIGENT
        $role_abbr = substr($role, 0, 3);
        $initiales = "";
        foreach (explode(" ", $nom) as $m) { $initiales .= strtoupper(substr($m, 0, 1)); }
        
        $stmtCount = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE role = ?");
        $stmtCount->execute([$role]);
        $count = $stmtCount->fetchColumn() + 1;
        
        $matricule = "{$role_abbr}-{$initiales}-{$naissance}-" . str_pad($count, 3, '0', STR_PAD_LEFT);

        // B. GESTION PHOTO
        $photo_name = "default_user.png";
        if ($photo && $photo['error'] === 0) {
            $ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
            $photo_name = "USER-" . $matricule . "." . $ext;
            $targetPath = __DIR__ . "/uploads/users/" . $photo_name;
            
            if (!is_dir(dirname($targetPath))) mkdir(dirname($targetPath), 0775, true);
            move_uploaded_file($photo['tmp_name'], $targetPath);
        }

        // C. INSERTION BDD
        $sql = "INSERT INTO utilisateurs (matricule, nom_complet, email, annee_naissance, password, role, photo_path, first_login) 
                VALUES (:mat, :nom, :email, :annee, :pass, :role, :photo, 0)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'mat'   => $matricule,
            'nom'   => $nom,
            'email' => $email,
            'annee' => $naissance,
            'pass'  => password_hash($pass_clair, PASSWORD_BCRYPT),
            'role'  => $role,
            'photo' => $photo_name
        ]);

        $newId = $db->lastInsertId();
        
        // Liaison Agence Obligatoire
        $db->prepare("INSERT INTO user_agences (id_utilisateur, id_agence) VALUES (?, ?)")
           ->execute([$newId, $id_agence]);

        $db->commit();
        $message = "<div class='success'>✅ Agent <b>$nom</b> créé !<br>Matricule : <b>$matricule</b></div>";

    } catch (Exception $e) {
        if(isset($db)) $db->rollBack();
        $message = "<div class='error'>❌ Erreur : " . $e->getMessage() . "</div>";
    }
}

// Récupération des agences pour le sélecteur
try {
    $db = Connection::getInstance();
    $agences = $db->query("SELECT id, nom FROM agences")->fetchAll();
} catch (Exception $e) {
    $agences = [];
    $message = "<div class='error'>Liaison BDD impossible : " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enrôlement Agent - WinTech V2.5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .glass-card { background: white; color: #1e293b; border-radius: 20px; padding: 40px; width: 100%; max-width: 550px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); border-top: 6px solid #e11d48; }
        .form-label { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: #64748b; margin-top: 10px;}
        .form-control, .form-select { border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .btn-submit { background: #e11d48; color: white; font-weight: 800; border: none; padding: 12px; border-radius: 8px; width: 100%; margin-top: 20px;}
        .success { background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="glass-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold">ENRÔLEMENT AGENT</h3>
        <p class="text-muted small">YAOCOM'S GROUPE — Accès Production</p>
    </div>

    <?= $message ?>

    <form method="POST" enctype="multipart/form-data">
        <label class="form-label">Nom complet</label>
        <input type="text" name="nom_complet" class="form-control" placeholder="Prénom et Nom" required>
        
        <div class="row">
            <div class="col-md-7">
                <label class="form-label">Email Pro</label>
                <input type="email" name="email" class="form-control" placeholder="agent@yaocomsgroup.tech" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Année Naissance</label>
                <input type="number" name="annee_naissance" class="form-control" placeholder="1990" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Mot de passe</label>
                <input type="text" name="password" class="form-control" placeholder="Mot de passe" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Rôle</label>
                <select name="role" class="form-select">
                    <option value="SUPERADMIN">SuperAdmin</option>
                    <option value="ADMIN">Administrateur</option>
                    <option value="COMMERCIAL">Commercial</option>
                </select>
            </div>
        </div>

        <label class="form-label">Agence de rattachement</label>
        <select name="id_agence" class="form-select">
            <?php foreach($agences as $ag): ?>
                <option value="<?= $ag['id'] ?>"><?= $ag['nom'] ?></option>
            <?php endforeach; ?>
        </select>

        <label class="form-label">Photo de profil (JPG/PNG)</label>
        <input type="file" name="photo" class="form-control" accept="image/*">

        <button type="submit" class="btn-submit shadow">CRÉER L'ACCÈS SUR LE SERVEUR</button>
    </form>
</div>

</body>
</html>