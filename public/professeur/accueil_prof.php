<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use src\Controllers\AccueilProfController;

$controller = new AccueilProfController();
$controller->index();
