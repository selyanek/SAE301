<?php

/**
 * Creation de l'adresse mail avec la récupération de nom et prenom dans la base de donnée
 */
class EmailService
{
    /**
     * Constructeur pour faire l'adresse mail via le preom et le nom
     * @param $prenom
     * @param $nom
     * @return string
     */
    public static function buildEmailUphf($prenom, $nom){
        $prenom = self::nettoyerTexte($prenom);
        $nom = self::nettoyerTexte($nom);
        return strtolower($prenom . '.' . $nom . '@uphf.fr');
    }

    /**
     * Récuperation de l'adresse mail
     * @param $prenom
     * @param $nom
     * @return string
     */
    public static function recupereEmailUphf($prenom, $nom)
    {
        return self::buildEmailUphf($prenom, $nom);
    }

    /**
     * Création du message de confirmation de dépot de justificatif
     * @param $destinataire
     * @param $nomComplet
     * @return bool
     */
    public static function messageConfirmation($destinataire, $nomComplet){
        $sujet = "[GESTION - ABS]";
        $message = "
            <html>
            <body>
            <div class='container'>
                <div class='header'>
                    <h2>Confirmation de dépôt</h2>
                </div>
                <div class='content'>
                    <p>Bonjour " . htmlspecialchars($nomComplet) . ",</p>
                    <p>Nous vous confirmons que votre dépôt a bien été enregistré.</p>
                    <p>Vous recevrez prochainement des informations complémentaires concernant le traitement de votre demande.</p>
                    <p>Cordialement,<br>L'équipe UPHF</p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "gestion.absences12@gmail.com" . "\r\n";

        return mail($destinataire, $sujet, $message, $headers);
    }
}