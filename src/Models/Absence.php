<?php

namespace src\Models;

use PDO;
use PDOException;

class Absence
{
    private ?string $dateDebut;
    private ?string $dateFin;
    private ?string $motif;
    private ?bool $justifie; // null = en attente, true = validÃ©, false = refusÃ©
    private ?int $idEtudiant;
    private ?int $idCours;
    private ?string $uriJustificatif;

    private $conn;
    private $table = 'Absence';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // --- SETTERS ---
    public function setDateDebut(string $dateDebut): void { $this->dateDebut = $dateDebut; }
    public function setDateFin(string $dateFin): void { $this->dateFin = $dateFin; }
    public function setMotif(?string $motif): void { $this->motif = $motif; }
    public function setJustifie(?bool $justifie): void { $this->justifie = $justifie; } // Accepte null
    public function setIdEtudiant(int $idEtudiant): void { $this->idEtudiant = $idEtudiant; }
    public function setIdCours(int $idCours): void { $this->idCours = $idCours; }
    public function setUriJustificatif(?string $uriJustificatif): void { $this->uriJustificatif = $uriJustificatif; }

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

    public function updateJustifie($idAbsence, bool $value, ?string $raisonRefus = null)
    {
        try {
            if ($value === false && $raisonRefus !== null && trim($raisonRefus) !== '') {
                // Si refusÃ© avec une raison
                $sql = "UPDATE $this->table SET justifie = :value, raison_refus = :raisonRefus WHERE idabsence = :idAbsence";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':value', $value, PDO::PARAM_BOOL);
                $stmt->bindValue(':raisonRefus', trim($raisonRefus), PDO::PARAM_STR);
                $stmt->bindValue(':idAbsence', $idAbsence, PDO::PARAM_INT);
            } else {
                // Si validÃ© ou refusÃ© sans raison
                $sql = "UPDATE $this->table SET justifie = :value WHERE idabsence = :idAbsence";
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
     * Compte le nombre de pÃ©riodes d'absences en attente de traitement (justifie IS NULL)
     * Les absences consÃ©cutives (moins de 24h d'Ã©cart) sont regroupÃ©es en une seule pÃ©riode
     * @return int Nombre de pÃ©riodes d'absences en attente
     */
    public function countEnAttente(): int
    {
        try {
            // RÃ©cupÃ©rer toutes les absences en attente, triÃ©es par Ã©tudiant et date
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
            
            // Regrouper par Ã©tudiant
            $absencesParEtudiant = [];
            foreach ($absences as $absence) {
                $idEtudiant = $absence['idetudiant'];
                if (!isset($absencesParEtudiant[$idEtudiant])) {
                    $absencesParEtudiant[$idEtudiant] = [];
                }
                $absencesParEtudiant[$idEtudiant][] = $absence;
            }
            
            // Compter les pÃ©riodes regroupÃ©es
            $nombrePeriodes = 0;
            foreach ($absencesParEtudiant as $absencesEtudiant) {
                $periodeActuelle = null;
                
                foreach ($absencesEtudiant as $absence) {
                    $debutActuel = strtotime($absence['date_debut']);
                    $finActuelle = strtotime($absence['date_fin']);
                    
                    if ($periodeActuelle === null) {
                        // PremiÃ¨re absence, crÃ©er une nouvelle pÃ©riode
                        $periodeActuelle = [
                            'date_debut' => $absence['date_debut'],
                            'date_fin' => $absence['date_fin']
                        ];
                    } else {
                        // VÃ©rifier si cette absence est consÃ©cutive (moins de 24h d'Ã©cart)
                        $finPeriode = strtotime($periodeActuelle['date_fin']);
                        $ecart = $debutActuel - $finPeriode;
                        
                        if ($ecart <= 86400) { // 24h
                            // Fusionner avec la pÃ©riode actuelle
                            $periodeActuelle['date_fin'] = $absence['date_fin'];
                        } else {
                            // Nouvelle pÃ©riode non consÃ©cutive
                            $nombrePeriodes++;
                            $periodeActuelle = [
                                'date_debut' => $absence['date_debut'],
                                'date_fin' => $absence['date_fin']
                            ];
                        }
                    }
                }
                
                // Ajouter la derniÃ¨re pÃ©riode
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

    // =============================================
    // US-9 : Verrouillage et revision des decisions
    // =============================================

    /**
     * Verrouille une decision (empeche l'etudiant de resoumettre)
     */
    public function verrouiller($idAbsence, $idResponsable)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET verrouille = TRUE, 
                        date_decision = NOW(),
                        id_responsable_decision = :idResp
                    WHERE idAbsence = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $idAbsence, PDO::PARAM_INT);
            $stmt->bindValue(':idResp', $idResponsable, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur verrouiller : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deverrouille une decision (permet a l'etudiant de resoumettre)
     */
    public function deverrouiller($idAbsence)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET verrouille = FALSE 
                    WHERE idAbsence = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $idAbsence, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur deverrouiller : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifie si une absence est verrouillee
     */
    public function isVerrouilleById($idAbsence)
    {
        try {
            $sql = "SELECT verrouille FROM {$this->table} WHERE idAbsence = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $idAbsence, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (bool)$result['verrouille'] : false;
        } catch (PDOException $e) {
            error_log("Erreur isVerrouilleById : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre une absence en révision ou retirer la révision
     * @param int $idAbsence
     * @param bool $value
     * @return bool
     */
    public function setEnRevision($idAbsence, bool $value)
    {
        try {
            $sql = "UPDATE {$this->table} SET revision = :rev WHERE idAbsence = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':rev', $value, PDO::PARAM_BOOL);
            $stmt->bindValue(':id', $idAbsence, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur setEnRevision : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revise une decision (change le statut justifie et la raison)
     */
    public function reviserDecision($idAbsence, $nouveauStatut, $nouvelleRaison, $idResponsable)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET justifie = :statut,
                        raison_refus = :raison,
                        date_decision = NOW(),
                        id_responsable_decision = :idResp
                    WHERE idAbsence = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $idAbsence, PDO::PARAM_INT);
            $stmt->bindValue(':idResp', $idResponsable, PDO::PARAM_INT);
            $stmt->bindValue(':raison', $nouvelleRaison);
            
            // Gestion du statut null/true/false
            if ($nouveauStatut === null) {
                $stmt->bindValue(':statut', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':statut', $nouveauStatut, PDO::PARAM_BOOL);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur reviserDecision : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recupere les absences verrouillees d'un etudiant
     */
    public function getAbsencesVerrouilleesEtudiant($idEtudiant)
    {
        try {
            $sql = "SELECT idAbsence, date_debut, date_fin, verrouille
                    FROM {$this->table}
                    WHERE idEtudiant = :idEtu AND verrouille = TRUE";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':idEtu', $idEtudiant, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getAbsencesVerrouilleesEtudiant : " . $e->getMessage());
            return [];
        }
    }
}