<?php
session_start();
include "db.php";

if (!isset($_SESSION['employe_id'])) {
    die("Accès refusé");
}

if (!isset($_GET['token'])) {
    die("QR invalide");
}

$token = $_GET['token'];



/* 1. Trouver l'employé via le QR */
$employe_id = $_SESSION['employe_id'];
$code = $_GET['code'];
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$employe = mysqli_fetch_assoc($result);

if (!$employe) {
    die("Employé non reconnu");
}

$employe_id = $employe['id'];
$date = date("Y-m-d");
$heure = date("H:i:s");

/* 2. Vérifier si présence existe déjà */
$sql = "SELECT id FROM presences WHERE employe_id = ? AND date_pointage = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $employe_id, $date);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$presence = mysqli_fetch_assoc($res);

if (!$presence) {

    // ENTREE
    $sql = "INSERT INTO presences (employe_id, date_pointage, heure_entree, statut)
            VALUES (?, ?, ?, 'présent')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $employe_id, $date, $heure);
    mysqli_stmt_execute($stmt);

    echo "Entrée enregistrée à $heure";

} else {

    // SORTIE
    $sql = "UPDATE presences SET heure_sortie = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $heure, $presence['id']);
    mysqli_stmt_execute($stmt);

    echo "Sortie enregistrée à $heure";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
</body>
</html>