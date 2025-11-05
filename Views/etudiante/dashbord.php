<?php
session_start();
require "../Models/Redirect.php";
include '../Views/layout/header.php';
include '../Views/layout/navigation.php';
?>
<header class="text">
    <h1> Bonjour, <?php echo $_SESSION['nom'] ?> </h1>
    <a href="../Views/gererAbsEtu.php">
        <button type="submit" class="btn">Consulter vos justificatifs</button>
    </a>
</header>
</body>
<?php
include '../Views/layout/footer.php';
?>
</html>

