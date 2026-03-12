<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Gestion des Absences'; ?></title>
    <link rel="stylesheet" href="../../../public/asset/CSS/cssDeBase.css">
    <link rel="stylesheet" href="../../../public/asset/CSS/cssDeconnexion.css">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<div class="uphf">
    <a href="https://www.uphf.fr/iut"><img src="../../../public/asset/img/logouphf.png" alt="Logo uphf"></a>
</div>
<div class="logoEdu">
    <img src="../../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<?php if (isset($_SESSION) && !empty($_SESSION['login'])): ?>
<a href="/src/Views/logout.php" class="btn-logout">
    ← Déconnexion
</a>
<?php endif; ?>