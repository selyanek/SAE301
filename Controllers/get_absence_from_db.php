<?php
// get_id.php
// (à compléter)
// Ce script récupère toutes les informations d'absence.
require 'Database.php';

class Get_Absence {


    public function fetchAbsences(): array|string {

        try {
            $db = new Database();
            $pdo = $db->getConnection();
            $sql = 'SELECT * FROM Absence WHERE justifie = FALSE ORDER BY date_debut DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            return "Erreur : " . $e->getMessage();
        }
    }
}