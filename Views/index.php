<?php
require '../Models/Database.php';
require '../Models/Login.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $_POST['identifiant'] ?? '';
    $mot_de_passe = password_hash(($_POST['mot_de_passe'] ?? ''), PASSWORD_DEFAULT);
    $login = new Login($identifiant, $mot_de_passe);
    try {
        $bd = new Database();
        $pdo = $bd->getConnection();

        if ($login->verifierConnexion($pdo)) {
            $message = "Connexion réussie !";
            session_start();
            $idSession = session_id();
            $_SESSION['login'] = $identifiant;
            $_SESSION['nom'] = $login->getName($pdo);
            $_SESSION["mdp"] = $login->getPwd($pdo);
            $_SESSION["role"] = $login->getRole($pdo);

            if ($login->verifRole($pdo) == 'etudiante') {
                header('Location: ../Controllers/accueil_etudiant.php');
                exit();
            } elseif (($login->verifRole($pdo) == 'professeur')) {
                header('Location: ../Controllers/accueil_prof.php');
                exit();
            } elseif (($login->verifRole($pdo) == 'responsable_pedagogique')) {
                header('Location: ../Controllers/accueil_responsable.php');
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