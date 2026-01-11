<?php
// Modèle Login
// Gère l'authentification et les informations de connexion des utilisateurs

namespace src\Models;
use PDO;

class Login
{
    private $identifiant;
    private $mot_de_passe;

    // Constructeur - Initialise l'identifiant et le mot de passe
    public function __construct($identifiant, $mot_de_passe)
    {
        $this->identifiant = $identifiant;
        $this->mot_de_passe = $mot_de_passe;
    }

    // Vérifie si les identifiants de connexion sont corrects
    public function verifierConnexion($pdo)
    {
        // Récupérer le hash du mot de passe pour cet utilisateur
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM Compte WHERE identifiantCompte = :identifiant");
        $stmt->execute([':identifiant' => $this->identifiant]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        if ($result && password_verify($this->mot_de_passe, $result['mot_de_passe'])) {
            return true;
        }
        
        return false;
    }

    // Retourne le rôle (fonction) de l'utilisateur connecté
    public function verifRole($pdo)
    {
        // Récupérer le hash du mot de passe et la fonction
        $stmt = $pdo->prepare("SELECT fonction, mot_de_passe FROM Compte WHERE identifiantCompte = :id");
        $stmt->execute([':id' => $this->identifiant]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        if ($result && password_verify($this->mot_de_passe, $result['mot_de_passe'])) {
            return $result['fonction'];
        }
        
        return false;
    }

    // Retourne le nom complet de l'utilisateur connecté
    public function getName($pdo)
    {
        // Récupérer le hash du mot de passe, nom et prénom
        $stmt = $pdo->prepare("SELECT nom, prenom, mot_de_passe FROM Compte WHERE identifiantCompte = :id");
        $stmt->execute([':id' => $this->identifiant]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        if ($result && password_verify($this->mot_de_passe, $result['mot_de_passe'])) {
            return $result['prenom'] . ' ' . $result['nom'];
        }
        
        return false;
    }

    // Retourne le mot de passe de l'utilisateur
    public function getPwd($pdo)
    {
        // Récupérer le hash du mot de passe
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM Compte WHERE identifiantCompte = :id");
        $stmt->execute([':id' => $this->identifiant]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        if ($result && password_verify($this->mot_de_passe, $result['mot_de_passe'])) {
            return $result['mot_de_passe'];
        }
        
        return false;
    }

    public function getRole($pdo)
    {
        $stmt = $pdo->prepare("SELECT fonction FROM Compte WHERE identifiantCompte = :id");
        $stmt->execute([
            ':id' => $this->identifiant
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['fonction'] : false;
    }

    public function getIdUtilisateur($pdo)
    {
        $stmt = $pdo->prepare("SELECT idcompte FROM compte WHERE identifiantcompte = :identifiant");
        $stmt->execute([':identifiant' => $this->identifiant]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function getIdentifiantEtu($pdo)
    {
        $stmt = $pdo->prepare("
            SELECT e.identifiantEtu 
            FROM Etudiant e 
            JOIN Compte c ON e.idEtudiant = c.idCompte 
            WHERE c.identifiantCompte = :identifiant
        ");
        $stmt->execute([':identifiant' => $this->identifiant]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['identifiantetu'] : null;
    }
    /**
     * Retourne toutes les données utilisateur en une fois
     * Évite les multiples requêtes à la base de données
     */
    public function getAllUserData($pdo)
    {
        $stmt = $pdo->prepare("
            SELECT 
                c.idcompte,
                c.identifiantCompte,
                c.nom,
                c.prenom,
                c.fonction,
                c.mot_de_passe,
                e.identifiantEtu
            FROM Compte c
            LEFT JOIN Etudiant e ON c.idCompte = e.idEtudiant
            WHERE c.identifiantCompte = :identifiant 
            AND c.mot_de_passe = :mdp
        ");
        
        $stmt->execute([
            ':identifiant' => $this->identifiant,
            ':mdp' => $this->mot_de_passe
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}