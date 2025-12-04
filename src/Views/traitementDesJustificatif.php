<?php
session_start();
require __DIR__ . '/../Controllers/session_timeout.php'; // Gestion du timeout de session
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';

// Instancier les classes sans l'usage de "use" pour garder la compatibilité dans le template
$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);

// ID reçu depuis gestionAbsResp.php
$id = isset($_GET['id']) ? intval($_GET['id']) : -1;

// Absence correspondante depuis la DB
$absence = ($id > 0) ? $absenceModel->getById($id) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Traitement des justificatifs</title>
    <link href="/public/asset/CSS/cssTraitementDesJustificatifs.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>
<body>

<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>

<div class="sidebar">
    <ul>
        <li><a href="accueil_responsable.php">Accueil</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="historiqueAbsResp.php">Historique des absences</a></li>
        <!-- <li><a href="#">Statistiques</a></li> -->
    </ul>
</div>

<header class="titre">
    <h1>Détails du justificatif des absences</h1>
</header>

<div class="trait"></div>

<?php if (!$absence): ?>
    <div class="error-message">Absence introuvable.</div>
    <div class="buttons">
        <a href="gestionAbsResp.php"><button type="button" class="btn">Retour</button></a>
    </div>

<?php else: ?>

    <div class="encadre1">
        <div class="encadre2">
            <h3>Date de début de l'absence</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))); ?></p>
            </div>
        </div>

        <div class="encadre2">
            <h3>Date de fin de l'absence</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_fin']))); ?></p>
            </div>
        </div>
    </div>

    <div class="encadre1">
        <div class="encadre2">
            <h3>Date de soumission</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))); ?></p>
            </div>
        </div>

        <div class="encadre2">
            <h3>Motif</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars($absence['motif'] ?? '—'); ?></p>
            </div>
        </div>
    </div>

    <div class="trait"></div>

    <form action="../Controllers/traiter_absence.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <div class="encadre1">
            <div class="encadre2">
                <h3>Document(s) justificatif(s)</h3>
                <div class="encadre3">
                    <?php
                    if (!empty($absence['urijustificatif'])) {
                        $fichiers = json_decode($absence['urijustificatif'], true);
                        if (is_array($fichiers) && count($fichiers) > 0) {
                            foreach ($fichiers as $index => $fichier) {
                                $fichierPath = "../../uploads/" . htmlspecialchars($fichier);
                                echo "<p><a href='" . $fichierPath . "' target='_blank' style='color: #0066cc; text-decoration: none;'>" . htmlspecialchars($fichier) . "</a></p>";
                            }
                        } else {
                            echo "<p>—</p>";
                        }
                    } else {
                        echo "<p>Aucun document fourni</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="encadre2">
                <h3>Cours</h3>
                    <div class="encadre3">
                        <p><?php echo htmlspecialchars($absence['cours_type'] ?? '—'); ?></p>
                        <p><?php echo htmlspecialchars($absence['ressource_nom'] ?? '—'); ?></p>
                    </div>
            </div>
        </div>

        <div class="boutons">
            <button type="submit" name="action" value="valider">Valider</button>
            <button type="button" onclick="afficherRaisonRefus()">Refuser</button>
            <button type="submit" name="action" value="Demande_justif">Demander justificatif</button>
            <a href="gestionAbsResp.php"><button type="button">Retour</button></a>
        </div>
        
        <!-- Zone pour saisir la raison du refus (cachée par défaut) -->
        <div id="zone-raison-refus" style="display: none; margin-top: 20px; padding: 15px; border: 2px solid #f44336; border-radius: 8px; background-color: #fff5f5; max-width: 600px;">
            <h3 style="color: #f44336; margin-top: 0;">Raison du refus</h3>
            <p style="margin-bottom: 10px; color: #666;">Indiquer la raison du refus :</p>
            <textarea name="raison_refus" id="raison_refus" rows="4" 
                      style="width: 100%; padding: 3px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif; font-size: 14px;" 
                      placeholder="Ex: usage de faux, document(s) illisible(s) ..."></textarea>
            <div style="margin-top: 10px; display: flex; gap: 10px;">
                <button type="submit" name="action" value="refuser" style="background: #f44336; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Confirmer le refus</button>
                <button type="button" onclick="cacherRaisonRefus()" style="background: #999; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Annuler</button>
            </div>
        </div>
    </form>

    <script>
        function afficherRaisonRefus() {
            document.getElementById('zone-raison-refus').style.display = 'block';
            document.getElementById('raison_refus').focus();
        }
        
        function cacherRaisonRefus() {
            document.getElementById('zone-raison-refus').style.display = 'none';
            document.getElementById('raison_refus').value = '';
        }
        
        // Validation côté client pour s'assurer qu'une raison est fournie lors du refus
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const action = e.submitter ? e.submitter.value : null;
                    const raisonRefus = document.getElementById('raison_refus');
                    
                    if (action === 'refuser' && raisonRefus && raisonRefus.value.trim() === '') {
                        e.preventDefault();
                        alert('Veuillez indiquer une raison pour le refus.');
                        raisonRefus.focus();
                        return false;
                    }
                });
            }
        });
    </script>

<?php endif; ?>

<div style="height: 150px;"></div>

<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="">Aides</a>
    </nav>
</footer>
</body>
</html>
