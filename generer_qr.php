<?php
session_start();
include "db.php";

// Récupérer l'ID de l'employé depuis l'URL ou la session
$employe_id = isset($_GET['id']) ? intval($_GET['id']) : ($_SESSION['employe_id'] ?? null);

if (!$employe_id) {
    die("ID de l'employé manquant");
}

// Générer le qr_token
$token = bin2hex(random_bytes(16));

$sql = "UPDATE employes SET qr_token = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $token, $employe_id);
    mysqli_stmt_execute($stmt);
    echo "QR généré avec succès. Token : " . htmlspecialchars($token);
} else {
    echo "Erreur lors de la préparation de la requête";
}
?>