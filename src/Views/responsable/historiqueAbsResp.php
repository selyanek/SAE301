<?php
// US-9 : Historique des absences avec verrouillage/revision
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../../Controllers/session_timeout.php';

// Vérifier connexion
if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
    header('Location: /public/index.php');
    exit();
}

// Vérifier rôle responsable
if ($_SESSION['role'] !== 'responsable_pedagogique') {
    header('Location: /public/index.php');
    exit();
}

// Charger les dépendances
require __DIR__ . '/../../Database/Database.php';
require __DIR__ . '/../../Models/Absence.php';
require __DIR__ . '/../../Models/HistoriqueDecision.php';

$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);
$historiqueModel = new \src\Models\HistoriqueDecision($pdo);

// Récupérer toutes les absences
$absences = $absenceModel->getAll();

// Filtres
$nomFiltre = isset($_POST['nom']) ? strtolower(trim($_POST['nom'])) : '';
$dateFiltre = isset($_POST['date']) ? $_POST['date'] : '';
$statutFiltre = isset($_POST['statut']) ? $_POST['statut'] : '';

// Messages flash
$successMsg = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$errorMsg = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Inclure header et navigation
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>

<link href="/public/asset/CSS/cssHistoriqueResponsable.css" rel="stylesheet">

<!-- Messages flash -->
<?php if ($successMsg): ?>
    <div class="alert-success"><?= $successMsg ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="alert-error"><?= $errorMsg ?></div>
<?php endif; ?>

<header class="text">
    <h1>Historique des absences</h1>
    <p>Consultez et gérez l'historique des absences traitées.</p>
</header>

<!-- Formulaire de filtre -->
<form method="post" class="filter-form">
    <label for="nom">Nom étudiant :</label>
    <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">

    <label for="date">Date :</label>
    <input type="date" name="date" id="date" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">

    <label for="statut">Statut :</label>
    <select name="statut" id="statut">
        <option value="">Tous</option>
        <option value="valide" <?= (($_POST['statut'] ?? '') == 'valide') ? 'selected' : '' ?>>Valide</option>
        <option value="refuse" <?= (($_POST['statut'] ?? '') == 'refuse') ? 'selected' : '' ?>>Refusé</option>
    </select>

    <button type="submit" class="btn">Filtrer</button>
    <a href="historiqueAbsResp.php" class="btn">Réinitialiser</a>
</form>

<!-- Tableau des absences -->
<div class="table-wrapper">
    <table id="tableAbsences">
        <thead>
        <tr>
            <th>Date soumission</th>
            <th>Date début</th>
            <th>Date fin</th>
            <th>Étudiant</th>
            <th>Motif</th>
            <th>Document</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!$absences || count($absences) === 0) {
            echo "<tr><td colspan='8' class='empty-message'>Aucune absence enregistrée.</td></tr>";
        } else {
            $absencesParEtudiant = [];
            foreach ($absences as $absence) {
                $prenomEtu = $absence['prenomcompte'] ?? '';
                $nomEtu = $absence['nomcompte'] ?? '';
                $nomEtudiant = trim($prenomEtu . ' ' . $nomEtu);
                if (empty($nomEtudiant)) $nomEtudiant = $absence['identifiantetu'] ?? 'Etudiant inconnu';

                if ($nomFiltre && strpos(strtolower($nomEtudiant), $nomFiltre) === false) continue;

                $dateJour = date('Y-m-d', strtotime($absence['date_debut'] ?? 'now'));
                if ($dateFiltre && $dateJour != $dateFiltre) continue;

                $statut = 'en_attente';
                if (isset($absence['justifie'])) {
                    if ($absence['justifie'] === true || $absence['justifie'] === 't' || $absence['justifie'] === '1') $statut = 'valide';
                    elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0') $statut = 'refuse';
                }

                if ($statut === 'en_attente') continue;
                if ($statutFiltre && $statut != $statutFiltre) continue;

                $idEtudiant = $absence['idetudiant'] ?? 0;
                if (!isset($absencesParEtudiant[$idEtudiant])) {
                    $absencesParEtudiant[$idEtudiant] = ['nom' => $nomEtudiant, 'absences' => []];
                }
                $absencesParEtudiant[$idEtudiant]['absences'][] = [
                    'date_debut' => $absence['date_debut'],
                    'date_fin' => $absence['date_fin'],
                    'motif' => $absence['motif'] ?? '-',
                    'urijustificatif' => $absence['urijustificatif'] ?? '',
                    'statut' => $statut,
                    'idabsence' => $absence['idabsence'],
                    'verrouille' => $absence['verrouille'] ?? false,
                    'raison_refus' => $absence['raison_refus'] ?? null
                ];
            }

            function regrouperAbsencesHistoUS9($absences) {
                if (empty($absences)) return [];
                usort($absences, fn($a,$b) => strtotime($a['date_debut']) - strtotime($b['date_debut']));
                $periodes = [];
                $periode = null;
                foreach ($absences as $abs) {
                    $debut = strtotime($abs['date_debut']);
                    if ($periode === null) $periode = $abs;
                    else {
                        $fin = strtotime($periode['date_fin']);
                        if ($debut - $fin <= 86400) $periode['date_fin'] = $abs['date_fin'];
                        else { $periodes[] = $periode; $periode = $abs; }
                    }
                }
                if ($periode !== null) $periodes[] = $periode;
                return $periodes;
            }

            $periodesTotales = [];
            foreach ($absencesParEtudiant as $idEtu => $data) {
                $periodesEtu = regrouperAbsencesHistoUS9($data['absences']);
                foreach ($periodesEtu as $p) $periodesTotales[] = array_merge($p, ['etudiant' => $data['nom']]);
            }

            if (count($periodesTotales) === 0) {
                echo "<tr><td colspan='8' class='empty-message'>Aucune absence ne correspond aux critères.</td></tr>";
            } else {
                foreach ($periodesTotales as $p) {
                    $statutClass = $p['statut'] === 'valide' ? 'statut-valide' : 'statut-refuse';
                    $statutLabel = $p['statut'] === 'valide' ? 'Valide' : 'Refusé';
                    $verrouille = $p['verrouille'] === true || $p['verrouille'] === 't' || $p['verrouille'] === '1';
                    $idAbs = $p['idabsence'];

                    echo "<tr>";
                    echo "<td>" . date('d/m/Y', strtotime($p['date_debut'])) . "</td>";
                    echo "<td>" . date('d/m/Y H:i', strtotime($p['date_debut'])) . "</td>";
                    echo "<td>" . date('d/m/Y H:i', strtotime($p['date_fin'])) . "</td>";
                    echo "<td>" . htmlspecialchars($p['etudiant']) . "</td>";
                    echo "<td>" . htmlspecialchars($p['motif']) . "</td>";

                    // Documents
                    echo "<td>";
                    if (!empty($p['urijustificatif'])) {
                        $fichiers = json_decode($p['urijustificatif'], true);
                        if (is_array($fichiers)) foreach ($fichiers as $f) echo "<a href='/uploads/".htmlspecialchars($f)."' target='_blank'>".htmlspecialchars($f)."</a><br>";
                        else echo "-";
                    } else echo "-";
                    echo "</td>";

                    // Statut
                    echo "<td class='$statutClass'>";
                    echo $statutLabel;
                    if ($verrouille) echo " <span class='badge-verrouille' title='Décision verrouillée'>🔒</span>";
                    if ($p['statut'] === 'refuse' && !empty($p['raison_refus'])) {
                        // afficher la raison dans un élément dédié en dehors des boutons
                        echo "<div class='refus-reason'><strong>Raison:</strong> ".htmlspecialchars($p['raison_refus'])."</div>";
                    }
                    echo "</td>";

                    // Actions (placer les boutons DANS un conteneur <div class='actions'> pour garantir le style)
                    echo "<td>";
                    echo "<div class='actions'>";
                    if ($verrouille) {
                        echo "<button class='btn-deverrouiller' onclick='confirmerDeverrouillage($idAbs)'>Déverrouiller</button>";
                    } else {
                        echo "<button class='btn-verrouiller' onclick='confirmerVerrouillage($idAbs)'>Verrouiller</button>";
                    }
                    echo "<button class='btn-reviser' onclick='ouvrirModaleRevision($idAbs,\"{$p['statut']}\")'>Réviser</button>";
                    echo "<button class='btn-historique' onclick='voirHistorique($idAbs)'>Historique</button>";
                    echo "</div>";
                    echo "</td>";

                    echo "</tr>";
                }
            }
        }
        ?>
        </tbody>
    </table>
</div>

<div class="spacer-150"></div>

<!-- Footer -->
<?php include __DIR__.'/../layout/footer.php'; ?>


<script src="/public/asset/JS/jsHistoriqueResponsable.js"></script>
</body>
</html>
