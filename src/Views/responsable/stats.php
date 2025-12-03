
<?php
$pageTitle = 'Statistiques';
$additionalCSS = ['../../../public/asset/CSS/cssGestionAbsResp.css'];
// require '../layout/header.php';
// require '../layout/navigation.php';

// TODO intégrer le style du site

// Exécuter le script Python pour générer les images
$projectRoot = dirname(dirname(dirname(__DIR__)));
$pythonScript = $projectRoot . '/stats.py';

// Vérifier que le fichier existe et l'exécuter
if (file_exists($pythonScript)) {
    $output = [];
    $returnCode = 0;
    exec('python3 ' . escapeshellarg($pythonScript), $output, $returnCode);
    
    if ($returnCode !== 0) {
        error_log('Erreur lors de l\'exécution de stats.py: ' . implode('\n', $output));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statistiques</title>
    <link href="/public/asset/cssDeBase.css" rel="stylesheet">
</head>
<body>
    <header>
        <img src="/public/asset/img/logouphf.png" alt="Logo UPHF" class="logo">
    </header>
    <h1> Statistiques </h1>
    
    <div id = "stats_bar">
        <img src="/public/asset/stats/absences.png" alt="Répartition par cours" id="1">
        <img src="/public/asset/stats/absences2.png" alt="Répartition par heure" id="2">
        <img src="/public/asset/stats/absences3.png" alt="Absences 14 derniers jours" id="3">
        <img src="/public/asset/stats/absences4.png" alt="Top 3 des absents" id="4">
        <img src="/public/asset/stats/absences5.png" alt="Absences ce mois-ci" id="5">
    </div>
    <style>
        #stats_bar {
            display: inline-block;
            align-items: center;

        }

        img {
            box-shadow: inset;
            width: 35%;
        }

    </style>

</body>
</html>
