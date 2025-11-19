<?php

namespace src\Models;
use Exception;
use PDO;
use src\Database\Database;

// Classe pour gérer la lecture et l'affichage de fichiers CSV
class GestionCSV
{
    public function getCSV($files, $index)
    {
        try {
            if (file_exists($files) !== false) {
                if (($handle = fopen($files, 'r')) !== false) {
                    $dataCSV = [];

                    while (($data = fgetcsv($handle, 100000, ';', '"', '\\')) !== false) {
                        $dataCSV[] = $data;
                    }
                    fclose($handle);
                    for ($i = 1; $i < count($dataCSV); $i++) {
                        echo $dataCSV[$i][$index] . "\n";
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getAllData($files) // Retourne tout le fichier CSV sous la forme d'un tableau de tableaux pour traitement futur
    {
        try {
            if (file_exists($files) !== false) {
                if (($handle = fopen($files, 'r')) !== false) {
                    $dataCSV = [];

                    while (($data = fgetcsv($handle, 100000, ';', '"', '\\')) !== false) {
                        $dataCSV[] = $data;
                    }
                    fclose($handle);
                    return $dataCSV;
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function exportToDB($files, Database $database): ?string
    {
        $csv_array = $this->getAllData($files);
        $pdo = $database->getConnection(); 

        foreach ($csv_array as $row) {
            $motif = $row[18];
            $justifie = $row[17] !== "Non justifié"; // Booléen si justifié ou non

            // TODO: implementer la logique pour trouver l'id_cours et l'id_etudiant
            // pour l'instant, id mis à 0

            $id_cours = 0;
            $id_etudiant = 0;

            $d = $this->getDate($row[9], $row[10]); // recup string heure et date du CSV
            $date_start = \date('Y-m-d H:i:s', $d);
            $date_end = \date('Y-m-d H:i:s', $d + $this->convertToSeconds($row[11]));

            $sql = "INSERT INTO Absence (date_debut, date_fin, motif, justifie) 
                    VALUES (:date_debut, :date_fin, :motif, :justifie)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':date_debut' => $date_start,
                ':date_fin' => $date_end,
                ':motif' => $motif,
                ':justifie' => $justifie ? 1 : 0 // Convert boolean to int for DB
            ]);
        }
        return $pdo->lastInsertId();
    }

    private function convertToSeconds(string $str): int
    {
        return match($str) { // R
            '1H30' => 5400,
            '3H00' => 10800,
            default => 0
        };
    }

    private function getDate(string $date, string $heure): int
    {
        $jour = \intval(\substr($date, 0, 2));
        $mois = \intval(\substr($date, 3, 2));
        $annee = \intval(\substr($date, 6, 4));

        $heures = \strlen($heure) === 4 
            ? \intval(\substr($heure, 0, 1)) 
            : \intval(\substr($heure, 0, 2));
        $minutes = \intval(\substr($heure, \strlen($heure) === 4 ? 2 : 3, 2));

        return \mktime($heures, $minutes, 0, $mois, $jour, $annee);
    }
}
?>