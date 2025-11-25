<?php
// Page d'accueil responsable
session_start();
require '../../Controllers/Redirect.php';
require '../layout/header.php';
require '../layout/navigation.php';
?>
<header class="text">
    <h1> Bonjour, <?php echo $_SESSION['nom'] ?> </h1>
    <a href="gestionAbsence.php">
        <button type="submit" class="btn">Consulter les absences</button>
    </a>
</header>
</body>
<?php
require '../layout/footer.php';
?>
</html>