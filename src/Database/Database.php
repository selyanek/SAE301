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
    private string $host;
    private string $port;
    private string $dbname;
    private string $user;
    private string $pass;
    private ?PDO $pdo = null;

    // Constructeur - Établit la connexion à la base de données PostgreSQL
    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'];
        $this->port = $_ENV['DB_PORT'];
        $this->dbname = $_ENV['DB_NAME'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASSWORD'];
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