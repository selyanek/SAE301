<?php
// Vue pour la page de profil utilisateur
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - EduTrack</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/profile.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1>Mon Profil</h1>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-info">
            <h2>Informations Personnelles</h2>
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['nom'] ?? ''); ?></p>
            <p><strong>Prénom :</strong> <?php echo htmlspecialchars($user['prenom'] ?? ''); ?></p>
            <p><strong>Identifiant :</strong> <?php echo htmlspecialchars($user['identifiantCompte'] ?? ''); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
            <p><strong>Rôle :</strong> <?php echo htmlspecialchars(ucfirst($user['fonction'] ?? '')); ?></p>
            <?php if (isset($user['formation'])): ?>
                <p><strong>Formation :</strong> <?php echo htmlspecialchars($user['formation']); ?></p>
            <?php endif; ?>
        </div>

        <div class="password-update">
            <h2>Changer le mot de passe</h2>
            <form action="" method="post">
                <input type="hidden" name="action" value="update_password">
                <div class="form-group">
                    <label for="old_password">Ancien mot de passe :</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe :</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn">Mettre à jour</button>
            </form>
        </div>

        <div class="back-link">
            <a href="javascript:history.back()">Retour</a>
        </div>
    </div>
</body>
</html>
