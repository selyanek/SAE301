<?php
// Classe Database
// Gère la connexion à la base de données PostgreSQL
// Utilise PDO pour les interactions avec la base de données

namespace src\Database;
use PDO;
use PDOException;

class Database
{
    // Paramètres de connexion à la base de données
    private string $host = "node2.liruz.fr";
    private string $port = "5435";
    private string $dbname = "sae";
    private string $user = "sae";
    private string $pass = "1zevkN&49b&&a*Pi97C";
    private ?PDO $pdo = null;

    // Constructeur - Établit la connexion à la base de données PostgreSQL
    public function __construct()
    {
        try {
            $this->pdo = new PDO(
                "pgsql:host=$this->host;port=$this->port;dbname=$this->dbname",
                $this->user,
                $this->pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    // Retourne la connexion PDO courante
    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }

    // Ferme la connexion à la base de données
    public function endConnection(): void
    {
        $this->pdo = null;
    }
}