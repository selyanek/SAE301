/**
 * formAjax.js
 * 
 * Gestionnaire Ajax générique et réutilisable pour les formulaires.
 * Gère les spinners de chargement, erreurs serveur/réseau, timeouts et validation.
 * 
 * Utilisation:
 * 1. Ajouter class="form-ajax" au formulaire
 * 2. Ajouter data-endpoint="chemin/endpoint.php" avec l'action côté serveur
 * 3. Ajouter data-redirect-url="page-redirectiom.php" (optionnel) pour redirection après succès
 * 4. Ajouter data-timeout="5000" (optionnel, défaut 30000ms)
 * 5. Ajouter un div avec class="form-feedback" pour affichage messages
 */

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form.form-ajax');
    
    forms.forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });
});

/**
 * Gère la soumission d'un formulaire avec Ajax
 * @param {Event} event - Événement submit du formulaire
 */
async function handleFormSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const endpoint = form.getAttribute('data-endpoint');
    const redirectUrl = form.getAttribute('data-redirect-url');
    const timeout = parseInt(form.getAttribute('data-timeout') || '30000');
    
    if (!endpoint) {
        console.error('data-endpoint manquant sur le formulaire');
        return;
    }
    
    // Récupérer les éléments de feedback et du spinner
    const feedbackDiv = form.querySelector('.form-feedback') || 
                        createFeedbackDiv(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = createOrGetSpinner(form);
    
    // Réinitialiser le feedback
    feedbackDiv.innerHTML = '';
    feedbackDiv.className = 'form-feedback';
    
    // Désactiver le bouton et afficher le spinner
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
    }
    if (spinner) {
        spinner.style.display = 'flex';
    }
    
    try {
        // Créer le payload à partir du formulaire
        const formData = new FormData(form);
        
        // Créer un contrôleur d'annulation avec timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        
        // Envoyer la requête Ajax
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        // Traiter la réponse
        let result;
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            result = await response.json();
        } else {
            // Si ce n'est pas du JSON, créer un objet d'erreur
            const text = await response.text();
            console.error('Réponse non-JSON du serveur:', text);
            result = {
                success: false,
                message: 'Erreur serveur: réponse inattendue'
            };
        }
        
        // Afficher le résultat
        displayFeedback(result, feedbackDiv);
        
        // Redirection si succès
        if (result.success && redirectUrl) {
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1500);
        } else if (result.success) {
            // Réinitialiser le formulaire après succès
            setTimeout(() => {
                form.reset();
            }, 500);
        }
        
    } catch (error) {
        // Gérer les erreurs réseau et timeouts
        let errorMessage = 'Erreur lors de l\'envoi du formulaire.';
        
        if (error.name === 'AbortError') {
            errorMessage = `Délai d'attente dépassé (${timeout}ms). Vérifiez votre connexion.`;
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
        // Réactiver le bouton et masquer le spinner
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        }
        if (spinner) {
            spinner.style.display = 'none';
        }
    }
}

/**
 * Créer un div de feedback s'il n'existe pas
 * @param {HTMLFormElement} form - Le formulaire
 * @returns {HTMLElement} Le div de feedback
 */
function createFeedbackDiv(form) {
    const feedback = document.createElement('div');
    feedback.className = 'form-feedback';
    form.parentNode.insertBefore(feedback, form);
    return feedback;
}

/**
 * Créer ou récupérer le spinner de chargement
 * @param {HTMLFormElement} form - Le formulaire
 * @returns {HTMLElement|null} Le spinner ou null
 */
function createOrGetSpinner(form) {
    let spinner = form.querySelector('.form-spinner');
    
    if (!spinner) {
        spinner = document.createElement('div');
        spinner.className = 'form-spinner';
        spinner.innerHTML = `
            <div class="spinner-content">
                <div class="spinner"></div>
                <p>Envoi en cours...</p>
            </div>
        `;
        form.appendChild(spinner);
    }
    
    return spinner;
}

/**
 * Afficher le retour utilisateur (succès ou erreur)
 * @param {Object} result - Objet {success, message}
 * @param {HTMLElement} feedbackDiv - Le div de feedback
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
 * @param {string} text - Texte à échapper
 * @returns {string} Texte échappé
 */
function htmlSpecialCharsEscape(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
