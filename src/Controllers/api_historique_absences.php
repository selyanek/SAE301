<?php
session_start();

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/session_timeout.php';
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'responsable_pedagogique') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Acces refuse.'
    ]);
    exit();
}

try {
    $db = new \src\Database\Database();
    $pdo = $db->getConnection();
    $absenceModel = new \src\Models\Absence($pdo);
    $absences = $absenceModel->getAll();

    $nomFiltre    = isset($_GET['nom'])    ? strtolower(trim((string) $_GET['nom']))    : '';
    $dateFiltre   = isset($_GET['date'])   ? trim((string) $_GET['date'])               : '';
    $statutFiltre = isset($_GET['statut']) ? trim((string) $_GET['statut'])             : '';

    $absencesParEtudiant = [];

    foreach ($absences as $absence) {
        $prenomEtu  = $absence['prenomcompte'] ?? $absence['prenomCompte'] ?? '';
        $nomEtu     = $absence['nomcompte']    ?? $absence['nomCompte']    ?? '';
        $nomEtudiant = trim($prenomEtu . ' ' . $nomEtu);
        if ($nomEtudiant === '') {
            $nomEtudiant = $absence['identifiantetu'] ?? $absence['identifiantEtu'] ?? 'Etudiant inconnu';
        }

        if ($nomFiltre !== '' && strpos(strtolower($nomEtudiant), $nomFiltre) === false) {
            continue;
        }

        $dateJour = date('Y-m-d', strtotime($absence['date_debut'] ?? 'now'));
        if ($dateFiltre !== '' && $dateJour !== $dateFiltre) {
            continue;
        }

        // Calcul du statut
        $statut = 'en_attente';
        if (isset($absence['revision']) && ($absence['revision'] === true || $absence['revision'] === 't' || $absence['revision'] === '1' || $absence['revision'] === 1)) {
            $statut = 'en_revision';
        } elseif (isset($absence['justifie']) && $absence['justifie'] !== null) {
            if ($absence['justifie'] === true || $absence['justifie'] === 't' || $absence['justifie'] === '1' || $absence['justifie'] === 1) {
                $statut = 'valide';
            } elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || $absence['justifie'] === '0' || $absence['justifie'] === 0) {
                $statut = 'refuse';
            }
        }

        // Historique : uniquement les absences décidées
        if ($statut === 'en_attente' || $statut === 'en_revision') {
            continue;
        }

        if ($statutFiltre !== '' && $statut !== $statutFiltre) {
            continue;
        }

        $idEtudiant = $absence['idetudiant'] ?? $absence['idEtudiant'] ?? 0;
        if (!isset($absencesParEtudiant[$idEtudiant])) {
            $absencesParEtudiant[$idEtudiant] = [
                'nom'      => $nomEtudiant,
                'absences' => []
            ];
        }

        $verrouille = $absence['verrouille'] ?? false;

        $absencesParEtudiant[$idEtudiant]['absences'][] = [
            'date_debut'       => $absence['date_debut'],
            'date_fin'         => $absence['date_fin'],
            'motif'            => $absence['motif'] ?? '-',
            'urijustificatif'  => $absence['urijustificatif'] ?? '',
            'statut'           => $statut,
            'idabsence'        => $absence['idabsence'],
            'verrouille'       => $verrouille,
            'raison_refus'     => $absence['raison_refus'] ?? null
        ];
    }

    // Regroupe les absences consécutives d'un même étudiant
    $regrouperAbsences = function (array $liste): array {
        if (empty($liste)) return [];

        usort($liste, fn($a, $b) => strtotime($a['date_debut']) - strtotime($b['date_debut']));

        $periodes      = [];
        $periodeActuelle = null;

        foreach ($liste as $absence) {
            $debut = strtotime($absence['date_debut']);
            if ($periodeActuelle === null) {
                $periodeActuelle = $absence;
            } else {
                $fin   = strtotime($periodeActuelle['date_fin']);
                $ecart = $debut - $fin;
                if ($ecart <= 86400) {
                    $periodeActuelle['date_fin'] = $absence['date_fin'];
                } else {
                    $periodes[]      = $periodeActuelle;
                    $periodeActuelle = $absence;
                }
            }
        }
        if ($periodeActuelle !== null) {
            $periodes[] = $periodeActuelle;
        }
        return $periodes;
    };

    $periodesTotales = [];
    foreach ($absencesParEtudiant as $data) {
        $periodes = $regrouperAbsences($data['absences']);
        foreach ($periodes as $p) {
            $periodesTotales[] = array_merge($p, ['etudiant' => $data['nom']]);
        }
    }

    // Tri : date DESC
    usort($periodesTotales, fn($a, $b) => strtotime($b['date_debut']) - strtotime($a['date_debut']));

    ob_start();
    foreach ($periodesTotales as $p) {
        $statutClass = $p['statut'] === 'valide' ? 'statut-valide' : 'statut-refuse';
        $statutLabel = $p['statut'] === 'valide' ? 'Valide'        : 'Refusé';
        $verrouille  = $p['verrouille'] === true || $p['verrouille'] === 't' || $p['verrouille'] === '1';
        $idAbs       = (int) $p['idabsence'];

        echo "<tr>";
        echo "<td>" . htmlspecialchars(date('d/m/Y',    strtotime($p['date_debut']))) . "</td>";
        echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($p['date_debut']))) . "</td>";
        echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($p['date_fin'])))   . "</td>";
        echo "<td>" . htmlspecialchars($p['etudiant']) . "</td>";
        echo "<td>" . htmlspecialchars($p['motif'])    . "</td>";

        echo "<td>";
        if (!empty($p['urijustificatif'])) {
            $fichiers = json_decode($p['urijustificatif'], true);
            if (is_array($fichiers)) {
                foreach ($fichiers as $f) {
                    echo "<a href='/uploads/" . htmlspecialchars(rawurlencode($f)) . "' target='_blank'>" . htmlspecialchars($f) . "</a><br>";
                }
            } else {
                echo "-";
            }
        } else {
            echo "-";
        }
        echo "</td>";

        echo "<td class='" . htmlspecialchars($statutClass) . "'>";
        echo $statutLabel;
        if ($verrouille) {
            echo " <span class='badge-verrouille' title='Décision verrouillée'>🔒</span>";
        }
        if ($p['statut'] === 'refuse' && !empty($p['raison_refus'])) {
            echo "<div class='refus-reason'><strong>Raison:</strong> " . htmlspecialchars($p['raison_refus']) . "</div>";
        }
        echo "</td>";

        echo "<td><div class='actions'>";
        if ($verrouille) {
            echo "<button class='btn-deverrouiller' onclick='confirmerDeverrouillage({$idAbs})'>Déverrouiller</button>";
        } else {
            echo "<button class='btn-verrouiller' onclick='confirmerVerrouillage({$idAbs})'>Verrouiller</button>";
        }
        echo "<button class='btn-reviser' onclick='ouvrirModaleRevision({$idAbs},\"{$p['statut']}\")'>Réviser</button>";
        echo "<button class='btn-historique' onclick='voirHistorique({$idAbs})'>Historique</button>";
        echo "</div></td>";

        echo "</tr>";
    }

    $rowsHtml = trim((string) ob_get_clean());
    if ($rowsHtml === '') {
        $rowsHtml = "<tr><td colspan='8' class='empty-message'>Aucune absence ne correspond aux critères.</td></tr>";
    }

    echo json_encode([
        'success' => true,
        'html'    => $rowsHtml,
        'count'   => count($periodesTotales)
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
