<?php
require __DIR__ . '/../vendor/autoload.php';

use src\Database\Database;
use src\Models\EmailService;

$message = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['identifiant_email'] ?? '');

    if (empty($input)) {
        $message = 'Veuillez renseigner votre identifiant.';
    } else {
        try {
            $db = new Database();
            $pdo = $db->getConnection();

            // Chercher l'utilisateur par identifiant
            $stmt = $pdo->prepare('SELECT identifiantcompte AS identifiant, nom, prenom, fonction FROM compte WHERE identifiantcompte = :identifiant');
            $stmt->execute([':identifiant' => $input]);
            
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                $message = 'Aucun utilisateur trouvé pour cet identifiant.';
            } else {
                // Générer un nouveau mot de passe
                $newPwd = bin2hex(random_bytes(5));
                
                // Hacher le mot de passe avant de le stocker
                $hashedPwd = password_hash($newPwd, PASSWORD_DEFAULT);

                // Mettre à jour en base avec le mot de passe haché
                $update = $pdo->prepare('UPDATE compte SET mot_de_passe = :pwd WHERE identifiantcompte = :identifiant');
                $updated = $update->execute([':pwd' => $hashedPwd, ':identifiant' => $user['identifiant']]);

                if ($updated) {
                    // Générer l'email - tous les utilisateurs ont @uphf.fr
                    $email = $user['identifiant'] . '@uphf.fr';
                    
                    // Envoyer l'email
                    $emailService = new EmailService();
                    $name = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
                    $sent = $emailService->sendPasswordResetEmail($email, $name ?: $user['identifiant'], $newPwd);

                    if ($sent) {
                        $message = 'Un email contenant votre nouveau mot de passe a été envoyé à ' . htmlspecialchars($email) . '.';
                    } else {
                        $message = 'Impossible d\'envoyer l\'email. Contactez un administrateur.';
                    }
                } else {
                    $message = 'Impossible de mettre à jour le mot de passe en base.';
                }
            }

        } catch (\Exception $e) {
            $message = 'Erreur serveur : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Gestion des Absences</title>
    <link rel="stylesheet" href="asset/CSS/cssDeBase.css">
    <link rel="stylesheet" href="asset/CSS/cssConnexion.css">
</head>
<body>
<div class="uphf">
    <img src="asset/img/logouphf.png" alt="Logo uphf">
</div>
<div class="logoEdu">
    <img src="asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="wapper">
    <h2>Mot de passe oublié</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form action="mdpOublier.php" method="post">
        <label for="identifiant_email">Identifiant :</label>
        <input type="text" id="identifiant_email" name="identifiant_email" required>
        <br>
        <button type="submit">Recevoir un nouveau mot de passe</button>
    </form>

    <p><a href="index.php">Retour à la connexion</a></p>
</div>

</body>
</html>
