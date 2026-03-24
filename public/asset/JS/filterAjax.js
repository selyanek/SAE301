// Script Ajax unique pour toutes les pages avec filtres.
// Prerequis cote HTML:
// - form id="absenceFilterForm" ou id="statsFilterForm"
// - data-endpoint="..."
// - loader id="tableLoader"/"statsLoader"
// - feedback id="tableFeedback"/"statsFeedback"
// - reset id="resetFiltersButton"/"resetStatsFilters"

(function () {
    function byId(id) {
        return document.getElementById(id);
    }

    function setFeedback(feedbackEl, message, isError) {
        if (!feedbackEl) return;
        feedbackEl.textContent = message || '';
        feedbackEl.className = isError ? 'ajax-feedback error' : 'ajax-feedback success';
        feedbackEl.hidden = !message;
    }

    function setLoading(form, loader, loading) {
        if (loader) loader.hidden = !loading;
        if (form) form.classList.toggle('is-loading', loading);
    }

    function queryFromForm(form) {
        const params = new URLSearchParams();
        const data = new FormData(form);
        for (const [key, value] of data.entries()) {
            params.set(key, value.toString());
        }
        return params.toString();
    }

    function updateStatCards(globales) {
        const mapping = [
            ['statTotal', 'total'],
            ['statJustifiees', 'justifiees'],
            ['statNonJustifiees', 'non_justifiees'],
            ['statEvaluations', 'evaluations']
        ];

        mapping.forEach(([id, key]) => {
            const el = byId(id);
            if (el && globales && Object.prototype.hasOwnProperty.call(globales, key)) {
                el.textContent = String(globales[key] ?? 0);
            }
        });
    }

    function updateChartsIfAny(donnees) {
        if (typeof Chart === 'undefined') return;

        const chartData = donnees || {};

        const update = function (chartId, key) {
            const chart = Chart.getChart(chartId);
            const source = chartData[key] || {};
            if (!chart) return;
            chart.data.labels = Object.keys(source);
            chart.data.datasets[0].data = Object.values(source);
            chart.update();
        };

        update('chartTypes', 'types');
        update('chartHeures', 'heures');
        update('chartMatieres', 'matieres');
        update('chartTendances', 'tendances');
    }

    function resolveElements() {
        const form = byId('absenceFilterForm') || byId('statsFilterForm');
        if (!form) return null;

        return {
            form: form,
            endpoint: form.dataset.endpoint || '',
            body: byId('tableAbsencesBody'),
            loader: byId('tableLoader') || byId('statsLoader'),
            feedback: byId('tableFeedback') || byId('statsFeedback'),
            reset: byId('resetFiltersButton') || byId('resetStatsFilters'),
            nomInput: byId('nom'),
            dateInput: byId('date') || byId('date_debut'),
            statutInput: byId('statut') || byId('type_cours')
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const ctx = resolveElements();
        if (!ctx || !ctx.endpoint) return;

        let debounceTimer = null;
        let activeController = null;

        const loadFilteredData = async function () {
            if (activeController) {
                activeController.abort();
            }

            activeController = new AbortController();
            setLoading(ctx.form, ctx.loader, true);
            setFeedback(ctx.feedback, '', false);

            try {
                const response = await fetch(ctx.endpoint + '?' + queryFromForm(ctx.form), {
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

                // Mode absences: insertion HTML.
                if (ctx.body && typeof payload.html === 'string') {
                    ctx.body.innerHTML = payload.html;
                    if (typeof payload.count === 'number') {
                        setFeedback(ctx.feedback, payload.count + ' resultat(s) affiche(s).', false);
                    }
                    return;
                }

                // Mode statistiques: mise a jour KPI + charts.
                if (payload.globales || payload.donnees || payload.types) {
                    const donnees = payload.donnees || payload;
                    updateStatCards(payload.globales || {});
                    updateChartsIfAny(donnees);
                    setFeedback(ctx.feedback, 'Statistiques mises a jour.', false);
                    return;
                }

                setFeedback(ctx.feedback, 'Filtre applique.', false);
            } catch (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                setFeedback(ctx.feedback, (error && error.message) ? error.message : 'Une erreur est survenue.', true);
            } finally {
                setLoading(ctx.form, ctx.loader, false);
            }
        };

        const debouncedLoad = function () {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(loadFilteredData, 250);
        };

        ctx.form.addEventListener('submit', function (event) {
            event.preventDefault();
            loadFilteredData();
        });

        if (ctx.nomInput) ctx.nomInput.addEventListener('input', debouncedLoad);
        if (ctx.dateInput) ctx.dateInput.addEventListener('change', loadFilteredData);
        if (ctx.statutInput) ctx.statutInput.addEventListener('change', loadFilteredData);

        if (ctx.reset) {
            ctx.reset.addEventListener('click', function (event) {
                event.preventDefault();
                ctx.form.reset();
                loadFilteredData();
            });
        }
    });
})();
