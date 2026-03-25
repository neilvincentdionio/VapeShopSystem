<?php
// Ensure this points to the public directory
$pathsConfig = __DIR__ . "/app/Config/Paths.php";
require_once $pathsConfig;

$app = new CodeIgniter\CodeIgniter();
$app->run();
PHP