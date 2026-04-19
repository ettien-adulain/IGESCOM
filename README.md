# WinTech ERP V2.5 - GIA Edition

Plateforme de gestion intégrée (ATIC, Ventes, Logistique, RH) pour YAOCOM'S GROUPE.

## 🚀 Installation Rapide
1. Transférez le dossier complet sur `/home/dywytkyvna/wintech_erp/`.
2. Configurez le "Document Root" de votre sous-domaine vers `/wintech_erp/public/`.
3. Importez le fichier SQL fourni dans `phpMyAdmin`.
4. Éditez le fichier `.env` avec les identifiants de production.

## 🛠 Maintenance
Le système dispose d'une console CLI pour les tâches administratives :
- `php bin/console db:backup` : Force une sauvegarde immédiate.
- `php bin/console app:clear` : Vide le cache et les logs temporaires.
- `php bin/console user:create-admin [matricule] [password]` : Crée un accès d'urgence.

## 🔐 Archivage (Module 5)
Tous les documents sont archivés dans `public/uploads/pdf_archives/`. 
Le journal de sécurité est disponible dans `storage/logs/security.log`.

---
© 2026 YAOCOM'S GROUPE - Système Certifié.