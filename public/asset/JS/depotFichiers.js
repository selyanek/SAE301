let filesList = [];
const MAX_FILE_SIZE = 5 * 1024 * 1024;   // 5MB par fichier
const MAX_TOTAL_SIZE = 20 * 1024 * 1024; // 20MB au total
const allowedTypes = ["application/pdf", "image/jpeg", "image/png"];

function addFile() {
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
    filesList.splice(index, 1);
    updateFileList();
}

function updateFileList() {
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
    const messageDiv = document.getElementById('message');
    const submitBtn = document.getElementById('submitBtn');

    const formData = new FormData();

    // Ajouter les fichiers uniquement s'il y en a
    if (filesList.length > 0) {
        filesList.forEach(file => {
            formData.append('files[]', file);
        });
    }

    formData.append('date_start', document.getElementById('date_start').value);
    formData.append('date_end', document.getElementById('date_end').value);
    formData.append('motif', document.getElementById('motif').value);

    // Ajouter l'ID d'absence et le flag ressoumission si présents
    const idAbsenceInput = document.getElementById('id_absence');
    const ressoumissionInput = document.getElementById('ressoumission');
    if (idAbsenceInput) {
        formData.append('id_absence', idAbsenceInput.value);
    }
    if (ressoumissionInput) {
        formData.append('ressoumission', ressoumissionInput.value);
    }

    messageDiv.innerHTML = '<p>Envoi en cours...</p>';
    submitBtn.disabled = true;

    try {
        const response = await fetch('../../Controllers/upload.php', {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Réponse serveur:', text);
            messageDiv.innerHTML = `<div class="message error">Erreur serveur: ${text}</div>`;
            submitBtn.disabled = false;
            return;
        }

        if (result.success) {
            messageDiv.innerHTML = `<div class="message success">${result.message}</div>`;

            // Rediriger vers l'historique des absences
            setTimeout(() => {
                window.location.href = '/src/Views/etudiant/historiqueAbsences.php';
            }, 1500);
        } else {
            messageDiv.innerHTML = `<div class="message error">${result.message}</div>`;
        }

    } catch (error) {
        messageDiv.innerHTML = `<div class="message error">Erreur: ${error.message}</div>`;
    }

    submitBtn.disabled = false;
}

function resetForm() {
    filesList = [];
    updateFileList();
    document.getElementById('absenceForm').reset();
    document.getElementById('message').innerHTML = '';
}

document.addEventListener('DOMContentLoaded', function () {
    updateFileList();
});
