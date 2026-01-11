<?php
// Déterminer la page d'aide selon le rôle
$pageLienAccueil = '/src/Views/accueil_etudiant.php';
$pageLienAide = '/src/Views/etudiant/aide.php';

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'responsable_pedagogique':
            $pageLienAccueil = '/src/Views/responsable/dashboard.php';
            $pageLienAide = '/src/Views/responsable/aide.php';
            break;
        case 'secretaire':
            $pageLienAccueil = '/src/Views/secretaire/dashboard.php';
            $pageLienAide = '/src/Views/secretaire/aide.php';
            break;
        case 'etudiant':
        case 'etudiante':
            $pageLienAccueil = '/src/Views/etudiant/dashboard.php';
            $pageLienAide = '/src/Views/etudiant/aide.php';
            break;
    }
}
?>
<footer class="footer">
    <nav class="footer-nav">
        <a href="<?php echo $pageLienAccueil; ?>">Accueil</a>
        <span>|</span>
        <a href="<?php echo $pageLienAide; ?>">Aides</a>
    </nav>
</footer>