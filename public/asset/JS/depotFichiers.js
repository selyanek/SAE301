let filesList = [];
const MAX_FILE_SIZE = 5 * 1024 * 1024;   // 5MB par fichier
const MAX_TOTAL_SIZE = 20 * 1024 * 1024; // 20MB au total
const allowedTypes = ["application/pdf", "image/jpeg", "image/png"];

function addFile() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];

    if (!file) {
        alert('Veuillez sélectionner un fichier');
        return;
    }

    // Vérification type
    if (!allowedTypes.includes(file.type)) {
        alert("Format non accepté (.pdf, .jpg, .png)");
        fileInput.value = '';
        return;
    }

    // Vérification taille individuelle
    if (file.size > MAX_FILE_SIZE) {
        alert("Fichier trop volumineux (max 5MB).");
        fileInput.value = '';
        return;
    }

    // Vérification taille totale
    const totalSize = filesList.reduce((sum, f) => sum + f.size, 0) + file.size;
    if (totalSize > MAX_TOTAL_SIZE) {
        alert("Taille totale dépassée (max 20MB).");
        fileInput.value = '';
        return;
    }

    // Vérification doublon (nom + taille)
    const isDuplicate = filesList.some(f =>
        f.name === file.name && f.size === file.size
    );
    if (isDuplicate) {
        alert('Ce fichier est déjà dans la liste');
        fileInput.value = '';
        return;
    }

    // Ajouter
    filesList.push(file);

    // Reset input
    fileInput.value = '';

    updateFileList();
}

function removeFile(index) {
    filesList.splice(index, 1);
    updateFileList();
}

function updateFileList() {
    const container = document.getElementById('fileListContainer');
    const submitBtn = document.getElementById('submitBtn');
    const fileCount = document.getElementById('fileCount');

    if (filesList.length === 0) {
        container.innerHTML = '<div class="empty-state">Aucun fichier ajouté</div>';
        submitBtn.disabled = true;
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
                <div class="file-name">${file.name}</div>
                <div class="file-size">${displaySize}</div>
            </div>
            <button class="btn-remove" onclick="removeFile(${index})">✕</button>
        `;

        container.appendChild(fileItem);
    });

    submitBtn.disabled = false;
    fileCount.textContent = `Total : ${filesList.length} fichier${filesList.length > 1 ? 's' : ''}`;
}

async function uploadFiles() {
    if (filesList.length === 0) return;

    const messageDiv = document.getElementById('message');
    const submitBtn = document.getElementById('submitBtn');

    const formData = new FormData();

    // Ajouter fichiers
    filesList.forEach(file => {
        formData.append('files[]', file);
    });

    // Ajouter champs du formulaire
    formData.append('date_start', document.getElementById('date_start').value);
    formData.append('date_end', document.getElementById('date_end').value);
    formData.append('motif', document.getElementById('motif').value);

    messageDiv.innerHTML = '<p>Envoi en cours...</p>';
    submitBtn.disabled = true;

    try {
        const response = await fetch('../../src/Controllers/upload.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            messageDiv.innerHTML = `<div class="message success">${result.message}</div>`;
            filesList = [];
            updateFileList();
        } else {
            messageDiv.innerHTML = `<div class="message error">${result.message}</div>`;
        }

    } catch (error) {
        messageDiv.innerHTML = `<div class="message error">Erreur: ${error.message}</div>`;
    }

    submitBtn.disabled = false;
}


// Ajout automatique après sélection
document.getElementById('fileInput').addEventListener('change', addFile);
