<?php
class Database {
    private $host = "node2.liruz.fr";
    private $port = "5435";
    private $dbname = "sae";
    private $user = "sae";
    private $pass = "1zevkN&49b&&a*Pi97C";
    private $pdo;

    public function __construct() {
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
        return $this->pdo;
    }
}