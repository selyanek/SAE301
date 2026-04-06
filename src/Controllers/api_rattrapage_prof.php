<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/session_timeout.php';
require_once __DIR__ . '/../Models/Rattrapage.php';

function sendJsonResponse(array $payload, int $statusCode = 200): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload);
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    sendJsonResponse(['success' => false, 'message' => 'Acces refuse.'], 403);
}

$idProfesseur = $_SESSION['idCompte'] ?? null;
if (!$idProfesseur) {
    sendJsonResponse(['success' => false, 'message' => 'Session invalide.'], 401);
}

try {
    $rattrapageModel = new \src\Models\Rattrapage();
    $absencesEvaluations = $rattrapageModel->getAbsencesEvaluationsPourProfesseur($idProfesseur);

    $filtreDate = isset($_GET['filtre_date']) ? trim((string) $_GET['filtre_date']) : '';

    if ($filtreDate !== '') {
        $absencesEvaluations = array_filter($absencesEvaluations, function ($absence) use ($filtreDate) {
            $dateEval = isset($absence['cours_date_debut']) ? date('Y-m-d', strtotime($absence['cours_date_debut'])) : '';
            return $dateEval === $filtreDate;
        });
    }

    $absencesEvaluations = array_values($absencesEvaluations);

    ob_start();

    if (empty($absencesEvaluations)) {
        echo "<tr><td colspan='11' class='no-data'>Aucune absence lors de vos évaluations</td></tr>";
    } else {
        foreach ($absencesEvaluations as $absence) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['cours_date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars($absence['ressource_nom']) . "</td>";
            echo "<td>" . htmlspecialchars($absence['cours_type']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($absence['etudiant_prenom'] . ' ' . $absence['etudiant_nom']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($absence['formation']) . "</td>";
            echo "<td>" . htmlspecialchars($absence['motif'] ?? '—') . "</td>";

            echo "<td>";
            if ($absence['justifie'] === null || $absence['justifie'] === 'NULL') {
                echo "<span class='badge badge-warning'>En attente</span>";
            } elseif ($absence['justifie'] === 't' || $absence['justifie'] === true || $absence['justifie'] === '1') {
                echo "<span class='badge badge-success'>Justifiée</span>";
            } else {
                echo "<span class='badge badge-danger'>Non justifiée</span>";
            }
            echo "</td>";

            echo "<td>";
            echo !empty($absence['date_rattrapage'])
                ? '<strong>' . htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_rattrapage']))) . '</strong>'
                : '—';
            echo "</td>";

            echo "<td>" . htmlspecialchars($absence['salle'] ?? '—') . "</td>";

            echo "<td>";
            if (empty($absence['idrattrapage'])) {
                echo "<span class='badge badge-secondary'>À planifier</span>";
            } else {
                switch ($absence['rattrapage_statut']) {
                    case 'a_planifier':
                        echo "<span class='badge badge-warning'>À planifier</span>";
                        break;
                    case 'planifie':
                        echo "<span class='badge badge-info'>Planifié</span>";
                        break;
                    case 'effectue':
                        echo "<span class='badge badge-success'>Effectué</span>";
                        break;
                    case 'annule':
                        echo "<span class='badge badge-danger'>Annulé</span>";
                        break;
                }
            }
            echo "</td>";

            $idAbsence = (int) ($absence['idabsence'] ?? 0);
            $idRattrapage = !empty($absence['idrattrapage']) ? (int) $absence['idrattrapage'] : 'null';
            $dateRattrapage = htmlspecialchars($absence['date_rattrapage'] ?? '', ENT_QUOTES);
            $salle = htmlspecialchars($absence['salle'] ?? '', ENT_QUOTES);
            $remarque = htmlspecialchars($absence['remarque'] ?? '', ENT_QUOTES);
            $statut = htmlspecialchars($absence['rattrapage_statut'] ?? 'a_planifier', ENT_QUOTES);
            $etudiant = htmlspecialchars(($absence['etudiant_prenom'] ?? '') . ' ' . ($absence['etudiant_nom'] ?? ''), ENT_QUOTES);
            $ressource = htmlspecialchars($absence['ressource_nom'] ?? '', ENT_QUOTES);
            $label = empty($absence['idrattrapage']) ? 'Planifier' : 'Modifier';

            echo "<td>";
            echo "<button class='btn-action btn-edit' onclick=\"openModal(" . $idAbsence . ", " . $idRattrapage . ", '" . $dateRattrapage . "', '" . $salle . "', '" . $remarque . "', '" . $statut . "', '" . $etudiant . "', '" . $ressource . "')\">" . htmlspecialchars($label) . "</button>";
            echo "</td>";

            echo "</tr>";
        }
    }

    $html = trim((string) ob_get_clean());
    sendJsonResponse([
        'success' => true,
        'html' => $html,
        'count' => count($absencesEvaluations)
    ]);
} catch (Throwable $e) {
    error_log('api_rattrapage_prof: ' . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Erreur serveur.'], 500);
}
