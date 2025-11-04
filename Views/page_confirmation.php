<!-- Page de confirmation après le dépôt d'un justificatif -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de dépôt</title>
    <link rel="stylesheet" href="../CSS/cssDeBase.css.css">
</head>
<body>
<div class="container">
    <?php
    session_start();

    if (isset($_SESSION['confirmation']) && $_SESSION['confirmation']) {
        $email = htmlspecialchars($_SESSION['email_confirmation']);
        $nom = htmlspecialchars($_SESSION['nom_complet']);
        $emailEnvoye = $_SESSION['email_envoye'] ?? false;

        echo '<div class="confirmation">';
        echo '<h2>Dépôt confirmé</h2>';
        echo '<p>Bonjour <strong>' . $nom . '</strong>,</p>';
        echo '<p>Votre dépôt a bien été enregistré dans notre système.</p>';
        echo '</div>';

        echo '<div class="info-box">';
        echo '<p><strong> Email de confirmation</strong></p>';
        if ($emailEnvoye) {
            echo '<p>Un email de confirmation a été envoyé à l\'adresse : <span class="email">' . $email . '</span></p>';
        } else {
            echo '<p style="color: #856404;">Attention : L\'email n\'a pas pu être envoyé. Votre dépôt est cependant bien enregistré.</p>';
            echo '<p>Adresse email concernée : <span class="email">' . $email . '</span></p>';
        }
        echo '</div>';

        // Nettoyer la session
        unset($_SESSION['confirmation']);
        unset($_SESSION['email_confirmation']);
        unset($_SESSION['nom_complet']);
        unset($_SESSION['email_envoye']);
    } else {
        echo '<div class="erreur">';
        echo '<p><strong>Aucune confirmation disponible</strong></p>';
        echo '<p>Veuillez soumettre le formulaire pour effectuer un dépôt.</p>';
        echo '</div>';
    }
    ?>

    <div class="retour">
        <a href="../Views/depotJustif.php">← Retour au formulaire</a>
    </div>
</div>
</body>
</html>