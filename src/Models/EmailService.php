<?php

namespace src\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    
    /**
     * Configuration SMTP par défaut
     * À modifier selon votre configuration serveur
     */
    private $smtpConfig = [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'gestion.absences12@gmail.com',
        'password' => 'zxxm srcb zvox vzgx ',
        'from_email' => 'gestion.absences12@gmail.com',
        'from_name' => '[GESTION-ABS]',
        'smtp_secure' => PHPMailer::ENCRYPTION_STARTTLS
    ];

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Configure les paramètres SMTP
     */
    private function configureSMTP()
    {
        try {
            // Configuration SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->smtpConfig['host'];
            $this->mailer->SMTPAuth = !empty($this->smtpConfig['username']);
            $this->mailer->Username = $this->smtpConfig['username'];
            $this->mailer->Password = $this->smtpConfig['password'];
            $this->mailer->SMTPSecure = $this->smtpConfig['smtp_secure'];
            $this->mailer->Port = $this->smtpConfig['port'];
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            
            $this->mailer->setFrom(
                $this->smtpConfig['from_email'],
                $this->smtpConfig['from_name']
            );
        } catch (Exception $e) {
            error_log("Erreur de configuration SMTP: " . $this->mailer->ErrorInfo);
        }
    }

    /**
     * Envoie un email de confirmation à l'étudiant après le dépôt d'un justificatif
     * 
     * @param string $studentEmail Email de l'étudiant
     * @param string $studentName Nom de l'étudiant
     * @param string $dateStart Date de début d'absence
     * @param string $dateEnd Date de fin d'absence
     * @param string $motif Motif de l'absence
     * @return bool True si l'email a été envoyé avec succès, false sinon
     */
    public function sendAbsenceConfirmationEmail($studentEmail, $studentName, $dateStart, $dateEnd, $motif)
    {
        try {
            // Réinitialiser les destinataires pour chaque envoi
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Destinataire
            $this->mailer->addAddress($studentEmail, $studentName);
            
            // Sujet
            $this->mailer->Subject = 'Confirmation de dépôt de justificatif d\'absence';
            
            // Corps du message HTML
            $htmlBody = $this->generateConfirmationEmailHTML($studentName, $dateStart, $dateEnd, $motif);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            
            // Version texte pour les clients email qui ne supportent pas HTML
            $this->mailer->AltBody = $this->generateConfirmationEmailText($studentName, $dateStart, $dateEnd, $motif);
            
            // Envoi
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de l'email: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    /**
     * Envoie un email de notification au responsable pédagogique
     * 
     * @param string $responsibleEmail Email du responsable
     * @param string $studentName Nom de l'étudiant
     * @param string $studentId Identifiant de l'étudiant
     * @param string $dateStart Date de début d'absence
     * @param string $dateEnd Date de fin d'absence
     * @param string $motif Motif de l'absence
     * @param string $filePath Chemin du fichier justificatif (optionnel)
     * @return bool True si l'email a été envoyé avec succès, false sinon
     */
    public function sendNotificationToResponsible($responsibleEmail, $studentName, $studentId, $dateStart, $dateEnd, $motif, $filePath = null)
    {
        try {
            // Réinitialiser les destinataires pour chaque envoi
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Destinataire
            $this->mailer->addAddress($responsibleEmail);
            
            // Sujet
            $this->mailer->Subject = 'Nouveau justificatif d\'absence - ' . $studentName;
            
            // Ajouter la pièce jointe si fournie
            if ($filePath && file_exists($filePath)) {
                $this->mailer->addAttachment($filePath);
            }
            
            // Corps du message HTML
            $htmlBody = $this->generateNotificationEmailHTML($studentName, $studentId, $dateStart, $dateEnd, $motif);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            
            // Version texte
            $this->mailer->AltBody = $this->generateNotificationEmailText($studentName, $studentId, $dateStart, $dateEnd, $motif);
            
            // Envoi
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de l'email au responsable: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    /**
     * Génère le corps HTML de l'email de confirmation pour l'étudiant
     */
    private function generateConfirmationEmailHTML($studentName, $dateStart, $dateEnd, $motif)
    {
        $dateStartFormatted = date('d/m/Y à H:i', strtotime($dateStart));
        $dateEndFormatted = date('d/m/Y à H:i', strtotime($dateEnd));
        
        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #29acc8; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .info-box { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #29acc8; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Confirmation de dépôt de justificatif</h2>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>{$studentName}</strong>,</p>
                    <p>Nous vous confirmons la réception de votre justificatif d'absence.</p>
                    
                    <div class='info-box'>
                        <h3>Détails de votre absence :</h3>
                        <p><strong>Date de début :</strong> {$dateStartFormatted}</p>
                        <p><strong>Date de fin :</strong> {$dateEndFormatted}</p>
                        <p><strong>Motif :</strong> {$motif}</p>
                    </div>
                    
                    <p>Vous serez informé(e) de la suite donnée à votre demande.</p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                    <p>EduTrack - Gestion des Absences</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Génère le corps texte de l'email de confirmation pour l'étudiant
     */
    private function generateConfirmationEmailText($studentName, $dateStart, $dateEnd, $motif)
    {
        $dateStartFormatted = date('d/m/Y à H:i', strtotime($dateStart));
        $dateEndFormatted = date('d/m/Y à H:i', strtotime($dateEnd));
        
        return "Bonjour {$studentName},\n\n" .
               "Nous vous confirmons la réception de votre justificatif d'absence.\n\n" .
               "Détails de votre absence :\n" .
               "Date de début : {$dateStartFormatted}\n" .
               "Date de fin : {$dateEndFormatted}\n" .
               "Motif : {$motif}\n\n" .
               "Vous serez informé(e) de la suite donnée à votre demande.\n\n" .
               "Cet email a été envoyé automatiquement, merci de ne pas y répondre.\n" .
               "EduTrack - Gestion des Absences";
    }

    /**
     * Génère le corps HTML de l'email de notification pour le responsable
     */
    private function generateNotificationEmailHTML($studentName, $studentId, $dateStart, $dateEnd, $motif)
    {
        $dateStartFormatted = date('d/m/Y à H:i', strtotime($dateStart));
        $dateEndFormatted = date('d/m/Y à H:i', strtotime($dateEnd));
        
        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2196F3; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .info-box { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #2196F3; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Nouveau justificatif d'absence</h2>
                </div>
                <div class='content'>
                    <p>Bonjour,</p>
                    <p>Un nouvel étudiant a déposé un justificatif d'absence.</p>
                    
                    <div class='info-box'>
                        <h3>Informations de l'étudiant :</h3>
                        <p><strong>Nom :</strong> {$studentName}</p>
                        <p><strong>Identifiant :</strong> {$studentId}</p>
                        <p><strong>Date de début :</strong> {$dateStartFormatted}</p>
                        <p><strong>Date de fin :</strong> {$dateEndFormatted}</p>
                        <p><strong>Motif :</strong> {$motif}</p>
                    </div>
                    
                    <p>Le justificatif est joint à cet email.</p>
                    <p>Veuillez traiter cette demande dans les plus brefs délais.</p>
                </div>
                <div class='footer'>
                    <p>EduTrack - Gestion des Absences</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Génère le corps texte de l'email de notification pour le responsable
     */
    private function generateNotificationEmailText($studentName, $studentId, $dateStart, $dateEnd, $motif)
    {
        $dateStartFormatted = date('d/m/Y à H:i', strtotime($dateStart));
        $dateEndFormatted = date('d/m/Y à H:i', strtotime($dateEnd));
        
        return "Bonjour,\n\n" .
               "Un nouvel étudiant a déposé un justificatif d'absence.\n\n" .
               "Informations de l'étudiant :\n" .
               "Nom : {$studentName}\n" .
               "Identifiant : {$studentId}\n" .
               "Date de début : {$dateStartFormatted}\n" .
               "Date de fin : {$dateEndFormatted}\n" .
               "Motif : {$motif}\n\n" .
               "Le justificatif est joint à cet email.\n" .
               "Veuillez traiter cette demande dans les plus brefs délais.\n\n" .
               "EduTrack - Gestion des Absences";
    }

    /**
     * Permet de modifier la configuration SMTP
     */
    public function setSMTPConfig($config)
    {
        $this->smtpConfig = array_merge($this->smtpConfig, $config);
        $this->configureSMTP();
    }
}

