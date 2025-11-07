<?php

namespace src\Controllers;
class Redirect
{
    private $roleNecessaire;

    public function __construct($roleNecessaire)
    {
        $this->roleNecessaire = $roleNecessaire;
    }

    public function redirect()
    {
        if (!isset($_SESSION['login']) || !isset($_SESSION['role'])) {
            header('Location:../Views/index.php');
            exit();
        }
        if ($_SESSION['role'] !== $this->roleNecessaire) {
            switch ($_SESSION['role']) {
                case 'etudiante':
                    header('Location: ../Views/etudiant/dashbord.php');
                    exit();
                case 'professeur':
                    header('Location: ../Views/professeur/dashboard.php');
                    exit();
                case 'responsable_pedagogique':
                    header('Location: ../Views/reponsable/dashbord.php');
                    exit();
                default:
                    header('Location: index.php');
                    exit();
            }
        }
    }
}