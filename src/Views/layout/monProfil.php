<?php
// Affiche le profil utilisateur et formulaire de changement de mot de passe
// Variables attendues : $user (assoc), $message (string)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil</title>
    <link rel="stylesheet" href="/public/asset/CSS/cssDeBase.css">
    <style>
        .profile-card { max-width:600px; margin:30px auto; padding:20px; border:1px solid #ddd; border-radius:6px; }
        .profile-field { margin:8px 0; }
        .form-actions { margin-top:12px; }
    </style>
</head>
<body>
<?php include __DIR__ . '/navigation.php'; ?>
<div class="profile-card">
    <h2>Mon profil</h2>
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($user): ?>
        <div class="profile-field"><strong>Prénom :</strong> <?php echo htmlspecialchars($user['prenom'] ?? ''); ?></div>
        <div class="profile-field"><strong>Nom :</strong> <?php echo htmlspecialchars($user['nom'] ?? ''); ?></div>
        <div class="profile-field"><strong>Email :</strong> <?php echo htmlspecialchars($user['email'] ?? ($_SESSION['login'] . '@etu.uphf.fr')); ?></div>
        <div class="profile-field"><strong>Rôle :</strong> <?php echo htmlspecialchars($user['fonction'] ?? $_SESSION['role']); ?></div>

        <?php if (isset($user['fonction']) && (strtolower($user['fonction']) === 'etudiant' || strtolower($user['fonction']) === 'etudiante')): ?>
            <div class="profile-field"><strong>Groupe/Formation :</strong> <?php echo htmlspecialchars($user['groupe'] ?? 'Non renseigné'); ?></div>
        <?php elseif (isset($user['fonction']) && strtolower($user['fonction']) === 'professeur'): ?>
            <div class="profile-field"><strong>Matières :</strong>
                <?php if (!empty($user['matieres']) && is_array($user['matieres'])): ?>
                    <?php echo htmlspecialchars(implode(', ', $user['matieres'])); ?>
                <?php else: ?>
                    Non renseigné
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <hr>
        <h3>Changer le mot de passe</h3>
        <form method="post" action="">
            <input type="hidden" name="action" value="update_password">
            <label>Nouveau mot de passe :</label><br>
            <input type="password" name="new_password" required><br>
            <label>Confirmer :</label><br>
            <input type="password" name="confirm_password" required><br>
            <div class="form-actions">
                <button type="submit">Valider</button>
                <a href="javascript:history.back();"><button type="button">Annuler</button></a>
            </div>
        </form>

    <?php else: ?>
        <p>Utilisateur introuvable.</p>
    <?php endif; ?>
</div>
</body>
</html>
