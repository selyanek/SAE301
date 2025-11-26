<?php
// IMPORTANT: Le code PHP doit être AVANT tout HTML pour éviter "headers already sent"
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../Controllers/session_timeout.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
    header('Location: /public/index.php');
    exit();
}

// Vérifier le rôle responsable
if ($_SESSION['role'] !== 'responsable_pedagogique') {
    header('Location: /public/index.php');
    exit();
}

// Charger les dépendances
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';

// Connexion à la base de données
$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);

// Récupérer toutes les absences
$absences = $absenceModel->getAll();

// Récupération des filtres
$nomFiltre = isset($_POST['nom']) ? strtolower(trim($_POST['nom'])) : '';
$dateFiltre = isset($_POST['date']) ? $_POST['date'] : '';
$statutFiltre = isset($_POST['statut']) ? $_POST['statut'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des absences</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>
<body>
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
        <li><a href="historiqueAbsResp.php">Historique des absences</a></li>
        <li><a href="#">Statistiques</a></li>
    </ul>
</div>

<header class="text">
    <h1>Historique des absences</h1>
    <p>Consultez l'historique de toutes les absences validées ou refusées.</p>
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
        <option value="valide" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'valide') ? 'selected' : ''; ?>>Validé</option>
        <option value="refuse" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'refuse') ? 'selected' : ''; ?>>Refusé</option>
    </select>

    <button type="submit">Filtrer</button>
    <a href="historiqueAbsResp.php"><button type="button">Réinitialiser</button></a>
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
    </tr>
    </thead>
    <tbody>
    <?php
    // Vérifier si des absences existent
    if (!$absences || count($absences) === 0) {
        echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>Aucune absence enregistrée pour le moment.</td></tr>";
    } else {
        // Affichage des absences filtrées
        $count = 0;
        foreach ($absences as $absence) {
            // Application des filtres
            // Récupérer le nom et prénom de l'étudiant
            $prenomEtudiant = $absence['prenomcompte'] ?? $absence['prenomCompte'] ?? '';
            $nomEtudiantNom = $absence['nomcompte'] ?? $absence['nomCompte'] ?? '';
            $nomEtudiant = trim($prenomEtudiant . ' ' . $nomEtudiantNom);
            
            // Si le nom est vide, essayer d'autres champs possibles
            if (empty(trim($nomEtudiant))) {
                $nomEtudiant = $absence['identifiantetu'] ?? $absence['identifiantEtu'] ?? 'Étudiant inconnu';
            }
            
            if ($nomFiltre && strpos(strtolower($nomEtudiant), $nomFiltre) === false) {
                continue;
            }

            $dateDebut = date('Y-m-d', strtotime($absence['date_debut'] ?? 'now'));
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
            
            // Afficher uniquement les absences validées ou refusées dans l'historique
            // Les absences en attente sont dans gestionAbsResp.php
            if ($statut === 'en_attente') {
                continue; // Ne pas afficher les absences en attente dans l'historique
            }
            
            if ($statutFiltre && $statut != $statutFiltre) {
                continue;
            }

            // Affichage de la ligne
            $count++;
            $statutClass = '';
            $statutLabel = '';

            switch($statut) {
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
            // Date de soumission (pour l'instant = date_debut, à améliorer plus tard)
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_fin']))) . "</td>";
            echo "<td>" . htmlspecialchars($nomEtudiant) . "</td>";
            echo "<td>" . htmlspecialchars($absence['motif'] ?? '—') . "</td>";
            
            // Document justificatif
            if (!empty($absence['uriJustificatif'])) {
                echo "<td><a href='" . htmlspecialchars($absence['uriJustificatif']) . "' target='_blank'>📄 Voir le document</a></td>";
            } else {
                echo "<td>—</td>";
            }
            
            echo "<td class='$statutClass'>$statutLabel</td>";
            echo "</tr>";
        }

        if ($count == 0) {
            echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>Aucune absence traitée ne correspond aux critères de filtrage.</td></tr>";
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

<style>
    .statut-valide { color: green; font-weight: bold; }
    .statut-refuse { color: red; font-weight: bold; }
</style>

</body>
</html>
