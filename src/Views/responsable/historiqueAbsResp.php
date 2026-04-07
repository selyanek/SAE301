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

<header class="text fade-in">
    <h1>Historique des absences</h1>
    <p>Consultez et gérez l'historique des absences traitées.</p>
</header>

<!-- Messages flash -->
<div class="text fade-in">
<?php if ($successMsg): ?>
    <div class="alert-success"><?= $successMsg ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="alert-error"><?= $errorMsg ?></div>
<?php endif; ?>
</div>

<div class="text fade-in">
<!-- Formulaire de filtre -->
<form method="post" id="absenceFilterForm" class="filter-form" data-endpoint="../../Controllers/api_absences.php">
    <input type="hidden" name="mode" value="historique">
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
    <a href="historiqueAbsResp.php" id="resetFiltersButton" class="btn" role="button">Réinitialiser</a>
</form>
<div id="tableLoader" class="ajax-loader" hidden>Chargement des absences...</div>
<div id="tableFeedback" class="ajax-feedback" hidden></div>

<!-- Tableau des absences -->
<div class="table-wrapper">
    <table id="tableAbsences" data-pagination="true" data-page-size="8">
        <thead>
        <tr>
            <th>Dates</th>
            <th>Étudiant</th>
            <th>Motif</th>
            <th>Document</th>
            <th>Statut</th>
        </tr>
        </thead>
        <tbody id="tableAbsencesBody">
        <?php
        if (!$absences || count($absences) === 0) {
            echo "<tr><td colspan='5' class='empty-message'>Aucune absence enregistrée.</td></tr>";
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
                echo "<tr><td colspan='5' class='empty-message'>Aucune absence ne correspond aux critères.</td></tr>";
            } else {
                foreach ($periodesTotales as $p) {
                    $statutClass = $p['statut'] === 'valide' ? 'statut-valide' : 'statut-refuse';
                    $statutLabel = $p['statut'] === 'valide' ? 'Valide' : 'Refusé';
                    $verrouille = $p['verrouille'] === true || $p['verrouille'] === 't' || $p['verrouille'] === '1';
                    $idAbs = $p['idabsence'];

                    echo "<tr>";
                    // Dates empilées (US-27)
                    echo "<td class='td-dates' data-label='Dates'>";
                    echo "<span class='date-debut'>" . date('d/m/Y H:i', strtotime($p['date_debut'])) . "</span>";
                    echo "<span class='date-fin'>" . date('d/m/Y H:i', strtotime($p['date_fin'])) . "</span>";
                    echo "</td>";
                    // Nom empilé (US-27)
                    $nomParts = explode(' ', $p['etudiant'], 2);
                    echo "<td class='td-etudiant' data-label='Étudiant'>";
                    echo "<span class='etudiant-prenom'>" . htmlspecialchars($nomParts[0] ?? '') . "</span>";
                    echo "<span class='etudiant-nom'>" . htmlspecialchars($nomParts[1] ?? '') . "</span>";
                    echo "</td>";

                    // Motif — bouton "Voir" pour mobile (US-27)
                    $motifTexte = htmlspecialchars($p['motif']);
                    echo "<td class='td-motif' data-label='Motif'>";
                    echo "<span class='cell-full'>" . $motifTexte . "</span>";
                    echo "<button class='btn-voir' onclick='ouvrirModale(\"Motif\", this.dataset.content)' data-content='" . htmlspecialchars($motifTexte, ENT_QUOTES) . "'>Voir</button>";
                    echo "</td>";

                    // Document — bouton "Voir" pour mobile (US-27)
                    $docHtml = '';
                    if (!empty($p['urijustificatif'])) {
                        $fichiers = json_decode($p['urijustificatif'], true);
                        if (is_array($fichiers)) {
                            foreach ($fichiers as $f) {
                                $docHtml .= "<a href='/uploads/".htmlspecialchars($f)."' target='_blank'>".htmlspecialchars($f)."</a><br>";
                            }
                        } else {
                            $docHtml = "-";
                        }
                    } else {
                        $docHtml = "-";
                    }
                    echo "<td class='td-document' data-label='Document'>";
                    echo "<span class='cell-full'>" . $docHtml . "</span>";
                    echo "<button class='btn-voir btn-voir-doc' onclick='ouvrirModaleDoc(this)' data-content='" . htmlspecialchars($docHtml, ENT_QUOTES) . "'>Voir</button>";
                    echo "</td>";

                    // Statut avec span (US-27)
                    echo "<td data-label='Statut'>";
                    echo "<span class='statut-badge $statutClass'>$statutLabel</span>";
                    if ($verrouille) echo " <span class='badge-verrouille' title='Décision verrouillée'>🔒</span>";
                    if ($p['statut'] === 'refuse' && !empty($p['raison_refus'])) {
                        echo "<div class='refus-reason'><strong>Raison:</strong> ".htmlspecialchars($p['raison_refus'])."</div>";
                    }
                    echo "</td>";

                    echo "</tr>";
                }
            }
        }
        ?>
        </tbody>
    </table>
</div>
</div> <!-- Fin de text fade-in -->

<div class="spacer-150"></div>

<!-- Footer -->
<?php include __DIR__.'/../layout/footer.php'; ?>

<script src="/public/asset/JS/jsHistoriqueResponsable.js"></script>
<script src="/public/asset/JS/filterAjax.js"></script>
<script src="/public/asset/JS/tablePagination.js"></script>

<!-- US-27 : Modale pour afficher le contenu des colonnes sur mobile -->
<div id="modaleDetail" class="modale-overlay" style="display:none;" onclick="if(event.target===this)fermerModale()">
    <div class="modale-content">
        <div class="modale-header">
            <h3 id="modale-titre"></h3>
            <button class="modale-close" onclick="fermerModale()">✕</button>
        </div>
        <div id="modale-body" class="modale-body"></div>
    </div>
</div>

<script>
function ouvrirModale(titre, contenu) {
    document.getElementById('modale-titre').textContent = titre;
    document.getElementById('modale-body').textContent = contenu;
    document.getElementById('modaleDetail').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function ouvrirModaleDoc(btn) {
    document.getElementById('modale-titre').textContent = 'Document';
    document.getElementById('modale-body').innerHTML = btn.dataset.content;
    document.getElementById('modaleDetail').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function fermerModale() {
    document.getElementById('modaleDetail').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fermerModale();
});
</script>
</body>
</html>