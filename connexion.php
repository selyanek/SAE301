<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>
<h1> Connexion </h1>
<form action="accueil_étudiant.php" method="post">
    <label>Nom d'utilisateur :</label>
    <input name="username" id="username" type="text" />
    <br>
    <label>Mot de passe :</label>
    <input name="password" id="password" type="password" />
    <br>
    <button type="reset">Réinitialiser</button>
    <button type="submit">Se connecter</button>
</form>
</body>
</html>
