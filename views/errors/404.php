<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>404 - Page Introuvable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .error-box { text-align: center; border-top: 5px solid #e11d48; background: rgba(255,255,255,0.05); padding: 50px; border-radius: 20px; }
        h1 { font-size: 6rem; font-weight: 800; color: #e11d48; margin: 0; }
    </style>
</head>
<body>
    <div class="error-box shadow-lg">
        <h1>404</h1>
        <h3 class="fw-bold">RESSOURCE INTROUVABLE</h3>
        <p class="text-white-50">La page que vous tentez d'ouvrir n'existe pas ou a été déplacée.</p>
        <a href="/public/dashboard" class="btn btn-danger px-4 mt-3 fw-bold rounded-pill">Retour au Dashboard</a>
    </div>
</body>
</html>