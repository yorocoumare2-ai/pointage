<?php
// Connexion de la base de donnée
date_default_timezone_set ('Africa/Bamako');

$server = "localhost";
$user   = "root";
$pass   = "72004800";
$db     = "cira_pointage";
$conn = new mysqli($server, $user, $pass, $db);
if ($conn-> connect_error) {
    die("erreur de connection:" .$conn-> connect_error);
} else{
    echo "";
}
?>