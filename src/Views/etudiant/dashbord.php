<?php
// Page d'accueil étudiant
session_start();
require '../../Controllers/session_timeout.php';
require '../../Controllers/Redirect.php';
require '../layout/header.php';
require '../layout/navigation.php';
?>

<header class="text">
    <h1 style ="color: #29acc8;">
        <span>Bonjour,</span> 
        <?php echo htmlspecialchars($_SESSION['nom']); ?>
    </h1>
     <a href="justificatif.php">
        <button type="submit" class="btn">Consulter vos justificatifs</button>
    </a>
</header>

</body>
<?php
require '../layout/footer.php';
?>
</html>
