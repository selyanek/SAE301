/**
 * traiterAbsenceAjax.js
 * 
 * Gestionnaire Ajax pour les opérations de traitement d'absence
 * - Valider une absence
 * - Refuser une absence
 * - Demander des justificatifs
 */

document.addEventListener('DOMContentLoaded', function() {
    // Gérer les formulaires de traitement
    const mainForm = document.querySelector('form[action="../../Controllers/traiter_absence.php"]');
    
    if (mainForm) {
        mainForm.addEventListener('submit', handleMainFormSubmit);
    }

    // Gérer le formulaire de demande de justificatif si présent
    const demandForm = document.querySelector('.zone-demande-justif form');
    if (demandForm) {
        demandForm.addEventListener('submit', handleDemandFormSubmit);
    }

    // Gérer le click sur le bouton "Refuser"
    const refuseBtn = document.querySelector('button[onclick="afficherRaisonRefus()"]');
    if (refuseBtn) {
        refuseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            afficherRaisonRefus();
        });
    }
});

/**
 * Gérer la soumission du formulaire principal (Valider/Refuser/Demander justificatif)
 */
async function handleMainFormSubmit(event) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = event.submitter;
    const action = submitBtn ? submitBtn.value : '';
    const idAbsence = form.querySelector('input[name="id"]').value;

    // Créer le feedback si nécessaire
    let feedbackDiv = document.querySelector('.form-feedback');
    if (!feedbackDiv) {
        feedbackDiv = document.createElement('div');
        feedbackDiv.className = 'form-feedback';
        form.parentNode.insertBefore(feedbackDiv, form);
    }

    // Validation spéciale pour refus
    if (action === 'refuser') {
        const raisonRefusDiv = document.getElementById('zone-raison-refus');
        const raisonInput = document.getElementById('raison_refus');

        if (!raisonDiv || raisonDiv.style.display === 'none') {
            afficherRaisonRefus();
            return;
        }

        if (!raisonInput.value.trim()) {
            displayFeedback({
                success: false,
                message: 'Veuillez indiquer une raison pour le refus.'
            }, feedbackDiv);
            return;
        }
    }

    // Création du payload
    const formData = new FormData();
    formData.append('action', action);
    formData.append('id', idAbsence);

    // Ajouter raison refus si applicable
    if (action === 'refuser') {
        formData.append('raison_refus', document.getElementById('raison_refus').value);
    }

    // Afficher le spinner
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');
    feedbackDiv.innerHTML = '';
    feedbackDiv.className = 'form-feedback';

    try {
        // Créer un contrôle de timeout (30 secondes)
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);

        const response = await fetch('../../Controllers/api_traiter_absence.php', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        // Traiter la réponse JSON
        const result = await response.json();
        displayFeedback(result, feedbackDiv);

        // Redirection si succès
        if (result.success) {
            setTimeout(() => {
                window.location.href = 'gestionAbsence.php';
            }, 2000);
        }

    } catch (error) {
        let errorMessage = 'Erreur lors du traitement.';

        if (error.name === 'AbortError') {
            errorMessage = 'Délai d\'attente dépassé (30s). Vérifiez votre connexion.';
        } else if (error instanceof TypeError) {
            errorMessage = 'Erreur réseau. Vérifiez votre connexion internet.';
        } else {
            errorMessage = `Erreur: ${error.message}`;
        }

        displayFeedback({
            success: false,
            message: errorMessage
        }, feedbackDiv);

    } finally {
        submitBtn.disabled = false;
        submitBtn.classList.remove('loading');
    }
}

/**
 * Gérer la soumission du formulaire de demande de justificatif
 */
async function handleDemandFormSubmit(event) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const motifTextarea = document.getElementById('motif_demande');
    const idAbsence = form.querySelector('input[name="id"]').value;

    // Créer le feedback si nécessaire
    let feedbackDiv = document.querySelector('.zone-demande-justif .form-feedback');
    if (!feedbackDiv) {
        feedbackDiv = document.createElement('div');
        feedbackDiv.className = 'form-feedback';
        form.parentNode.insertBefore(feedbackDiv, form);
    }

    // Validation
    if (!motifTextarea.value.trim()) {
        displayFeedback({
            success: false,
            message: 'Veuillez remplir le motif de la demande.'
        }, feedbackDiv);
        return;
    }

    // Créer le payload
    const formData = new FormData();
    formData.append('action', 'envoyer_demande_justif');
    formData.append('id', idAbsence);
    formData.append('motif_demande', motifTextarea.value);

    // Afficher le spinner
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');
    feedbackDiv.innerHTML = '';
    feedbackDiv.className = 'form-feedback';

    try {
        // Créer un contrôle de timeout (30 secondes)
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);

        const response = await fetch('../../Controllers/api_traiter_absence.php', {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        // Traiter la réponse JSON
        const contentType = response.headers.get('content-type');
        let result;

        if (contentType && contentType.includes('application/json')) {
            result = await response.json();
        } else {
            throw new Error('Réponse serveur invalide');
        }

        displayFeedback(result, feedbackDiv);

        // Réinitialiser et redirection si succès
        if (result.success) {
            setTimeout(() => {
                form.reset();
                // Redirection optionnelle
                window.location.href = 'gestionAbsence.php';
            }, 1500);
        }

    } catch (error) {
        let errorMessage = 'Erreur lors de l\'envoi de la demande.';

        if (error.name === 'AbortError') {
            errorMessage = 'Délai d\'attente dépassé (30s). Vérifiez votre connexion.';
        } else if (error instanceof TypeError) {
            errorMessage = 'Erreur réseau. Vérifiez votre connexion internet.';
        } else {
            errorMessage = error.message;
        }

        displayFeedback({
            success: false,
            message: errorMessage
        }, feedbackDiv);

    } finally {
        submitBtn.disabled = false;
        submitBtn.classList.remove('loading');
    }
}

/**
 * Afficher le formulaire de raison du refus
 */
function afficherRaisonRefus() {
    const zoneRefus = document.getElementById('zone-raison-refus');
    if (zoneRefus) {
        zoneRefus.style.display = 'block';
        document.getElementById('raison_refus')?.focus();
    }
}

/**
 * Cacher le formulaire de raison du refus
 */
function cacherRaisonRefus() {
    const zoneRefus = document.getElementById('zone-raison-refus');
    if (zoneRefus) {
        zoneRefus.style.display = 'none';
    }
}

/**
 * Afficher le feedback (succès ou erreur)
 */
function displayFeedback(result, feedbackDiv) {
    if (!feedbackDiv) return;

    const className = result.success ? 'feedback-success' : 'feedback-error';
    const icon = result.success ? '✓' : '✕';

    feedbackDiv.className = `form-feedback ${className}`;
    feedbackDiv.innerHTML = `
        <div class="feedback-message">
            <span class="feedback-icon">${icon}</span>
            <span class="feedback-text">${htmlSpecialCharsEscape(result.message)}</span>
        </div>
    `;

    // Auto-masquer le feedback après 5 secondes si succès
    if (result.success) {
        setTimeout(() => {
            feedbackDiv.style.opacity = '0';
            feedbackDiv.style.transition = 'opacity 0.3s ease';
        }, 4000);
    }
}

/**
 * Échapper les caractères spéciaux HTML
 */
function htmlSpecialCharsEscape(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
