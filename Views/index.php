<?php ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Views/style2.css" rel="stylesheet">
    <title>Login</title>
</head>
<header class="header">
    <img src="../img/logouphf.png" alt="Logo uphf">
</header>
<body>
    <div class="logoEdu">
        <img src="../img/logoedutrack.png" alt="Logo EduTrack">
    </div>
    <div class="separator"></div>
    <div class="text">
         <h1>Bienvenue sur le portail d'authentification</h1>
    </div>
    <div class="text-with-image">
        <img src="../img/logoco.png" alt="Connexion">
        <h2>Connexion</h2>
    </div>

    <div class="sidebar"></div>

    <div class="wapper">
        <form action="index.php" method="post">
            <label for="identifiant">Identifiant :</label>
            <input type="text" id="identifiant" name="identifiant">
            <br>
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe">
            <br>
            <button type="submit">Se connecter</button>
            <br>
            <a href="mdpOublier.php">Mot de passe oublié ?</a>
        </form>
    </div>
</body>
</html>