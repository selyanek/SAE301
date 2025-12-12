<?php
// filepath: /users/info/selyane.khentache/SAE301-3/testgestioncsv.php

require_once __DIR__ . '/../../vendor/autoload.php';

use src\Models\GestionCSV;

$file = 'data/CSV/BUT2-251204-251217_nouveau.CSV';

function testgestioncsv($files)
{
    $csv = new GestionCSV();
    $csv_array = $csv->getAllData($files);
    \print_r($csv_array);
    \print_r($csv -> check_if_valid_csv($files));
}

testgestioncsv($file);