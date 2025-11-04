<?php
session_start();
require_once '../';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $justificatifId = $_POST['justificatif_id'] ?? null;
    $action = $_POST['action'] ?? null;

    // Traiter l'action en fonction du bouton cliqué
    switch ($action) {
        case 'valider':
            $nouveauStatut = 'valide';
            $message = 'Votre justificatif a été validé.';
            break;
        case 'refuser':
            $nouveauStatut = 'refuse';
            $message = 'Votre justificatif a été refusé.';
            break;
        case 'demander_supplémentaires':
            $nouveauStatut = 'attente';
            $message = 'Des justificatifs supplémentaires ont été demandés.';
            break;
        default:
            // Action inconnue
            break;
    }

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=edutrack', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Mettre à jour le statut du justificatif dans la base de données
        $stmt = $pdo->prepare("UPDATE justificatifs SET statut = :statut WHERE id = :id");
        $stmt->execute(['statut' => $nouveauStatut, 'id' => $justificatifId]);
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
    // Rediriger ou afficher un message de confirmation
    header('Location: ../Views/traitementDesJustificatif.php');
    exit();
}