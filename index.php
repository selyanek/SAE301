<?php ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style2.css" rel="stylesheet">
    <title>Login</title>
</head>
<body>
    <header>
        <h1>Bienvenue sur le portail d'authentification</h1>
    </header>

    <div class="wapper">
        <form action="index.php" method="post">
            <label for="identifiant">Identifiant :</label>
            <input type="text" id="identifiant" name="identifiant">

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe">

            <button type="submit">Se connecter</button>
            <a href="mdpOublier.php">Mot de passe oublié ?</a>
        </form>
    </div>
</body>
</html>