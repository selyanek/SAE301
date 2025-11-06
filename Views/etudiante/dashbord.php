<?php
require "../../Models/Redirect.php";
require '../layout/header.php';
require '../layout/navigation.php';
session_start();
?>
<header class="text">
    <h1> Bonjour, <?php echo $_SESSION['nom'] ?> </h1>
    <a href="../gererAbsEtu.php">
        <button type="submit" class="btn">Consulter vos justificatifs</button>
    </a>
</header>
</body>
<?php
require '../layout/footer.php';
?>
</html>

