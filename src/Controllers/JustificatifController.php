<?php

namespace src\Controllers;

require_once __DIR__ . '/../Database/Database.php';
require_once __DIR__ . '/../Models/Absence.php';

use src\Models\Absence;
use src\Database\Database;

class JustificatifController
{
    private $absence;
    private $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->absence = new Absence($this->pdo);
    }

    /**
     * Récupère toutes les absences en attente pour un étudiant donné
     * @param string $studentId L'identifiant de l'étudiant
     * @return array Tableau des absences en attente formatées
     */
    public function getAbsencesEnAttente($studentId)
    {
        if (empty($studentId)) {
            return [];
        }

        // Récupérer toutes les absences de l'étudiant
        $toutesLesAbsences = $this->absence->getByStudentIdentifiant($studentId);
        
        $absencesEnAttente = [];

        foreach ($toutesLesAbsences as $absence) {
            // Déterminer le statut de l'absence
            $statut = $this->determinerStatut($absence);

            // Ne garder que les absences en attente
            if ($statut !== 'en_attente') {
                continue;
            }

            // Formater l'absence pour l'affichage
            $absencesEnAttente[] = $this->formaterAbsence($absence, $statut);
        }

        return $absencesEnAttente;
    }

    /**
     * Détermine le statut d'une absence
     * @param array $absence Les données de l'absence
     * @return string Le statut ('en_attente', 'valide', 'refuse')
     */
    private function determinerStatut($absence)
    {
        if (!isset($absence['justifie']) || $absence['justifie'] === null) {
            return 'en_attente';
        }

        if ($absence['justifie'] === true || $absence['justifie'] === 't' || 
            $absence['justifie'] === '1' || $absence['justifie'] === 1) {
            return 'valide';
        } elseif ($absence['justifie'] === false || $absence['justifie'] === 'f' || 
                  $absence['justifie'] === '0' || $absence['justifie'] === 0) {
            return 'refuse';
        }

        return 'en_attente';
    }

    /**
     * Formate une absence pour l'affichage dans la vue
     * @param array $absence Les données brutes de l'absence
     * @param string $statut Le statut de l'absence
     * @return array L'absence formatée
     */
    private function formaterAbsence($absence, $statut)
    {
        // Déterminer la classe CSS et le label selon le statut
        $statutInfo = $this->getStatutInfo($statut);

        // Formater les dates
        $dateSoumission = date('d/m/Y à H:i', strtotime($absence['date_debut']));
        $dateDebut = date('d/m/Y à H:i', strtotime($absence['date_debut']));
        $dateFin = date('d/m/Y à H:i', strtotime($absence['date_fin']));

        // Traiter les justificatifs
        $justificatifs = $this->traiterJustificatifs($absence['urijustificatif'] ?? '');

        return [
            'id' => $absence['id'] ?? null,
            'date_soumission' => $dateSoumission,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'motif' => $absence['motif'] ?? '',
            'justificatifs' => $justificatifs,
            'statut_class' => $statutInfo['class'],
            'statut_label' => $statutInfo['label']
        ];
    }

    /**
     * Retourne les informations de style et label pour un statut
     * @param string $statut Le statut
     * @return array Tableau avec 'class' et 'label'
     */
    private function getStatutInfo($statut)
    {
        $info = [
            'en_attente' => ['class' => 'statut-attente', 'label' => 'En attente'],
            'valide' => ['class' => 'statut-valide', 'label' => 'Validé'],
            'refuse' => ['class' => 'statut-refuse', 'label' => 'Refusé']
        ];

        return $info[$statut] ?? ['class' => 'statut-attente', 'label' => 'En attente'];
    }

    /**
     * Traite les justificatifs pour créer les liens HTML
     * @param string $uriJustificatif JSON ou chaîne vide
     * @return array Tableau de liens HTML
     */
    private function traiterJustificatifs($uriJustificatif)
    {
        if (empty($uriJustificatif)) {
            return [];
        }

        $fichiers = json_decode($uriJustificatif, true);
        
        if (!is_array($fichiers) || count($fichiers) === 0) {
            return [];
        }

        $links = [];
        foreach ($fichiers as $fichier) {
            $fichierPath = "../../../uploads/" . htmlspecialchars($fichier);
            $links[] = [
                'path' => $fichierPath,
                'nom' => htmlspecialchars($fichier)
            ];
        }

        return $links;
    }
}
