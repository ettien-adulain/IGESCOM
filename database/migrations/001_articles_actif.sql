-- WinTech ERP / IGESCOM — désactivation catalogue (soft)
-- Exécuter une fois dans phpMyAdmin ou mysql CLI sur la base du projet.
-- Si la colonne existe déjà, ignorer l’erreur ou adapter.

ALTER TABLE `articles`
  ADD COLUMN `actif` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=visible catalogue, 0=désactivé' AFTER `id_categorie`;
