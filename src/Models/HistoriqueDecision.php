<?php

namespace src\Models;

use PDO;
use PDOException;

// US-9 : Modele pour l'historique des decisions
class HistoriqueDecision
{
    private $conn;
    private $table = 'Historique_Decision';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Ajoute une entree dans l'historique
    public function ajouter($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (id_absence, id_responsable, ancien_statut, ancienne_raison, 
                     ancien_verrouillage, nouveau_statut, nouvelle_raison, 
                     nouveau_verrouillage, type_action, justification, ip_address, user_agent)
                    VALUES 
                    (:id_absence, :id_responsable, :ancien_statut, :ancienne_raison,
                     :ancien_verrouillage, :nouveau_statut, :nouvelle_raison,
                     :nouveau_verrouillage, :type_action, :justification, :ip_address, :user_agent)";
            
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':id_absence', $data['id_absence'], PDO::PARAM_INT);
            $stmt->bindValue(':id_responsable', $data['id_responsable'], PDO::PARAM_INT);
            $stmt->bindValue(':ancien_statut', $data['ancien_statut'] ?? null);
            $stmt->bindValue(':ancienne_raison', $data['ancienne_raison'] ?? null);
            
            // Gestion des booleens
            if (isset($data['ancien_verrouillage']) && $data['ancien_verrouillage'] !== null) {
                $stmt->bindValue(':ancien_verrouillage', $data['ancien_verrouillage'], PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue(':ancien_verrouillage', null, PDO::PARAM_NULL);
            }
            
            $stmt->bindValue(':nouveau_statut', $data['nouveau_statut'] ?? null);
            $stmt->bindValue(':nouvelle_raison', $data['nouvelle_raison'] ?? null);
            
            if (isset($data['nouveau_verrouillage']) && $data['nouveau_verrouillage'] !== null) {
                $stmt->bindValue(':nouveau_verrouillage', $data['nouveau_verrouillage'], PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue(':nouveau_verrouillage', null, PDO::PARAM_NULL);
            }
            
            $stmt->bindValue(':type_action', $data['type_action']);
            $stmt->bindValue(':justification', $data['justification'] ?? null);
            $stmt->bindValue(':ip_address', $data['ip_address'] ?? null);
            $stmt->bindValue(':user_agent', $data['user_agent'] ?? null);
            
            $stmt->execute();
            return $this->conn->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Erreur ajouter HistoriqueDecision : " . $e->getMessage());
            return false;
        }
    }

    // Recupere l'historique d'une absence
    public function getByAbsence($idAbsence)
    {
        try {
            $sql = "SELECT h.*, c.nom as nom_responsable, c.prenom as prenom_responsable
                    FROM {$this->table} h
                    LEFT JOIN Compte c ON h.id_responsable = c.idCompte
                    WHERE h.id_absence = :id_absence
                    ORDER BY h.date_action DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_absence', $idAbsence, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getByAbsence : " . $e->getMessage());
            return [];
        }
    }

    // Compte les revisions
    public function countRevisions($idAbsence)
    {
        try {
            $sql = "SELECT COUNT(*) as nb FROM {$this->table} 
                    WHERE id_absence = :id_absence AND type_action = 'revision'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_absence', $idAbsence, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['nb'] ?? 0);
            
        } catch (PDOException $e) {
            error_log("Erreur countRevisions : " . $e->getMessage());
            return 0;
        }
    }
}
