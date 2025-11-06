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
                header('Location: ../Views/etudiant/dashbord.php');
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