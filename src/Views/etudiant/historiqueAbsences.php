<?php
// Page d'historique des absences pour l'étudiant
session_start();
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

    // Chemin vers les fichiers CSV (depuis etudiant/, remonter de 2 niveaux)
    $csvFile = '../../data/uploads.csv';
    $csvStatuts = '../../data/statuts.csv';

    // Charger les statuts
    $statuts = [];
    if (file_exists($csvStatuts)) {
        $handleStatuts = fopen($csvStatuts, 'r');
        if ($handleStatuts) {
            while (($data = fgetcsv($handleStatuts, 1000, ';', '"', '')) !== FALSE) {
                if (count($data) >= 3) {
                    // Clé: date_soumission|chemin_fichier
                    $key = $data[0] . '|' . $data[1];
                    $statuts[$key] = $data[2]; // statut
                }
            }
            fclose($handleStatuts);
        }
    }

    // Vérifier si le fichier existe
    if (!file_exists($csvFile)) {
        echo "<tr><td colspan='6'>Aucune absence enregistrée pour le moment.</td></tr>";
    } else {
        // Lecture du fichier CSV
        $handle = fopen($csvFile, 'r');
        $absences = [];

        if ($handle) {
            while (($data = fgetcsv($handle, 1000, ';', '"', '')) !== FALSE) {
                // Structure: date_soumission, date_start, date_end, motif, chemin_fichier, nom_fichier_original
                if (count($data) >= 6) {
                    $key = $data[0] . '|' . $data[4];
                    $absences[] = [
                        'date_soumission' => $data[0],
                        'date_start' => $data[1],
                        'date_end' => $data[2],
                        'motif' => $data[3],
                        'fichier' => $data[4],
                        'nom_fichier' => $data[5],
                        'statut' => isset($statuts[$key]) ? $statuts[$key] : 'en_attente'
                    ];
                }
            }
            fclose($handle);

            // Filtrer les absences de l'étudiant connecté
            // Note: On suppose que les absences sont liées à l'étudiant via la session
            // Si vous avez un système d'identification dans le CSV, adaptez cette partie
            $mesAbsences = [];
            foreach ($absences as $absence) {
                // Pour l'instant, on affiche toutes les absences
                // Vous pouvez ajouter un filtre basé sur l'identifiant de l'étudiant si disponible dans le CSV
                $mesAbsences[] = $absence;
            }

            // Affichage des absences filtrées (les plus récentes en premier)
            $count = 0;
            foreach (array_reverse($mesAbsences) as $absence) {
                // Application des filtres
                $dateDebut = date('Y-m-d', strtotime($absence['date_start']));
                if ($dateFiltre && $dateDebut != $dateFiltre) {
                    continue;
                }

                if ($statutFiltre && $absence['statut'] != $statutFiltre) {
                    continue;
                }

                // Affichage de la ligne
                $count++;
                $statutClass = '';
                $statutLabel = '';

                switch($absence['statut']) {
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
                echo "<td>" . htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_soumission']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_start']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_end']))) . "</td>";
                echo "<td>" . htmlspecialchars($absence['motif']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($absence['fichier']) . "' target='_blank'>Voir le justificatif</a></td>";
                echo "<td class='$statutClass'>$statutLabel</td>";
                echo "</tr>";
            }

            if ($count == 0) {
                echo "<tr><td colspan='6'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='6'>Erreur lors de la lecture du fichier CSV.</td></tr>";
        }
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

