<?php
// Service d'envoi d'emails
// Gère l'envoi de notifications par email via SMTP (Gmail)

namespace src\Models;

// Charger l'autoloader de Composer si ce n'est pas déjà fait
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    
    // Configuration SMTP par défaut (Gmail)
    private $smtpConfig = [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'gestion.absences12@gmail.com',
        'password' => 'zxxm srcb zvox vzgx ',
        'from_email' => 'gestion.absences12@gmail.com',
        'from_name' => '[GESTION-ABS]',
        'smtp_secure' => PHPMailer::ENCRYPTION_STARTTLS
    ];

    // Constructeur - Initialise PHPMailer et configure SMTP
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }

    // Configure les paramètres SMTP pour l'envoi d'emails
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
        
        // Options SSL pour Mac
        $this->mailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Timeout plus long
        $this->mailer->Timeout = 30;
        
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
     * Permet de modifier la configuration SMTP
     */
    public function setSMTPConfig($config)
    {
        $this->smtpConfig = array_merge($this->smtpConfig, $config);
        $this->configureSMTP();
    }

    /**
 * Envoie un email contenant le nouveau mot de passe après réinitialisation.
 *
 * @param string $toEmail
 * @param string $toName
 * @param string $newPassword
 * @return bool
 */
public function sendPasswordResetEmail($toEmail, $toName, $newPassword)
{
    try {
        $this->mailer->clearAddresses();
        $this->mailer->clearAttachments();
        $this->mailer->addAddress($toEmail, $toName);

        $this->mailer->Subject = 'Réinitialisation de votre mot de passe';

        // Corps du message HTML
        $htmlBody = $this->generatePasswordResetHTML($toName, $newPassword);
        $this->mailer->isHTML(true);
        $this->mailer->Body = $htmlBody;
        
        // Version texte
        $this->mailer->AltBody = $this->generatePasswordResetText($toName, $newPassword);

        $this->mailer->send();
        return true;
    } catch (Exception $e) {
        error_log('Erreur lors de l\'envoi email reset: ' . $this->mailer->ErrorInfo);
        return false;
    }
}

/**
 * Génère le corps HTML de l'email de réinitialisation de mot de passe
 */
private function generatePasswordResetHTML($toName, $newPassword)
{
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
            .password-box { background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 20px; margin: 15px 0; text-align: center; }
            .password { font-size: 1.3em; font-weight: bold; color: #2e7d32; letter-spacing: 2px; font-family: 'Courier New', monospace; }
            .warning-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Réinitialisation de votre mot de passe</h2>
            </div>
            <div class='content'>
                <p>Bonjour <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                <p>Votre mot de passe a été réinitialisé avec succès. Voici votre nouveau mot de passe temporaire :</p>
                
                <div class='password-box'>
                    <p style='margin: 0 0 10px 0; font-size: 0.9em; color: #666;'>Votre nouveau mot de passe :</p>
                    <div class='password'>" . htmlspecialchars($newPassword) . "</div>
                </div>
                
                <div class='warning-box'>
                    <strong>Important :</strong> Pour des raisons de sécurité, veuillez vous connecter et modifier votre mot de passe dès que possible.
                </div>
                
                <div class='info-box'>
                    <h3>Étapes à suivre :</h3>
                    <ol style='margin: 10px 0; padding-left: 20px;'>
                        <li>Connectez-vous avec ce mot de passe temporaire</li>
                        <li>Accédez à votre profil</li>
                        <li>Modifiez votre mot de passe immédiatement</li>
                    </ol>
                </div>
                
                <p>Si vous n'avez pas demandé cette réinitialisation, veuillez contacter immédiatement le service de gestion des absences.</p>
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
 * Génère le corps texte de l'email de réinitialisation de mot de passe
 */
private function generatePasswordResetText($toName, $newPassword)
{
    return "Bonjour " . $toName . ",\n\n" .
           "Votre mot de passe a été réinitialisé avec succès.\n\n" .
           "Votre nouveau mot de passe temporaire : " . $newPassword . "\n\n" .
           "IMPORTANT : Pour des raisons de sécurité, veuillez vous connecter et modifier votre mot de passe dès que possible.\n\n" .
           "Étapes à suivre :\n" .
           "1. Connectez-vous avec ce mot de passe temporaire\n" .
           "2. Accédez à votre profil\n" .
           "3. Modifiez votre mot de passe immédiatement\n\n" .
           "Si vous n'avez pas demandé cette réinitialisation, veuillez contacter immédiatement le service.\n\n" .
           "Cet email a été envoyé automatiquement, merci de ne pas y répondre.\n" .
           "EduTrack - Gestion des Absences";
}

/**
 * Envoie un email à l'étudiant pour demander des justificatifs supplémentaires
 * 
 * @param string $toEmail Email de l'étudiant
 * @param string $toName Nom de l'étudiant
 * @param string $motif Motif de la demande
 * @return bool True si l'email a été envoyé avec succès
 */
public function sendJustificationRequestEmail($toEmail, $toName, $motif)
{
    try {
        $this->mailer->clearAddresses();
        $this->mailer->clearAttachments();
        $this->mailer->addAddress($toEmail, $toName);

        $this->mailer->Subject = 'Demande de justificatifs supplémentaires';

        // Corps du message HTML
        $htmlBody = $this->generateJustificationRequestHTML($toName, $motif);
        $this->mailer->isHTML(true);
        $this->mailer->Body = $htmlBody;
        
        // Version texte
        $this->mailer->AltBody = $this->generateJustificationRequestText($toName, $motif);

        $this->mailer->send();
        return true;
    } catch (Exception $e) {
        error_log('Erreur lors de l\'envoi de la demande de justificatif: ' . $this->mailer->ErrorInfo);
        return false;
    }
}

/**
 * Génère le corps HTML de l'email de demande de justificatif
 */
private function generateJustificationRequestHTML($toName, $motif)
{
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
            .motif-box { background-color: #fff8e1; border-left: 4px solid #ffa726; padding: 15px; margin: 15px 0; }
            .action-box { background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 15px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Demande de justificatifs supplémentaires</h2>
            </div>
            <div class='content'>
                <p>Bonjour <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                <p>Le responsable pédagogique vous demande de fournir des justificatifs supplémentaires concernant votre absence.</p>
                
                <div class='motif-box'>
                    <h3 style='margin-top: 0;'>Motif de la demande :</h3>
                    <p style='margin-bottom: 0;'>" . nl2br(htmlspecialchars($motif)) . "</p>
                </div>
                
                <div class='action-box'>
                    <h3 style='margin-top: 0;'>Action requise :</h3>
                    <p>Veuillez envoyer les documents nécessaires dès que possible via la plateforme de gestion des absences.</p>
                </div>
                
                <div class='info-box'>
                    <h3>Documents acceptés :</h3>
                    <ul style='margin: 10px 0; padding-left: 20px;'>
                        <li>Certificat médical original</li>
                        <li>Justificatif officiel (convocation, attestation)</li>
                        <li>Tout document probant relatif à votre absence</li>
                    </ul>
                </div>
                
                <p>Pour toute question, n'hésitez pas à contacter votre responsable pédagogique.</p>
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
 * Génère le corps texte de l'email de demande de justificatif
 */
private function generateJustificationRequestText($toName, $motif)
{
    return "Bonjour " . $toName . ",\n\n" .
           "Le responsable pédagogique vous demande de fournir des justificatifs supplémentaires concernant votre absence.\n\n" .
           "Motif de la demande :\n" .
           $motif . "\n\n" .
           "Action requise :\n" .
           "Veuillez envoyer les documents nécessaires dès que possible via la plateforme de gestion des absences.\n\n" .
           "Documents acceptés :\n" .
           "- Certificat médical original\n" .
           "- Justificatif officiel (convocation, attestation)\n" .
           "- Tout document probant relatif à votre absence\n\n" .
           "Pour toute question, n'hésitez pas à contacter votre responsable pédagogique.\n\n" .
           "Cet email a été envoyé automatiquement, merci de ne pas y répondre.\n" .
           "EduTrack - Gestion des Absences";
}


    /**
     * Méthode générique pour envoyer un email personnalisé
     * 
     * @param string $toEmail Email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $htmlBody Corps de l'email en HTML
     * @param string $toName Nom du destinataire (optionnel)
     * @param string $textBody Version texte de l'email (optionnel)
     * @return bool True si l'email a été envoyé avec succès, false sinon
     */
    public function envoyerEmail($toEmail, $subject, $htmlBody, $toName = '', $textBody = '')
    {
        try {
            // Réinitialiser les destinataires pour chaque envoi
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Destinataire
            $this->mailer->addAddress($toEmail, $toName);
            
            // Sujet
            $this->mailer->Subject = $subject;
            
            // Corps du message HTML
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            
            // Version texte si fournie
            if (!empty($textBody)) {
                $this->mailer->AltBody = $textBody;
            }
            
            // Envoi
            $this->mailer->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de l'email: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    /**
 * Envoie un email d'alerte à l'étudiant pour lui rappeler de fournir un justificatif
 * 
 * @param string $studentEmail Email de l'étudiant
 * @param string $studentName Nom de l'étudiant
 * @param int $absenceId ID de l'absence
 * @param string $dateStart Date de début d'absence
 * @param string $dateEnd Date de fin d'absence
 * @param string $cours Nom du cours concerné
 * @param bool $isUrgent True si c'est un jour d'évaluation
 * @param int $heuresRestantes Nombre d'heures restantes pour justifier
 * @return bool True si l'email a été envoyé avec succès
 */
public function sendAbsenceAlertEmail($studentEmail, $studentName, $absenceId, $dateStart, $dateEnd, $cours, $isUrgent = false, $heuresRestantes = 48)
{
    try {
        // Réinitialiser les destinataires pour chaque envoi
        $this->mailer->clearAddresses();
        $this->mailer->clearAttachments();
        
        // Destinataire
        $this->mailer->addAddress($studentEmail, $studentName);
        
        // Sujet
        $urgencyPrefix = $isUrgent ? '[URGENT] ' : '';
        $this->mailer->Subject = $urgencyPrefix . 'Justificatif d\'absence requis';
        
        // Corps du message HTML
        $htmlBody = $this->generateAbsenceAlertHTML($studentName, $dateStart, $dateEnd, $cours, $isUrgent, $heuresRestantes);
        $this->mailer->isHTML(true);
        $this->mailer->Body = $htmlBody;
        
        // Version texte pour les clients email qui ne supportent pas HTML
        $this->mailer->AltBody = $this->generateAbsenceAlertText($studentName, $dateStart, $dateEnd, $cours, $isUrgent, $heuresRestantes);
        
        // Envoi
        $this->mailer->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi de l'alerte d'absence: " . $this->mailer->ErrorInfo);
        return false;
    }
}

/**
 * Génère le corps HTML de l'email d'alerte d'absence
 */
private function generateAbsenceAlertHTML($studentName, $dateStart, $dateEnd, $cours, $isUrgent, $heuresRestantes)
{
    $dateStartFormatted = date('d/m/Y à H:i', strtotime($dateStart));
    $dateEndFormatted = date('d/m/Y à H:i', strtotime($dateEnd));
    
    $urgencyStyle = $isUrgent ? 'background-color: #dc3545;' : 'background-color: #29acc8;';
    $urgencyMessage = $isUrgent ? 
        '<div class="warning-box">
            <strong>ATTENTION :</strong> Cette absence concerne un jour d\'évaluation. Votre justificatif est requis de manière urgente.
        </div>' : '';
    
    return "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { {$urgencyStyle} color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 20px; }
            .info-box { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #29acc8; }
            .warning-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
            .deadline-box { background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; font-weight: bold; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Justificatif d'absence requis</h2>
            </div>
            <div class='content'>
                <p>Bonjour <strong>{$studentName}</strong>,</p>
                <p>Nous avons constaté une absence de votre part et vous devez fournir un justificatif dans les délais impartis.</p>
                
                {$urgencyMessage}
                
                <div class='info-box'>
                    <h3>Détails de votre absence :</h3>
                    <p><strong>Cours :</strong> {$cours}</p>
                    <p><strong>Date de début :</strong> {$dateStartFormatted}</p>
                    <p><strong>Date de fin :</strong> {$dateEndFormatted}</p>
                </div>
                
                <div class='deadline-box'>
                    <strong>Délai restant :</strong> {$heuresRestantes} heures
                </div>
                
                <p><strong>Important :</strong> Vous disposez de 48 heures après votre retour en cours pour déposer votre justificatif. Passé ce délai, l'absence sera considérée comme non justifiée et verrouillée définitivement.</p>
                
                <p>Veuillez déposer votre justificatif dès que possible via la plateforme de gestion des absences.</p>
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
 * Génère le corps texte de l'email d'alerte d'absence
 */
private function generateAbsenceAlertText($studentName, $dateStart, $dateEnd, $cours, $isUrgent, $heuresRestantes)
{
    $dateStartFormatted = date('d/m/Y à H:i', strtotime($dateStart));
    $dateEndFormatted = date('d/m/Y à H:i', strtotime($dateEnd));
    
    $urgencyText = $isUrgent ? "\nATTENTION : Cette absence concerne un jour d'évaluation.\n" : '';
    
    return "Bonjour {$studentName},\n\n" .
           "Nous avons constaté une absence de votre part et vous devez fournir un justificatif dans les délais impartis.\n" .
           $urgencyText . "\n" .
           "Détails de votre absence :\n" .
           "Cours : {$cours}\n" .
           "Date de début : {$dateStartFormatted}\n" .
           "Date de fin : {$dateEndFormatted}\n\n" .
           "Délai restant : {$heuresRestantes} heures\n\n" .
           "IMPORTANT : Vous disposez de 48 heures après votre retour en cours pour déposer votre justificatif. " .
           "Passé ce délai, l'absence sera considérée comme non justifiée et verrouillée définitivement.\n\n" .
           "Veuillez déposer votre justificatif dès que possible via la plateforme de gestion des absences.\n\n" .
           "Cet email a été envoyé automatiquement, merci de ne pas y répondre.\n" .
           "EduTrack - Gestion des Absences";
}

}

