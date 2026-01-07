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
            // redirect to public index (login page)
            header('Location: /index.php');
            exit();
        }
        if ($_SESSION['role'] !== $this->roleNecessaire) {
            switch ($_SESSION['role']) {
                case 'etudiant':
                    header('Location: /src/Views/etudiant/dashbord.php');
                    exit();
                case 'professeur':
                    // redirect to the professor's accueil page
                    header('Location: /src/Views/accueil_prof.php');
                    exit();
                case 'responsable_pedagogique':
                    // redirect to the responsable's dashboard page
                    header('Location: /src/Views/responsable/dashbord.php');
                    exit();
                case 'secretaire':
                    // redirect to the secretaire's dashboard page
                    header('Location: /src/Views/secretaire/dashboard.php');
                    exit();
                default:
                    header('Location: /index.php');
                    exit();
            }
        }
    }
}