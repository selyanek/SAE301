<?php
// Affiche le profil utilisateur et formulaire de changement de mot de passe
// Variables attendues : $user (assoc), $message (string), $messageType (string)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil</title>
    <link rel="stylesheet" href="/public/asset/CSS/cssDeBase.css">
    <link rel="stylesheet" href="/public/asset/CSS/cssProfile.css">
</head>
<body>
<!-- US-26 : Bouton hamburger pour mobile -->
<button class="hamburger" id="hamburgerBtn" aria-label="Menu de navigation" onclick="toggleMenu()">☰</button>

<script>
function toggleMenu() {
    var sidebar = document.querySelector('.sidebar');
    var btn = document.getElementById('hamburgerBtn');
    sidebar.classList.toggle('open');
    btn.textContent = sidebar.classList.contains('open') ? '✕' : '☰';
}
document.addEventListener('DOMContentLoaded', function() {
    var links = document.querySelectorAll('.sidebar a');
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', function() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.remove('open');
            document.getElementById('hamburgerBtn').textContent = '☰';
        });
    }
    document.addEventListener('click', function(e) {
        var sidebar = document.querySelector('.sidebar');
        var btn = document.getElementById('hamburgerBtn');
        if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== btn) {
            sidebar.classList.remove('open');
            btn.textContent = '☰';
        }
    });
});
</script>
<!-- Affichage du logo de l'université -->
<div class="uphf">
    <img src="/public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<!-- Affichage du logo EduTrack -->
<div class="logoEdu">
    <img src="/public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>

<?php include __DIR__ . '/../layout/navigation.php'; ?>
<div class="profile-card">
    <div class="profile-header-bar">
        <h2>Mon profil</h2>
    </div>
    <?php if (!empty($message)): ?>
        <?php
            $msgClass = 'message';
            if (isset($messageType) && $messageType === 'success') {
                $msgClass .= ' success';
            } elseif (isset($messageType) && $messageType === 'error') {
                $msgClass .= ' error';
            }
        ?>
        <div class="<?php echo $msgClass; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($user): ?>
        <div class="profile-field"><strong>Prénom :</strong> <?php echo htmlspecialchars($user['prenom'] ?? ''); ?></div>
        <div class="profile-field"><strong>Nom :</strong> <?php echo htmlspecialchars($user['nom'] ?? ''); ?></div>
        <div class="profile-field"><strong>Email :</strong> <?php echo htmlspecialchars($user['email'] ?? ($_SESSION['login'] . '@uphf.fr')); ?></div>
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
            <form method="post" action="" class="form-ajax" data-endpoint="/src/Controllers/api_profile.php" data-timeout="30000">
            <input type="hidden" name="action" value="update_password">
            <label>Ancien mot de passe :</label><br>
            <input type="password" name="old_password" required><br>
            <label>Nouveau mot de passe :</label><br>
            <input type="password" name="new_password" required><br>
            <label>Confirmer :</label><br>
            <input type="password" name="confirm_password" required><br>
            <div class="form-actions">
                <button type="submit" class="btn">Valider</button>
                <a href="javascript:history.back();" class="btn">Annuler</a>
            </div>
            <div class="form-feedback"></div>
        </form>

    <?php else: ?>
        <p>Utilisateur introuvable.</p>
    <?php endif; ?>
</div>
    <link rel="stylesheet" href="/public/asset/CSS/cssFormAjax.css">
    <script src="/public/asset/JS/formAjax.js"></script>
</body>
</html>