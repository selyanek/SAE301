<?php
session_start();
require __DIR__ . "/../../Controllers/session_timeout.php";
require __DIR__ . "/../../Controllers/Redirect.php";

use src\Controllers\Redirect;

$redirect = new Redirect('secretaire');
$redirect->redirect();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoie des absences</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/secretaire.css" rel="stylesheet">
    <link href="/public/asset/CSS/envoie_absences.css" rel="stylesheet">
</head>
<div class="uphf">
    <img src="../../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<body>
<div class="logoEdu">
    <img src="../../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>
<div class="sidebar">
    <ul>
        <li><a href="dashboard.php">Accueil</a></li>
        <li><a href="envoie_des_absences.php">Envoie des absences</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="/src/Views/secretaire/aide.php">Aides</a></li>
    </ul>
</div>

<header class="text">
    <h1>Envoie des absences</h1>
</header>

<main class="content">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?php echo $_SESSION['message_type']; ?>">
            <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <form id="uploadForm" action="/src/Controllers/import_absences.php" method="post" enctype="multipart/form-data">
        <div class="drop-zone" id="dropZone">
            <p><strong> Glissez-déposez vos fichiers CSV ici</strong></p>
            <p>ou cliquez pour sélectionner</p>
            <input type="file" id="fileInput" name="files[]" accept=".csv,.CSV" multiple style="display: none;">
        </div>
        
        <div class="file-list" id="fileList"></div>
        
        <div class="submit-btn">
            <button type="submit" class="btn" id="submitBtn" style="display: none;">Valider et importer</button>
        </div>
    </form>
</main>

<footer class="footer">
    <nav class="footer-nav">
        <a href="dashboard.php">Accueil</a>
        <span>|</span>
        <a href="/src/Views/secretaire/aide.php">Aide</a>
    </nav>
</footer>

<!-- Overlay de chargement -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner"></div>
        <h2>Import en cours</h2>
        <p>Veuillez patienter, traitement des fichiers CSV...</p>
        <p style="margin-top: 10px; font-size: 14px; color: #999;">Cela peut prendre quelques secondes...</p>
    </div>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const submitBtn = document.getElementById('submitBtn');
    const uploadForm = document.getElementById('uploadForm');
    const overlay = document.getElementById('loadingOverlay');
    
    let selectedFiles = [];
    let isSubmitting = false;
    
    // Garder l'overlay visible pendant la navigation
    window.addEventListener('beforeunload', function() {
        if (isSubmitting && overlay) {
            overlay.style.display = 'flex';
            overlay.style.visibility = 'visible';
            overlay.style.opacity = '1';
        }
    });

    // Clic sur la zone de drop
    dropZone.addEventListener('click', () => fileInput.click());

    // Gestion du drag & drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });

    // Sélection de fichiers via input
    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        handleFiles(files);
    });

    function handleFiles(files) {
        files.forEach(file => {
            if (file.name.toLowerCase().endsWith('.csv')) {
                if (!selectedFiles.find(f => f.name === file.name)) {
                    selectedFiles.push(file);
                }
            }
        });
        updateFileList();
    }

    function updateFileList() {
        fileList.innerHTML = '';
        
        if (selectedFiles.length > 0) {
            submitBtn.style.display = 'inline-block';
            
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <span>${file.name} (${(file.size / 1024).toFixed(2)} Ko)</span>
                    <button type="button" onclick="removeFile(${index})">Supprimer</button>
                `;
                fileList.appendChild(fileItem);
            });
        } else {
            submitBtn.style.display = 'none';
        }
        
        // Mettre à jour l'input file avec les fichiers sélectionnés
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFileList();
    }

    // Soumission du formulaire
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Bloquer la soumission immédiate
        console.log('=== FORMULAIRE SOUMIS ===');
        
        if (selectedFiles.length === 0) {
            alert('Veuillez sélectionner au moins un fichier CSV.');
            return false;
        }
        
        console.log('Fichiers sélectionnés:', selectedFiles.length);
        
        // Marquer qu'on est en train de soumettre
        isSubmitting = true;
        
        // Afficher l'overlay de chargement IMMÉDIATEMENT
        console.log('Overlay élément:', overlay);
        
        if (overlay) {
            overlay.style.display = 'flex';
            overlay.style.visibility = 'visible';
            overlay.style.opacity = '1';
            console.log('Overlay affiché!');
        }
        
        // Désactiver le bouton pour éviter les double-clics
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Import en cours...';
            submitBtn.style.opacity = '0.6';
        }
        
        // Attendre 100ms pour que l'overlay soit visible, puis soumettre
        setTimeout(function() {
            console.log('Soumission du formulaire après délai...');
            uploadForm.submit(); // Soumettre le formulaire de manière native
        }, 100);
        
        return false; // Empêcher la soumission immédiate
    });
</script>
</body>
</html>
