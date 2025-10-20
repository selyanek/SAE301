<?php
session_start();
require_once '../Models/delai_check.php';
require_once '../Models/Database.php';

// Vérifie et verrouiller les absences expirées
verrouillerAbsences();

// Récupérer l'ID de l'étudiant connecté
$id_etudiant = $_SESSION['login'] ?? null;

// Récupérer les absences
$absences = [];
if ($id_etudiant) {
    $db = new Database();
    $pdo = $db->getConnection();
    
    $sql = "SELECT a.idAbsence, a.date_debut, a.date_fin, a.motif, a.justifie, a.verrouille,
                   c.type as type_cours, r.nom as nom_ressource
            FROM Absence a
            JOIN Cours c ON a.idCours = c.idCours
            JOIN Ressource r ON c.idRessource = r.idRessource
            WHERE a.idEtudiant = ?
            ORDER BY a.date_debut DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_etudiant]);
    $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer mes absences</title>
    <link href="/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/CSS/cssGererAbsEtu.css" rel="stylesheet">
    
</head>
<div class="uphf">
    <img src="../img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../img/logoedutrack.png" alt="Logo EduTrack">
</div>
<header class="text">
    <h1>Gérer mes absences</h1>
    <p>Cette page vous donne accès aux informations et réponses liées à vos absences.</p>
    
    <a href="../Views/depotJustif.php"><button type="button" class="btn">Soumettre un nouveau justificatif</button></a>
</header>
<div class="sidebar">
    <ul>
        <li><a href="../Controllers/accueil_etudiant.php">Accueil</a></li>
        <li><a href="../Views/gererAbsEtu.php">Gérer des absences</a></li>
        <li><a href="#">Historique des absences</a></li>
        <li><a href="../Views/aide.php">Aides</a></li>
    </ul>
</div>

<table class="liste-absences"> 
    <tr>
        <th>ID</th>
        <th>Cours</th>
        <th>Date de début</th>
        <th>Date de fin</th>
        <th>Statut</th>
        <th>Temps restant</th>
        <th>Actions</th>
    </tr>
    
    <?php
    if (count($absences) == 0) {
        echo "<tr><td colspan='7' style='text-align: center;'>Aucune absence enregistrée</td></tr>";
    } else {
        foreach ($absences as $absence) {
            $verification = verifieDelai($absence['idabsence']);
            
            echo "<tr>";
            echo "<td>" . $absence['idabsence'] . "</td>";
            echo "<td>" . htmlspecialchars($absence['nom_ressource']) . " (" . htmlspecialchars($absence['type_cours']) . ")</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($absence['date_debut'])) . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($absence['date_fin'])) . "</td>";
            
            // Statut
            if ($absence['justifie']) {
                echo "<td style='color: green; font-weight: bold;'>✓ Justifiée</td>";
            } elseif ($absence['verrouille'] || !$verification['valide']) {
                echo "<td style='color: red; font-weight: bold;'>✗ Verrouillée</td>";
            } else {
                echo "<td style='color: orange; font-weight: bold;'>⏳ En attente</td>";
            }
            
            // Temps restant
            if ($verification['valide'] && !$absence['justifie']) {
                echo "<td style='color: orange;'>" . $verification['heures'] . "h " . $verification['minutes'] . "min</td>";
            } elseif ($absence['justifie']) {
                echo "<td style='color: green;'>-</td>";
            } else {
                echo "<td style='color: red;'>Expiré</td>";
            }
            
            // Actions
            echo "<td>";
            if ($verification['valide'] && !$absence['justifie']) {
                echo "<a href='../Views/depotJustif.php?id_absence=" . $absence['idabsence'] . "'>";
                echo "<button type='button' class='btn'>Justifier</button>";
                echo "</a>";
            } elseif ($absence['justifie']) {
                echo "<span style='color: green;'>Déjà justifiée</span>";
            } else {
                echo "<button type='button' class='btn' disabled style='background: #ccc; cursor: not-allowed;'>Bloqué</button>";
            }
            echo "</td>";
            
            echo "</tr>";
        }
    }
    ?>
</table>
<br>
<div class="text">
    <a href="../Controllers/accueil_etudiant.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>
</body>
<footer class="footer">
    <nav class="footer-nav">
        <a href="../Controllers/accueil_etudiant.php">Accueil</a>
        <span>|</span>
        <a href="../Views/aide.php">Aides</a>
    </nav>
</footer>
</html>