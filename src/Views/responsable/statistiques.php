<?php
session_start();
require_once __DIR__ . '/../../Controllers/session_timeout.php';

// Charger le modèle
require_once __DIR__ . '/../../Models/Statistiques.php';

use src\Models\Statistiques;

// Récupérer les filtres
$filtres = [
    'annee_but' => $_GET['annee_but'] ?? '',
    'type_cours' => $_GET['type_cours'] ?? '',
    'matiere' => $_GET['matiere'] ?? '',
    'groupe' => $_GET['groupe'] ?? '',
    'date_debut' => $_GET['date_debut'] ?? '',
    'date_fin' => $_GET['date_fin'] ?? ''
];

// Charger les statistiques
$stats = new Statistiques();
$stats->chargerAbsences($filtres);
$globales = $stats->calculerStatistiquesGlobales();
$matieres = $stats->getListeMatieres();
$groupes = $stats->getListeGroupes();
$rattrapages = $stats->getRattrapages();

// Données pour les graphiques
$donneesGraphiques = $stats->getDonneesAPI();

$pageTitle = 'Statistiques des absences';
$additionalCSS = ['/public/asset/CSS/cssStatistiques.css'];
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navigation.php';
?>

<div class="stats-container">
    <!-- En-tête -->
    <div class="stats-header">
        <h1>Statistiques des Absences</h1>
        <p class="stats-subtitle">Analyse des absences</p>
    </div>

    <!-- Filtres -->
    <div class="stats-filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="annee_but">Année BUT</label>
                <select name="annee_but" id="annee_but">
                    <option value="">Toutes les années</option>
                    <option value="BUT1" <?= $filtres['annee_but'] === 'BUT1' ? 'selected' : '' ?>>BUT 1</option>
                    <option value="BUT2" <?= $filtres['annee_but'] === 'BUT2' ? 'selected' : '' ?>>BUT 2</option>
                    <option value="BUT3" <?= $filtres['annee_but'] === 'BUT3' ? 'selected' : '' ?>>BUT 3</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="type_cours">Type de cours</label>
                <select name="type_cours" id="type_cours">
                    <option value="">Tous les types</option>
                    <option value="CM" <?= $filtres['type_cours'] === 'CM' ? 'selected' : '' ?>>CM</option>
                    <option value="TD" <?= $filtres['type_cours'] === 'TD' ? 'selected' : '' ?>>TD</option>
                    <option value="TP" <?= $filtres['type_cours'] === 'TP' ? 'selected' : '' ?>>TP</option>
                    <option value="DS" <?= $filtres['type_cours'] === 'DS' ? 'selected' : '' ?>>DS</option>
                    <option value="BEN" <?= $filtres['type_cours'] === 'BEN' ? 'selected' : '' ?>>BEN</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="date_debut">Date début</label>
                <input type="date" name="date_debut" id="date_debut" value="<?= htmlspecialchars($filtres['date_debut']) ?>">
            </div>

            <div class="filter-group">
                <label for="date_fin">Date fin</label>
                <input type="date" name="date_fin" id="date_fin" value="<?= htmlspecialchars($filtres['date_fin']) ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="statistiques.php" class="btn btn-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- Statistiques globales - Grille 4 colonnes -->
    <div class="stats-global">
        <div class="stat-card stat-danger">
            <span class="stat-icon">📉</span>
            <div class="stat-content">
                <h3><?= $globales['total'] ?></h3>
                <p>Absences totales</p>
            </div>
        </div>

        <div class="stat-card stat-success">
            <span class="stat-icon">✅</span>
            <div class="stat-content">
                <h3><?= $globales['justifiees'] ?></h3>
                <p>Absences justifiées</p>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <span class="stat-icon">⚠️</span>
            <div class="stat-content">
                <h3><?= $globales['non_justifiees'] ?></h3>
                <p>Non justifiées</p>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <span class="stat-icon">📝</span>
            <div class="stat-content">
                <h3><?= $globales['evaluations'] ?></h3>
                <p>Lors d'évaluations</p>
            </div>
        </div>
    </div>

    <!-- Graphiques - Grille 2x2 -->
    <div class="stats-charts">
        <div class="chart-row">
            <div class="chart-container">
                <h2>Répartition par type de cours</h2>
                <canvas id="chartTypes"></canvas>
            </div>

            <div class="chart-container">
                <h2>Répartition par heure</h2>
                <canvas id="chartHeures"></canvas>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-container">
                <h2>Top 10 des matières</h2>
                <canvas id="chartMatieres"></canvas>
            </div>

            <div class="chart-container">
                <h2>Tendances mensuelles</h2>
                <canvas id="chartTendances"></canvas>
            </div>
        </div>
    </div>

    <!-- Rattrapages à planifier -->
    <?php if (!empty($rattrapages)): ?>
    <div class="stats-rattrapages">
        <h2>Rattrapages à planifier (absences justifiées lors d'évaluations)</h2>
        <table class="table-rattrapages">
            <thead>
                <tr>
                    <th>Ressource</th>
                    <th>Type</th>
                    <th>Nb étudiants</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rattrapages as $rattrapage): ?>
                <tr>
                    <td><?= htmlspecialchars($rattrapage['ressource']) ?></td>
                    <td><?= htmlspecialchars($rattrapage['type_cours']) ?></td>
                    <td><?= $rattrapage['nb_etudiants'] ?></td>
                    <td><span class="badge badge-warning"><?= $rattrapage['statut'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Données PHP → JavaScript
const donneesTypes = <?= json_encode($donneesGraphiques['types']) ?>;
const donneesHeures = <?= json_encode($donneesGraphiques['heures']) ?>;
const donneesMatieres = <?= json_encode($donneesGraphiques['matieres']) ?>;
const donneesTendances = <?= json_encode($donneesGraphiques['tendances']) ?>;

// Couleurs
const couleurs = ['#29acc8', '#0d4f94', '#4CAF50', '#FFC107', '#f44336', '#9C27B0'];

// Graphique par type de cours (Camembert)
new Chart(document.getElementById('chartTypes'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(donneesTypes),
        datasets: [{
            data: Object.values(donneesTypes),
            backgroundColor: couleurs
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Graphique par heure (Barres)
new Chart(document.getElementById('chartHeures'), {
    type: 'bar',
    data: {
        labels: Object.keys(donneesHeures),
        datasets: [{
            label: 'Nombre d\'absences',
            data: Object.values(donneesHeures),
            backgroundColor: '#29acc8'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Graphique par matière (Barres horizontales)
new Chart(document.getElementById('chartMatieres'), {
    type: 'bar',
    data: {
        labels: Object.keys(donneesMatieres),
        datasets: [{
            label: 'Nombre d\'absences',
            data: Object.values(donneesMatieres),
            backgroundColor: '#0d4f94'
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        scales: {
            x: { beginAtZero: true }
        }
    }
});

// Graphique tendances (Courbe)
new Chart(document.getElementById('chartTendances'), {
    type: 'line',
    data: {
        labels: Object.keys(donneesTendances),
        datasets: [{
            label: 'Absences par mois',
            data: Object.values(donneesTendances),
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>