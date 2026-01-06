<?php
// Modèle Absence
// Gère les opérations CRUD pour les absences des étudiants

namespace src\Models;

use PDO;
use PDOException;

class Absence
{
    // Propriétés de l'absence
    private ?string $dateDebut;
    private ?string $dateFin;
    private ?string $motif;
    private ?bool $justifie; // null = en attente, true = validé, false = refusé
    private ?int $idEtudiant;
    private ?int $idCours;
    private ?string $uriJustificatif;

    private $conn;
    private $table = 'Absence';

    // Constructeur - Initialise la connexion à la base de données
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Setters pour définir les propriétés de l'absence
    public function setDateDebut(string $dateDebut): void { $this->dateDebut = $dateDebut; }
    public function setDateFin(string $dateFin): void { $this->dateFin = $dateFin; }
    public function setMotif(?string $motif): void { $this->motif = $motif; }
    public function setJustifie(?bool $justifie): void { $this->justifie = $justifie; }
    public function setIdEtudiant(int $idEtudiant): void { $this->idEtudiant = $idEtudiant; }
    public function setIdCours(int $idCours): void { $this->idCours = $idCours; }
    public function setUriJustificatif(?string $uriJustificatif): void { $this->uriJustificatif = $uriJustificatif; }

    // Ajoute une nouvelle absence dans la base de données
    public function ajouterAbsence()
    {
        try {
            $sql = "INSERT INTO absence
                (idcours, idetudiant, date_debut, date_fin, motif, justifie, urijustificatif)
                VALUES (:idCours, :idEtudiant, :date_debut, :date_fin, :motif, :justifie, :uriJustificatif)";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(':idCours', $this->idCours, PDO::PARAM_INT);
            $stmt->bindValue(':idEtudiant', $this->idEtudiant, PDO::PARAM_INT);
            $stmt->bindValue(':date_debut', $this->dateDebut);
            $stmt->bindValue(':date_fin', $this->dateFin);
            $stmt->bindValue(':motif', $this->motif);
            // Si justifie est null, on utilise PDO::PARAM_NULL
            if ($this->justifie === null) {
                $stmt->bindValue(':justifie', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':justifie', $this->justifie, PDO::PARAM_BOOL);
            }
            $stmt->bindValue(':uriJustificatif', $this->uriJustificatif);

            $stmt->execute();

            return $this->conn->lastInsertId();

        } catch (PDOException $e) {
            error_log("Erreur d'insertion dans Absence : " . $e->getMessage());
            throw new \Exception("Erreur BDD: " . $e->getMessage());
        }
    }

    // Marque une absence comme justifiée
    public function justifierAbsence($idAbsence)
    {
        try {
            $sql = "UPDATE Absence SET justifie = true WHERE idabsence = :idAbsence";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':idAbsence' => $idAbsence]);
        } catch (PDOException $e) {
            error_log("Erreur Update Justifie : " . $e->getMessage());
            return false;
        }
    }

    // Met à jour le statut de justification d'une absence (valider/refuser)
    public function updateJustifie($idAbsence, bool $value, ?string $raisonRefus = null, ?string $typeRefus = null)
    {
        try {
            if ($value === false && $raisonRefus !== null && trim($raisonRefus) !== '') {
                // Si refusé avec une raison et type de refus - réinitialiser revision à false
                $sql = "UPDATE $this->table SET justifie = :value, raison_refus = :raisonRefus, type_refus = :typeRefus, revision = false WHERE idabsence = :idAbsence";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':value', $value, PDO::PARAM_BOOL);
                $stmt->bindValue(':raisonRefus', trim($raisonRefus), PDO::PARAM_STR);
                $stmt->bindValue(':typeRefus', $typeRefus, PDO::PARAM_STR);
                $stmt->bindValue(':idAbsence', $idAbsence, PDO::PARAM_INT);
            } else {
                // Si validé ou refusé sans raison - réinitialiser revision à false
                $sql = "UPDATE $this->table SET justifie = :value, revision = false WHERE idabsence = :idAbsence";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':value', $value, PDO::PARAM_BOOL);
                $stmt->bindValue(':idAbsence', $idAbsence, PDO::PARAM_INT);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur Update Justifie (value) : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour l'état de révision d'une absence
     * Utilisé quand le responsable demande un justificatif supplémentaire
     */
    public function setEnRevision($idAbsence, bool $value)
    {
        try {
            $sql = "UPDATE $this->table SET revision = :value WHERE idabsence = :idAbsence";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':value', $value, PDO::PARAM_BOOL);
            $stmt->bindValue(':idAbsence', $idAbsence, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur setEnRevision : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Permet à l'étudiant de modifier une absence refusée avec possibilité de ressoumission
     * Remet l'absence en attente (justifie = NULL) et efface le type de refus
     */
    public function resoumettre($idAbsence, $nouveauMotif, $nouvelleUriJustificatif)
    {
        try {
            $sql = "UPDATE $this->table 
                    SET motif = :motif, 
                        urijustificatif = :uriJustificatif, 
                        justifie = NULL, 
                        type_refus = NULL,
                        raison_refus = NULL
                    WHERE idabsence = :idAbsence 
                    AND justifie = false 
                    AND type_refus = 'ressoumission'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':motif', $nouveauMotif, PDO::PARAM_STR);
            $stmt->bindValue(':uriJustificatif', $nouvelleUriJustificatif, PDO::PARAM_STR);
            $stmt->bindValue(':idAbsence', $idAbsence, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur resoumettre : " . $e->getMessage());
            return false;
        }
    }

    // Récupère toutes les absences avec les informations associées (étudiant, cours, ressource)
    public function getAll() {
        try {
            $sql = "SELECT a.*, e.identifiantEtu, c.type AS cours_type, r.nom AS ressource_nom, c.date_debut AS cours_date_debut, comp.nom AS nomCompte, comp.prenom AS prenomCompte
                    FROM $this->table a
                    JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
                    JOIN Compte comp ON e.idEtudiant = comp.idCompte
                    JOIN Cours c ON a.idCours = c.idCours
                    LEFT JOIN Ressource r ON c.idRessource = r.idRessource
                    ORDER BY a.date_debut DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            return [];
        }
    }

    // Récupère la durée d'une absence (date début et fin)
    public function getDuree($idAbsence) {
        try {
            $sql = "SELECT date_debut, date_fin 
                    FROM $this->table 
                    WHERE idabsence = :idAbsence
                   ";
            $stmt  = $this->conn->prepare($sql);
            $stmt->execute([':idAbsence' => $idAbsence]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL : " . $e->getMessage());
            return [];
        }
    }

    // Récupère une absence spécifique par son ID avec toutes les informations associées
    public function getById($idAbsence)
    {
        try {
            $sql = "SELECT a.*, e.identifiantEtu, comp.nom AS nomCompte, comp.prenom AS prenomCompte, comp.identifiantCompte, c.type AS cours_type, r.nom AS ressource_nom
                    FROM $this->table a
                    JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
                    JOIN Compte comp ON e.idEtudiant = comp.idCompte
                    JOIN Cours c ON a.idCours = c.idCours
                    LEFT JOIN Ressource r ON c.idRessource = r.idRessource
                    WHERE a.idAbsence = :idAbsence";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':idAbsence' => $idAbsence]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getById Absence : " . $e->getMessage());
            return null;
        }
    }

    public function getByStudentIdentifiant(string $identifiantEtu)
    {
        try {
            $sql = "SELECT a.*, e.identifiantEtu, comp.nom AS nomCompte, comp.prenom AS prenomCompte, c.type AS cours_type, r.nom AS ressource_nom
                    FROM $this->table a
                    JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
                    JOIN Compte comp ON e.idEtudiant = comp.idCompte
                    JOIN Cours c ON a.idCours = c.idCours
                    LEFT JOIN Ressource r ON c.idRessource = r.idRessource
                    WHERE e.identifiantEtu = :identifiantEtu
                    ORDER BY a.date_debut DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':identifiantEtu' => $identifiantEtu]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getByStudentIdentifiant Absence : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre de périodes d'absences en attente de traitement (justifie IS NULL)
     * Les absences consécutives (moins de 24h d'écart) sont regroupées en une seule période
     * @return int Nombre de périodes d'absences en attente
     */
    public function countEnAttente(): int
    {
        try {
            // Récupérer toutes les absences en attente, triées par étudiant et date
            $sql = "SELECT idabsence, idetudiant, date_debut, date_fin
                    FROM $this->table 
                    WHERE justifie IS NULL
                    ORDER BY idetudiant, date_debut";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($absences)) {
                return 0;
            }
            
            // Regrouper par étudiant
            $absencesParEtudiant = [];
            foreach ($absences as $absence) {
                $idEtudiant = $absence['idetudiant'];
                if (!isset($absencesParEtudiant[$idEtudiant])) {
                    $absencesParEtudiant[$idEtudiant] = [];
                }
                $absencesParEtudiant[$idEtudiant][] = $absence;
            }
            
            // Compter les périodes regroupées
            $nombrePeriodes = 0;
            foreach ($absencesParEtudiant as $absencesEtudiant) {
                $periodeActuelle = null;
                
                foreach ($absencesEtudiant as $absence) {
                    $debutActuel = strtotime($absence['date_debut']);
                    $finActuelle = strtotime($absence['date_fin']);
                    
                    if ($periodeActuelle === null) {
                        // Première absence, créer une nouvelle période
                        $periodeActuelle = [
                            'date_debut' => $absence['date_debut'],
                            'date_fin' => $absence['date_fin']
                        ];
                    } else {
                        // Vérifier si cette absence est consécutive (moins de 24h d'écart)
                        $finPeriode = strtotime($periodeActuelle['date_fin']);
                        $ecart = $debutActuel - $finPeriode;
                        
                        if ($ecart <= 86400) { // 24h
                            // Fusionner avec la période actuelle
                            $periodeActuelle['date_fin'] = $absence['date_fin'];
                        } else {
                            // Nouvelle période non consécutive
                            $nombrePeriodes++;
                            $periodeActuelle = [
                                'date_debut' => $absence['date_debut'],
                                'date_fin' => $absence['date_fin']
                            ];
                        }
                    }
                }
                
                // Ajouter la dernière période
                if ($periodeActuelle !== null) {
                    $nombrePeriodes++;
                }
            }
            
            return $nombrePeriodes;
            
        } catch (PDOException $e) {
            error_log("Erreur countEnAttente Absence : " . $e->getMessage());
            return 0;
        }
    }
}