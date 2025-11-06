<?php 
require '../Controllers/AuthenController.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/CSS/cssConnexion.css" rel="stylesheet">
    <title>Authentification</title>
</head>
<header class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</header>
<body>
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>
<section class="text-with-image-section">
    <div class="text-with-image">
        <img src="../img/logoco.png" alt="Connexion">
        <h2>Connexion</h2>
    </div>
</section>
<div class="sidebar"></div>
<div class="wapper">
    <form action="index.php" method="post">
        <label for="identifiant">Identifiant :</label>
        <input type="text" id="identifiant" name="identifiant" required>
        <br>
        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        <br>
        <button type="submit">Se connecter</button>
        <br>
        <a href="mdpOublier.php">Mot de passe oublié ?</a>
    </form>
    <?php if (!empty($message)): ?>
        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
</div>
</body>
<section class="container">
    <footer class="footer">
        <nav class="footer-nav">
            <a href="#">Accueil</a>
            <span>|</span>
            <a href="../Views/aide.php">Aides</a>
        </nav>
    </footer>
</section>
</html>