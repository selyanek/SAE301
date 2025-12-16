<?php
// Activer l'affichage des erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ . '/../Models/Absence.php';

use src\Database\Database;
use src\Models\Absence;

// Vérifier que l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'etudiant' && $_SESSION['role'] !== 'etudiante')) {
    header('Location: /public/index.php');
    exit();
}

// Récupérer les absences en attente de l'étudiant
try {
    $db = new Database();
    $absenceModel = new Absence($db->getConnection());
    $identifiantEtu = $_SESSION['identifiantEtu'] ?? $_SESSION['login']; // Utiliser identifiantEtu ou login en fallback
    
    $absences = $absenceModel->getByStudentIdentifiant($identifiantEtu);
    
    // Filtrer uniquement les absences en attente (justifie IS NULL)
    $absencesEnAttente = array_filter($absences, function($absence) {
        return $absence['justifie'] === null;
    });
    
} catch (Exception $e) {
    // En cas d'erreur, initialiser un tableau vide
    $absencesEnAttente = [];
    // Afficher l'erreur en commentaire HTML
    echo "<!-- Erreur: " . htmlspecialchars($e->getMessage()) . " -->";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer mes absences</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGererAbsEtu.css" rel="stylesheet">

</head>
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<header class="text">
    <h1>Gérer mes absences </h1>
    <div style="background: red; color: white; padding: 10px; margin: 10px 0; font-weight: bold;">
        🔴 FICHIER MODIFIÉ - VERSION DU 16 DEC 2025 🔴
    </div>
    <p>Cette page vous donne accès aux informations et réponses liées à vos absences en attente de validation.</p>
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
    <a href="/src/Views/depotJustif.php"><button type="button" class="btn">Soumettre un nouveau justificatif</button></a>
</header>
<div class="sidebar">
    <ul>
        <li><a href="/src/Views/etudiant/dashbord.php">Accueil</a></li>
        <li><a href="/src/Views/gererAbsEtu.php">Gérer des absences</a></li>
        <li><a href="/src/Views/etudiant/historiqueAbsences.php">Historique des absences</a></li>
        <li><a href="/src/Views/etudiant/aide.php">Aides</a></li>
    </ul>
</div>

<?php if (empty($absencesEnAttente)): ?>
    <div class="text">
        <p>Vous n'avez aucune absence en attente de validation.</p>
    </div>
<?php else: ?>
<table class="liste-absences"> 
    <tr>
        <th>Date de début</th>
        <th>Date de fin</th>
        <th>Cours</th>
        <th>Motif</th>
        <th>Justificatif</th>
        <th>Statut</th>
    </tr>
    <?php foreach ($absencesEnAttente as $absence): ?>
    <tr>
        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))); ?></td>
        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_fin']))); ?></td>
        <td><?php echo htmlspecialchars($absence['ressource_nom'] ?? $absence['cours_type'] ?? 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($absence['motif'] ?? 'Non renseigné'); ?></td>
        <td>
            <?php if (!empty($absence['urijustificatif'])): ?>
                <a href="<?php echo htmlspecialchars($absence['urijustificatif']); ?>" target="_blank">Voir le justificatif</a>
            <?php else: ?>
                Aucun justificatif
            <?php endif; ?>
        </td>
        <td><span class="statut-attente">En attente</span></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<br>
    <div class="text">
    <a href="/src/Views/etudiant/dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>
</body>
<footer class="footer">
        <nav class="footer-nav">
        <a href="/src/Views/etudiant/dashbord.php">Accueil</a>
        <span>|</span>
        <a href="/src/Views/etudiant/aide.php">Aides</a>
    </nav>
</footer>
</html>