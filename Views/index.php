<?php
require '../Models/Database.php';
require '../Models/Login.php';

$message = "";

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $identifiant = $_POST['identifiant'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';


    $login = new Login($identifiant, $mot_de_passe);

    try {
        // Connexion à la base de données
        $bd = new Database();
        $pdo = $bd->getConnection();

        // Vérification des identifiants
        if ($login->verifierConnexion($pdo)) {
            $message = "Connexion réussie !";
            
            // Redirection selon le rôle de l'utilisateur
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
            // Message d'erreur en cas d'échec
            $message = "Identifiant ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        // Gestion des erreurs de connexion
        $message = "Erreur de connexion : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Importation des styles (CSS) -->
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/CSS/cssConnexion.css" rel="stylesheet">
    <title>Authentification</title>
</head>

<!-- Logo UPHF -->
<header class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</header>

<body>
    <!-- Logo EduTrack -->
    <div class="logoEdu">
        <img src="../img/logoedutrack.png" alt="Logo EduTrack">
    </div>
    
    <!-- Section avec icône de connexion -->
    <section class="text-with-image-section">
        <div class="text-with-image">
            <img src="../img/logoco.png" alt="Connexion">
            <h2>Connexion</h2>
        </div>
    </section>


    <div class="sidebar"></div>

    <!-- Formulaire de connexion -->
    <div class="wapper">
        <!-- Affichage des messages (succès ou erreur) -->
        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <!-- Formulaire avec champs identifiant et mot de passe -->
        <form action="index.php" method="post">
            <label for="identifiant">Identifiant :</label>
            <input type="text" id="identifiant" name="identifiant" required>
            <br>
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            <br>
            <button type="submit">Se connecter</button>
            <br>
            <!-- Lien mot de passe oublié -->
            <a href="mdpOublier.php">Mot de passe oublié ?</a>
        </form>
    </div>
</body>

<footer class="footer">
    <nav class="footer-nav">
        <a href="#">Accueil</a>
        <span>|</span>
        <a href="../Views/aide.php">Aides</a>
    </nav>
</footer>
</html>