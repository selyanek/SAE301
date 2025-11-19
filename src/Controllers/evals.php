<?php
//require __DIR__ . './vendor/autoload.php';

// ...existing code...
use src\Models\GestionCSV as GestionCSV;


// TODO

// FIX LES PATH
// finirai demain

$csv = new GestionCSV();
$result = [];
$csv_array = $csv->getAllData("data/CSV/BUT1-240122-240223_anonymise.CSV");

// FILTRER EVALS
foreach ($csv_array as $row) {
    if ($row[23] == "Oui") {
        $result += ($row);
    }
}

echo implode("," , $result);
?>