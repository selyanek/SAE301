<?php
session_start();
require_once __DIR__ . '/../../Controllers/session_timeout.php';

// Charger le modèle
require_once __DIR__ . '/../../Models/Statistiques.php';
require_once __DIR__ . '/../../Database/Database.php';

use src\Models\Statistiques;
use src\Database\Database;

// Récupérer les filtres
$filtres = [
    'annee_but' => $_GET['annee_but'] ?? '',
    'type_cours' => $_GET['type_cours'] ?? '',
    'matiere' => $_GET['matiere'] ?? '',
    'groupe' => $_GET['groupe'] ?? '',
    'date_debut' => $_GET['date_debut'] ?? '',
    'date_fin' => $_GET['date_fin'] ?? ''
];

// Charger les statistiques depuis la base de données
$db = new Database();
$pdo = $db->getConnection();
$stats = new Statistiques($pdo);
$stats->chargerAbsences($filtres);
$globales = $stats->calculerStatistiquesGlobales();
$matieres = $stats->getListeMatieres();
$groupes = $stats->getListeGroupes();

// Données pour les graphiques
$donneesGraphiques = $stats->getDonneesAPI();

$pageTitle = 'Statistiques des absences';
$additionalCSS = ['/public/asset/CSS/cssStatistiques.css'];
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navigation.php';
?>

<div class="stats-container">
    <?php // En-tête ?>
    <div class="stats-header">
        <h1>Statistiques des Absences</h1>
        <p class="stats-subtitle">Analyse des absences</p>
    </div>

    <?php // Filtres ?>
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

    <?php // Statistiques globales - Grille 4 colonnes ?>
    <div class="stats-global">
        <div class="stat-card stat-danger">
            <div class="stat-content">
                <h3><?= $globales['total'] ?></h3>
                <p>Absences totales</p>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-content">
                <h3><?= $globales['justifiees'] ?></h3>
                <p>Absences justifiées</p>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-content">
                <h3><?= $globales['non_justifiees'] ?></h3>
                <p>Non justifiées</p>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-content">
                <h3><?= $globales['evaluations'] ?></h3>
                <p>Lors d'évaluations</p>
            </div>
        </div>
    </div>

    <?php // Graphiques - Grille 2x2 ?>
    <div class="stats-charts">
        <div class="chart-row">
            <div class="chart-container">
                <h2>Répartition par type de cours</h2>
                <canvas id="chartTypes"></canvas>
                <button class="btn-export" onclick="exporterGraphique('chartTypes', 'Répartition par type de cours')">
                    Exporter en PDF
                </button>
            </div>

            <div class="chart-container">
                <h2>Répartition par heure</h2>
                <canvas id="chartHeures"></canvas>
                <button class="btn-export" onclick="exporterGraphique('chartHeures', 'Répartition par heure')">
                    Exporter en PDF
                </button>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-container">
                <h2>Top 10 des matières</h2>
                <canvas id="chartMatieres"></canvas>
                <button class="btn-export" onclick="exporterGraphique('chartMatieres', 'Top 10 des matières')">
                    Exporter en PDF
                </button>
            </div>

            <div class="chart-container">
                <h2>Tendances mensuelles</h2>
                <canvas id="chartTendances"></canvas>
                <button class="btn-export" onclick="exporterGraphique('chartTendances', 'Tendances mensuelles')">
                    Exporter en PDF
                </button>
            </div>
        </div>
    </div>
</div>

<?php // Chart.js et jsPDF ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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

// Fonction pour exporter un graphique en PDF
function exporterGraphique(canvasId, titre) {
    const { jsPDF } = window.jspdf;
    const canvas = document.getElementById(canvasId);
    // Créer un PDF
    const pdf = new jsPDF({
        orientation: 'landscape',
        unit: 'mm',
        format: 'a4'
    });
    // Obtenir l'image du canvas
    const imgData = canvas.toDataURL('image/png');
    // Ajouter le titre
    pdf.setFontSize(16);
    pdf.text(titre, 10, 15);
    // Dimensions de l'image
    const imgWidth = 250;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    const x = 10;
    const y = 25;
    // Ajouter l'image
    pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);
    // Télécharger le PDF
    const nomFichier = titre.replace(/\s+/g, '_').toLowerCase() + '_' + Date.now() + '.pdf';
    pdf.save(nomFichier);
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>