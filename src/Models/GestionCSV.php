<?php

namespace src\Models;
use Exception;

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

}
?>