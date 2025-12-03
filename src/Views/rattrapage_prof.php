<?php
session_start();
require __DIR__ . "/../Controllers/session_timeout.php";
require __DIR__ . "/../Controllers/Redirect.php";

use src\Controllers\Redirect;

$redirect = new Redirect('professeur');
$redirect->redirect();

// Connexion à la base de données
require __DIR__ . '/../Database/Database.php';

$db = new \src\Database\Database();
$pdo = $db->getConnection();

// Récupérer l'ID du professeur connecté
$idProfesseur = $_SESSION['idCompte'] ?? null;

if (!$idProfesseur) {
    header('Location: /public/index.php');
    exit();
}

// Récupérer les absences lors d'évaluations pour les cours de ce professeur
$sql = "SELECT 
            a.idabsence,
            a.date_debut AS absence_debut,
            a.date_fin AS absence_fin,
            a.motif,
            a.justifie,
            c.idcours,
            c.type AS cours_type,
            c.date_debut AS cours_date_debut,
            c.date_fin AS cours_date_fin,
            r.nom AS ressource_nom,
            comp.nom AS etudiant_nom,
            comp.prenom AS etudiant_prenom,
            e.identifiantetu,
            e.formation,
            rt.idrattrapage,
            rt.date_rattrapage,
            rt.salle,
            rt.remarque,
            rt.statut AS rattrapage_statut
        FROM Cours c
        JOIN Absence a ON c.idCours = a.idCours
        JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
        JOIN Compte comp ON e.idEtudiant = comp.idCompte
        JOIN Ressource r ON c.idRessource = r.idRessource
        LEFT JOIN Rattrapage rt ON a.idAbsence = rt.idAbsence
        WHERE c.idProfesseur = :idProfesseur 
        AND c.evaluation = TRUE
        AND a.justifie = TRUE
        ORDER BY c.date_debut DESC, comp.nom, comp.prenom";

$stmt = $pdo->prepare($sql);
$stmt->execute([':idProfesseur' => $idProfesseur]);
$absencesEvaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Rattrapages</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/rattrapage.css" rel="stylesheet">
</head>
<body>
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="sidebar">
    <ul>
        <li><a href="accueil_prof.php">Accueil</a></li>
        <li><a href="rattrapage_prof.php">Rattrapages</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="aide.php">Aides</a></li>
    </ul>
</div>

<header class="text">
    <h1>Gestion des Rattrapages</h1>
    <p class="subtitle">Planifiez les rattrapages pour vos évaluations</p>
</header>

<div class="content-wrapper">
    <div class="info-section">
        <p class="info-text">
            Cette page recense tous les étudiants absents lors de vos évaluations. 
            Vous pouvez planifier des rattrapages directement depuis cette interface.
        </p>
        <div class="stats-box">
            <span class="stat-item">
                <strong>Total absences à vos évaluations :</strong> <?php echo count($absencesEvaluations); ?>
            </span>
            <span class="stat-item">
                <strong>Rattrapages planifiés :</strong> 
                <?php 
                $planifies = array_filter($absencesEvaluations, function($abs) {
                    return !empty($abs['idrattrapage']) && $abs['rattrapage_statut'] === 'planifie';
                });
                echo count($planifies); 
                ?>
            </span>
            <span class="stat-item">
                <strong>À planifier :</strong> 
                <?php 
                $aPlanifier = array_filter($absencesEvaluations, function($abs) {
                    return empty($abs['idrattrapage']) || $abs['rattrapage_statut'] === 'a_planifier';
                });
                echo count($aPlanifier); 
                ?>
            </span>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="message success-message">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="message error-message">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <table class="rattrapage-table">
        <thead>
            <tr>
                <th>Date Évaluation</th>
                <th>Ressource</th>
                <th>Type</th>
                <th>Étudiant</th>
                <th>Formation</th>
                <th>Motif Absence</th>
                <th>Justification</th>
                <th>Date Rattrapage</th>
                <th>Salle</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($absencesEvaluations)): ?>
                <tr>
                    <td colspan="11" class="no-data">Aucune absence lors de vos évaluations</td>
                </tr>
            <?php else: ?>
                <?php foreach ($absencesEvaluations as $absence): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($absence['cours_date_debut'])); ?></td>
                        <td><?php echo htmlspecialchars($absence['ressource_nom']); ?></td>
                        <td><?php echo htmlspecialchars($absence['cours_type']); ?></td>
                        <td><strong><?php echo htmlspecialchars($absence['etudiant_prenom'] . ' ' . $absence['etudiant_nom']); ?></strong></td>
                        <td><?php echo htmlspecialchars($absence['formation']); ?></td>
                        <td><?php echo htmlspecialchars($absence['motif'] ?? '—'); ?></td>
                        <td>
                            <?php 
                            if ($absence['justifie'] === null || $absence['justifie'] === 'NULL') {
                                echo '<span class="badge badge-warning">En attente</span>';
                            } elseif ($absence['justifie'] === 't' || $absence['justifie'] === true || $absence['justifie'] === '1') {
                                echo '<span class="badge badge-success">Justifiée</span>';
                            } else {
                                echo '<span class="badge badge-danger">Non justifiée</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            echo !empty($absence['date_rattrapage']) 
                                ? '<strong>' . date('d/m/Y H:i', strtotime($absence['date_rattrapage'])) . '</strong>'
                                : '—'; 
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($absence['salle'] ?? '—'); ?></td>
                        <td>
                            <?php 
                            if (empty($absence['idrattrapage'])) {
                                echo '<span class="badge badge-secondary">À planifier</span>';
                            } else {
                                switch($absence['rattrapage_statut']) {
                                    case 'a_planifier':
                                        echo '<span class="badge badge-warning">À planifier</span>';
                                        break;
                                    case 'planifie':
                                        echo '<span class="badge badge-info">Planifié</span>';
                                        break;
                                    case 'effectue':
                                        echo '<span class="badge badge-success">Effectué</span>';
                                        break;
                                    case 'annule':
                                        echo '<span class="badge badge-danger">Annulé</span>';
                                        break;
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <button class="btn-action btn-edit" 
                                    onclick="openModal(<?php echo $absence['idabsence']; ?>, 
                                                       <?php echo $absence['idrattrapage'] ? $absence['idrattrapage'] : 'null'; ?>,
                                                       '<?php echo $absence['date_rattrapage'] ?? ''; ?>',
                                                       '<?php echo htmlspecialchars($absence['salle'] ?? '', ENT_QUOTES); ?>',
                                                       '<?php echo htmlspecialchars($absence['remarque'] ?? '', ENT_QUOTES); ?>',
                                                       '<?php echo $absence['rattrapage_statut'] ?? 'a_planifier'; ?>',
                                                       '<?php echo htmlspecialchars($absence['etudiant_prenom'] . ' ' . $absence['etudiant_nom'], ENT_QUOTES); ?>',
                                                       '<?php echo htmlspecialchars($absence['ressource_nom'], ENT_QUOTES); ?>')">
                                <?php echo empty($absence['idrattrapage']) ? 'Planifier' : 'Modifier'; ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal pour planifier/modifier un rattrapage -->
<div id="rattrapageModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Planifier un rattrapage</h2>
        <p class="modal-info" id="modalInfo"></p>
        <form action="/src/Controllers/traiter_rattrapage_prof.php" method="POST">
            <input type="hidden" name="idAbsence" id="idAbsence">
            <input type="hidden" name="idRattrapage" id="idRattrapage">
            
            <div class="form-group">
                <label for="dateRattrapage">Date et heure du rattrapage * :</label>
                <input type="datetime-local" name="dateRattrapage" id="dateRattrapage" required>
            </div>
            
            <div class="form-group">
                <label for="salle">Salle :</label>
                <input type="text" name="salle" id="salle" placeholder="Ex: A101, B203, Amphi 1...">
            </div>
            
            <div class="form-group">
                <label for="statut">Statut :</label>
                <select name="statut" id="statut" required>
                    <option value="a_planifier">À planifier</option>
                    <option value="planifie">Planifié</option>
                    <option value="effectue">Effectué</option>
                    <option value="annule">Annulé</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="remarque">Remarque / Instructions :</label>
                <textarea name="remarque" id="remarque" rows="3" placeholder="Informations complémentaires pour l'étudiant..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
            </div>
        </form>
    </div>
</div>

<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_prof.php">Accueil</a>
        <span>|</span>
        <a href="rattrapage_prof.php">Rattrapages</a>
        <span>|</span>
        <a href="aide.php">Aides</a>
    </nav>
</footer>

<script>
function openModal(idAbsence, idRattrapage, dateRattrapage, salle, remarque, statut, etudiant, ressource) {
    document.getElementById('rattrapageModal').style.display = 'block';
    document.getElementById('idAbsence').value = idAbsence;
    document.getElementById('idRattrapage').value = idRattrapage || '';
    
    // Afficher les informations
    document.getElementById('modalInfo').textContent = `Étudiant: ${etudiant} - Ressource: ${ressource}`;
    
    if (idRattrapage) {
        document.getElementById('modalTitle').textContent = 'Modifier le rattrapage';
        if (dateRattrapage) {
            // Convertir la date au format datetime-local
            const date = new Date(dateRattrapage);
            const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
            document.getElementById('dateRattrapage').value = localDate.toISOString().slice(0, 16);
        }
        document.getElementById('salle').value = salle || '';
        document.getElementById('remarque').value = remarque || '';
        document.getElementById('statut').value = statut || 'a_planifier';
    } else {
        document.getElementById('modalTitle').textContent = 'Planifier un rattrapage';
        document.getElementById('dateRattrapage').value = '';
        document.getElementById('salle').value = '';
        document.getElementById('remarque').value = '';
        document.getElementById('statut').value = 'planifie';
    }
}

function closeModal() {
    document.getElementById('rattrapageModal').style.display = 'none';
}

// Fermer la modal si on clique en dehors
window.onclick = function(event) {
    const modal = document.getElementById('rattrapageModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Fermer avec la touche Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>

</body>
</html>
