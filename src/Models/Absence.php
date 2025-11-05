<?php

namespace src\Models;
class Absence
{
    private $dateDebut;
    private $dateFin;
    private string $motif;
    private ?int $idEtudiant;
    private ?bool $justifie;
    private ?int $idCours;
    private ?string $uriJustificatif;

    // Connexion BDD
    private $conn;
    private $table = 'Absence';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function ajouterAbsence()
    {
        try {
            $sql = "INSERT INTO $this->table 
                (date_debut, date_fin, motif, uriJustificatif) 
                VALUES (:dateDebut, :dateFin, :motif, :uriJustificatif)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':date_debut' => $this->dateDebut,
                ':date_fin' => $this->dateFin,
                ':motif' => $this->motif,
                ':uriJustificatif' => $this->uriJustificatif ?? null
            ]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur d’insertion : " . $e->getMessage());
            return false;
        }
    }

    public function justifierAbsence($idAbsence)
    {
        try {
            $sql = "UPDATE $this->table SET justifie = true WHERE idabsence = :idAbsence";
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
}