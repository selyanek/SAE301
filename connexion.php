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
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST["username"] ?? '';
    $pass = $_POST["password"] ?? '';
    if ($user && $pass ) {
    header('Location: accueil_étudiant.php');
    } else {
    echo "Identifiant ou mot de passe incorrect";
    }
}

?>

</body>
</html>
