<?php
$success = false; // Initialisation
$errorMessage = "";

try {
    // 1. Récupération des données du formulaire
    $date_start = $_POST['date_start'] ?? '';
    $date_end = $_POST['date_end'] ?? '';
    $motif = $_POST['motif'] ?? '';

    // 2. Validation
    if (empty($motif)) {
        throw new Exception("Le motif est obligatoire");
    }

    // 3. Traitement du fichier uploadé
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

        // Vérification de la taille (5MB max)
        if ($fileSize > 5 * 1024 * 1024) {
            throw new Exception("Le fichier est trop volumineux (max 5MB)");
        }

        // Vérification de l'extension
        $allowedExtensions = ['pdf', 'jpg', 'png'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Format de fichier non autorisé");
        }

        // 4. Déplacement du fichier uploadé
        $uploadDir = '../uploads/justificatifs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = uniqid() . '_' . basename($fileName);
        $destination = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmpPath, $destination)) {
            throw new Exception("Erreur lors du téléchargement du fichier");
        }

        // 5. Envoi de l'email (exemple)
        $to = "responsable@example.com";
        $subject = "Nouveau justificatif d'absence";
        $message = "Date début: $date_start\nDate fin: $date_end\nMotif: $motif";

        if (mail($to, $subject, $message)) {
            $success = true;
        } else {
            throw new Exception("Erreur lors de l'envoi de l'email");
        }

    } else {
        throw new Exception("Aucun fichier n'a été uploadé");
    }

} catch (Exception $e) {
    $success = false;
    $errorMessage = $e->getMessage();
}

// 6. Redirection selon le résultat
if ($success) {
    header('Location: ../Views/accueil_etudiant.php?message=success');
    exit();
} else {
    header('Location: ../Views/depot_justificatif.php?error=' . urlencode($errorMessage));
    exit();
}
?>
