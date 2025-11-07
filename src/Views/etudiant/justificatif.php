<?php
session_start();
$pageTitle = 'Gérer mes absences';
$additionalCSS = ['../../../public/asset/CSS/cssGererAbsEtu.css'];
require '../layout/header.php';
require '../layout/navigation.php';
?>

<header class="text">
    <h1>Gérer mes absences</h1>
    <p>Cette page vous donne accès aux informations et réponses liées à vos absences justifiées.</p>
    <?php
    if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
        echo '<div class="error-messages">';
        foreach ($_SESSION['errors'] as $error) {
            echo '<p class="error">' . htmlspecialchars($error) . '</p>';
        }
        echo '</div>';
        unset($_SESSION['errors']);
    }
    ?>
    <a href="depotJustificatif.php"><button type="button" class="btn">Soumettre un nouveau justificatif</button></a>    
</header>

<table class="liste-absences"> 
    <tr>
        <th>Date de début</th>
        <th>Date de fin</th>
        <th>Motif</th>
        <th>Justificatif</th>
        <th>Actions</th>
    </tr>
    <tr>
        <td>2024-01-15 09:00</td>
        <td>2024-01-15 12:00</td>
        <td>Rendez-vous médical</td>
        <td><a href="justificatif1.pdf" target="_blank">Voir le justificatif</a></td>
        <td>
            <button type="button">Modifier</button>
            <button type="button">Supprimer</button>
        </td>
    </tr>
    <tr>
        <td>2024-02-10 14:00</td>
        <td>2024-02-10 16:00</td>
        <td>Problème familial</td>
        <td><a href="justificatif2.jpg" target="_blank">Voir le justificatif</a></td>
        <td>
            <button type="button">Modifier</button>
            <button type="button">Supprimer</button>
        </td>
    </tr>
</table>

<br>

<div class="text">
    <a href="dashbord.php"><button type="button" class="btn">Retour à l'accueil</button></a>
</div>

<?php
require '../layout/footer.php';
?>
