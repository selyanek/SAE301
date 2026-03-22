<?php
// Fichier de VUE PURE

// L'affichage du header et de la navigation est géré par le contrôleur
?>

<header class="text">
    <h1>Modifier et resoumettre votre absence</h1>
    <p>Votre absence a été refusée. Vous pouvez la modifier et la resoumettre.</p>
</header>

<?php if (isset($erreur)): ?>
<div class="error-messages">
    <p class="error"><?php echo htmlspecialchars($erreur); ?></p>
</div>
<?php endif; ?>

<div class="ressoumission-container">
    <div class="info-absence">
        <h3>Informations de l'absence</h3>
        <p><strong>Cours :</strong> <?php echo htmlspecialchars($absence['ressource_nom'] ?? $absence['cours_type'] ?? 'N/A'); ?></p>
        <p><strong>Début :</strong> <?php echo htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut']))); ?></p>
        <p><strong>Fin :</strong> <?php echo htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_fin']))); ?></p>
        <div class="raison-refus-box">
            <strong>Raison du refus :</strong>
            <p><?php echo htmlspecialchars($absence['raison_refus'] ?? 'Non précisée'); ?></p>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="form-ressoumission">
        <div class="form-group">
            <label for="motif"><strong>Nouveau motif * :</strong></label>
            <textarea name="motif" id="motif" rows="5" required placeholder="Expliquez votre absence..."><?php echo isset($_POST['motif']) ? htmlspecialchars($_POST['motif']) : htmlspecialchars($absence['motif'] ?? ''); ?></textarea>
            <small>Veuillez fournir un motif clair et détaillé pour votre absence.</small>
        </div>

        <div class="form-group">
            <label for="justificatif"><strong>Nouveau justificatif (optionnel) :</strong></label>
            <input type="file" name="justificatif" id="justificatif" accept=".pdf,.jpg,.jpeg,.png">
            <small>Si vous ne téléchargez pas de nouveau fichier, l'ancien justificatif sera conservé.</small>
            <?php if (!empty($absence['urijustificatif'])): ?>
                <p style="margin-top: 10px;"><strong>Justificatif actuel :</strong> 
                    <?php
                    $fichiers = json_decode($absence['urijustificatif'], true);
                    if (is_array($fichiers) && count($fichiers) > 0) {
                        foreach ($fichiers as $fichier) {
                            echo htmlspecialchars($fichier) . ' ';
                        }
                    }
                    ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Resoumettre l'absence</button>
            <a href="justificatif.php"><button type="button" class="btn-cancel">Annuler</button></a>
        </div>
    </form>
</div>
