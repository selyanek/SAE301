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

            // Connexion à la BDD
            $db = new Database();
            $pdo = $db->getConnection();

            // Requête pour obtenir l'ID du compte
            $sql = 'SELECT idCompte FROM Compte WHERE nom = :username';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $this->username]);

            // Retour de l'ID trouvé
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['idCompte'];

        } catch (PDOException $e) {
            // Gestion des erreurs
            return "Erreur : " . $e->getMessage();
        }
    }
}