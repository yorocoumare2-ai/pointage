<?php
include("db.php");

$id = $_GET['id'];

// Supprimer l'employé
mysqli_query($conn, "DELETE FROM employes WHERE id=$id");
header ("Location: employes.php");
?>