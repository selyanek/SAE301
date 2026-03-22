<?php

namespace src\Models;

use PDO;
use src\Database\Database;

class ProfileModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getUserByIdentifiant($identifiant)
    {
        $pdo = $this->db->getConnection();
        $stmt = $pdo->prepare('SELECT idCompte, prenom, nom, identifiantCompte, fonction, mot_de_passe FROM Compte WHERE identifiantCompte = :id');
        $stmt->execute([':id' => $identifiant]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUserPassword($identifiant, $newPassword)
    {
        $pdo = $this->db->getConnection();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE Compte SET mot_de_passe = :mdp WHERE identifiantCompte = :id');
        return $stmt->execute([':mdp' => $hashedPassword, ':id' => $identifiant]);
    }

    public function getEtudiantDetails($idCompte, $identifiant)
    {
        $pdo = $this->db->getConnection();
        if ($idCompte !== null) {
            $stmt = $pdo->prepare('SELECT formation FROM Etudiant WHERE idEtudiant = :idCompte');
            $stmt->execute([':idCompte' => $idCompte]);
        } else {
            $stmt = $pdo->prepare('SELECT formation FROM Etudiant WHERE identifiantEtu = :id');
            $stmt->execute([':id' => $identifiant]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
