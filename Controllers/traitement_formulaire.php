<?php
// fichier de traitement du formulaire d'absence pour les étudiants
session_start();
require_once('../Models/Database.php');
require_once('../Models/EmailService.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]) ?? '';
    $prenom = trim($_POST["prenom"]) ?? '';

    if (empty($nom) || empty($prenom)) {
        $_SESSION['erreur'] = "Le nom et le prénom sont requis.";
        header('Location: ../Views/gererAbsEtu.php');
        exit;
    }

    try {
        $email = EmailService::recupererEmail($nom, $prenom);
        $nomComplet = $prenom . ' ' . $nom;

        $emailEnvoye = EmailService::envoyerEmail($email, $nomComplet);

        $_SESSION['confirmation'] = true;
        $_SESSION['email_confirmation'] = $email;
        $_SESSION['nom_complet'] = $nomComplet;
        $_SESSION['email_envoye'] = $emailEnvoye;

        header('Location: ../Views/page_confirmation.php');
        exit();
    }
    catch (Exception $e) {
        $_SESSION['erreur'] = "Erreur lors du traitement : " . $e->getMessage();
        header('Location: ../Views/depotJustif.php');
        exit;
    }
}
?>
