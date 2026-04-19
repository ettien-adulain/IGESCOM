<?php
namespace App\Services;

/**
 * WINTECH ERP V2.5 - SOVEREIGN EMAIL ENGINE
 * Logiciel de Gestion Intégrée - YAOCOM'S GROUPE
 * 
 * Ce service utilise PHPMailer pour garantir une délivrabilité maximale 
 * sur les serveurs PlanetHoster (N0C) et Microsoft 365.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use App\Utils\Logger;
use App\Utils\Formatter;

class EmailService {

    private PHPMailer $mailer;
    private array $config;
    private string $mainEmail = "info@yaocomsgroup.tech";

    /**
     * CONSTRUCTEUR : Initialisation du moteur SMTP sécurisé
     */
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Configuration Serveur (Basée sur les standards N0C PlanetHoster)
            $this->mailer->isSMTP();
            $this->mailer->Host       = 'mail.yaocomsgroup.tech'; 
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->mainEmail;
            $this->mailer->Password   = 'Yaocoms*2021'; // Récupéré via .env en production
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL forcé
            $this->mailer->Port       = 465;
            
            // Identité Expéditeur
            $this->mailer->setFrom($this->mainEmail, "YAOCOM'S GROUPE - Services Commerciaux");
            $this->mailer->addReplyTo($this->mainEmail, "Support Client YCS");
            
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            Logger::log("EMAIL_INIT_ERROR", "Échec init SMTP: " . $this->mailer->ErrorInfo);
        }
    }

    /**
     * MODULE 4.4 : ENVOI DE DOCUMENT (PROFORMA / FACTURE)
     * @param array $docData Données issues du DocumentRepository
     * @param string $pdfRelativePath Chemin vers le fichier SAE
     */
    public function sendDocument(array $docData, string $pdfRelativePath): bool {
        try {
            $clientEmail = $docData['client_email'];
            $clientName  = $docData['client_nom'];
            $ref         = $docData['numero_officiel'];
            $type        = $docData['type_doc']; // PROFORMA ou FACTURE

            // 1. Destinataire
            $this->mailer->addAddress($clientEmail, $clientName);
            
            // 2. Copie invisible pour archivage admin (Module 5)
            $this->mailer->addBCC($this->mainEmail);

            // 3. Pièce Jointe
            $physicalPath = dirname(__DIR__, 2) . '/public/' . $pdfRelativePath;
            if (file_exists($physicalPath)) {
                $this->mailer->addAttachment($physicalPath, $ref . ".pdf");
            } else {
                throw new Exception("Fichier PDF introuvable pour l'envoi.");
            }

            // 4. Objet dynamique
            $this->mailer->Subject = "[$type] Votre document commercial : $ref - YAOCOM'S GROUPE";

            // 5. Corps du message (Template Elite V3)
            $this->mailer->Body = $this->getEliteTemplate([
                'title'   => ($type == 'FACTURE' ? "VOTRE FACTURE DÉFINITIVE" : "VOTRE FACTURE PROFORMA"),
                'client'  => $clientName,
                'content' => "Nous avons le plaisir de vous transmettre le document <strong>$ref</strong> lié à votre commande.<br><br>
                              Ce document a été certifié et archivé par notre système de gestion intégrée WinTech ERP.",
                'amount'  => Formatter::fcfa($docData['net_a_payer']),
                'footer'  => "Ce message est généré automatiquement. Pour toute assistance, contactez le Plateau, Avenue Chardy."
            ]);

            $this->mailer->send();
            
            Logger::log("EMAIL_SUCCESS", "Document $ref envoyé avec succès à $clientEmail");
            return true;

        } catch (Exception $e) {
            Logger::log("EMAIL_SEND_FAIL", "Erreur envoi document $ref : " . $this->mailer->ErrorInfo);
            return false;
        } finally {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
        }
    }

    /**
     * MODULE 4.3 : NOTIFICATION INTERNE (ALERTE VALIDATION)
     */
    public function notifyAdmin(string $subject, string $message): void {
        try {
            $this->mailer->addAddress($this->mainEmail, "Administrateur WinTech");
            $this->mailer->Subject = "🚨 ALERTE SYSTÈME : " . $subject;
            
            $this->mailer->Body = $this->getEliteTemplate([
                'title'   => "NOTIFICATION ADMINISTRATIVE",
                'client'  => "Équipe de Gestion",
                'content' => $message,
                'amount'  => "N/A",
                'footer'  => "Log d'activité généré par le serveur PlanetHoster N0C."
            ]);

            $this->mailer->send();
        } catch (Exception $e) {
            error_log("GIA Internal Notif Fail: " . $e->getMessage());
        } finally {
            $this->mailer->clearAddresses();
        }
    }

    /**
     * MOTEUR DE TEMPLATE HTML ÉLITE V3
     * Design: Noir Matte / Rouge Crimson / Blanc Pure
     */
    private function getEliteTemplate(array $p): string {
        return "
        <html>
        <body style='margin:0; padding:0; background-color:#f1f5f9; font-family:Arial, sans-serif;'>
            <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                <tr>
                    <td align='center' style='padding: 40px 0;'>
                        <table width='600' border='0' cellspacing='0' cellpadding='0' style='background-color:#ffffff; border-radius:15px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.1);'>
                            <!-- Header -->
                            <tr>
                                <td style='background-color:#0f172a; padding:40px; text-align:center;'>
                                    <h1 style='color:#ffffff; margin:0; font-size:24px; letter-spacing:2px;'>YAOCOM'S <span style='color:#e11d48;'>GROUPE</span></h1>
                                    <p style='color:#94a3b8; font-size:12px; margin-top:10px;'>EXCELLENCE TECHNOLOGIQUE & GESTION</p>
                                </td>
                            </tr>
                            <!-- Body -->
                            <tr>
                                <td style='padding:40px; color:#1e293b; line-height:1.6;'>
                                    <h2 style='color:#e11d48; font-size:18px; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;'>{$p['title']}</h2>
                                    <p>Bonjour <strong>{$p['client']}</strong>,</p>
                                    <p>{$p['content']}</p>
                                    
                                    <div style='margin:30px 0; background-color:#f8fafc; border-left:4px solid #e11d48; padding:20px; text-align:center;'>
                                        <span style='font-size:12px; color:#64748b; text-transform:uppercase; font-weight:bold;'>Montant Net à Payer</span><br>
                                        <span style='font-size:28px; color:#0f172a; font-weight:900;'>{$p['amount']}</span>
                                    </div>

                                    <p style='font-size:13px;'>Vous pouvez télécharger votre document en pièce jointe de ce courriel.</p>
                                </td>
                            </tr>
                            <!-- Footer -->
                            <tr>
                                <td style='background-color:#f1f5f9; padding:30px; text-align:center; color:#64748b; font-size:11px;'>
                                    <p style='margin:0;'>{$p['footer']}</p>
                                    <p style='margin-top:10px;'>&copy; 2026 YAOCOM'S GROUPE - Système Certifié WinTech ERP</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }
}