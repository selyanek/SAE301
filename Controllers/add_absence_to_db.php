<?php
require 'Database.php';
try {
    $db = new Database();
    $pdo = $db->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des données du formulaire
        $date_start = $_POST['date_start'];
        $date_end = $_POST['date_end'];
        $motif = $_POST['motif'];
        $justificatif = null;

        // Gestion du fichier justificatif
        if (isset($_FILES['justification']) && $_FILES['justification']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir);
            $fileName = uniqid() . '_' . basename($_FILES['justification']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['justification']['tmp_name'], $targetPath)) {
                $justificatif = $targetPath;
            }
        }

        // Appel de la fonction pour ajouter l'absence
        $absence_id = ajouterAbsence($pdo, $date_start, $date_end, $motif, $justificatif);
        echo "Absence ajoutée avec l'ID : " . $absence_id;
    } else {
        echo "Méthode de requête non prise en charge.";
    }
    $pdo = null;

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

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
function testTemps($pdo, $date_end) {
    if ($date_end > $date_end+2*60*60*24) {
        return false;
    } else {
        return true;
    }
}





// Fermeture de la connexion
$pdo = null;
?>