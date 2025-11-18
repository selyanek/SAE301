<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des absences</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>
<body>
<?php
session_start();
require __DIR__ . '/../Controllers/session_timeout.php'; // Gestion du timeout de session
?>
<!-- Affichage des logos -->
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>

<!-- Barre latérale de navigation -->
<div class="sidebar">
    <ul>
        <li><a href="accueil_responsable.php">Accueil</a></li>
        <li><a href="gestionAbsResp.php">Gestion des absences</a></li>
        <li><a href="traitementDesJustificatif.php">Traitement des Justificatifs</a></li>
        <li><a href="#">Historique des absences</a></li>
        <li><a href="#">Statistiques</a></li>
    </ul>
</div>

<header class="text">
    <h1>Gestion des absences</h1>
</header>

<!-- Filtrage -->
<form method="post">
    <label for="nom">Nom étudiant :</label>
    <input type="text" name="nom" id="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">

    <label for="date">Date :</label>
    <input type="date" name="date" id="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">

    <label for="statut">Statut :</label>
    <select name="statut" id="statut">
        <option value="">Tous</option>
        <option value="en_attente" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'en_attente') ? 'selected' : ''; ?>>En attente</option>
        <option value="valide" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'valide') ? 'selected' : ''; ?>>Validé</option>
        <option value="refuse" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'refuse') ? 'selected' : ''; ?>>Refusé</option>
    </select>

    <button type="submit">Filtrer</button>
    <a href="gestionAbsResp.php"><button type="button">Réinitialiser</button></a>
</form>

<!-- Tableau des absences -->
<table id="tableAbsences">
    <thead>
    <tr>
        <th scope='col'>Date de soumission</th>
        <th scope='col'>Date début</th>
        <th scope='col'>Date fin</th>
        <th scope='col'>Étudiant</th>
        <th scope='col'>Motif</th>
        <th scope='col'>Document</th>
        <th scope='col'>Statut</th>
        <th scope='col'>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // Récupération des filtres
    $nomFiltre = isset($_POST['nom']) ? strtolower(trim($_POST['nom'])) : '';
    $dateFiltre = isset($_POST['date']) ? $_POST['date'] : '';
    $statutFiltre = isset($_POST['statut']) ? $_POST['statut'] : '';

    // Afficher les messages
    if (isset($_GET['success'])) {
        echo "<div class='success-message'>✅ " . htmlspecialchars($_GET['success']) . "</div>";
    }
    if (isset($_GET['error'])) {
        echo "<div class='error-message'>❌ Erreur lors du traitement</div>";
    }

    // Chemin vers les fichiers CSV
    $csvFile = '../data/uploads.csv';
    $csvStatuts = '../data/statuts.csv';

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
        echo "<tr><td colspan='8'>Aucune absence enregistrée pour le moment.</td></tr>";
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

            // Affichage des absences filtrées
            $count = 0;
            foreach (array_reverse($absences) as $index => $absence) {
                // Application des filtres
                if ($nomFiltre && strpos(strtolower($absence['nom_fichier']), $nomFiltre) === false) {
                    continue;
                }

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
                        $statutLabel = '⏳ En attente';
                        break;
                    case 'valide':
                        $statutClass = 'statut-valide';
                        $statutLabel = '✅ Validé';
                        break;
                    case 'refuse':
                        $statutClass = 'statut-refuse';
                        $statutLabel = '❌ Refusé';
                        break;
                }

                echo "<tr>";
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_soumission']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_start']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_end']))) . "</td>";
                echo "<td>" . htmlspecialchars($absence['nom_fichier']) . "</td>";
                echo "<td>" . htmlspecialchars($absence['motif']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($absence['fichier']) . "' target='_blank'>📄 Voir le document</a></td>";
                echo "<td class='$statutClass'>$statutLabel</td>";

                // Actions
                echo "<td class='actions'>";
                if ($absence['statut'] == 'en_attente') {
                    echo "<button class='btn-valider' onclick='validerAbsence($index)'>✓ Valider</button>";
                    echo "<button class='btn-refuser' onclick='refuserAbsence($index)'>✗ Refuser</button>";
                } else {
                    echo "<span class='traite'>Traité</span>";
                }
                echo "</td>";
                echo "</tr>";
            }

            if ($count == 0) {
                echo "<tr><td colspan='8'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='8'>Erreur lors de la lecture du fichier CSV.</td></tr>";
        }
    }
    ?>
    </tbody>
</table>

<!-- Pied de page -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="">Aides</a>
    </nav>
</footer>

<script>
    function validerAbsence(index) {
        if (confirm('Voulez-vous vraiment valider cette absence ?')) {
            // À implémenter : appel AJAX vers un script PHP pour mettre à jour le statut
            window.location.href = '../Controllers/traiter_absence.php?action=valider&index=' + index;
        }
    }

    function refuserAbsence(index) {
        if (confirm('Voulez-vous vraiment refuser cette absence ?')) {
            // À implémenter : appel AJAX vers un script PHP pour mettre à jour le statut
            window.location.href = '../Controllers/traiter_absence.php?action=refuser&index=' + index;
        }
    }
</script>

<style>
    .statut-attente { color: orange; font-weight: bold; }
    .statut-valide { color: green; font-weight: bold; }
    .statut-refuse { color: red; font-weight: bold; }
    .actions { display: flex; gap: 5px; }
    .btn-valider { background: #4CAF50; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; }
    .btn-refuser { background: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px; }
    .btn-valider:hover { background: #45a049; }
    .btn-refuser:hover { background: #da190b; }
    .traite { color: #888; font-style: italic; }
</style>

</body>
</html>