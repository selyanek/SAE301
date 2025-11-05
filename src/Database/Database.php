<?php

namespace src\Database;
use PDO;
use PDOException;

class Database
{
    private string $host = "node2.liruz.fr";
    private string $port = "5435";
    private string $dbname = "sae";
    private string $user = "sae";
    private string $pass = "1zevkN&49b&&a*Pi97C";
    private PDO $pdo;

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

    public function getConnection()
    {
        return $this->pdo;
    }

    public function endConnection()
    {
        $this->pdo = null;
    }
}