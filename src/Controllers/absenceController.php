<?php

namespace src\Controllers;

use src\Models\Absence;
use src\Database\Database;


class AbsenceController
{
     private $absence;

    public function __construct() {
        $db = new Database();
        $this->absence = new Absence($db->getConnection());
    }

    public function CreerAbsence() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->absence->setIdCours($_POST['idCours']);
            $this->absence->setIdEtudiant($_POST['idEtudiant']);
            $this->absence->setDateDebut($_POST['date_debut']);
            $this->absence->setDateFin($_POST['date_fin']);
            $this->absence->setMotif($_POST['motif'] ?? null);
            $this->absence->setJustifie(isset($_POST['justifie']) ? (bool)$_POST['justifie'] : false);
            $this->absence->setUriJustificatif($_POST['uriJustificatif'] ?? null);

            $result = $this->absence->ajouterAbsence();

            if ($result) {
                echo json_encode(['message' => 'Absence ajoutée avec succès', 'id' => $result]);
            } else {
                echo json_encode(['error' => 'Erreur lors de l’ajout de l’absence']);
            }
        }
    }
}



