<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>403 - Accès Restreint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .forbidden-box { text-align: center; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 5px solid #0f172a; }
        .icon { font-size: 4rem; color: #e11d48; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="forbidden-box">
        <div class="icon"><i class="fas fa-shield-alt"></i></div>
        <h2 class="fw-bold">ACCÈS SÉCURISÉ</h2>
        <p class="text-muted">Vos privilèges actuels ne vous permettent pas d'accéder à ce module.</p>
        <p class="small">L'incident a été enregistré dans le journal d'audit de sécurité.</p>
        <a href="/public/dashboard" class="btn btn-dark px-4 mt-3 fw-bold">Demander l'accès</a>
    </div>
</body>
</html>