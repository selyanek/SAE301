<?php
session_start();
require __DIR__ . '/../Controllers/session_timeout.php'; // Gestion du timeout de session
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';

// Instancier les classes sans l'usage de "use" pour garder la compatibilité dans le template
$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);

// ID reçu depuis gestionAbsResp.php
$id = isset($_GET['id']) ? intval($_GET['id']) : -1;

// Absence correspondante depuis la DB
$absence = ($id > 0) ? $absenceModel->getById($id) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Traitement des justificatifs</title>
    <link href="/public/asset/CSS/cssTraitementDesJustificatifs.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
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
        <li><a href="accueil_responsable.php">Accueil</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="historiqueAbsResp.php">Historique des absences</a></li>
        <!-- <li><a href="#">Statistiques</a></li> -->
    </ul>
</div>

<header class="titre">
    <h1>Détails du justificatif des absences</h1>
</header>

<div class="trait"></div>

<?php if (!$absence): ?>
    <div class="error-message">Absence introuvable.</div>
    <div class="buttons">
        <a href="gestionAbsResp.php"><button type="button" class="btn">Retour</button></a>
    </div>

<?php else: ?>

    <div class="encadre1">
        <div class="encadre2">
            <h3>Date de début de l'absence</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))); ?></p>
            </div>
        </div>

        <div class="encadre2">
            <h3>Date de fin de l'absence</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_fin']))); ?></p>
            </div>
        </div>
    </div>

    <div class="encadre1">
        <div class="encadre2">
            <h3>Date de soumission</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))); ?></p>
            </div>
        </div>

        <div class="encadre2">
            <h3>Motif</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars($absence['motif'] ?? '—'); ?></p>
            </div>
        </div>
    </div>

    <div class="trait"></div>

    <form action="../Controllers/traiter_absence.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <div class="encadre1">
            <div class="encadre2">
                <h3>Document(s) justificatif(s)</h3>
                <div class="encadre3">
                    <?php
                    if (!empty($absence['urijustificatif'])) {
                        $fichiers = json_decode($absence['urijustificatif'], true);
                        if (is_array($fichiers) && count($fichiers) > 0) {
                            foreach ($fichiers as $index => $fichier) {
                                $fichierPath = "../../uploads/" . htmlspecialchars($fichier);
                                echo "<p><a href='" . $fichierPath . "' target='_blank' style='color: #0066cc; text-decoration: none;'>" . htmlspecialchars($fichier) . "</a></p>";
                            }
                        } else {
                            echo "<p>—</p>";
                        }
                    } else {
                        echo "<p>Aucun document fourni</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="encadre2">
                <h3>Cours</h3>
                    <div class="encadre3">
                        <p><?php echo htmlspecialchars($absence['cours_type'] ?? '—'); ?></p>
                        <p><?php echo htmlspecialchars($absence['ressource_nom'] ?? '—'); ?></p>
                    </div>
            </div>
        </div>

        <div class="boutons">
            <button type="submit" name="action" value="valider">Valider</button>
            <button type="submit" name="action" value="refuser">Refuser</button>
            <button type="submit" name="action" value="Demande_justif">Demander justificatif</button>
            <a href="gestionAbsResp.php"><button type="button">Retour</button></a>
        </div>
    </form>

<?php endif; ?>

<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="">Aides</a>
    </nav>
</footer>
</body>
</html>
