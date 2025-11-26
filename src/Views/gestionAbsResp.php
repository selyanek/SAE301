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

    // Nouvelle approche : charger les absences depuis la base de données
    require __DIR__ . '/../Database/Database.php';
    require __DIR__ . '/../Models/Absence.php';
    // Instancier la BDD et le modèle en utilisant des noms fully-qualified (éviter 'use' après du code)
    $db = new \src\Database\Database();
    $pdo = $db->getConnection();
    $absenceModel = new \src\Models\Absence($pdo);
    $absences = $absenceModel->getAll();
    if (!$absences || count($absences) === 0) {
        echo "<tr><td colspan='8'>Aucune absence enregistrée pour le moment.</td></tr>";
    } else {

            // Affichage des absences filtrées
            $count = 0;
            foreach ($absences as $absence) {
                // Application des filtres
                if ($nomFiltre && strpos(strtolower($absence['nom_fichier'] ?? ($absence['prenomCompte'] . ' ' . $absence['nomCompte'])), $nomFiltre) === false) {
                    continue;
                }

                $dateDebut = date('Y-m-d', strtotime($absence['date_debut'] ?? $absence['date_start'] ?? 'now'));
                if ($dateFiltre && $dateDebut != $dateFiltre) {
                    continue;
                }

                // Convertir le booléen 'justifie' en libellé de statut
                $statut = 'en_attente';
                if (isset($absence['justifie'])) {
                    $statut = $absence['justifie'] ? 'valide' : 'en_attente';
                }
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
                // Mappage aux champs de la BDD
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))) . "</td>";
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_fin']))) . "</td>";
                echo "<td>" . htmlspecialchars($absence['prenomCompte'] . ' ' . $absence['nomCompte']) . "</td>";
                echo "<td>" . htmlspecialchars($absence['motif']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($absence['uriJustificatif']) . "' target='_blank'>📄 Voir le document</a></td>";
                echo "<td class='$statutClass'>$statutLabel</td>";

                // Actions
                echo "<td class='actions'>";

                // Bouton pour voir les détails - toujours visible
                if ($statut == 'en_attente' || $statut == '') {
                    echo "<a href='../Views/traitementDesJustificatif.php?id=" . $absence['idabsence'] . "' class='btn_justif'>Détails</a>";
                } else {
                    echo "<span class='traite'>Traité</span>";
                }
                echo "</td>";
                echo "</tr>";
            }

            if ($count == 0) {
                echo "<tr><td colspan='8'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
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
            window.location.href = '../Controllers/traiter_absence.php?action=valider&id=' + index;
        }
    }

    function refuserAbsence(index) {
        if (confirm('Voulez-vous vraiment refuser cette absence ?')) {
            // À implémenter : appel AJAX vers un script PHP pour mettre à jour le statut
            window.location.href = '../Controllers/traiter_absence.php?action=refuser&id=' + index;
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