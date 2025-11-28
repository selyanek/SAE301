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

<<<<<<< HEAD
<!-- Tableau de l'historique -->
<table class="liste-absences">
    <thead>
        <tr>
            <th>Date de soumission</th>
            <th>Date de début</th>
            <th>Date de fin</th>
            <th>Cours</th>
            <th>Motif</th>
            <th>Justificatif</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
    <?php
=======
<!-- Liste des absences sous forme de cartes -->
<div class="absences-container">
<?php
>>>>>>> 38e7799923f213b154ac355487afa59093ff58eb
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

<<<<<<< HEAD
                // Déterminer le statut basé sur le champ justifie (null, true, false)
                $statut = 'en_attente'; // Par défaut
                if (isset($absence['justifie']) && $absence['justifie'] !== null) {
                    // PostgreSQL retourne 't' ou 'f' pour les booléens
                    if ($absence['justifie'] === true || $absence['justifie'] === 't' || $absence['justifie'] === '1' || $absence['justifie'] === 1) {
                        $statut = 'valide';
                    } elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0' || $absence['justifie'] === 0) {
                        $statut = 'refuse';
                    }
                }
                // Si justifie est null ou non défini, statut reste 'en_attente'
                
                if ($statutFiltre && $statut != $statutFiltre) {
                    continue;
                }

                // Affichage de la ligne
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

                echo "<tr>";
                echo "<td>" . htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_fin']))) . "</td>";

                // Compose course string from resource name and course type
                $ressourceNom = $absence['ressource_nom'] ?? null;
                $coursType = $absence['cours_type'] ?? null;
                $coursAffiche = '';
                if (!empty($coursType)) {
                    $coursAffiche .= $coursType;
                }
                if (!empty($ressourceNom)) {
                    $coursAffiche .= (!empty($coursAffiche) ? ' - ' : '') . $ressourceNom;
                }
                if (empty($coursAffiche)) {
                    $coursAffiche = '—';
                }
                echo "<td>" . htmlspecialchars($coursAffiche) . "</td>";
                echo "<td>" . htmlspecialchars($absence['motif'] ?? '—') . "</td>";
                
                // Documents justificatifs
                echo "<td>";
                if (!empty($absence['urijustificatif'])) {
                    $fichiers = json_decode($absence['urijustificatif'], true);
                    if (is_array($fichiers) && count($fichiers) > 0) {
                        foreach ($fichiers as $index => $fichier) {
                            $fichierPath = "../../../uploads/" . htmlspecialchars($fichier);
                            echo "<a href='" . $fichierPath . "' target='_blank'>" . htmlspecialchars($fichier) . "</a><br>";
                        }
                    } else {
                        echo "—";
                    }
                } else {
                    echo "—";
                }
                echo "</td>";
                
                echo "<td class='$statutClass'>$statutLabel</td>";
                echo "</tr>";
=======
        // Déterminer le statut
        $statut = 'en_attente';
        if (isset($absence['justifie']) && $absence['justifie'] !== null) {
            if ($absence['justifie'] === true || $absence['justifie'] === 't' || $absence['justifie'] === '1' || $absence['justifie'] === 1) {
                $statut = 'valide';
            } elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0' || $absence['justifie'] === 0) {
                $statut = 'refuse';
>>>>>>> 38e7799923f213b154ac355487afa59093ff58eb
            }
        }

<<<<<<< HEAD
            if ($count == 0) {
                echo "<tr><td colspan='7'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
=======
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
>>>>>>> 38e7799923f213b154ac355487afa59093ff58eb
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

