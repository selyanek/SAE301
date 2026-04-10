<?php
session_start();
require __DIR__ . "/../../Controllers/session_timeout.php"; // Gestion du timeout de session
require __DIR__ . "/../../Controllers/Redirect.php";

use src\Controllers\Redirect;

$redirect = new Redirect('secretaire');
$redirect->redirect();

$pageTitle = 'Aide et assistance';
$additionalCSS = ['/public/asset/CSS/cssAide.css'];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>

<section class="aide-container">
    <div class="aide-header">
        <h1>Aide et assistance</h1>
        <p class="aide-subtitle">Tout ce que vous devez savoir sur EduTrack</p>
    </div>

    <div class="aide-content">
        <div class="aide-card intro-card">
            <h2>Qu'est-ce qu'EduTrack ?</h2>
            <p>EduTrack est l'application officielle de l'IUT qui permet a la scolarite de centraliser et d'importer les absences. En tant que secretaire, vous envoyez les absences a traiter pour alimenter la base de donnees de suivi.</p>
        </div>

        <div class="aide-card steps-card">
            <h2>Comment ca marche ?</h2>
            <ol class="steps-list">
                <li>
                    <span class="step-number">1</span>
                    <div class="step-content">
                        <strong>Selectionnez vos fichiers CSV</strong>
                        <p>Ajoutez un ou plusieurs fichiers exportes depuis VT.</p>
                    </div>
                </li>
                <li>
                    <span class="step-number">2</span>
                    <div class="step-content">
                        <strong>Lancez l'import</strong>
                        <p>Les absences de chaque fichier sont integrees automatiquement.</p>
                    </div>
                </li>
                <li>
                    <span class="step-number">3</span>
                    <div class="step-content">
                        <strong>Consultez le resultat</strong>
                        <p>Un ecran de confirmation affiche le bilan de l'import.</p>
                    </div>
                </li>
            </ol>
        </div>

        <div class="aide-card info-card">
            <h2>Important a savoir</h2>
            <ul class="info-list">
                <li>Importez regulierement les fichiers pour garder le suivi des absences a jour.</li>
                <li>Verifiez que les fichiers sont bien au format <strong>.csv</strong> avant envoi.</li>
                <li>En cas d'erreur, corrigez le fichier source puis relancez un nouvel import.</li>
            </ul>
        </div>

        <div class="aide-card reglement-card">
            <h2>Reglement interieur</h2>
            <p>Consultez le reglement officiel de l'IUT concernant les absences et les justificatifs.</p>
            <a href="https://moodle.uphf.fr/course/view.php?id=3785" target="_blank" rel="noopener noreferrer" class="btn-reglement">
                <span>Voir le reglement sur Moodle</span>
                <span class="arrow">-></span>
            </a>
        </div>

        <div class="aide-actions">
            <a href="/src/Views/secretaire/dashboard.php" class="btn-retour">
                <span>&lt;- Retour a l'accueil</span>
            </a>
            <a href="/src/Views/secretaire/envoie_des_absences.php" class="btn-primary">
                <span>Envoyer des absences</span>
            </a>
        </div>
    </div>
</section>

<?php
require __DIR__ . '/../layout/footer.php';
?>