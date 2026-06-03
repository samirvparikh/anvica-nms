<?php
date_default_timezone_set('Asia/Kolkata');

define('DB_HOST', 'localhost');
define('DB_NAME', 'anvicfne_nms');
define('DB_USER', 'anvicfne_nms');
define('DB_PASS', '@nv!ca@2026');

define('APP_NAME', 'Anvica NMS');
define('BASE_URL', 'http://localhost/anvica_nms');

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    return $pdo;
}
?>
