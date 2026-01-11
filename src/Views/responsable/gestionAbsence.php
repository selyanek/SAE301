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
    <form method="post" class="filter-form">
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
        <a href="gestionAbsResp.php"><button type="button" class="btn">Réinitialiser</button></a>
    </form>

    <?php // Tableau des absences ?>
    <table id="tableAbsences">
        <thead>
        <tr>
            <th scope='col'>Date de soumission</th>
            <th scope='col'>Date début</th>
            <th scope='col'>Date fin</th>
            <th scope='col'>Étudiant</th>
            <th scope='col'>Motif</th>
            <th scope='col'>Document</th>
            <th scope='col'>Statut</th>
            <th scope='col'>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Vérifier si des absences existent
        if (!$absences || count($absences) === 0) {
            echo "<tr><td colspan='8' class='empty-message'>Aucune absence enregistrée pour le moment.</td></tr>";
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
            
            // Trier les périodes : d'abord "en_revision", puis "en_attente"
            usort($periodesTotales, function($a, $b) {
                // Ordre de priorité : en_revision > en_attente
                $ordre = ['en_revision' => 1, 'en_attente' => 2];
                $prioriteA = $ordre[$a['statut']] ?? 3;
                $prioriteB = $ordre[$b['statut']] ?? 3;
                
                // Si même statut, trier par date de début (plus récent d'abord)
                if ($prioriteA === $prioriteB) {
                    return strtotime($b['date_debut']) - strtotime($a['date_debut']);
                }
                
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
                // Date de soumission
                echo "<td>" . htmlspecialchars(date('d/m/Y', strtotime($periode['date_debut']))) . "</td>";
                // Date début
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($periode['date_debut']))) . "</td>";
                // Date fin
                echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($periode['date_fin']))) . "</td>";
                // Nom de l'étudiant
                echo "<td>" . htmlspecialchars($periode['etudiant']) . "</td>";
                // Motif + liste des cours
                echo "<td>";
                echo htmlspecialchars($periode['motif']);
                if (!empty($periode['cours'])) {
                    echo "<br><small class='small-gray'>";
                    echo "<strong>Cours concernés:</strong><br>";
                    foreach (array_unique($periode['cours']) as $cours) {
                        echo "• " . htmlspecialchars($cours) . "<br>";
                    }
                    echo "</small>";
                }
                echo "</td>";
                
                // Documents justificatifs
                echo "<td>";
                if (!empty($periode['urijustificatif'])) {
                    $fichiers = json_decode($periode['urijustificatif'], true);
                    if (is_array($fichiers) && count($fichiers) > 0) {
                        foreach ($fichiers as $index => $fichier) {
                            $fichierPath = "/uploads/" . htmlspecialchars($fichier);
                            echo "<a href='" . $fichierPath . "' target='_blank'>" . htmlspecialchars($fichier) . "</a><br>";
                        }
                    } else {
                        echo "—";
                    }
                } else {
                    echo "—";
                }
                echo "</td>";
                
                echo "<td class='$statutClass'>$statutLabel</td>";

                // Actions
                echo "<td class='actions'>";
                if ($statut == 'en_attente' || $statut == 'en_revision') {
                    echo "<a href='traitementDesJustificatif.php?id=" . htmlspecialchars($periode['idabsence']) . "' class='btn_justif'>Détails</a>";
                } else {
                    echo "<span class='traite'>Traité</span>";
                }
                echo "</td>";
                echo "</tr>";
            }

            if ($count == 0) {
                echo "<tr><td colspan='8' class='empty-message'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
            }
        }
        ?>
        </tbody>
    </table>
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

<?php
require __DIR__ . '/../layout/footer.php';
?>