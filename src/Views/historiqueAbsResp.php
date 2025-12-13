<?php
// IMPORTANT: Le code PHP doit être AVANT tout HTML pour éviter "headers already sent"
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../Controllers/session_timeout.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
    header('Location: /public/index.php');
    exit();
}

// Vérifier le rôle responsable
if ($_SESSION['role'] !== 'responsable_pedagogique') {
    header('Location: /public/index.php');
    exit();
}

// Charger les dépendances
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des absences</title>
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>
<body>
<!-- Affichage des logos -->
<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>

<!-- Barre latérale de navigation -->
<div class="sidebar">
    <ul>
        <li><a href="accueil_responsable.php">Accueil</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="/src/Views/gestionAbsResp.php">Gestion des absences</a></li>
        <li><a href="historiqueAbsResp.php">Historique des absences</a></li>
        <li><a href="/src/Views/responsable/statistiques.php">Statistiques</a></li>
    </ul>
</div>

<header class="text">
    <h1>Historique des absences</h1>
    <p>Consultez l'historique de toutes les absences validées ou refusées.</p>
</header>

<!-- Filtrage -->
<form method="post" style="max-width: 1200px; margin: 0 auto 20px auto; padding: 0 20px;">
    <label for="nom">Nom étudiant :</label>
    <input type="text" name="nom" id="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">

    <label for="date">Date :</label>
    <input type="date" name="date" id="date" value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">

    <label for="statut">Statut :</label>
    <select name="statut" id="statut">
        <option value="">Tous</option>
        <option value="valide" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'valide') ? 'selected' : ''; ?>>Validé</option>
        <option value="refuse" <?php echo (isset($_POST['statut']) && $_POST['statut'] == 'refuse') ? 'selected' : ''; ?>>Refusé</option>
    </select>

    <button type="submit">Filtrer</button>
    <a href="historiqueAbsResp.php"><button type="button">Réinitialiser</button></a>
</form>

<!-- Tableau des absences -->
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
    </tr>
    </thead>
    <tbody>
    <?php
    // Vérifier si des absences existent
    if (!$absences || count($absences) === 0) {
        echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>Aucune absence enregistrée pour le moment.</td></tr>";
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

            // Déterminer le statut basé sur le champ justifie (null, true, false)
            $statut = 'en_attente'; // Par défaut
            if (isset($absence['justifie']) && $absence['justifie'] !== null) {
                // PostgreSQL retourne 't' ou 'f' pour les booléens
                if ($absence['justifie'] === true || $absence['justifie'] === 't' || $absence['justifie'] === '1' || $absence['justifie'] === 1) {
                    $statut = 'valide';
                } elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0' || $absence['justifie'] === 0) {
                    $statut = 'refuse';
                }
            }
            
            // Afficher uniquement les absences validées ou refusées dans l'historique
            if ($statut === 'en_attente') {
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
                'ressource_nom' => $absence['ressource_nom'] ?? '',
                'raison_refus' => $absence['raison_refus'] ?? null
            ];
        }
        
        // Fonction pour regrouper les absences consécutives
        function regrouperAbsencesConsecutivesHistorique($absences) {
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
                        'raison_refus' => $absence['raison_refus'] ?? null,
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
                    
                    if ($ecart <= 86400) { // 86400 secondes = 24 heures
                        // Fusionner avec la période actuelle
                        $periodeActuelle['date_fin'] = $absence['date_fin'];
                        
                        // Garder le même motif et justificatif (généralement identiques)
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
                            'raison_refus' => $absence['raison_refus'] ?? null,
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
            $periodesEtudiant = regrouperAbsencesConsecutivesHistorique($data['absences']);
            
            foreach ($periodesEtudiant as $periode) {
                $periodesTotales[] = array_merge($periode, ['etudiant' => $data['nom']]);
            }
        }
        
        // Affichage des périodes d'absence
        $count = 0;
        foreach ($periodesTotales as $periode) {
            $count++;
            
            $statut = $periode['statut'];
            $statutClass = '';
            $statutLabel = '';

            switch($statut) {
                case 'valide':
                    $statutClass = 'statut-valide';
                    $statutLabel = '✅ Validé';
                    break;
                case 'refuse':
                    $statutClass = 'statut-refuse';
                    $statutLabel = '❌ Refusé';
                    break;
            }

            echo "<tr>";
            // Date de soumission (utilise la date de début)
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
                echo "<br><small style='color: #666; display: block; margin-top: 5px;'>";
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
            
            // Statut + raison du refus
            echo "<td class='$statutClass' style='min-width: 200px; vertical-align: top;'>";
            echo $statutLabel;
            
            // Afficher la raison du refus si elle existe
            if ($statut === 'refuse' && !empty($periode['raison_refus'])) {
                $raisonRefus = htmlspecialchars($periode['raison_refus']);
                echo "<div style='margin-top: 10px; padding: 8px; background-color: #ffe6e6; border-left: 3px solid #f44336; border-radius: 3px;'>";
                echo "<strong style='color: #d32f2f; font-size: 12px;'>Raison du refus :</strong><br>";
                echo "<span style='color: #333; font-size: 12px;'>{$raisonRefus}</span>";
                echo "</div>";
            }
            
            echo "</td>";
            echo "</tr>";
        }

        if ($count == 0) {
            echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>Aucune absence traitée ne correspond aux critères de filtrage.</td></tr>";
        }
    }
    ?>
    </tbody>
</table>

<div style="height: 150px;"></div>

<!-- Pied de page -->
<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="">Aides</a>
    </nav>
</footer>

<style>
    .statut-valide { color: green; font-weight: bold; }
    .statut-refuse { color: red; font-weight: bold; }
</style>

</body>
</html>
