<?php
// Ce fichier est la vue pour la gestion des rattrapages.
// La logique a été déplacée dans RattrapageController.php et Rattrapage.php.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Controllers/session_timeout.php';
require_once __DIR__ . '/../../Controllers/Redirect.php';

$redirect = new \src\Controllers\Redirect('professeur');
$redirect->redirect();

$absencesEvaluations = $absencesEvaluations ?? [];
$pageTitle = 'Gestion des Rattrapages';
$additionalCSS = ['/public/asset/CSS/rattrapage.css'];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>

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
                $planifies = array_filter($absencesEvaluations, function ($abs) {
                    return !empty($abs['idrattrapage']) && $abs['rattrapage_statut'] === 'planifie';
                });
                echo count($planifies);
                ?>
            </span>
            <span class="stat-item">
                <strong>À planifier :</strong>
                <?php
                $aPlanifier = array_filter($absencesEvaluations, function ($abs) {
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
    <form method="GET" action="" class="filter-form" id="absenceFilterForm" data-endpoint="/src/Controllers/api_rattrapage_prof.php">
        <label for="date">Filtrer par date d'évaluation :</label>
        <input type="date" id="date" name="filtre_date" value="<?php echo htmlspecialchars($_GET['filtre_date'] ?? ''); ?>">
        <button type="submit" id="appliquer-filtre" class="btn">Appliquer</button>
        <a href="?" class="btn" role="button" id="resetFiltersButton">Réinitialiser</a>
    </form>

    <div id="tableLoader" class="ajax-loader" hidden>Chargement des rattrapages...</div>
    <div id="tableFeedback" class="ajax-feedback" hidden></div>

    <table class="rattrapage-table" data-pagination="true" data-page-size="10">
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
        <tbody id="tableAbsencesBody">
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
                                switch ($absence['rattrapage_statut']) {
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
                <input type="text" name="salle" id="salle" placeholder="Ex: 113, Amphi...">
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

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script src="/public/asset/JS/filterAjax.js"></script>

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
    
    // Gérer l'obligation de la date selon le statut
    updateDateRequirement();
}

function updateDateRequirement() {
    const statutSelect = document.getElementById('statut');
    const dateInput = document.getElementById('dateRattrapage');
    const dateLabel = dateInput.previousElementSibling;
    
    if (statutSelect.value === 'annule') {
        // Si annulé, la date n'est pas obligatoire
        dateInput.removeAttribute('required');
        dateLabel.textContent = 'Date et heure du rattrapage :';
    } else {
        // Sinon, la date est obligatoire
        dateInput.setAttribute('required', 'required');
        dateLabel.textContent = 'Date et heure du rattrapage * :';
    }
}

// Écouter les changements de statut
document.addEventListener('DOMContentLoaded', function() {
    const statutSelect = document.getElementById('statut');
    if (statutSelect) {
        statutSelect.addEventListener('change', updateDateRequirement);
    }
});

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