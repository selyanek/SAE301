<?php
// Classe pour gérer la connexion à la base de données PostgreSQL
class Database {
    private $host = "node2.liruz.fr";
    private $port = "5435";
    private $dbname = "sae";
    private $user = "sae";
    private $pass = "1zevkN&49b&&a*Pi97C";
    private $pdo;

    public function __construct() {
        // Initialise la connexion PDO et gère les erreurs de connexion
        try {
            $this->pdo = new PDO(
                "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}",
                $this->user,
                $this->pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function getConnection() {
        // Retourne la connexion PDO
        return $this->pdo;
    }

    public function endConnection() {
        // Ferme la connexion en mettant l'objet PDO à null
        $this->pdo = null;
    }
}
