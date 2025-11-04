<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="../Views/style2.css" rel="stylesheet">
</head>
<body>
    <header>
        <img src="../../public/asset/img/logouphf.png" alt="Logo UPHF" class="logo">
    </header>
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
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        if ($username && $password) {
            header('Location: accueil_etudiant.php');
        } else {
            echo "Identifiant ou mot de passe incorrect";
        }
    }
?>

</body>
</html>
