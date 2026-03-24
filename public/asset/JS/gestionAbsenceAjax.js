// Gère le filtrage Ajax des absences (gestion/historique) sans rechargement de page.
document.addEventListener('DOMContentLoaded', () => {
    // Elements utilises pour piloter le filtrage et le rendu dynamique.
    const form = document.getElementById('absenceFilterForm');
    const body = document.getElementById('tableAbsencesBody');
    const loader = document.getElementById('tableLoader');
    const feedback = document.getElementById('tableFeedback');
    const reset = document.getElementById('resetFiltersButton');

    if (!form || !body || !loader || !feedback) {
        return;
    }

    const endpoint = form.dataset.endpoint;
    let debounceTimer = null;
    let activeController = null;

    // Affiche/masque l'etat de chargement et grise le formulaire pendant la requete.
    const setLoading = (loading) => {
        loader.hidden = !loading;
        form.classList.toggle('is-loading', loading);
    };

    // Affiche un message utilisateur (succes ou erreur).
    const setFeedback = (message, isError = false) => {
        feedback.textContent = message;
        feedback.className = isError ? 'ajax-feedback error' : 'ajax-feedback success';
        feedback.hidden = message === '';
    };

    // Transforme les champs du formulaire en query string pour le endpoint.
    const queryFromForm = () => {
        const params = new URLSearchParams();
        const data = new FormData(form);
        for (const [key, value] of data.entries()) {
            params.set(key, value.toString());
        }
        return params.toString();
    };

    // Lance le chargement Ajax des resultats avec annulation de la requete precedente.
    const loadRows = async () => {
        if (!endpoint) {
            return;
        }

        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();
        setLoading(true);
        setFeedback('');

        try {
            const response = await fetch(`${endpoint}?${queryFromForm()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: activeController.signal
            });

            const payload = await response.json();
            if (!response.ok || payload.success !== true) {
                throw new Error(payload.message || 'Erreur de chargement.');
            }

            body.innerHTML = payload.html;
            if (typeof payload.count === 'number') {
                setFeedback(`${payload.count} résultat(s) affiché(s).`);
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            setFeedback(error.message || 'Une erreur est survenue.', true);
        } finally {
            setLoading(false);
        }
    };

    // Debounce pour eviter de spammer l'API pendant la saisie texte.
    const debouncedLoad = () => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(loadRows, 250);
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        loadRows();
    });

    const nomInput = document.getElementById('nom');
    const dateInput = document.getElementById('date');
    const statutInput = document.getElementById('statut');

    // Filtrage en direct sur le nom.
    if (nomInput) {
        nomInput.addEventListener('input', debouncedLoad);
    }
    // Mise a jour immediate sur les champs select/date.
    if (dateInput) {
        dateInput.addEventListener('change', loadRows);
    }
    if (statutInput) {
        statutInput.addEventListener('change', loadRows);
    }

    // Reinitialise le formulaire puis recharge la liste sans recharger la page.
    if (reset) {
        reset.addEventListener('click', (event) => {
            event.preventDefault();
            form.reset();
            loadRows();
        });
    }
});
