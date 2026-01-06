<?php
// Activer l'affichage des erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ . '/../Models/Absence.php';

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
    $identifiantEtu = $_SESSION['identifiantEtu'] ?? $_SESSION['login']; // Utiliser identifiantEtu ou login en fallback
    
    $absences = $absenceModel->getByStudentIdentifiant($identifiantEtu);
    
    // Filtrer les absences en attente (justifie IS NULL) OU en révision (revision = true)
    $absencesATraiter = array_filter($absences, function($absence) {
        $enRevision = isset($absence['revision']) && ($absence['revision'] === true || $absence['revision'] === 't' || $absence['revision'] === '1' || $absence['revision'] === 1);
        $enAttente = $absence['justifie'] === null && !$enRevision;
        return $enAttente || $enRevision;
    });
    
} catch (Exception $e) {
    // En cas d'erreur, initialiser un tableau vide
    $absencesEnAttente = [];
    // Afficher l'erreur en commentaire HTML
    echo "<!-- Erreur: " . htmlspecialchars($e->getMessage()) . " -->";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer mes absences</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGererAbsEtu.css" rel="stylesheet">

</head>
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<header class="text">
    <h1>Gérer mes absences </h1>
    <p>Cette page vous donne accès aux informations et réponses liées à vos absences en attente de validation ou en révision.</p>
    <?php
    if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
        echo '<div class="error-messages">';
        foreach ($_SESSION['errors'] as $error) {
            echo '<p class="error">' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
        unset($_SESSION['errors']);
    }
    ?>
    <a href="/src/Views/depotJustif.php"><button type="button" class="btn">Soumettre un nouveau justificatif</button></a>
</header>
<div class="sidebar">
    <ul>
        <li><a href="/src/Views/etudiant/dashbord.php">Accueil</a></li>
        <li><a href="/src/Views/gererAbsEtu.php">Gérer des absences</a></li>
        <li><a href="/src/Views/etudiant/historiqueAbsences.php">Historique des absences</a></li>
        <li><a href="/src/Views/etudiant/aide.php">Aides</a></li>
    </ul>
</div>

<?php if (empty($absencesATraiter)): ?>
    <div class="text">
        <p>Vous n'avez aucune absence en attente de validation ou en révision.</p>
    </div>
<?php else: ?>
<table class="liste-absences"> 
    <tr>
        <th>Date de début</th>
        <th>Date de fin</th>
        <th>Cours</th>
        <th>Motif</th>
        <th>Justificatif</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($absencesATraiter as $absence): 
        // Déterminer le statut
        $enRevision = isset($absence['revision']) && ($absence['revision'] === true || $absence['revision'] === 't' || $absence['revision'] === '1' || $absence['revision'] === 1);
    ?>
    <tr>
        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))); ?></td>
        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_fin']))); ?></td>
        <td><?php echo htmlspecialchars($absence['ressource_nom'] ?? $absence['cours_type'] ?? 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($absence['motif'] ?? 'Non renseigné'); ?></td>
        <td>
            <?php if (!empty($absence['urijustificatif'])): 
                $fichiers = json_decode($absence['urijustificatif'], true);
                if (is_array($fichiers) && count($fichiers) > 0):
                    foreach ($fichiers as $fichier):
                        $fichierPath = "../../uploads/" . htmlspecialchars($fichier);
            ?>
                        <a href="<?php echo $fichierPath; ?>" target="_blank"><?php echo htmlspecialchars($fichier); ?></a><br>
            <?php   endforeach;
                endif;
            else: ?>
                Aucun justificatif
            <?php endif; ?>
        </td>
        <td>
            <?php if ($enRevision): ?>
                <span class="statut-revision" style="background-color: #fff3cd; color: #856404; font-weight: bold; padding: 5px 10px; border-radius: 4px; border: 1px solid #ffc107;">En révision</span>
            <?php else: ?>
                <span class="statut-attente">En attente</span>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($enRevision): 
                $idAbsence = htmlspecialchars($absence['idabsence']);
                $dateDebut = htmlspecialchars($absence['date_debut']);
                $dateFin = htmlspecialchars($absence['date_fin']);
                $motifUrl = urlencode($absence['motif']);
            ?>
                <a href="etudiant/depotJustificatif.php?id=<?php echo $idAbsence; ?>&date_start=<?php echo $dateDebut; ?>&date_end=<?php echo $dateFin; ?>&motif=<?php echo $motifUrl; ?>&ressoumission=1" class="btn-resoumettre" style="background: #ff9800; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Resoumettre un justificatif</a>
            <?php else: ?>
                <span style="color: #888;">—</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<br>
    <div class="text">
    <a href="/src/Views/etudiant/dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>
</body>
<footer class="footer">
        <nav class="footer-nav">
        <a href="/src/Views/etudiant/dashbord.php">Accueil</a>
        <span>|</span>
        <a href="/src/Views/etudiant/aide.php">Aides</a>
    </nav>
</footer>
</html>