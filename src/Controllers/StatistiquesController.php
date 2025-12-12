<?php

namespace src\Controllers;

use src\Database\Database;
use src\Models\Statistiques;

class StatistiquesController
{
    private $statistiques;

    public function __construct()
    {
        $db = new Database();
        $pdo = $db->getConnection();
        $this->statistiques = new Statistiques($pdo);
    }

    /**
     * Affiche la page des statistiques
     */
    public function afficherStatistiques()
    {
        // Récupération des filtres
        $filtres = $this->recupererFiltres();

        // Chargement des données
        $this->statistiques->chargerAbsences($filtres);
        $stats_globales = $this->statistiques->calculerStatistiquesGlobales();
        $matieres = $this->statistiques->getListeMatieres();
        $groupes = $this->statistiques->getListeGroupes();
        $rattrapages = $this->statistiques->getRattrapages();

        // Inclusion de la vue
        require_once __DIR__ . '/../Views/responsable/statistiques.php';
    }

    /**
     * API REST - Retourne les données au format JSON pour Chart.js
     */
    public function getDonneesJSON()
    {
        header('Content-Type: application/json');
        
        $filtres = $this->recupererFiltres();
        $this->statistiques->chargerAbsences($filtres);
        
        echo json_encode($this->statistiques->getDonneesAPI());
    }

    /**
     * Récupère les filtres depuis $_GET
     */
    private function recupererFiltres()
    {
        return [
            'annee_but' => $_GET['annee_but'] ?? '',
            'matiere' => $_GET['matiere'] ?? '',
            'type_cours' => $_GET['type_cours'] ?? '',
            'groupe' => $_GET['groupe'] ?? '',
            'date_debut' => $_GET['date_debut'] ?? '',
            'date_fin' => $_GET['date_fin'] ?? ''
        ];
    }
}