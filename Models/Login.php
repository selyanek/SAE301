<?php 
class Login {
    private $identifiant;
    private $mot_de_passe;

    public function __construct($identifiant, $mot_de_passe) {
        $this->identifiant = $identifiant;
        $this->mot_de_passe = $mot_de_passe;
    }

    public function verifierConnexion($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM Compte WHERE idCompte = :id AND mot_de_passe = :mdp");
        $stmt->execute([
            ':id' => $this->identifiant,
            ':mdp' => $this->mot_de_passe
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function verifRole($pdo) {
        $stmt = $pdo->prepare("SELECT fonction FROM Compte WHERE idCompte = :id AND mot_de_passe = :mdp");
        $stmt->execute([
            ':id' => $this->identifiant,
            ':mdp' => $this->mot_de_passe
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['fonction'] : false;
    }
}