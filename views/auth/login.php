<?php
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/public') . '/public';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IGESCOM - Authentification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/wintech.css?v=<?= time() ?>">
</head>
<body class="login-body-v5">

    <div class="login-master-card animate-up">
        
        <!-- DROITE : IMAGE AGENT (Ordre inversé par CSS flex-direction) -->
        <div class="login-side-visual">
            <div class="igescom-tag">IGESCOM</div>
        </div>

        <!-- GAUCHE : FORMULAIRE DE CONNEXION -->
        <div class="login-side-form">
            <div class="text-start mb-4">
                <img src="<?= $base_url ?>/assets/img/static/logo-gescom.png" height="35" alt="Gescom">
            </div>

            <h2>Bienvenue!</h2>
            <p class="subtitle">Se connecter avec son compte</p>

            <form action="<?= $base_url ?>/authenticate" method="POST">
                
                <div class="gia-field">
                    <input type="text" name="matricule" placeholder="Identifiant*" required autofocus>
                    <i class="fas fa-user"></i>
                </div>

                <div class="gia-field">
                    <input type="password" name="password" id="password" placeholder="Mot de passe*" required>
                    <i class="fas fa-eye-slash" onclick="togglePass()" style="cursor:pointer;"></i>
                </div>

                <div class="gia-field">
                    <select name="id_agence" required>
                        <option value="" disabled selected>Agence*</option>
                        <?php foreach($agences as $ag): ?>
                            <option value="<?= $ag['id'] ?>"><?= htmlspecialchars($ag['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-home"></i>
                </div>

                <button type="submit" class="btn-gia-primary shadow">
                    Se Connecter
                </button>

            </form>
            <div class="mt-4">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill" style="font-size: 0.7rem;">
                    yaocomsgroup
                </span>
            </div>
        </div>
    </div>

    <!-- BRANDING GLOBAL EN BAS -->
    <div class="login-footer-ycs">
        YAOCOM'S <span>GROUPE</span>
    </div>

    <script>
        function togglePass() {
            const p = document.getElementById('password');
            p.type = (p.type === 'password') ? 'text' : 'password';
        }
    </script>
</body>
</html>