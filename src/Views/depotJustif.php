<?php
session_start();
require '../Controllers/session_timeout.php'; // Gestion du timeout de session
require __DIR__ . '/layout/header.php';
require __DIR__ . '/layout/navigation.php';

// Gestion des messages de succès / erreur
if (!empty($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="success-message">Votre justificatif a été envoyé avec succès !</div>';
}

if (!empty($_GET['error'])) {
    $error_messages = [
            'motif' => 'Le motif est obligatoire.',
            'dates' => 'Veuillez renseigner les dates de début et de fin.',
            'dates_invalides' => 'La date de fin doit être après la date de début.',
            'file_required' => 'Veuillez joindre un justificatif.',
            'file_type' => 'Format de fichier non accepté. Utilisez .pdf, .jpg ou .png',
            'file_size' => 'Le fichier est trop volumineux (max 5MB).',
            'upload_failed' => 'Erreur lors de l\'upload du fichier.'
    ];

    $error = $_GET['error'];
    if (isset($error_messages[$error])) {
        echo '<div class="error-message">' . $error_messages[$error] . '</div>';
    }
}
?>

<script src="../../../public/asset/JS/depotJustificatif.js" defer></script>

<div class="form-group">
    <label class="label">Date et heure de début :</label>
    <input class="input" name="date_start" id="date_start" type="datetime-local" required>
</div>

<div class="form-group">
    <label class="label">Date et heure de fin :</label>
    <input class="input" name="date_end" id="date_end" type="datetime-local" required>
</div>

<div class="form-group">
    <label class="label">Motif de l'absence :</label>
    <textarea class="textarea" name="motif" id="motif" rows="2" required></textarea>
</div>

<div class="form-group">
    <label class="label" for="fileInput">Justification(s) :</label>
    <p class="info">Formats acceptés : .pdf, .jpg, .png | max 5MB</p>

    <input type="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png">

    <button type="button" class="btn" onclick="addFile()">Ajouter un fichier</button>

    <div id="fileListContainer" class="file-list"></div>
    <div id="fileCount"></div>

    <button class="btn" id="submitBtn" onclick="uploadFiles()">Envoyer</button>
    <div id="message"></div>

</div

<?php require __DIR__ . '/layout/footer.php'; ?>
</body>
</html>
