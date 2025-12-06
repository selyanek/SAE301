<?php
// Page d'accueil étudiant
session_start();
require '../../Controllers/session_timeout.php'; // Gestion du timeout de session
require '../../Controllers/Redirect.php';
require '../layout/header.php';
require '../layout/navigation.php';
?>
<header class="text">
    <h1> Bonjour, <?php echo $_SESSION['nom'] ?> </h1>
    <div style="display: flex; gap: 10px; justify-content: center; align-items: center; margin-top: 20px;">
        <a href="justificatif.php">
            <button type="button" class="btn">Consulter vos justificatifs</button>
        </a>
        <a href="depotJustificatif.php">
            <button type="button" class="btn">Soumettre un nouveau justificatif</button>
        </a>
    </div>
</header>
</body>
<?php
require '../layout/footer.php';
?>
</html>