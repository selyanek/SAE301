<?php
// Page de dépôt de justificatif pour l'étudiant
session_start();
require '../../Controllers/session_timeout.php'; // Gestion du timeout de session
require '../layout/header.php';
require '../layout/navigation.php';

// Vérifier si c'est une ressoumission
$isRessoumission = isset($_GET['ressoumission']) && $_GET['ressoumission'] == 1;
$idAbsence = isset($_GET['id']) ? intval($_GET['id']) : null;

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="success-message">Votre justificatif a été envoyé avec succès !</div>';
}
if (isset($_GET['error'])) {
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

// Afficher un message pour les ressoumissions
if ($isRessoumission) {
    echo '<div class="alert-ressoumission" style="background: #fff3cd; color: #856404; padding: 15px; margin: 20px auto; max-width: 800px; border-radius: 5px; border: 1px solid #ffc107;">';
    echo '<strong>Ressoumission de justificatif</strong><br>';
    echo 'Vous pouvez ajouter de nouveaux documents pour compléter votre justification.';
    echo '</div>';
}
?>

<link rel="stylesheet" href="../../../public/asset/CSS/cssDepot.css">
<link rel="stylesheet" href="../../../public/asset/CSS/cssEnvoieFichier.css">


<form id="absenceForm" class="absence-form" onsubmit="event.preventDefault(); uploadFiles();">
    
    <?php if ($isRessoumission && $idAbsence): ?>
        <input type="hidden" id="id_absence" name="id_absence" value="<?php echo htmlspecialchars($idAbsence); ?>" />
        <input type="hidden" id="ressoumission" name="ressoumission" value="1" />
    <?php endif; ?>

    <div class="form-group">
        <label class="label">Date et heure de début :</label>
        <input class="input" name="date_start" id="date_start" type="datetime-local" required
               value="<?php echo htmlspecialchars($_GET['date_start'] ?? ''); ?>" />
    </div>

    <div class="form-group">
        <label class="label">Date et heure de fin :</label>
        <input class="input" name="date_end" id="date_end" type="datetime-local" required
               value="<?php echo htmlspecialchars($_GET['date_end'] ?? ''); ?>" />
    </div>

    <div class="form-group">
        <label class="label" id="motif_label">Motif de l'absence :</label>
        <textarea class="textarea" name="motif" id="motif" rows="2" required><?php echo htmlspecialchars($_GET['motif'] ?? ''); ?></textarea>
    </div>

    <div class="form-group">
        <label class="label">Justificatifs (optionnel) :</label>
        <p class="info">Formats acceptés : .pdf, .jpg, .png | Taille max par fichier : 5MB | Taille totale max : 20MB</p>
        
        <!-- Input pour sélectionner un fichier -->
        <input class="file-input" type="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png" />
        <button type="button" class="btn-add" onclick="addFile()">+ Ajouter ce fichier</button>
        
        <!-- Liste des fichiers ajoutés -->
        <div class="file-list-container" id="fileListContainer">
            <div class="empty-state">Aucun fichier ajouté</div>
        </div>
        <div class="file-count" id="fileCount"></div>
    </div>

    <div id="message"></div>

    <div class="buttons">
        <button type="button" class="btn" onclick="resetForm()">Réinitialiser</button>
        <button type="submit" class="btn" id="submitBtn">Valider</button>
        <a href="dashbord.php"><button type="button" class="btn">Annuler</button></a>
    </div>

</form>

<script src="../../../public/asset/JS/depotFichiers.js"></script>

</body>
<?php
require '../layout/footer.php';
?>
</html>