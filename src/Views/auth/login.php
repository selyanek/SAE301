<?php require __DIR__ . '/../layout/header.php'; ?>

<link rel="stylesheet" href="/public/asset/CSS/cssConnexion.css">

<section class="text-with-image-section" aria-labelledby="connexion-title">
    <div class="text-with-image">
        <img src="/public/asset/img/logoco.png" alt="Connexion">
        <h2 id="connexion-title">Connexion</h2>
    </div>
</section>

<div class="sidebar"></div>

<main class="wapper" role="main">
    <p class="form-help" id="connexion-help">Utilisez vos identifiants institutionnels pour accéder à votre espace.</p>

    <?php if (!empty($message)): ?>
        <?php $type = htmlspecialchars($messageType ?? 'error'); ?>
        <div class="message <?= $type ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>" aria-live="<?= $type === 'error' ? 'assertive' : 'polite' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="index.php" method="post" aria-describedby="connexion-help" id="login-form" novalidate>
        <label for="identifiant">Identifiant <span class="required-marker" aria-hidden="true">*</span></label>
        <input type="text"
               id="identifiant"
               name="identifiant"
               value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>"
               autocomplete="username"
               inputmode="text"
               required
               autofocus>

        <label for="mot_de_passe">Mot de passe <span class="required-marker" aria-hidden="true">*</span></label>
        <input type="password"
               id="mot_de_passe"
               name="mot_de_passe"
               autocomplete="current-password"
               required>

        <div class="password-options">
            <input type="checkbox" id="show-password" aria-controls="mot_de_passe">
            <label for="show-password" class="inline-label">Afficher le mot de passe</label>
        </div>

        <button type="submit" id="submit-btn" class="btn">Se connecter</button>

        <a href="mdpOublier.php">Mot de passe oublié ?</a>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var passwordInput = document.getElementById('mot_de_passe');
    var showPasswordCheckbox = document.getElementById('show-password');
    var loginForm = document.getElementById('login-form');
    var submitButton = document.getElementById('submit-btn');

    if (showPasswordCheckbox && passwordInput) {
        showPasswordCheckbox.addEventListener('change', function () {
            passwordInput.type = this.checked ? 'text' : 'password';
        });
    }

    if (loginForm && submitButton) {
        loginForm.addEventListener('submit', function () {
            submitButton.disabled = true;
            submitButton.textContent = 'Connexion en cours...';
        });
    }
});
</script>
</body>
</html>
