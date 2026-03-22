<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/ProfileController.php';

use src\Controllers\ProfileController;

$controller = new ProfileController();
$controller->show();

