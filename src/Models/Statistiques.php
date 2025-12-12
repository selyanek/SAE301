<?php

namespace src\Models;

use PDO;
use PDOException;

class Statistiques
{
    private $absences = [];
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Charge les absences depuis la base de données
     */
    public function chargerAbsences($filtres = [])
    {
        $this->absences = [];
        
        if (!$this->pdo) {
            return $this->absences;
        }

        try {
            // Construction de la requête SQL avec les jointures nécessaires
            $sql = "SELECT 
                        comp.nom,
                        comp.prenom,
                        e.identifiantEtu as identifiant,
                        e.formation as diplome,
                        a.date_debut,
                        a.date_fin,
                        c.type,
                        r.nom as matiere,
                        a.justifie,
                        a.motif,
                        c.evaluation as controle,
                        prof.nom as prof_nom,
                        prof.prenom as prof_prenom
                    FROM Absence a
                    JOIN Cours c ON a.idCours = c.idCours
                    JOIN Ressource r ON c.idRessource = r.idRessource
                    JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
                    JOIN Compte comp ON e.idEtudiant = comp.idCompte
                    JOIN Professeur p ON c.idProfesseur = p.idProfesseur
                    JOIN Compte prof ON p.idProfesseur = prof.idCompte
                    WHERE 1=1";
            
            $params = [];

            // Application des filtres SQL
            if (!empty($filtres['annee_but'])) {
                $sql .= " AND e.formation LIKE :formation";
                $params[':formation'] = '%' . $filtres['annee_but'] . '%';
            }

            if (!empty($filtres['type_cours'])) {
                $sql .= " AND c.type = :type";
                $params[':type'] = strtoupper($filtres['type_cours']);
            }

            if (!empty($filtres['matiere'])) {
                $sql .= " AND r.nom LIKE :matiere";
                $params[':matiere'] = '%' . $filtres['matiere'] . '%';
            }

            if (!empty($filtres['date_debut'])) {
                $sql .= " AND a.date_debut >= :date_debut";
                $params[':date_debut'] = $filtres['date_debut'];
            }

            if (!empty($filtres['date_fin'])) {
                $sql .= " AND a.date_fin <= :date_fin";
                $params[':date_fin'] = $filtres['date_fin'];
            }

            $sql .= " ORDER BY a.date_debut DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Transformer les résultats au format attendu par les méthodes existantes
            foreach ($resultats as $row) {
                $this->absences[] = [
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'identifiant' => $row['identifiant'],
                    'diplome' => $row['diplome'],
                    'date' => date('d/m/Y', strtotime($row['date_debut'])),
                    'heure' => date('G\Hi', strtotime($row['date_debut'])),
                    'duree' => $this->calculerDuree($row['date_debut'], $row['date_fin']),
                    'type' => strtoupper($row['type']),
                    'matiere' => $row['matiere'],
                    'justification' => $this->getJustificationText($row['justifie']),
                    'motif' => $row['motif'] ?? '',
                    'groupe' => '', // Non disponible dans la BDD actuelle
                    'prof' => $row['prof_nom'] . ' ' . $row['prof_prenom'],
                    'controle' => $row['controle'] ? 'Oui' : 'Non',
                    'annee_but' => $this->extractAnnee($row['diplome'])
                ];
            }

        } catch (PDOException $e) {
            error_log("Erreur lors du chargement des absences : " . $e->getMessage());
        }

        return $this->absences;
    }

    /**
     * Convertit le booléen justifie en texte
     */
    private function getJustificationText($justifie)
    {
        if ($justifie === null) {
            return 'En attente';
        }
        return $justifie ? 'Absence justifiée' : 'Non justifié';
    }

    /**
     * Extrait l'année BUT de la formation (BUT1, BUT2, BUT3)
     */
    private function extractAnnee($formation)
    {
        if (preg_match('/BUT\s*(\d)/', $formation, $matches)) {
            return 'BUT' . $matches[1];
        }
        return 'BUT1'; // Valeur par défaut
    }

    /**
     * Calcule la durée entre deux timestamps
     */
    private function calculerDuree($debut, $fin)
    {
        $diff = strtotime($fin) - strtotime($debut);
        $heures = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        return sprintf('%dh%02d', $heures, $minutes);
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
            // La date est déjà au format JJ/MM/AAAA, on la convertit en timestamp
            $dateISO = $this->convertirDateJJMMAAAA($absence['date']);
            
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
     * Convertit une date JJ/MM/AAAA en AAAA-MM-JJ
     */
    private function convertirDateJJMMAAAA($date)
    {
        $parts = explode('/', $date);
        if (count($parts) === 3) {
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        return $date;
    }

    /**
     * Liste des matières disponibles
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
     * Liste des groupes disponibles
     */
    public function getListeGroupes()
    {
        $groupes = [];
        foreach ($this->absences as $absence) {
            if (!empty($absence['groupe'])) {
                $groupes[$absence['groupe']] = true;
            }
        }
        return array_keys($groupes);
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