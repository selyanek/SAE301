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
        $this->loadEnvironment();

        $this->host = $this->getRequiredEnv('DB_HOST');
        $this->port = $this->getRequiredEnv('DB_PORT');
        $this->dbname = $this->getRequiredEnv('DB_NAME');
        $this->user = $this->getRequiredEnv('DB_USER');
        $this->pass = $this->getRequiredEnv('DB_PASSWORD');

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

    private function getRequiredEnv(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            throw new \RuntimeException("Variable d'environnement manquante: {$key}");
        }

        return (string) $value;
    }

    private function loadEnvironment(): void
    {
        $projectRoot = dirname(__DIR__, 2);

        if (class_exists('Dotenv\\Dotenv')) {
            $dotenv = \Dotenv\Dotenv::createImmutable($projectRoot);
            $dotenv->safeLoad();
            return;
        }

        $envPath = $projectRoot . '/.env';
        if (!is_file($envPath) || !is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $parts = explode('=', $trimmed, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, "\"'");

            if ($key !== '' && !isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv($key . '=' . $value);
            }
        }
    }
}