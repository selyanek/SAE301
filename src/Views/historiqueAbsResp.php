<?php
// US-9 : Historique des absences avec verrouillage/revision
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../Controllers/session_timeout.php';

// Verifier connexion
if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
    header('Location: /public/index.php');
    exit();
}

// Verifier role responsable
if ($_SESSION['role'] !== 'responsable_pedagogique') {
    header('Location: /public/index.php');
    exit();
}

// Charger les dependances
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';
require __DIR__ . '/../Models/HistoriqueDecision.php';

$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);
$historiqueModel = new \src\Models\HistoriqueDecision($pdo);

// Recuperer toutes les absences
$absences = $absenceModel->getAll();

// Filtres
$nomFiltre = isset($_POST['nom']) ? strtolower(trim($_POST['nom'])) : '';
$dateFiltre = isset($_POST['date']) ? $_POST['date'] : '';
$statutFiltre = isset($_POST['statut']) ? $_POST['statut'] : '';

// Messages flash
$successMsg = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
$errorMsg = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des absences</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssHistoriqueAbsResp.css" rel="stylesheet">
</head>
<body>
<!-- Logos -->
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>

<!-- Sidebar -->
<div class="sidebar">
    <ul>
        <li><a href="accueil_responsable.php">Accueil</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="gestionAbsResp.php">Gestion des absences</a></li>
        <li><a href="historiqueAbsResp.php">Historique des absences</a></li>
    </ul>
</div>

<!-- Messages flash -->
<?php if ($successMsg): ?>
    <div class="alert-success"><?= $successMsg ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="alert-error"><?= $errorMsg ?></div>
<?php endif; ?>

<header class="text">
    <h1>Historique des absences</h1>
    <p>Consultez et gerez l'historique des absences traitees.</p>
</header>

<!-- Filtres -->
<form method="post" style="max-width: 1200px; margin: 0 auto 20px auto; padding: 0 20px;">
    <label for="nom">Nom etudiant :</label>
    <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">

    <label for="date">Date :</label>
    <input type="date" name="date" id="date" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">

    <label for="statut">Statut :</label>
    <select name="statut" id="statut">
        <option value="">Tous</option>
        <option value="valide" <?= (($_POST['statut'] ?? '') == 'valide') ? 'selected' : '' ?>>Valide</option>
        <option value="refuse" <?= (($_POST['statut'] ?? '') == 'refuse') ? 'selected' : '' ?>>Refuse</option>
    </select>

    <button type="submit">Filtrer</button>
    <a href="historiqueAbsResp.php"><button type="button">Reinitialiser</button></a>
</form>

<!-- Tableau -->
<table id="tableAbsences">
    <thead>
    <tr>
        <th>Date soumission</th>
        <th>Date debut</th>
        <th>Date fin</th>
        <th>Etudiant</th>
        <th>Motif</th>
        <th>Document</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (!$absences || count($absences) === 0) {
        echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>Aucune absence enregistree.</td></tr>";
    } else {
        // Regrouper par etudiant
        $absencesParEtudiant = [];
        
        foreach ($absences as $absence) {
            $prenomEtu = $absence['prenomcompte'] ?? '';
            $nomEtu = $absence['nomcompte'] ?? '';
            $nomEtudiant = trim($prenomEtu . ' ' . $nomEtu);
            
            if (empty(trim($nomEtudiant))) {
                $nomEtudiant = $absence['identifiantetu'] ?? 'Etudiant inconnu';
            }
            
            // Filtre nom
            if ($nomFiltre && strpos(strtolower($nomEtudiant), $nomFiltre) === false) {
                continue;
            }

            $dateJour = date('Y-m-d', strtotime($absence['date_debut'] ?? 'now'));
            
            // Filtre date
            if ($dateFiltre && $dateJour != $dateFiltre) {
                continue;
            }

            // Determiner statut
            $statut = 'en_attente';
            if (isset($absence['justifie']) && $absence['justifie'] !== null) {
                if ($absence['justifie'] === true || $absence['justifie'] === 't' || $absence['justifie'] === '1') {
                    $statut = 'valide';
                } elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0') {
                    $statut = 'refuse';
                }
            }
            
            // Afficher que les traitees
            if ($statut === 'en_attente') {
                continue;
            }
            
            // Filtre statut
            if ($statutFiltre && $statut != $statutFiltre) {
                continue;
            }
            
            $idEtudiant = $absence['idetudiant'] ?? 0;
            
            if (!isset($absencesParEtudiant[$idEtudiant])) {
                $absencesParEtudiant[$idEtudiant] = [
                    'nom' => $nomEtudiant,
                    'absences' => []
                ];
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
        
        // Fonction regroupement
        function regrouperAbsencesHistoUS9($absences) {
            if (empty($absences)) return [];
            
            usort($absences, function($a, $b) {
                return strtotime($a['date_debut']) - strtotime($b['date_debut']);
            });
            
            $periodes = [];
            $periode = null;
            
            foreach ($absences as $abs) {
                $debut = strtotime($abs['date_debut']);
                
                if ($periode === null) {
                    $periode = $abs;
                } else {
                    $fin = strtotime($periode['date_fin']);
                    if ($debut - $fin <= 86400) {
                        $periode['date_fin'] = $abs['date_fin'];
                    } else {
                        $periodes[] = $periode;
                        $periode = $abs;
                    }
                }
            }
            
            if ($periode !== null) {
                $periodes[] = $periode;
            }
            
            return $periodes;
        }
        
        // Construire les periodes
        $periodesTotales = [];
        foreach ($absencesParEtudiant as $idEtu => $data) {
            $periodesEtu = regrouperAbsencesHistoUS9($data['absences']);
            foreach ($periodesEtu as $p) {
                $periodesTotales[] = array_merge($p, ['etudiant' => $data['nom']]);
            }
        }
        
        // Affichage
        $count = 0;
        foreach ($periodesTotales as $p) {
            $count++;
            
            $statut = $p['statut'];
            $verrouille = $p['verrouille'] === true || $p['verrouille'] === 't' || $p['verrouille'] === '1';
            $idAbs = $p['idabsence'];
            
            $statutClass = ($statut === 'valide') ? 'statut-valide' : 'statut-refuse';
            $statutLabel = ($statut === 'valide') ? '✅ Valide' : '❌ Refuse';

            echo "<tr>";
            echo "<td>" . htmlspecialchars(date('d/m/Y', strtotime($p['date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($p['date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($p['date_fin']))) . "</td>";
            echo "<td>" . htmlspecialchars($p['etudiant']) . "</td>";
            echo "<td>" . htmlspecialchars($p['motif']) . "</td>";
            
            // Document
            echo "<td>";
            if (!empty($p['urijustificatif'])) {
                $fichiers = json_decode($p['urijustificatif'], true);
                if (is_array($fichiers)) {
                    foreach ($fichiers as $f) {
                        echo "<a href='/uploads/" . htmlspecialchars($f) . "' target='_blank'>" . htmlspecialchars($f) . "</a><br>";
                    }
                } else {
                    echo "-";
                }
            } else {
                echo "-";
            }
            echo "</td>";
            
            // Statut
            echo "<td class='$statutClass'>";
            echo $statutLabel;
            if ($verrouille) {
                echo " <span class='badge-verrouille' title='Decision verrouillee'>🔒</span>";
            }
            if ($statut === 'refuse' && !empty($p['raison_refus'])) {
                echo "<div style='margin-top: 8px; padding: 6px; background: #ffe6e6; border-left: 3px solid #f44336; font-size: 11px;'>";
                echo "<strong>Raison:</strong> " . htmlspecialchars($p['raison_refus']);
                echo "</div>";
            }
            echo "</td>";
            
            // Actions US-9
            echo "<td class='actions-cell'>";
            if ($verrouille) {
                echo "<button class='btn-deverrouiller' onclick='confirmerDeverrouillage($idAbs)'>🔓 Deverrouiller</button>";
            } else {
                echo "<button class='btn-verrouiller' onclick='confirmerVerrouillage($idAbs)'>🔒 Verrouiller</button>";
            }
            echo "<button class='btn-reviser' onclick='ouvrirModaleRevision($idAbs, \"$statut\")'>📝 Reviser</button>";
            echo "<button class='btn-historique' onclick='voirHistorique($idAbs)'>📋 Historique</button>";
            echo "</td>";
            
            echo "</tr>";
        }

        if ($count == 0) {
            echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>Aucune absence ne correspond aux criteres.</td></tr>";
        }
    }
    ?>
    </tbody>
</table>

<div style="height: 150px;"></div>

<!-- Footer -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="aideResp.php">Aides</a>
    </nav>
</footer>

<!-- Modale Revision -->
<div id="modaleRevision" class="modale" style="display: none;">
    <div class="modale-contenu">
        <span class="modale-fermer" onclick="fermerModale('modaleRevision')">&times;</span>
        <h2>Reviser la decision</h2>
        <form action="/src/Controllers/traiter_absence.php" method="POST">
            <input type="hidden" name="action" value="reviser">
            <input type="hidden" name="id" id="revisionIdAbsence">
            
            <div class="form-group">
                <label>Nouveau statut :</label>
                <select name="nouveau_statut" id="revisionStatut" required>
                    <option value="">-- Choisir --</option>
                    <option value="valide">Valide</option>
                    <option value="refuse">Refuse</option>
                    <option value="en_attente">Remettre en attente</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Nouvelle raison (optionnel) :</label>
                <textarea name="nouvelle_raison" rows="2" placeholder="Raison du nouveau statut..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Justification de la revision (obligatoire) :</label>
                <textarea name="justification_revision" rows="3" required placeholder="Pourquoi revisez-vous cette decision ?"></textarea>
            </div>
            
            <div class="modale-actions">
                <button type="button" class="btn-annuler" onclick="fermerModale('modaleRevision')">Annuler</button>
                <button type="submit" class="btn-confirmer">Confirmer la revision</button>
            </div>
        </form>
    </div>
</div>

<!-- Modale Historique -->
<div id="modaleHistorique" class="modale" style="display: none;">
    <div class="modale-contenu modale-large">
        <span class="modale-fermer" onclick="fermerModale('modaleHistorique')">&times;</span>
        <h2>Historique des modifications</h2>
        <div id="historiqueContenu">
            <p>Chargement...</p>
        </div>
    </div>
</div>

<style>
    .statut-valide { color: green; font-weight: bold; }
    .statut-refuse { color: red; font-weight: bold; }
</style>

<script>
// Verrouiller
function confirmerVerrouillage(id) {
    if (confirm('Verrouiller cette decision ? L\'etudiant ne pourra plus resoumettre de justificatif.')) {
        window.location.href = '/src/Controllers/traiter_absence.php?action=verrouiller&id=' + id;
    }
}

// Deverrouiller
function confirmerDeverrouillage(id) {
    if (confirm('Deverrouiller cette decision ? L\'etudiant pourra resoumettre un justificatif.')) {
        window.location.href = '/src/Controllers/traiter_absence.php?action=deverrouiller&id=' + id;
    }
}

// Ouvrir modale revision
function ouvrirModaleRevision(id, statutActuel) {
    document.getElementById('revisionIdAbsence').value = id;
    document.getElementById('revisionStatut').value = '';
    document.getElementById('modaleRevision').style.display = 'flex';
}

// Voir historique
function voirHistorique(id) {
    document.getElementById('historiqueContenu').innerHTML = '<p>Chargement...</p>';
    document.getElementById('modaleHistorique').style.display = 'flex';
    
    fetch('/src/Controllers/traiter_absence.php?action=voir_historique&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.historique.length > 0) {
                let html = '<table class="table-historique"><thead><tr>';
                html += '<th>Date</th><th>Action</th><th>Par</th><th>Details</th>';
                html += '</tr></thead><tbody>';
                
                data.historique.forEach(h => {
                    let badge = 'badge-' + h.type_action;
                    let details = '';
                    
                    if (h.type_action === 'revision') {
                        details = 'De: ' + (h.ancien_statut || '-') + ' → ' + (h.nouveau_statut || '-');
                        if (h.justification) details += '<br>Motif: ' + h.justification;
                    } else if (h.type_action === 'verrouillage') {
                        details = 'Decision verrouillee';
                    } else if (h.type_action === 'deverrouillage') {
                        details = 'Decision deverrouillee';
                    }
                    
                    html += '<tr>';
                    html += '<td>' + new Date(h.date_action).toLocaleString('fr-FR') + '</td>';
                    html += '<td><span class="badge-action ' + badge + '">' + h.type_action + '</span></td>';
                    html += '<td>' + (h.prenom_responsable || '') + ' ' + (h.nom_responsable || '') + '</td>';
                    html += '<td>' + details + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                document.getElementById('historiqueContenu').innerHTML = html;
            } else {
                document.getElementById('historiqueContenu').innerHTML = '<p>Aucun historique pour cette absence.</p>';
            }
        })
        .catch(err => {
            document.getElementById('historiqueContenu').innerHTML = '<p>Erreur de chargement.</p>';
        });
}

// Fermer modale
function fermerModale(id) {
    document.getElementById(id).style.display = 'none';
}

// Fermer modale en cliquant dehors
window.onclick = function(e) {
    if (e.target.classList.contains('modale')) {
        e.target.style.display = 'none';
    }
}
</script>

</body>
</html>
