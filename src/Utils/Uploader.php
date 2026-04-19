<?php

namespace App\Utils;

use Exception;

/**
 * Uploader - Utilitaire de gestion des fichiers pour WinTech ERP
 * Assure la sécurité des transferts et l'intégrité du stockage.
 */
class Uploader
{
    /**
     * Traite l'upload d'un fichier image.
     * 
     * @param array $file Le tableau $_FILES['input_name']
     * @param string $folder Le sous-dossier de destination (ex: 'articles')
     * @return string Le chemin relatif du fichier stocké
     * @throws Exception Si le fichier est invalide ou dangereux
     */
    public static function upload(array $file, string $folder): string
    {
        // 1. Vérification des erreurs natives PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur système lors du transfert du fichier.");
        }

        // 2. Validation de la taille (Max 2Mo pour le catalogue)
        if ($file['size'] > 2097152) {
            throw new Exception("Le fichier est trop lourd. Limite : 2 Mo.");
        }

        // 3. Validation stricte du type MIME
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("Format non autorisé. Utilisez JPG, PNG ou WEBP.");
        }

        // 4. Génération d'un nom immuable et unique (SAE compliant)
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = "WIN_" . bin2hex(random_bytes(8)) . "_" . time() . "." . $extension;

        // 5. Définition des chemins
        $relativePath = "uploads/" . $folder . "/" . $uniqueName;
        $absolutePath = dirname(__DIR__, 2) . "/public/" . $relativePath;

        // 6. Création du répertoire si absent (CHMOD 775)
        if (!is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0775, true);
        }

        // 7. Déplacement final vers le stockage public
        if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return $relativePath;
        }

        throw new Exception("Impossible d'écrire le fichier sur le serveur.");
    }
}