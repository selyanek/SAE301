<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Autoloader de Composer

class EmailService {
    private $mailer;
    private $db;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
        
        // Connexion BDD pour logs
        $database = new Database();
        $this->db = $database->getConnection();
    }

     /**
     * Configuration du serveur SMTP
     */
    private function configureSMTP() {
        try {
            // Mode debug
            if (EMAIL_DEBUG) {
                $this->mailer->SMTPDebug = 2; // 0=off, 1=client, 2=server
            }

            // Configuration serveur
            $this->mailer->isSMTP();
            $this->mailer->Host = USE_DEV_SMTP ? SMTP_HOST_DEV : SMTP_HOST;
            $this->mailer->Port = USE_DEV_SMTP ? SMTP_PORT_DEV : SMTP_PORT;
            
            if (!USE_DEV_SMTP) {
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = SMTP_USERNAME;
                $this->mailer->Password = SMTP_PASSWORD;
                $this->mailer->SMTPSecure = SMTP_ENCRYPTION;
            }

            // Encodage
            $this->mailer->CharSet = 'UTF-8';
            
            // Expéditeur par défaut
            $this->mailer->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            
        } catch (Exception $e) {
            error_log("Erreur configuration SMTP: " . $e->getMessage());
            throw $e;
        }
    }
    public function envoyerConfirmationSoumission($emailEtudiant, $idAbsence) {
        $sujet = "Justificatif reçu - En attente de traitement";
        
        $message = "
            <h2>Votre justificatif a bien été reçu</h2>
            <p>Bonjour,</p>
            <p>Nous vous confirmons la réception de votre justificatif d'absence.</p>
            
            <div>
                <strong>Référence :</strong> #{$idAbsence}<br>
                <strong>Statut :</strong> En attente de traitement<br>
                <strong>Date de soumission :</strong> " . date('d/m/Y à H:i') . "
            </div>
            
            <p>Votre justificatif sera examiné par le responsable pédagogique dans les plus brefs délais.</p>
            <p>Vous recevrez une notification par email dès qu'une décision sera prise.</p>
            
            <div style='margin: 30px 0;'>
                <a href='https://votre-site.fr/etudiant/suivi-justificatifs' 
                   style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                    Suivre mon justificatif
                </a>
            </div>
        ";
        
        return $this->send($emailEtudiant, $sujet, $message);
    }
    /**
     * Calculer le délai restant
     */
    private function calculerDelaiRestant($dateFin) {
        $fin = new DateTime($dateFin);
        $limite = clone $fin;
        $limite->modify('+48 hours');
        $maintenant = new DateTime();
        
        $diff = $maintenant->diff($limite);
        
        if ($diff->invert) {
            return "Expiré";
        }
        
        $heures = ($diff->days * 24) + $diff->h;
        return "{$heures}h {$diff->i}min";
    }
}