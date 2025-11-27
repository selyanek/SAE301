<?php

namespace src\Models;

use PDO;
use PDOException;

class Absence
{
    private ?string $dateDebut;
    private ?string $dateFin;
    private ?string $motif;
    private ?bool $justifie;
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
    public function setJustifie(bool $justifie): void { $this->justifie = $justifie; }
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
            $stmt->bindValue(':justifie', $this->justifie, PDO::PARAM_BOOL);
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

    public function getAll() {
        try {
            $sql = "SELECT * FROM $this->table ORDER BY date_debut DESC";
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
}