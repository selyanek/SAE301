<?php

namespace src\Models;

use PDO;
use PDOException;

class Absence
{
    private ?string $dateDebut;
    private ?string $dateFin;
    private ?string $motif;
    private ?bool $justifie; // null = en attente, true = validé, false = refusé
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
                // Si refusé avec une raison
                $sql = "UPDATE $this->table SET justifie = :value, raison_refus = :raisonRefus WHERE idabsence = :idAbsence";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':value', $value, PDO::PARAM_BOOL);
                $stmt->bindValue(':raisonRefus', trim($raisonRefus), PDO::PARAM_STR);
                $stmt->bindValue(':idAbsence', $idAbsence, PDO::PARAM_INT);
            } else {
                // Si validé ou refusé sans raison
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
            $sql = "SELECT a.*, e.identifiantEtu, comp.nom AS nomCompte, comp.prenom AS prenomCompte
                    FROM $this->table a
                    JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
                    JOIN Compte comp ON e.idEtudiant = comp.idCompte
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
}