<?php
// Connexion de la base de donnée
date_default_timezone_set ('Africa/Bamako');

// Read dynamic environment variables (with default fallbacks for local XAMPP)
$server = getenv('DB_HOST') ?: "localhost";
$user   = getenv('DB_USER') ?: "root";
$pass   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";
$db     = getenv('DB_NAME') ?: "cira_pointage";
$port   = getenv('DB_PORT') ?: null;
$socket = getenv('DB_SOCKET') ?: null;

// Connect using socket (ideal for Google App Engine / Cloud Run) or TCP (local dev)
if ($socket) {
    $conn = new mysqli(null, $user, $pass, $db, null, $socket);
} else {
    $conn = new mysqli($server, $user, $pass, $db, $port);
}

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}
?>