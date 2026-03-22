<?php

namespace src\Controllers;

require_once __DIR__ . '/session_timeout.php';
require_once __DIR__ . '/Redirect.php';

class AccueilProfController
{
    public function index()
    {
        session_start();
        $redirect = new Redirect('professeur');
        $redirect->redirect();

        // La logique de récupération de données (si nécessaire) irait ici.
        // Pour l'instant, nous n'avons besoin que des données de session.

        require __DIR__ . '/../Views/professeur/accueil_prof_vue.php';
    }
}
