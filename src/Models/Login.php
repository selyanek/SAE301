<?php

namespace src\Models;
use PDO;

class Login
{
    private $identifiant;
    private $mot_de_passe;

    public function __construct($identifiant, $mot_de_passe)
    {
        $this->identifiant = $identifiant;
        $this->mot_de_passe = $mot_de_passe;
    }

    public function verifierConnexion($pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM Compte WHERE identifiantCompte = :identifiant AND mot_de_passe = :mdp");
        $stmt->execute([
            ':identifiant' => $this->identifiant,
            ':mdp' => $this->mot_de_passe
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function verifRole($pdo)
    {
        $stmt = $pdo->prepare("SELECT fonction FROM Compte WHERE identifiantCompte = :id AND mot_de_passe = :mdp");
        $stmt->execute([
            ':id' => $this->identifiant,
            ':mdp' => $this->mot_de_passe
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['fonction'] : false;
    }

    public function getName($pdo)
    {
        $stmt = $pdo->prepare("SELECT nom, prenom FROM Compte WHERE identifiantCompte = :id AND mot_de_passe = :mdp");
        $stmt->execute([
            ':id' => $this->identifiant,
            ':mdp' => $this->mot_de_passe
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['prenom'] . ' ' . $result['nom'] : false;
    }

    public function getPwd($pdo)
    {
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM Compte WHERE identifiantCompte = :id AND mot_de_passe = :mdp");
        $stmt->execute([
            ':id' => $this->identifiant,
            ':mdp' => $this->mot_de_passe
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['mot_de_passe'] : false;
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
}