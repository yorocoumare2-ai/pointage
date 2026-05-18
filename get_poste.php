<?php
include "db.php";

if (isset($_GET['service_id'])) {

    $service_id = $_GET['service_id'];

    $sql = "SELECT * FROM postes WHERE service_id = '$service_id'";
    $result = mysqli_query($conn, $sql);

    echo '<option value="">-- Choisir un poste --</option>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'">'.$row['nom_poste'].'</option>';
    }
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