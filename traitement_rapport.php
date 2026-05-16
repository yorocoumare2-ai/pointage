<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "db.php";

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if(!$id || !$action) {
    die("Paramètre manquants");
}

$id = intval($_GET['id']);
    $action = $_GET['action'];

    // Récupérer le rapport
    $sql = "SELECT * FROM presences WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if(!$result   || mysqli_num_rows($result) === 0) {
        die("");
    }

    $presences = mysqli_fetch_assoc($result);

    if (!$presences) {
        die("");
    }

    //=========== ARCHIVER ============
    if ($action === "archiver") {

        $insert = "
        INSERT INTO rapports_archives
        (employe_id, date_presence, statut, heure_entree, heure_sortie, date_archivage)
        VALUES (
            {$presences['employe_id']},
            '{$presences['date_presence']}',
            '{$presences['statut']}',
            '{$presences['heure_entree']}',
            '{$presences['heure_sortie']}',
            NOW()
        )
        ";
        header("Location: presence.php?msg=archives_ok");
        exit();
        
        if (!mysqli_query($conn, $insert)) {
            die("Erreur INSERT : " . mysqli_error($conn));
        }
       
    }

            // ======= Supprimer ======/
        if($action === "supprimer") {
            mysqli_query($conn, "DELETE FROM presences WHERE id = $id");
            header("Location: presence.php?msg=supprime_ok");
            exit();
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
<

</body>
</html>