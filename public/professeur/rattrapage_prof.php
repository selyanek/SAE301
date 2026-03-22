<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use src\Controllers\RattrapageController;

$controller = new RattrapageController();
$controller->gererRattrapages();
