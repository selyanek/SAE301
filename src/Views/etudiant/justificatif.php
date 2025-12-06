<?php
// Page de gestion des justificatifs pour l'étudiant
session_start();
require '../../Controllers/session_timeout.php'; // Gestion du timeout de session

// Utilisation du contrôleur pour récupérer les données
require_once __DIR__ . '/../../Controllers/JustificatifController.php';

$controller = new \src\Controllers\JustificatifController();
$studentId = $_SESSION['login'] ?? '';
$absencesEnAttente = $controller->getAbsencesEnAttente($studentId);

$pageTitle = 'Gérer mes absences';
$additionalCSS = ['../../../public/asset/CSS/cssGererAbsEtu.css'];
require '../layout/header.php';
require '../layout/navigation.php';
?>

<header class="text">
    <h1>Gérer mes absences</h1>
    <p>Cette page vous donnes accès aux absences actuelles en attentes.</p>
    <?php
    if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
        echo '<div class="error-messages">';
        foreach ($_SESSION['errors'] as $error) {
            echo '<p class="error">' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
        unset($_SESSION['errors']);
    }
    ?>
</header>

<!-- Liste des absences en attente -->
<div class="absences-container">
<?php
    if (count($absencesEnAttente) > 0) {
        foreach ($absencesEnAttente as $absence) {
            echo "<div class='absence-card {$absence['statut_class']}'>";
            echo "  <div class='card-dates'>";
            echo "    <div><strong>Soumission</strong><br>{$absence['date_soumission']}</div>";
            echo "    <div><strong>Début</strong><br>{$absence['date_debut']}</div>";
            echo "    <div><strong>Fin</strong><br>{$absence['date_fin']}</div>";
            echo "  </div>";
            echo "  <div class='card-info'>";
            echo "    <div class='motif'><strong>Motif :</strong> " . htmlspecialchars($absence['motif']) . "</div>";
            echo "    <div class='justif'><strong>Justificatif :</strong><br>";
            
            if (count($absence['justificatifs']) > 0) {
                foreach ($absence['justificatifs'] as $justif) {
                    echo "<a href='{$justif['path']}' target='_blank'>{$justif['nom']}</a><br>";
                }
            } else {
                echo "—";
            }
            
            echo "    </div>";
            echo "  </div>";
            echo "  <div class='card-status'>";
            echo "    <span class='status-badge'>{$absence['statut_label']}</span>";
            echo "  </div>";
            echo "</div>";
        }
    } else {
        echo "<div class='no-results'>";
        echo "    <p>Vous n'avez aucune absence en attente de traitement.</p>";
        echo "    <p>Toutes vos absences ont été traitées ou vous n'avez pas encore soumis de justificatif.</p>";
        echo "</div>";
    }
?>
</div>

<br>

<div class="text">
    <a href="dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>

<?php
require '../layout/footer.php';
?>
