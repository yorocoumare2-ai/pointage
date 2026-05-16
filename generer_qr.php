<?php
session_start();
include "db.php";
 // Générer le qr_token
 $token = bin2hex(random_bytes(16));

 $sql = "UPDATE employes SET qr_token = ? WHERE id = ?";
 $stmt = mysqli_prepare($conn, $sql);
 mysqli_stmt_bind_param($conn, "si" $token, $employe_id);
 mysqli_stmt_execute($stmt);

 echo "QR généré avec succès";
 ?>