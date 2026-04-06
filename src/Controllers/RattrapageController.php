<?php

namespace src\Controllers;

require_once __DIR__ . '/../Models/Rattrapage.php';
require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ . '/session_timeout.php';
require_once __DIR__ . '/Redirect.php';

use src\Models\Rattrapage;

class RattrapageController
{
    public function gererRattrapages()
    {
        $redirect = new Redirect('professeur');
        $redirect->redirect();

        $idProfesseur = $_SESSION['idCompte'] ?? null;

        if (!$idProfesseur) {
            header('Location: /public/index.php');
            exit();
        }

        $rattrapageModel = new Rattrapage();
        $absencesEvaluations = $rattrapageModel->getAbsencesEvaluationsPourProfesseur($idProfesseur);

        require __DIR__ . '/../Views/professeur/rattrapage_prof.php';
    }
}
