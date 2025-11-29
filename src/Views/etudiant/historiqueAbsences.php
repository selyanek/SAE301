<?php
// Page d'historique des absences pour l'étudiant
session_start();
require '../../Controllers/session_timeout.php'; // Gestion du timeout de session
$pageTitle = 'Historique des absences';
$additionalCSS = ['../../../public/asset/CSS/cssGererAbsEtu.css'];
require '../layout/header.php';
require '../layout/navigation.php';
?>

<header class="text">
    <h1>Historique de mes absences</h1>
    <p>Consultez l'historique de toutes vos absences et leur statut de traitement.</p>
</header>

<!-- Formulaire de filtrage -->
<form method="post" class="filtre-form">
    <label for="date">Filtrer par date :</label>
    <input type="date" name="date" id="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">

    <label for="statut">Filtrer par statut :</label>
    <select name="statut" id="statut">
        <option value="">Tous</option>
        <option value="en_attente" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'en_attente') ? 'selected' : ''; ?>>En attente</option>
        <option value="valide" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'valide') ? 'selected' : ''; ?>>Validé</option>
        <option value="refuse" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'refuse') ? 'selected' : ''; ?>>Refusé</option>
    </select>

    <button type="submit">Filtrer</button>
    <a href="historiqueAbsences.php"><button type="button">Réinitialiser</button></a>
</form>

<!-- Liste des absences sous forme de cartes -->
<div class="absences-container">
<?php
    // Récupération des filtres
    $dateFiltre = isset($_POST['date']) ? $_POST['date'] : '';
    $statutFiltre = isset($_POST['statut']) ? $_POST['statut'] : '';

    // Identifier l'étudiant connecté
    $studentId = $_SESSION['login'] ?? '';
    $studentName = $_SESSION['nom'] ?? '';

    // Charger les absences depuis la BDD pour l'étudiant connecté
    require __DIR__ . '/../../Database/Database.php';
    require __DIR__ . '/../../Models/Absence.php';
    $db = new \src\Database\Database();
    $pdo = $db->getConnection();
    $absenceModel = new \src\Models\Absence($pdo);
    $mesAbsences = [];
    if (!empty($studentId)) {
        $mesAbsences = $absenceModel->getByStudentIdentifiant($studentId);
    }

    $count = 0;
    foreach ($mesAbsences as $absence) {
        // Application des filtres
        $dateDebut = date('Y-m-d', strtotime($absence['date_debut']));
        if ($dateFiltre && $dateDebut != $dateFiltre) {
            continue;
        }

        // Déterminer le statut
        $statut = 'en_attente';
        if (isset($absence['justifie']) && $absence['justifie'] !== null) {
            if ($absence['justifie'] === true || $absence['justifie'] === 't' || $absence['justifie'] === '1' || $absence['justifie'] === 1) {
                $statut = 'valide';
            } elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0' || $absence['justifie'] === 0) {
                $statut = 'refuse';
            }
        }

        if ($statutFiltre && $statut != $statutFiltre) {
            continue;
        }

        $count++;
        $statutClass = '';
        $statutLabel = '';
        switch($statut) {
            case 'en_attente':
                $statutClass = 'statut-attente';
                $statutLabel = 'En attente';
                break;
            case 'valide':
                $statutClass = 'statut-valide';
                $statutLabel = 'Validé';
                break;
            case 'refuse':
                $statutClass = 'statut-refuse';
                $statutLabel = 'Refusé';
                break;
        }

        // Préparer les dates et justificatifs
        $dateSoumission = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut'])));
        $dateFin = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_fin'])));
        $motif = htmlspecialchars($absence['motif']);

        // Construire la liste des fichiers justificatifs
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
            }
        }

        echo "<div class='absence-card {$statutClass}'>";
        echo "  <div class='card-dates'>";
        echo "    <div><strong>Soumission</strong><br>{$dateSoumission}</div>";
        echo "    <div><strong>Début</strong><br>" . htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut']))) . "</div>";
        echo "    <div><strong>Fin</strong><br>{$dateFin}</div>";
        echo "  </div>";
        echo "  <div class='card-info'>";
        echo "    <div class='motif'><strong>Motif :</strong> {$motif}</div>";
        echo "    <div class='justif'><strong>Justificatif :</strong><br>{$justificatifsHtml}</div>";
        
        // Afficher la raison du refus si l'absence est refusée
        if ($statut === 'refuse' && !empty($absence['raison_refus'])) {
            $raisonRefus = htmlspecialchars($absence['raison_refus']);
            echo "    <div class='raison-refus' style='margin-top: 15px; padding: 12px; background-color: #ffe6e6; border-left: 4px solid #f44336; border-radius: 4px;'>";
            echo "      <strong style='color: #d32f2f;'> Raison du refus :</strong><br>";
            echo "      <span style='color: #333; display: block; margin-top: 5px;'>{$raisonRefus}</span>";
            echo "    </div>";
        }
        
        echo "  </div>";
        echo "  <div class='card-status'>";
        echo "    <span class='status-badge'>{$statutLabel}</span>";
        echo "  </div>";
        echo "</div>";
    }

    if ($count == 0) {
        echo "<p class='no-results'>Aucune absence ne correspond aux critères de filtrage.</p>";
    }
?>
</div>

<br>

<div class="text">
    <a href="dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>

<?php
require '../layout/footer.php';
?>

