<?php
session_start();
require '../../Controllers/session_timeout.php'; // Gestion du timeout de session
$pageTitle = 'Gestion des absences';
$additionalCSS = ['../../../public/asset/CSS/cssGestionAbsResp.css'];
require '../layout/header.php';
require '../layout/navigation.php';
?>

<header class="text">
    <h1>Gestion des absences</h1>
</header>

<?php // Filtrage ?>
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
    <a href="gestionAbsence.php"><button type="button">Réinitialiser</button></a>
</form>

<?php // Tableau des absences ?>
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
        echo "<div class='success-message'>" . htmlspecialchars($_GET['success']) . "</div>";
    }
    if (isset($_GET['error'])) {
        echo "<div class='error-message'>Erreur lors du traitement</div>";
    }

    // Charger depuis la base de données
    require __DIR__ . '/../../Database/Database.php';
    require __DIR__ . '/../../Models/Absence.php';
    // Instancier la BDD et le modèle en utilisant des noms fully-qualified (éviter 'use' après du code)
    $db = new \src\Database\Database();
    $pdo = $db->getConnection();
    $absenceModel = new \src\Models\Absence($pdo);
    $absences = $absenceModel->getAll();

    // Affichage des absences filtrées
    $count = 0;
    foreach ($absences as $absence) {
                // Application des filtres
                if ($nomFiltre && strpos(strtolower($absence['prenomCompte'] . ' ' . $absence['nomCompte']), $nomFiltre) === false) {
                    continue;
                }

                $dateDebut = date('Y-m-d', strtotime($absence['date_debut']));
                if ($dateFiltre && $dateDebut != $dateFiltre) {
                    continue;
                }

                $statut = isset($absence['justifie']) && $absence['justifie'] ? 'valide' : 'en_attente';
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
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_fin']))) . "</td>";
                echo "<td>" . htmlspecialchars($absence['prenomCompte'] . ' ' . $absence['nomCompte']) . "</td>";
                echo "<td>" . htmlspecialchars($absence['motif']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($absence['uriJustificatif']) . "' target='_blank'>Voir le document</a></td>";
                echo "<td class='$statutClass'>$statutLabel</td>";

                // Actions
                echo "<td class='actions'>";
                if ($statut == 'en_attente') {
                    echo "<button class='btn-valider' onclick='validerAbsence(" . $absence['idabsence'] . ")'>✓ Valider</button>";
                    echo "<button class='btn-refuser' onclick='refuserAbsence(" . $absence['idabsence'] . ")'>✗ Refuser</button>";
                } else {
                    echo "<span class='traite'>Traité</span>";
                }
                echo "</td>";
                echo "</tr>";
            }

            if ($count == 0) {
                echo "<tr><td colspan='8'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
            }
    ?>
    </tbody>
</table>

<script>
    function validerAbsence(idAbsence) {
        if (confirm('Voulez-vous vraiment valider cette absence ?')) {
            // À implémenter : appel AJAX vers un script PHP pour mettre à jour le statut
            window.location.href = '../../Controllers/traiter_absence.php?action=valider&id=' + idAbsence;
        }
    }

    function refuserAbsence(idAbsence) {
        if (confirm('Voulez-vous vraiment refuser cette absence ?')) {
            // À implémenter : appel AJAX vers un script PHP pour mettre à jour le statut
            window.location.href = '../../Controllers/traiter_absence.php?action=refuser&id=' + idAbsence;
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

<?php
require '../layout/footer.php';
?>
