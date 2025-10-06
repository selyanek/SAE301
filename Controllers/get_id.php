<?php
// get_id.php
// (à compléter)
// Ce script récupère l'ID d'un étudiant à partir de son nom d'utilisateur, qui sera affiché sur la page d'accueil.
require 'Database.php';

class Get_ID {

    private string $username; // Nom d'utilisateur : à récupérer depuis la session ou un autre moyen

    public function __construct(string $username) {
        $this->username = $username; // Initialiser le nom d'utilisateur
    }

    public function fetchIdByUsername(): string|int {

        try {
            $db = new Database();
            $pdo = $db->getConnection();
            $sql = 'SELECT idCompte FROM Compte WHERE nom = :username';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $this->username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['idCompte'];
        } catch (PDOException $e) {
            return "Erreur : " . $e->getMessage();
        }
    }
}