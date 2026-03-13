<?php
session_start();

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/session_timeout.php';
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acces refuse.']);
    exit();
}

$modeEarly = isset($_GET['mode']) ? trim((string) $_GET['mode']) : 'gestion';

if ($modeEarly === 'etudiant') {
    if ($_SESSION['role'] !== 'etudiant' && $_SESSION['role'] !== 'etudiante') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acces refuse.']);
        exit();
    }
} else {
    if ($_SESSION['role'] !== 'responsable_pedagogique') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acces refuse.']);
        exit();
    }
}

try {
    $db = new \src\Database\Database();
    $pdo = $db->getConnection();
    $absenceModel = new \src\Models\Absence($pdo);

    $nomFiltre    = isset($_GET['nom'])    ? strtolower(trim((string) $_GET['nom']))  : '';
    $dateFiltre   = isset($_GET['date'])   ? trim((string) $_GET['date'])             : '';
    $statutFiltre = isset($_GET['statut']) ? trim((string) $_GET['statut'])           : '';
    $mode         = isset($_GET['mode'])   ? trim((string) $_GET['mode'])             : 'gestion';

    // ── Mode ÉTUDIANT : rendu cartes ───────────────────────────────────────────
    if ($mode === 'etudiant') {
        $studentId = $_SESSION['login'] ?? '';
        $mesAbsences = !empty($studentId) ? $absenceModel->getByStudentIdentifiant($studentId) : [];

        ob_start();
        $count = 0;
        foreach ($mesAbsences as $absence) {
            $dateDebut = date('Y-m-d', strtotime($absence['date_debut']));
            if ($dateFiltre !== '' && $dateDebut !== $dateFiltre) continue;

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

            if ($statutFiltre !== '' && $statut !== $statutFiltre) continue;

            $count++;
            $statutClass = match($statut) {
                'en_attente'  => 'statut-attente',
                'en_revision' => 'statut-revision',
                'valide'      => 'statut-valide',
                'refuse'      => 'statut-refuse',
                default       => ''
            };
            $statutLabel = match($statut) {
                'en_attente'  => 'En attente',
                'en_revision' => 'En révision',
                'valide'      => 'Validé',
                'refuse'      => 'Refusé',
                default       => ''
            };

            $dateSoumission = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut'])));
            $dateDebutFmt   = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_debut'])));
            $dateFinFmt     = htmlspecialchars(date('d/m/Y à H:i', strtotime($absence['date_fin'])));
            $motifFmt       = htmlspecialchars($absence['motif'] ?? '—');

            $justificatifsHtml = '—';
            if (!empty($absence['urijustificatif'])) {
                $fichiers = json_decode($absence['urijustificatif'], true);
                if (is_array($fichiers) && count($fichiers) > 0) {
                    $links = [];
                    foreach ($fichiers as $fichier) {
                        $links[] = "<a href='/uploads/" . htmlspecialchars($fichier) . "' target='_blank'>" . htmlspecialchars($fichier) . "</a>";
                    }
                    $justificatifsHtml = implode('<br>', $links);
                }
            }

            echo "<div class='absence-card'>";
            echo "<div class='header-card'>" . $dateSoumission . "</div>";
            echo "<div class='dates'><strong>Du :</strong> " . $dateDebutFmt . " <strong>au</strong> " . $dateFinFmt . "</div>";
            echo "<div class='motif'><strong>Motif :</strong> " . $motifFmt . "</div>";
            echo "<div class='justificatifs'><strong>Justificatifs :</strong><br>" . $justificatifsHtml . "</div>";
            echo "<div class='statut " . $statutClass . "'><strong>Statut :</strong> " . $statutLabel . "</div>";
            if ($statut === 'refuse' && !empty($absence['raison_refus'])) {
                echo "<div class='refus-reason'><strong>Raison du refus :</strong> " . htmlspecialchars($absence['raison_refus']) . "</div>";
            }
            echo "</div>";
        }

        if ($count === 0) {
            echo "<div class='no-results'>Aucune absence trouvée pour ces critères.</div>";
        }

        $html = trim((string) ob_get_clean());
        echo json_encode(['success' => true, 'html' => $html, 'count' => $count]);
        exit();
    }

    // ── Modes GESTION / HISTORIQUE ─────────────────────────────────────────────
    $absences = $absenceModel->getAll();
    $absencesParEtudiant = [];

    foreach ($absences as $absence) {
        $prenomEtudiant = $absence['prenomcompte'] ?? $absence['prenomCompte'] ?? '';
        $nomEtudiantNom = $absence['nomcompte'] ?? $absence['nomCompte'] ?? '';
        $nomEtudiant = trim($prenomEtudiant . ' ' . $nomEtudiantNom);

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

        if ($mode === 'historique') {
            if ($statut !== 'valide' && $statut !== 'refuse') continue;
        } else {
            if ($statut !== 'en_attente' && $statut !== 'en_revision') continue;
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

        $absencesParEtudiant[$idEtudiant]['absences'][] = [
            'date_debut'      => $absence['date_debut'],
            'date_fin'        => $absence['date_fin'],
            'motif'           => $absence['motif'] ?? '—',
            'urijustificatif' => $absence['urijustificatif'] ?? '',
            'statut'          => $statut,
            'idabsence'       => $absence['idabsence'],
            'cours_type'      => $absence['cours_type'] ?? '',
            'ressource_nom'   => $absence['ressource_nom'] ?? '',
            'verrouille'      => $absence['verrouille'] ?? false,
            'raison_refus'    => $absence['raison_refus'] ?? null,
        ];
    }

    $regrouperAbsencesConsecutives = function (array $liste): array {
        if (empty($liste)) {
            return [];
        }

        usort($liste, function ($a, $b) {
            return strtotime($a['date_debut']) - strtotime($b['date_debut']);
        });

        $periodes = [];
        $periodeActuelle = null;

        foreach ($liste as $absence) {
            $debutActuel = strtotime($absence['date_debut']);

            if ($periodeActuelle === null) {
                $periodeActuelle = [
                    'date_debut'     => $absence['date_debut'],
                    'date_fin'       => $absence['date_fin'],
                    'motif'          => $absence['motif'],
                    'urijustificatif'=> $absence['urijustificatif'],
                    'statut'         => $absence['statut'],
                    'idabsence'      => $absence['idabsence'],
                    'verrouille'     => $absence['verrouille'] ?? false,
                    'raison_refus'   => $absence['raison_refus'] ?? null,
                    'cours'          => []
                ];
            } else {
                $finPeriode = strtotime($periodeActuelle['date_fin']);
                $ecart = $debutActuel - $finPeriode;

                if ($ecart <= 86400) {
                    $periodeActuelle['date_fin'] = $absence['date_fin'];

                    if ($absence['motif'] !== '—' && ($periodeActuelle['motif'] === '—' || empty($periodeActuelle['motif']))) {
                        $periodeActuelle['motif'] = $absence['motif'];
                    }

                    if (!empty($absence['urijustificatif']) && empty($periodeActuelle['urijustificatif'])) {
                        $periodeActuelle['urijustificatif'] = $absence['urijustificatif'];
                    }
                } else {
                    $periodes[] = $periodeActuelle;
                    $periodeActuelle = [
                        'date_debut'      => $absence['date_debut'],
                        'date_fin'        => $absence['date_fin'],
                        'motif'           => $absence['motif'],
                        'urijustificatif' => $absence['urijustificatif'],
                        'statut'          => $absence['statut'],
                        'idabsence'       => $absence['idabsence'],
                        'verrouille'      => $absence['verrouille'] ?? false,
                        'raison_refus'    => $absence['raison_refus'] ?? null,
                        'cours'           => []
                    ];
                }
            }

            $coursInfo = '';
            if (!empty($absence['cours_type'])) {
                $coursInfo .= $absence['cours_type'];
            }
            if (!empty($absence['ressource_nom'])) {
                $coursInfo .= ' - ' . $absence['ressource_nom'];
            }
            if ($coursInfo !== '') {
                $periodeActuelle['cours'][] = $coursInfo;
            }
        }

        if ($periodeActuelle !== null) {
            $periodes[] = $periodeActuelle;
        }

        return $periodes;
    };

    $periodesTotales = [];
    foreach ($absencesParEtudiant as $data) {
        $periodesEtudiant = $regrouperAbsencesConsecutives($data['absences']);
        foreach ($periodesEtudiant as $periode) {
            $periodesTotales[] = array_merge($periode, ['etudiant' => $data['nom']]);
        }
    }

    usort($periodesTotales, function ($a, $b) {
        $timeA = strtotime($a['date_debut'] ?? 0);
        $timeB = strtotime($b['date_debut'] ?? 0);

        if ($timeA !== $timeB) {
            return $timeB - $timeA;
        }

        $ordre = ['en_revision' => 1, 'en_attente' => 2];
        $prioriteA = $ordre[$a['statut']] ?? 3;
        $prioriteB = $ordre[$b['statut']] ?? 3;

        return $prioriteA - $prioriteB;
    });

    ob_start();
    foreach ($periodesTotales as $periode) {
        $idAbs = (int) $periode['idabsence'];

        if ($mode === 'historique') {
            // ---- Rendu HISTORIQUE (valide / refusé) ----
            $statutClass = $periode['statut'] === 'valide' ? 'statut-valide' : 'statut-refuse';
            $statutLabel = $periode['statut'] === 'valide' ? 'Valide'        : 'Refusé';
            $verrouille  = $periode['verrouille'] === true || $periode['verrouille'] === 't' || $periode['verrouille'] === '1';

            echo "<tr>";
            echo "<td>" . htmlspecialchars(date('d/m/Y',     strtotime($periode['date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($periode['date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($periode['date_fin'])))   . "</td>";
            echo "<td>" . htmlspecialchars($periode['etudiant']) . "</td>";
            echo "<td>" . htmlspecialchars($periode['motif'])    . "</td>";

            echo "<td>";
            if (!empty($periode['urijustificatif'])) {
                $fichiers = json_decode($periode['urijustificatif'], true);
                if (is_array($fichiers)) {
                    foreach ($fichiers as $f) {
                        echo "<a href='/uploads/" . htmlspecialchars(rawurlencode($f)) . "' target='_blank'>" . htmlspecialchars($f) . "</a><br>";
                    }
                } else { echo '-'; }
            } else { echo '-'; }
            echo "</td>";

            echo "<td class='" . htmlspecialchars($statutClass) . "'>";
            echo $statutLabel;
            if ($verrouille) echo " <span class='badge-verrouille' title='Décision verrouillée'>🔒</span>";
            if ($periode['statut'] === 'refuse' && !empty($periode['raison_refus'])) {
                echo "<div class='refus-reason'><strong>Raison:</strong> " . htmlspecialchars($periode['raison_refus']) . "</div>";
            }
            echo "</td>";

            echo "<td><div class='actions'>";
            if ($verrouille) {
                echo "<button class='btn-deverrouiller' onclick='confirmerDeverrouillage({$idAbs})'>Déverrouiller</button>";
            } else {
                echo "<button class='btn-verrouiller' onclick='confirmerVerrouillage({$idAbs})'>Verrouiller</button>";
            }
            echo "<button class='btn-reviser' onclick='ouvrirModaleRevision({$idAbs},\"{$periode['statut']}\")'>Réviser</button>";
            echo "<button class='btn-historique' onclick='voirHistorique({$idAbs})'>Historique</button>";
            echo "</div></td>";
            echo "</tr>";

        } else {
            // ---- Rendu GESTION (en_attente / en_revision) ----
            $statutClass = '';
            $statutLabel = '';
            switch ($periode['statut']) {
                case 'en_attente':  $statutClass = 'statut-attente';  $statutLabel = '⏳ En attente';  break;
                case 'en_revision': $statutClass = 'statut-revision'; $statutLabel = '🔍 En révision'; break;
                case 'valide':      $statutClass = 'statut-valide';   $statutLabel = '✓ Validé';       break;
                case 'refuse':      $statutClass = 'statut-refuse';   $statutLabel = '✗ Refusé';       break;
            }

            echo "<tr>";
            echo "<td>" . htmlspecialchars(date('d/m/Y',     strtotime($periode['date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($periode['date_debut']))) . "</td>";
            echo "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($periode['date_fin'])))   . "</td>";
            echo "<td>" . htmlspecialchars($periode['etudiant']) . "</td>";

            echo "<td>";
            echo htmlspecialchars($periode['motif']);
            if (!empty($periode['cours'])) {
                echo "<br><small class='small-gray'><strong>Cours concernés:</strong><br>";
                foreach (array_unique($periode['cours']) as $cours) {
                    echo "• " . htmlspecialchars($cours) . "<br>";
                }
                echo "</small>";
            }
            echo "</td>";

            echo "<td>";
            if (!empty($periode['urijustificatif'])) {
                $fichiers = json_decode($periode['urijustificatif'], true);
                if (is_array($fichiers) && count($fichiers) > 0) {
                    foreach ($fichiers as $fichier) {
                        echo "<a href='/uploads/" . rawurlencode($fichier) . "' target='_blank'>" . htmlspecialchars($fichier) . "</a><br>";
                    }
                } else { echo '—'; }
            } else { echo '—'; }
            echo "</td>";

            echo "<td class='" . htmlspecialchars($statutClass) . "'>" . htmlspecialchars($statutLabel) . "</td>";
            echo "<td><div class='actions'><a href='traitementDesJustificatif.php?id=" . htmlspecialchars((string) $idAbs) . "' class='btn_justif'>Détails</a></div></td>";
            echo "</tr>";
        }
    }

    $emptyMsg  = $mode === 'historique'
        ? "<tr><td colspan='8' class='empty-message'>Aucune absence ne correspond aux critères.</td></tr>"
        : "<tr><td colspan='8' class='empty-message'>Aucune absence ne correspond aux critères de filtrage.</td></tr>";
    $rowsHtml  = trim((string) ob_get_clean());
    if ($rowsHtml === '') {
        $rowsHtml = $emptyMsg;
    }

    echo json_encode([
        'success' => true,
        'html' => $rowsHtml,
        'count' => count($periodesTotales)
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des absences.',
        'details' => $exception->getMessage()
    ]);
}
