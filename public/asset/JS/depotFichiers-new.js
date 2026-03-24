// Gère le dépôt de justificatifs: sélection, validation locale, upload et feedback utilisateur.
// Version améliorée avec gestion d'erreurs, timeout et spinners
let filesList = [];
const MAX_FILE_SIZE = 5 * 1024 * 1024;   // 5MB par fichier
const MAX_TOTAL_SIZE = 20 * 1024 * 1024; // 20MB au total
const allowedTypes = ["application/pdf", "image/jpeg", "image/png"];
const UPLOAD_TIMEOUT = 60000; // 60 secondes

function addFile() {
    // Récupère les fichiers sélectionnés et applique les validations avant ajout à la liste.
    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;

    if (!files || files.length === 0) {
        alert('Veuillez sélectionner un fichier');
        return;
    }

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        if (!allowedTypes.includes(file.type)) {
            alert(`Format non accepté pour "${file.name}" (.pdf, .jpg, .png uniquement)`);
            continue;
        }

        if (file.size > MAX_FILE_SIZE) {
            alert(`Fichier "${file.name}" trop volumineux (max 5MB).`);
            continue;
        }

        const totalSize = filesList.reduce((sum, f) => sum + f.size, 0) + file.size;
        if (totalSize > MAX_TOTAL_SIZE) {
            alert("Taille totale dépassée (max 20MB).");
            continue;
        }

        const isDuplicate = filesList.some(f =>
            f.name === file.name && f.size === file.size
        );
        if (isDuplicate) {
            alert(`Le fichier "${file.name}" est déjà dans la liste`);
            continue;
        }

        filesList.push(file);
    }

    fileInput.value = '';

    updateFileList();
}

function removeFile(index) {
    // Supprime un fichier de la liste locale puis met à jour l'affichage.
    filesList.splice(index, 1);
    updateFileList();
}

function updateFileList() {
    // Rafraîchit l'état visuel de la liste des fichiers et du compteur.
    const container = document.getElementById('fileListContainer');
    const submitBtn = document.getElementById('submitBtn');
    const fileCount = document.getElementById('fileCount');

    if (filesList.length === 0) {
        container.innerHTML = '<div class="empty-state">Aucun fichier ajouté</div>';
        fileCount.textContent = '';
        return;
    }

    container.innerHTML = '';

    filesList.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';

        const sizeKB = (file.size / 1024).toFixed(2);
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        const displaySize = file.size > 1024 * 1024 ? `${sizeMB} MB` : `${sizeKB} KB`;

        fileItem.innerHTML = `
            <div class="file-info">
                <div class="file-name">${escapeHtml(file.name)}</div>
                <div class="file-size">${displaySize}</div>
            </div>
            <button class="btn-remove" onclick="removeFile(${index})">✕</button>
        `;

        container.appendChild(fileItem);
    });

    submitBtn.disabled = false;
    fileCount.textContent = `Total : ${filesList.length} fichier${filesList.length > 1 ? 's' : ''}`;
}

/**
 * Envoyer les fichiers avec gestion complète d'erreurs et timeouts
 */
async function uploadFiles() {
    // Construire le FormData avec validation
    const messageDiv = document.getElementById('message');
    const submitBtn = document.getElementById('submitBtn');

    // Validation des champs obligatoires
    const dateStart = document.getElementById('date_start').value;
    const dateEnd = document.getElementById('date_end').value;
    const motif = document.getElementById('motif').value;

    if (!dateStart || !dateEnd || !motif) {
        displayUploadFeedback(messageDiv, false, 'Veuillez remplir tous les champs obligatoires.');
        return;
    }

    const formData = new FormData();

    // Ajouter les fichiers uniquement s'il y en a
    if (filesList.length > 0) {
        filesList.forEach(file => {
            formData.append('files[]', file);
        });
    }

    formData.append('date_start', dateStart);
    formData.append('date_end', dateEnd);
    formData.append('motif', motif);

    // Ajouter l'ID d'absence et le flag ressoumission si présents
    const idAbsenceInput = document.getElementById('id_absence');
    const ressoumissionInput = document.getElementById('ressoumission');
    if (idAbsenceInput) {
        formData.append('id_absence', idAbsenceInput.value);
    }
    if (ressoumissionInput) {
        formData.append('ressoumission', ressoumissionInput.value);
    }

    // Interface utilisateur
    displayUploadFeedback(messageDiv, null, 'Envoi en cours...');
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');

    try {
        // Configurer le timeout avec AbortController
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), UPLOAD_TIMEOUT);

        const response = await fetch('../../Controllers/upload.php', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        // Traiter la réponse
        let result;
        const contentType = response.headers.get('content-type');

        if (contentType && contentType.includes('application/json')) {
            const text = await response.text();
            result = JSON.parse(text);
        } else {
            throw new Error('Le serveur n\'a pas retourné du JSON');
        }

        if (result.success) {
            displayUploadFeedback(messageDiv, true, result.message);

            // Redirection après succès
            setTimeout(() => {
                window.location.href = '/src/Views/etudiant/historiqueAbsences.php';
            }, 1500);
        } else {
            displayUploadFeedback(messageDiv, false, result.message || 'Erreur inconnue');
        }

    } catch (error) {
        // Gestion complète des erreurs
        let errorMsg = 'Erreur lors de l\'envoi.';

        if (error.name === 'AbortError') {
            errorMsg = `Délai d'attente dépassé (${UPLOAD_TIMEOUT / 1000}s). Vérifiez votre connexion et essayez à nouveau.`;
        } else if (error instanceof TypeError) {
            errorMsg = 'Erreur réseau. Vérifiez votre connexion internet.';
        } else if (error instanceof SyntaxError) {
            errorMsg = 'Réponse serveur invalide. Contactez l\'administrateur.';
        } else {
            errorMsg = `Erreur: ${error.message}`;
        }

        console.error('Upload error:', error);
        displayUploadFeedback(messageDiv, false, errorMsg);

    } finally {
        submitBtn.disabled = false;
        submitBtn.classList.remove('loading');
    }
}

/**
 * Afficher le retour utilisateur pour l'upload
 * @param {HTMLElement} messageDiv - Le conteneur des messages
 * @param {boolean|null} success - true/false pour succès/erreur, null pour loading
 * @param {string} message - Le message à afficher
 */
function displayUploadFeedback(messageDiv, success, message) {
    if (!messageDiv) return;

    if (success === null) {
        // Mode loading
        messageDiv.innerHTML = '<p class="loading-text">Envoi en cours...</p>';
        messageDiv.className = 'loading';
    } else if (success) {
        // Mode succès
        const icon = '<span class="feedback-icon">✓</span>';
        messageDiv.innerHTML = `<div class="message success">${icon} ${escapeHtml(message)}</div>`;
        messageDiv.className = 'success';
    } else {
        // Mode erreur
        const icon = '<span class="feedback-icon">✕</span>';
        messageDiv.innerHTML = `<div class="message error">${icon} ${escapeHtml(message)}</div>`;
        messageDiv.className = 'error';
    }
}

/**
 * Réinitialiser le formulaire complètement
 */
function resetForm() {
    // Réinitialise le formulaire, la liste locale et les messages UI.
    filesList = [];
    updateFileList();
    document.getElementById('absenceForm').reset();
    document.getElementById('message').innerHTML = '';
    document.getElementById('message').className = '';
}

/**
 * Échapper les caractères spéciaux HTML pour éviter les failles XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialise l'UI de la liste au chargement.
    updateFileList();
});
