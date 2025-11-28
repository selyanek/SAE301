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
    // Récupération des filtres
    $dateFiltre = isset($_POST['date']) ? $_POST['date'] : '';
    $statutFiltre = isset($_POST['statut']) ? $_POST['statut'] : '';

    // Identifier l'étudiant connecté
    $studentId = $_SESSION['login'] ?? '';
    $studentName = $_SESSION['nom'] ?? '';

    // Charger les absences depuis la BDD pour l'étudiant connecté
    require __DIR__ . '/../../Database/Database.php';
    require __DIR__ . '/../../Models/Absence.php';
    // Instancier la BDD et le modèle en utilisant des noms fully-qualified (éviter 'use' après du code)
    $db = new \src\Database\Database();
    $pdo = $db->getConnection();
    $absenceModel = new \src\Models\Absence($pdo);
    $mesAbsences = [];
    if (!empty($studentId)) {
        // On suppose que $studentId correspond à identifiantEtu enregistré dans la session
        $mesAbsences = $absenceModel->getByStudentIdentifiant($studentId);
    }

            // Affichage des absences filtrées (les plus récentes en premier). We already ordered by date_debut desc
            $count = 0;
            foreach ($mesAbsences as $absence) {
                // Application des filtres
                $dateDebut = date('Y-m-d', strtotime($absence['date_debut']));
                if ($dateFiltre && $dateDebut != $dateFiltre) {
                    continue;
                }

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
            }

            if ($count == 0) {
                echo "<tr><td colspan='7'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
            }
    ?>
    </tbody>
</table>

<br>

<div class="text">
    <a href="dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>

<style>
    .filtre-form {
        margin: 20px 0;
        padding: 15px;
        background-color: #f5f5f5;
        border-radius: 5px;
    }
    .filtre-form label {
        margin-right: 10px;
        font-weight: bold;
    }
    .filtre-form input,
    .filtre-form select {
        margin-right: 15px;
        padding: 5px;
    }
    .filtre-form button {
        padding: 5px 15px;
        margin-right: 10px;
    }
    .statut-attente { 
        color: orange; 
        font-weight: bold; 
    }
    .statut-valide { 
        color: green; 
        font-weight: bold; 
    }
    .statut-refuse { 
        color: red; 
        font-weight: bold; 
    }
</style>

<?php
require '../layout/footer.php';
?>

