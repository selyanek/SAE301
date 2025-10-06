<?php
require '../Models/Database.php';
require '../Models/Login.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $_POST['identifiant'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $login = new Login($identifiant, $mot_de_passe);

    try {
        $bd = new Database();
        $pdo = $bd->getConnection();

        if ($login->verifierConnexion($pdo)) {
            $message = "Connexion réussie !";
            if($login->verifRole($pdo) == 'etudiante'){
                header('Location: https://localhost/Controllers/accueil_etudiant.php');
                exit();
            } elseif (($login->verifRole($pdo) == 'professeur')){
                header('Location: https://localhost/Controllers/accueil_prof.php');
                exit();
            } elseif (($login->verifRole($pdo) == 'responsable_pedagogique')) {
                header('Location: https://localhost/Controllers/accueil_rp.php');
                exit();
            } else {
                echo 'vous n exister pas';
            }

        } else {
            $message = "Identifiant ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $message = "Erreur de connexion : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/Views/style2.css" rel="stylesheet">
    <title>Authentification</title>
</head>
<header class="header">
    <img src="../img/logouphf.png" alt="Logo uphf">
</header>
<body>
    <div class="logoEdu">
        <img src="../img/logoedutrack.png" alt="Logo EduTrack">
    </div>
    <div class="separator"></div>

    <section class="text-with-image-section">
        <div class="text-with-image">
            <img src="../img/logoco.png" alt="Connexion">
            <h2>Connexion</h2>
        </div>
    </section>

    <div class="sidebar"></div>

    <div class="wapper">
        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
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
    </div>
</body>
</html>