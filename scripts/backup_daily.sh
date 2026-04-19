#!/bin/bash

# Configuration
DB_USER="dywytkyvna_aboua"
DB_PASS="Yaocoms*2021"
DB_NAME="dywytkyvna_wintech_erp_v2"
BACKUP_DIR="/home/dywytkyvna/wintech_erp/storage/backups"
DATE=$(date +%Y-%m-%d_%Hh%M)

# Création du dossier si inexistant
mkdir -p $BACKUP_DIR

# Dump de la base de données
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Archivage électronique : Supprimer les sauvegardes de plus de 30 jours
find $BACKUP_DIR -type f -mtime +30 -name "*.sql.gz" -delete

echo "Sauvegarde WinTech effectuée avec succès le $DATE"