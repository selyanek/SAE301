<?php
// Page de gestion des absences en attente pour l'étudiant
session_start();
require '../../Controllers/session_timeout.php';

require_once __DIR__ . '/../../Database/Database.php';
require_once __DIR__ . '/../../Models/Absence.php';

use src\Database\Database;
use src\Models\Absence;

// Vérifier que l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'etudiant' && $_SESSION['role'] !== 'etudiante')) {
    header('Location: /public/index.php');
    exit();
}

// Récupérer les absences en attente de l'étudiant
try {
    $db = new Database();
    $absenceModel = new Absence($db->getConnection());
    $identifiantEtu = $_SESSION['identifiantEtu'] ?? $_SESSION['login'];
    
    $absences = $absenceModel->getByStudentIdentifiant($identifiantEtu);
    
    // Filtrer uniquement les absences en attente (justifie IS NULL)
    $absencesEnAttente = array_filter($absences, function($absence) {
        return $absence['justifie'] === null;
    });
    
    // Filtrer les absences refusées avec possibilité de ressoumission
    $absencesResoumettre = array_filter($absences, function($absence) {
        return ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0')
            && isset($absence['type_refus']) 
            && $absence['type_refus'] === 'ressoumission';
    });
    
} catch (Exception $e) {
    $absencesEnAttente = [];
    $absencesResoumettre = [];
}

$pageTitle = 'Gérer mes absences';
$additionalCSS = ['../../../public/asset/CSS/cssGererAbsEtu.css'];
require '../layout/header.php';
require '../layout/navigation.php';
?>

<header class="text">
    <h1>Gérer mes absences</h1>
    <p>Cette page vous donne accès aux informations et réponses liées à vos absences en attente de validation.</p>
    <?php
    if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
        echo '<div class="error-messages">';
        foreach ($_SESSION['errors'] as $error) {
            echo '<p class="error">' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
        unset($_SESSION['errors']);
    }
    if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
        echo '<div class="success-messages">';
        echo '<p class="success">' . htmlspecialchars($_SESSION['success']) . '</p>';
        echo '</div>';
        unset($_SESSION['success']);
    }
    ?>
    <a href="depotJustificatif.php"><button type="button" class="btn">Soumettre un nouveau justificatif</button></a>    
</header>

<!-- Afficher un badge si des absences peuvent être ressoumises -->
<?php if (!empty($absencesResoumettre)): ?>
<div class="alert-ressoumission">
    <strong>Attention !</strong> Vous avez <?php echo count($absencesResoumettre); ?> absence(s) refusée(s) que vous pouvez modifier et resoumettre. 
    <a href="#absences-a-resoumettre">Voir ci-dessous</a>
</div>
<?php endif; ?>

<!-- Liste des absences en attente sous forme de cartes -->
<div class="absences-container">
<?php if (empty($absencesEnAttente)): ?>
    <p class='no-results'>Vous n'avez aucune absence en attente de validation.</p>
<?php else: ?>
    <?php foreach ($absencesEnAttente as $absence): ?>
        <?php
        // Préparer les données
        $dateDebut = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut'])));
        $dateFin = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_fin'])));
        $cours = htmlspecialchars($absence['ressource_nom'] ?? $absence['cours_type'] ?? 'N/A');
        $motif = htmlspecialchars($absence['motif'] ?? 'Non renseigné');
        
        // Justificatifs
        $justificatifsHtml = '—';
        if (!empty($absence['urijustificatif'])) {
            $fichiers = json_decode($absence['urijustificatif'], true);
            if (is_array($fichiers) && count($fichiers) > 0) {
                $links = [];
                foreach ($fichiers as $fichier) {
                    $fichierPath = "../../../uploads/" . htmlspecialchars($fichier);
                    $links[] = "<a href='" . $fichierPath . "' target='_blank'>" . htmlspecialchars($fichier) . "</a>";
                }
                $justificatifsHtml = implode('<br>', $links);
            } elseif (is_string($absence['urijustificatif'])) {
                // Si c'est juste un string (ancien format)
                $justificatifsHtml = "<a href='" . htmlspecialchars($absence['urijustificatif']) . "' target='_blank'>Voir le justificatif</a>";
            }
        }
        ?>
        
        <div class='absence-card statut-attente'>
            <div class='card-dates'>
                <div><strong>Cours</strong><br><?php echo $cours; ?></div>
                <div><strong>Début</strong><br><?php echo $dateDebut; ?></div>
                <div><strong>Fin</strong><br><?php echo $dateFin; ?></div>
            </div>
            <div class='card-info'>
                <div class='motif'><strong>Motif :</strong> <?php echo $motif; ?></div>
                <div class='justif'><strong>Justificatif :</strong><br><?php echo $justificatifsHtml; ?></div>
            </div>
            <div class='card-status'>
                <span class='status-badge'>En attente</span>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<!-- Section des absences refusées à resoumettre -->
<?php if (!empty($absencesResoumettre)): ?>
<div id="absences-a-resoumettre" style="margin-top: 40px;">
    <h2 style="color: #ff6b00;">Absences refusées - Vous pouvez les modifier</h2>
    <p style="margin-bottom: 20px;">Ces absences ont été refusées mais vous avez la possibilité de les modifier et les resoumettre.</p>
    
    <div class="absences-container">
        <?php foreach ($absencesResoumettre as $absence): ?>
            <?php
            // Préparer les données
            $dateDebut = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut'])));
            $dateFin = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_fin'])));
            $cours = htmlspecialchars($absence['ressource_nom'] ?? $absence['cours_type'] ?? 'N/A');
            $motif = htmlspecialchars($absence['motif'] ?? 'Non renseigné');
            $raisonRefus = htmlspecialchars($absence['raison_refus'] ?? 'Non précisée');
            $idAbsence = $absence['idabsence'];
            ?>
            
            <div class='absence-card statut-refuse'>
                <div class='card-dates'>
                    <div><strong>Cours</strong><br><?php echo $cours; ?></div>
                    <div><strong>Début</strong><br><?php echo $dateDebut; ?></div>
                    <div><strong>Fin</strong><br><?php echo $dateFin; ?></div>
                </div>
                <div class='card-info'>
                    <div class='motif'><strong>Motif actuel :</strong> <?php echo $motif; ?></div>
                    <div class='raison-refus' style='color: #d32f2f; margin-top: 10px;'>
                        <strong>Raison du refus :</strong> <?php echo $raisonRefus; ?>
                    </div>
                </div>
                <div class='card-status'>
                    <span class='status-badge'>Refusé</span>
                    <a href="resoumettre_absence.php?id=<?php echo $idAbsence; ?>" style="margin-top: 10px;">
                        <button class="btn-resoumettre">Modifier et resoumettre</button>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<br>

<div class="text">
    <a href="dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>

<?php
require '../layout/footer.php';
?>

