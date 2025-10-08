<?php
require 'Database.php';

try {
    // Connexion à la base de données
    $db = new Database();
    $pdo = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Récupération des données du formulaire
        $date_start = filter_input(INPUT_POST, 'date_start', FILTER_SANITIZE_STRING);
        $date_end = filter_input(INPUT_POST, 'date_end', FILTER_SANITIZE_STRING);
        $motif = filter_input(INPUT_POST, 'motif', FILTER_SANITIZE_STRING);
        $justificatif = null;

        // Upload du fichier justificatif
        if (isset($_FILES['justification']) && $_FILES['justification']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir);
            $fileName = uniqid() . '_' . basename($_FILES['justification']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['justification']['tmp_name'], $targetPath)) {
                $justificatif = $targetPath;
            }
        }

        // Insertion de l'absence dans base de données
        $absence_id = ajouterAbsence($pdo, $date_start, $date_end, $motif, $justificatif);
        echo "Absence ajoutée avec l'ID : " . $absence_id;
    } else {
        echo "Méthode de requête non prise en charge.";
    }
    
    $pdo = null;

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction d'ajout d'une absence
function ajouterAbsence($pdo, $date_start, $date_end, $motif, $justificatif) {
    $sql = "INSERT INTO Absence (date_debut, date_fin, motif, justificatif) VALUES (:date_debut, :date_fin, :motif, :justificatif)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':date_debut' => $date_start,
        ':date_fin' => $date_end,
        ':motif' => $motif,
        ':justificatif' => $justificatif
    ]);
    return $pdo->lastInsertId();
}

// Fonction de vérification du délai (non utilisée)
function testTemps($pdo, $date_end) {
    if ($date_end > $date_end+2*60*60*24) {
        return false;
    } else {
        return true;
    }
}

$pdo = null;
?>