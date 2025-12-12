<?php

namespace src\Models;

class Statistiques
{
    private $absences = [];
    private $cheminCSV;

    public function __construct($pdo = null)
    {
        // On n'utilise plus PDO, mais on garde le paramètre pour compatibilité
        $this->cheminCSV = __DIR__ . '/../../data/CSV/';
    }

    /**
     * Charge les absences depuis les fichiers CSV VT
     */
    public function chargerAbsences($filtres = [])
    {
        $this->absences = [];
        
        // Liste des fichiers CSV à charger
        $fichiers = [
            'BUT1' => $this->cheminCSV . 'BUT1-240122-240223_anonymise.CSV',
            'BUT2' => $this->cheminCSV . 'BUT2-240122-240223_anonymise.CSV',
            'BUT3' => $this->cheminCSV . 'BUT3-240122-240223_anonymise.CSV'
        ];

        foreach ($fichiers as $annee => $fichier) {
            // Filtre par année BUT si spécifié
            if (!empty($filtres['annee_but']) && $filtres['annee_but'] !== $annee) {
                continue;
            }

            if (file_exists($fichier)) {
                $this->lireCSV($fichier, $annee, $filtres);
            }
        }

        return $this->absences;
    }

    /**
     * Lit un fichier CSV et extrait les absences
     */
    private function lireCSV($fichier, $annee, $filtres)
    {
        $handle = fopen($fichier, 'r');
        if (!$handle) return;

        // Sauter la ligne d'en-tête (PHP 8.4+ compatible)
        $header = fgetcsv($handle, 0, ';', '"', '');

        while (($ligne = fgetcsv($handle, 0, ';', '"', '')) !== false) {
            if (count($ligne) < 24) continue;

            // Extraction des colonnes importantes
            $absence = [
                'nom' => $ligne[0],
                'prenom' => $ligne[1],
                'identifiant' => $ligne[4],
                'diplome' => $ligne[5],
                'date' => $ligne[8],           // Format: JJ/MM/AAAA
                'heure' => $ligne[9],          // Format: 8H00, 9H30...
                'duree' => $ligne[10],
                'type' => strtoupper($ligne[11]),  // CM, TD, TP, DS, BEN
                'matiere' => $this->simplifierMatiere($ligne[12]),
                'justification' => $ligne[17], // "Absence justifiée" ou "Non justifié"
                'motif' => $ligne[18],
                'groupe' => $ligne[20],
                'prof' => $ligne[22],
                'controle' => $ligne[23],      // Oui/Non = évaluation
                'annee_but' => $annee
            ];

            // Application des filtres
            if (!$this->appliquerFiltres($absence, $filtres)) {
                continue;
            }

            $this->absences[] = $absence;
        }

        fclose($handle);
    }

    /**
     * Simplifie le nom de la matière (enlève les codes)
     */
    private function simplifierMatiere($matiere)
    {
        // Extrait juste le nom principal avant les parenthèses
        if (preg_match('/^[A-Z]+-(.+?)\s*\(/', $matiere, $matches)) {
            return trim($matches[1]);
        }
        return $matiere;
    }

    /**
     * Applique les filtres sur une absence
     */
    private function appliquerFiltres($absence, $filtres)
    {
        // Filtre par type de cours
        if (!empty($filtres['type_cours']) && $absence['type'] !== strtoupper($filtres['type_cours'])) {
            return false;
        }

        // Filtre par matière
        if (!empty($filtres['matiere']) && stripos($absence['matiere'], $filtres['matiere']) === false) {
            return false;
        }

        // Filtre par groupe
        if (!empty($filtres['groupe']) && stripos($absence['groupe'], $filtres['groupe']) === false) {
            return false;
        }

        // Filtre par date début
        if (!empty($filtres['date_debut'])) {
            $dateAbsence = $this->convertirDate($absence['date']);
            if ($dateAbsence < $filtres['date_debut']) {
                return false;
            }
        }

        // Filtre par date fin
        if (!empty($filtres['date_fin'])) {
            $dateAbsence = $this->convertirDate($absence['date']);
            if ($dateAbsence > $filtres['date_fin']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convertit une date JJ/MM/AAAA en AAAA-MM-JJ
     */
    private function convertirDate($date)
    {
        $parts = explode('/', $date);
        if (count($parts) === 3) {
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        return $date;
    }

    /**
     * Calcule les statistiques globales
     */
    public function calculerStatistiquesGlobales()
    {
        if (empty($this->absences)) {
            return [
                'total' => 0,
                'justifiees' => 0,
                'non_justifiees' => 0,
                'evaluations' => 0
            ];
        }

        $total = count($this->absences);
        $justifiees = 0;
        $non_justifiees = 0;
        $evaluations = 0;

        foreach ($this->absences as $absence) {
            // Vérifier si justifiée
            if (stripos($absence['justification'], 'justifiée') !== false) {
                $justifiees++;
            } else {
                $non_justifiees++;
            }

            // Vérifier si évaluation (contrôle = Oui ou type = DS)
            if ($absence['controle'] === 'Oui' || $absence['type'] === 'DS') {
                $evaluations++;
            }
        }

        return [
            'total' => $total,
            'justifiees' => $justifiees,
            'non_justifiees' => $non_justifiees,
            'evaluations' => $evaluations
        ];
    }

    /**
     * Répartition par type de cours (CM, TD, TP, DS, BEN)
     */
    public function getRepartitionParType()
    {
        if (empty($this->absences)) {
            return [];
        }

        $repartition = [];
        foreach ($this->absences as $absence) {
            $type = $absence['type'];
            if (!isset($repartition[$type])) {
                $repartition[$type] = 0;
            }
            $repartition[$type]++;
        }

        return $repartition;
    }

    /**
     * Répartition par matière (top 10)
     */
    public function getRepartitionParMatiere()
    {
        if (empty($this->absences)) {
            return [];
        }

        $repartition = [];
        foreach ($this->absences as $absence) {
            $matiere = $absence['matiere'];
            if (!isset($repartition[$matiere])) {
                $repartition[$matiere] = 0;
            }
            $repartition[$matiere]++;
        }

        arsort($repartition);
        return array_slice($repartition, 0, 10, true);
    }

    /**
     * Répartition par heure de la journée
     */
    public function getRepartitionParHeure()
    {
        $tranches = [
            '8h-9h30' => 0,
            '9h30-11h' => 0,
            '11h-12h30' => 0,
            '14h-15h30' => 0,
            '15h30-17h' => 0,
            '17h-18h30' => 0
        ];

        foreach ($this->absences as $absence) {
            $heure = $this->extraireHeure($absence['heure']);
            
            if ($heure >= 8 && $heure < 9.5) {
                $tranches['8h-9h30']++;
            } elseif ($heure >= 9.5 && $heure < 11) {
                $tranches['9h30-11h']++;
            } elseif ($heure >= 11 && $heure < 12.5) {
                $tranches['11h-12h30']++;
            } elseif ($heure >= 14 && $heure < 15.5) {
                $tranches['14h-15h30']++;
            } elseif ($heure >= 15.5 && $heure < 17) {
                $tranches['15h30-17h']++;
            } elseif ($heure >= 17 && $heure <= 18.5) {
                $tranches['17h-18h30']++;
            }
        }

        return $tranches;
    }

    /**
     * Extrait l'heure numérique de "8H00", "9H30", etc.
     */
    private function extraireHeure($heureStr)
    {
        if (preg_match('/(\d+)H(\d+)?/', $heureStr, $matches)) {
            $h = (int)$matches[1];
            $m = isset($matches[2]) ? (int)$matches[2] : 0;
            return $h + ($m / 60);
        }
        return 0;
    }

    /**
     * Tendances d'absences (par mois)
     */
    public function getTendances($granularite = 'mois')
    {
        if (empty($this->absences)) {
            return [];
        }

        $tendances = [];
        
        foreach ($this->absences as $absence) {
            $dateISO = $this->convertirDate($absence['date']);
            
            if ($granularite === 'mois') {
                $mois = (int)date('n', strtotime($dateISO));
                $annee = date('Y', strtotime($dateISO));
                $periode = $this->getMoisFrancais($mois) . ' ' . $annee;
            } else {
                $periode = $dateISO;
            }

            if (!isset($tendances[$periode])) {
                $tendances[$periode] = 0;
            }
            $tendances[$periode]++;
        }

        return $tendances;
    }

    /**
     * Liste des matières disponibles (depuis les CSV)
     */
    public function getListeMatieres()
    {
        $matieres = [];
        foreach ($this->absences as $absence) {
            $matieres[$absence['matiere']] = true;
        }
        return array_keys($matieres);
    }

    /**
     * Liste des groupes disponibles (depuis les CSV)
     */
    public function getListeGroupes()
    {
        $groupes = [];
        foreach ($this->absences as $absence) {
            $groupes[$absence['groupe']] = true;
        }
        return array_keys($groupes);
    }

    /**
     * Rattrapages à planifier (absences justifiées lors d'évaluations)
     */
    public function getRattrapages()
    {
        $rattrapages = [];

        foreach ($this->absences as $absence) {
            // Seulement les absences justifiées lors d'évaluations
            $estJustifiee = stripos($absence['justification'], 'justifiée') !== false;
            $estEvaluation = $absence['controle'] === 'Oui' || $absence['type'] === 'DS';

            if ($estJustifiee && $estEvaluation) {
                $cle = $absence['matiere'] . '|' . $absence['type'];
                if (!isset($rattrapages[$cle])) {
                    $rattrapages[$cle] = [
                        'ressource' => $absence['matiere'],
                        'type_cours' => $absence['type'],
                        'nb_etudiants' => 0,
                        'statut' => 'À planifier'
                    ];
                }
                $rattrapages[$cle]['nb_etudiants']++;
            }
        }

        // Trier par nombre d'étudiants
        usort($rattrapages, function($a, $b) {
            return $b['nb_etudiants'] - $a['nb_etudiants'];
        });

        return array_values($rattrapages);
    }

    /**
     * Formater les données pour Chart.js
     */
    public function getDonneesAPI()
    {
        return [
            'tendances' => $this->getTendances(),
            'matieres' => $this->getRepartitionParMatiere(),
            'types' => $this->getRepartitionParType(),
            'heures' => $this->getRepartitionParHeure()
        ];
    }

    /**
     * Convertir numéro de mois en français
     */
    private function getMoisFrancais($numero)
    {
        $mois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        return $mois[$numero] ?? '';
    }
}