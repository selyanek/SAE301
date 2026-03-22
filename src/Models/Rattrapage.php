<?php

namespace src\Models;

use PDO;
use src\Database\Database;

class Rattrapage
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getAbsencesEvaluationsPourProfesseur($idProfesseur)
    {
        $pdo = $this->db->getConnection();
        $sql = "SELECT 
                    a.idabsence,
                    a.date_debut AS absence_debut,
                    a.date_fin AS absence_fin,
                    a.motif,
                    a.justifie,
                    c.idcours,
                    c.type AS cours_type,
                    c.date_debut AS cours_date_debut,
                    c.date_fin AS cours_date_fin,
                    r.nom AS ressource_nom,
                    comp.nom AS etudiant_nom,
                    comp.prenom AS etudiant_prenom,
                    e.identifiantetu,
                    e.formation,
                    rt.idrattrapage,
                    rt.date_rattrapage,
                    rt.salle,
                    rt.remarque,
                    rt.statut AS rattrapage_statut
                FROM Cours c
                JOIN Absence a ON c.idCours = a.idCours
                JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
                JOIN Compte comp ON e.idEtudiant = comp.idCompte
                JOIN Ressource r ON c.idRessource = r.idRessource
                LEFT JOIN Rattrapage rt ON a.idAbsence = rt.idAbsence
                WHERE c.idProfesseur = :idProfesseur 
                AND c.evaluation = TRUE
                AND a.justifie = TRUE
                ORDER BY c.date_debut DESC, comp.nom, comp.prenom";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':idProfesseur' => $idProfesseur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
