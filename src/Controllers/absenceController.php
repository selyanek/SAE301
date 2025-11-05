<?php
use src\Models\Absence;
use src\Database\Database;

$db = new Database();
$pdo = $db->getConnection();

$absence = new Absence($pdo);

