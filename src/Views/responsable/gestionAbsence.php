<?php
// IMPORTANT: Le code PHP doit être AVANT tout HTML pour éviter "headers already sent"
session_start();
require __DIR__ . '/../../Controllers/session_timeout.php';
require __DIR__ . '/../../Controllers/Redirect.php';
require __DIR__ . '/../../Database/Database.php';
require __DIR__ . '/../../Models/Absence.php';

use src\Controllers\Redirect;

// Vérification du rôle
$redirect = new Redirect('responsable_pedagogique');
$redirect->redirect();

// Connexion à la base de données
$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);

// Récupérer toutes les absences
$absences = $absenceModel->getAll();

// Récupération des filtres
$nomFiltre = isset($_POST['nom']) ? strtolower(trim($_POST['nom'])) : '';
$dateFiltre = isset($_POST['date']) ? $_POST['date'] : '';
$statutFiltre = isset($_POST['statut']) ? $_POST['statut'] : '';

// Inclure le header et la navigation
require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../layout/navigation.php';
?>

<link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">

<header class="text fade-in">
    <h1>Gestion des absences</h1>
</header>

<?php // Messages de succès/erreur/info ?>
<div class="text fade-in">
    <?php
    if (isset($_GET['success'])) {
        echo "<div class='alert-success'>" . htmlspecialchars($_GET['success']) . "</div>";
    }
    if (isset($_GET['error'])) {
        echo "<div class='alert-error'>" . htmlspecialchars($_GET['error']) . "</div>";
    }
    if (isset($_GET['info'])) {
        echo "<div class='alert-info'>" . htmlspecialchars($_GET['info']) . "</div>";
    }
    ?>

    <?php // Formulaire de filtrage ?>
    <form method="post" class="filter-form" id="absenceFilterForm" data-endpoint="../../Controllers/api_absences.php">
        <label for="nom">Nom étudiant :</label>
        <input type="text" name="nom" id="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">

        <label for="date">Date :</label>
        <input type="date" name="date" id="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">

        <label for="statut">Statut :</label>
        <select name="statut" id="statut">
            <option value="">Tous</option>
            <option value="en_attente" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'en_attente') ? 'selected' : ''; ?>>En attente</option>
            <option value="en_revision" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'en_revision') ? 'selected' : ''; ?>>En révision</option>
            <option value="valide" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'valide') ? 'selected' : ''; ?>>Validé</option>
            <option value="refuse" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'refuse') ? 'selected' : ''; ?>>Refusé</option>
        </select>

        <button type="submit" class="btn">Filtrer</button>
        <a href="gestionAbsence.php" id="resetFiltersButton" class="btn">Réinitialiser</a>
    </form>

    <div id="tableLoader" class="ajax-loader" hidden>Chargement des absences...</div>
    <div id="tableFeedback" class="ajax-feedback" hidden></div>

    <?php // Tableau des absences ?>
    <div class="table-wrapper">
    <table id="tableAbsences" data-pagination="true" data-page-size="8">
        <thead>
        <tr>
            <th scope='col'>Dates</th>
            <th scope='col'>Étudiant</th>
            <th scope='col'>Motif</th>
            <th scope='col'>Document</th>
            <th scope='col'>Statut</th>
            <th scope='col'>Actions</th>
        </tr>
        </thead>
        <tbody id="tableAbsencesBody">
        <?php
        // Vérifier si des absences existent
        if (!$absences || count($absences) === 0) {
            echo "<tr><td colspan='6' class='empty-message'>Aucune absence enregistrée pour le moment.</td></tr>";
        } else {
            // Regrouper les absences par étudiant et par période continue
            $absencesParEtudiant = [];
            
            foreach ($absences as $absence) {
                // Récupérer le nom et prénom de l'étudiant
                $prenomEtudiant = $absence['prenomcompte'] ?? $absence['prenomCompte'] ?? '';
                $nomEtudiantNom = $absence['nomcompte'] ?? $absence['nomCompte'] ?? '';
                $nomEtudiant = trim($prenomEtudiant . ' ' . $nomEtudiantNom);
                
                // Si le nom est vide, essayer d'autres champs possibles
                if (empty(trim($nomEtudiant))) {
                    $nomEtudiant = $absence['identifiantetu'] ?? $absence['identifiantEtu'] ?? 'Étudiant inconnu';
                }
                
                // Application du filtre nom
                if ($nomFiltre && strpos(strtolower($nomEtudiant), $nomFiltre) === false) {
                    continue;
                }

                // Extraire la date (jour uniquement, sans heure)
                $dateJour = date('Y-m-d', strtotime($absence['date_debut'] ?? 'now'));
                
                // Application du filtre date
                if ($dateFiltre && $dateJour != $dateFiltre) {
                    continue;
                }

                // Déterminer le statut basé sur les colonnes revision puis justifie
                $statut = 'en_attente'; // Par défaut
                
                // Vérifier d'abord si l'absence est en révision
                if (isset($absence['revision']) && ($absence['revision'] === true || $absence['revision'] === 't' || $absence['revision'] === '1' || $absence['revision'] === 1)) {
                    $statut = 'en_revision';
                }
                elseif (isset($absence['justifie']) && $absence['justifie'] !== null) {
                    // PostgreSQL retourne 't' ou 'f' pour les booléens, PHP peut les convertir en true/false
                    if ($absence['justifie'] === true || $absence['justifie'] === 't' || $absence['justifie'] === '1' || $absence['justifie'] === 1) {
                        $statut = 'valide';
                    } elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0' || $absence['justifie'] === 0) {
                        $statut = 'refuse';
                    }
                }
                
                // Filtrer uniquement les absences en attente ou en révision dans cet onglet
                if ($statut !== 'en_attente' && $statut !== 'en_revision') {
                    continue;
                }
                
                // Application du filtre statut
                if ($statutFiltre && $statut != $statutFiltre) {
                    continue;
                }
                
                // Récupérer l'ID étudiant
                $idEtudiant = $absence['idetudiant'] ?? $absence['idEtudiant'] ?? 0;
                
                // Initialiser le tableau pour cet étudiant si nécessaire
                if (!isset($absencesParEtudiant[$idEtudiant])) {
                    $absencesParEtudiant[$idEtudiant] = [
                        'nom' => $nomEtudiant,
                        'absences' => []
                    ];
                }
                
                // Ajouter l'absence avec ses informations
                $absencesParEtudiant[$idEtudiant]['absences'][] = [
                    'date_debut' => $absence['date_debut'],
                    'date_fin' => $absence['date_fin'],
                    'motif' => $absence['motif'] ?? '—',
                    'urijustificatif' => $absence['urijustificatif'] ?? '',
                    'statut' => $statut,
                    'idabsence' => $absence['idabsence'],
                    'cours_type' => $absence['cours_type'] ?? '',
                    'ressource_nom' => $absence['ressource_nom'] ?? ''
                ];
            }
            
            // Fonction pour regrouper les absences consécutives
            function regrouperAbsencesConsecutives($absences) {
                if (empty($absences)) {
                    return [];
                }
                
                // Trier les absences par date de début
                usort($absences, function($a, $b) {
                    return strtotime($a['date_debut']) - strtotime($b['date_debut']);
                });
                
                $periodes = [];
                $periodeActuelle = null;
                
                foreach ($absences as $absence) {
                    $debutActuel = strtotime($absence['date_debut']);
                    $finActuelle = strtotime($absence['date_fin']);
                    
                    if ($periodeActuelle === null) {
                        // Première absence, créer une nouvelle période
                        $periodeActuelle = [
                            'date_debut' => $absence['date_debut'],
                            'date_fin' => $absence['date_fin'],
                            'motif' => $absence['motif'],
                            'urijustificatif' => $absence['urijustificatif'],
                            'statut' => $absence['statut'],
                            'idabsence' => $absence['idabsence'],
                            'cours' => []
                        ];
                        
                        // Ajouter le cours
                        $coursInfo = '';
                        if (!empty($absence['cours_type'])) {
                            $coursInfo .= $absence['cours_type'];
                        }
                        if (!empty($absence['ressource_nom'])) {
                            $coursInfo .= ' - ' . $absence['ressource_nom'];
                        }
                        if (!empty($coursInfo)) {
                            $periodeActuelle['cours'][] = $coursInfo;
                        }
                    } else {
                        // Vérifier si cette absence est consécutive à la période actuelle
                        $finPeriode = strtotime($periodeActuelle['date_fin']);
                        
                        // Considérer comme consécutif si moins de 24h d'écart
                        $ecart = $debutActuel - $finPeriode;
                        
                        if ($ecart <= 86400) { // 24h
                            // Fusionner avec la période actuelle
                            $periodeActuelle['date_fin'] = $absence['date_fin'];
                            
                            // Mettre à jour motif et justificatif si plus informatifs
                            if ($absence['motif'] !== '—' && ($periodeActuelle['motif'] === '—' || empty($periodeActuelle['motif']))) {
                                $periodeActuelle['motif'] = $absence['motif'];
                            }
                            if (!empty($absence['urijustificatif']) && empty($periodeActuelle['urijustificatif'])) {
                                $periodeActuelle['urijustificatif'] = $absence['urijustificatif'];
                            }
                            
                            // Ajouter le cours
                            $coursInfo = '';
                            if (!empty($absence['cours_type'])) {
                                $coursInfo .= $absence['cours_type'];
                            }
                            if (!empty($absence['ressource_nom'])) {
                                $coursInfo .= ' - ' . $absence['ressource_nom'];
                            }
                            if (!empty($coursInfo)) {
                                $periodeActuelle['cours'][] = $coursInfo;
                            }
                        } else {
                            // Nouvelle période non consécutive
                            $periodes[] = $periodeActuelle;
                            
                            $periodeActuelle = [
                                'date_debut' => $absence['date_debut'],
                                'date_fin' => $absence['date_fin'],
                                'motif' => $absence['motif'],
                                'urijustificatif' => $absence['urijustificatif'],
                                'statut' => $absence['statut'],
                                'idabsence' => $absence['idabsence'],
                                'cours' => []
                            ];
                            
                            // Ajouter le cours
                            $coursInfo = '';
                            if (!empty($absence['cours_type'])) {
                                $coursInfo .= $absence['cours_type'];
                            }
                            if (!empty($absence['ressource_nom'])) {
                                $coursInfo .= ' - ' . $absence['ressource_nom'];
                            }
                            if (!empty($coursInfo)) {
                                $periodeActuelle['cours'][] = $coursInfo;
                            }
                        }
                    }
                }
                
                // Ajouter la dernière période
                if ($periodeActuelle !== null) {
                    $periodes[] = $periodeActuelle;
                }
                
                return $periodes;
            }
            
            // Regrouper les absences par périodes continues pour chaque étudiant
            $periodesTotales = [];
            foreach ($absencesParEtudiant as $idEtudiant => $data) {
                $periodesEtudiant = regrouperAbsencesConsecutives($data['absences']);
                
                foreach ($periodesEtudiant as $periode) {
                    $periodesTotales[] = array_merge($periode, ['etudiant' => $data['nom']]);
                }
            }
            
            // Trier les périodes : par date de début décroissante (plus récentes en premier),
            // puis par statut (en_revision avant en_attente) si même date.
            usort($periodesTotales, function($a, $b) {
                $timeA = strtotime($a['date_debut'] ?? 0);
                $timeB = strtotime($b['date_debut'] ?? 0);

                // Priorité principale : date (plus récent d'abord)
                if ($timeA !== $timeB) {
                    return $timeB - $timeA;
                }

                // Si mêmes dates, appliquer ordre de priorité sur le statut
                $ordre = ['en_revision' => 1, 'en_attente' => 2];
                $prioriteA = $ordre[$a['statut']] ?? 3;
                $prioriteB = $ordre[$b['statut']] ?? 3;

                return $prioriteA - $prioriteB;
            });
            
            // Affichage des périodes d'absence
            $count = 0;
            foreach ($periodesTotales as $periode) {
                $count++;
                
                $statut = $periode['statut'];
                $statutClass = '';
                $statutLabel = '';

                switch($statut) {
                    case 'en_attente':
                        $statutClass = 'statut-attente';
                        $statutLabel = '⏳ En attente';
                        break;
                    case 'en_revision':
                        $statutClass = 'statut-revision';
                        $statutLabel = '🔍 En révision';
                        break;
                    case 'valide':
                        $statutClass = 'statut-valide';
                        $statutLabel = '✓ Validé';
                        break;
                    case 'refuse':
                        $statutClass = 'statut-refuse';
                        $statutLabel = '✗ Refusé';
                        break;
                }

                echo "<tr>";
                // Dates empilées : début au-dessus, fin en-dessous (US-27)
                echo "<td class='td-dates' data-label='Dates'>";
                echo "<span class='date-debut'>" . htmlspecialchars(date('d/m/Y H:i', strtotime($periode['date_debut']))) . "</span>";
                echo "<span class='date-fin'>" . htmlspecialchars(date('d/m/Y H:i', strtotime($periode['date_fin']))) . "</span>";
                echo "</td>";
                // Nom empilé : prénom au-dessus, nom en-dessous (US-27)
                $nomParts = explode(' ', $periode['etudiant'], 2);
                echo "<td class='td-etudiant' data-label='Étudiant'>";
                echo "<span class='etudiant-prenom'>" . htmlspecialchars($nomParts[0] ?? '') . "</span>";
                echo "<span class='etudiant-nom'>" . htmlspecialchars($nomParts[1] ?? '') . "</span>";
                echo "</td>";
                // Motif + liste des cours — bouton "Voir" pour mobile (US-27)
                $motifComplet = htmlspecialchars($periode['motif']);
                if (!empty($periode['cours'])) {
                    $motifComplet .= "\n\nCours concernés:\n";
                    foreach (array_unique($periode['cours']) as $cours) {
                        $motifComplet .= "• " . htmlspecialchars($cours) . "\n";
                    }
                }
                echo "<td class='td-motif' data-label='Motif'>";
                echo "<span class='cell-full'>" . nl2br($motifComplet) . "</span>";
                echo "<button class='btn-voir' onclick='ouvrirModale(\"Motif\", this.dataset.content)' data-content='" . htmlspecialchars($motifComplet, ENT_QUOTES) . "'>Voir</button>";
                echo "</td>";
                
                // Documents justificatifs — bouton "Voir" pour mobile (US-27)
                $docHtml = '';
                if (!empty($periode['urijustificatif'])) {
                    $fichiers = json_decode($periode['urijustificatif'], true);
                    if (is_array($fichiers) && count($fichiers) > 0) {
                        foreach ($fichiers as $index => $fichier) {
                            $fichierPath = "/uploads/" . htmlspecialchars($fichier);
                            $docHtml .= "<a href='" . $fichierPath . "' target='_blank'>" . htmlspecialchars($fichier) . "</a><br>";
                        }
                    } else {
                        $docHtml = "—";
                    }
                } else {
                    $docHtml = "—";
                }
                echo "<td class='td-document' data-label='Document'>";
                echo "<span class='cell-full'>" . $docHtml . "</span>";
                echo "<button class='btn-voir btn-voir-doc' onclick='ouvrirModaleDoc(this)' data-content='" . htmlspecialchars($docHtml, ENT_QUOTES) . "'>Voir</button>";
                echo "</td>";
                
                // Statut avec span pour éviter conflit CSS (US-27)
                echo "<td data-label='Statut'><span class='statut-badge $statutClass'>$statutLabel</span></td>";

                // Actions
                echo "<td data-label='Actions'>";
                echo "<div class='actions'>";
                if ($statut == 'en_attente' || $statut == 'en_revision') {
                    echo "<a href='traitementDesJustificatif.php?id=" . htmlspecialchars($periode['idabsence']) . "' class='btn_justif'>Détails</a>";
                } else {
                    echo "<span class='traite'>Traité</span>";
                }
                echo "</div>";
                echo "</td>";

            }

            if ($count == 0) {
                echo "<tr><td colspan='6' class='empty-message'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
            }
        }
        ?>
        </tbody>
    </table>
    </div>
</div>

<script>
    function validerAbsence(index) {
        if (confirm('Voulez-vous vraiment valider cette absence ?')) {
            window.location.href = '../Controllers/traiter_absence.php?action=valider&id=' + index;
        }
    }

    function refuserAbsence(index) {
        if (confirm('Voulez-vous vraiment refuser cette absence ?')) {
            window.location.href = '../Controllers/traiter_absence.php?action=refuser&id=' + index;
        }
    }
</script>
<script src="/public/asset/JS/filterAjax.js"></script>
<script src="/public/asset/JS/tablePagination.js"></script>

<!-- US-27 : Modale pour afficher le contenu des colonnes sur mobile -->
<div id="modaleDetail" class="modale-overlay" style="display:none;" onclick="if(event.target===this)fermerModale()">
    <div class="modale-content">
        <div class="modale-header">
            <h3 id="modale-titre"></h3>
            <button class="modale-close" onclick="fermerModale()">✕</button>
        </div>
        <div id="modale-body" class="modale-body"></div>
    </div>
</div>

<script>
function ouvrirModale(titre, contenu) {
    document.getElementById('modale-titre').textContent = titre;
    document.getElementById('modale-body').textContent = contenu;
    document.getElementById('modaleDetail').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function ouvrirModaleDoc(btn) {
    document.getElementById('modale-titre').textContent = 'Document';
    document.getElementById('modale-body').innerHTML = btn.dataset.content;
    document.getElementById('modaleDetail').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function fermerModale() {
    document.getElementById('modaleDetail').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fermerModale();
});
</script>

<?php
require __DIR__ . '/../layout/footer.php';
?>