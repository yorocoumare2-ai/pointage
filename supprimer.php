<?php
session_start();

// Protection d'accès Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login_admin.php");
    exit();
}

include("db.php");

// Récupérer et assainir l'identifiant
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Supprimer l'employé de manière sécurisée
    $stmt = mysqli_prepare($conn, "DELETE FROM employes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}

header("Location: employes.php");
exit();
?>