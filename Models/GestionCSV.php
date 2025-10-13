<?php

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
}
?>