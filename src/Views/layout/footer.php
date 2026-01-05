<?php
// Déterminer la page d'aide selon le rôle
$pageLienAccueil = '/src/Views/accueil_etudiant.php';
$pageLienAide = '/src/Views/etudiant/aide.php';

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'responsable_pedagogique':
            $pageLienAccueil = '/src/Views/accueil_responsable.php';
            $pageLienAide = '/src/Views/aideResp.php';
            break;
        case 'professeur':
            $pageLienAccueil = '/src/Views/accueil_prof.php';
            $pageLienAide = '/src/Views/aide.php'; // À créer si nécessaire
            break;
        case 'etudiant':
        case 'etudiante':
            $pageLienAccueil = '/src/Views/accueil_etudiant.php';
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