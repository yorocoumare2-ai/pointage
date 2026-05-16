<?php
if (!isset($_SESSION['employe_id'])) {
  header("Location: login.php");
  exit;
}
include "db.php";

$message = "";

// Récuperation des employés
$employes = mysqli_query ($conn, "SELECT id, nom, prenom FROM employes");

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $employe_id = $_POST['employe_id'];
  $date_jour = date("Y-m-d");
  $heure_actuelle = date("H:i:s");


// Pointer Entrée
if (isset($_POST['entree'])) {

    // Vérifier si l'employé a déjà pointer aujourd'hui
    $check = mysqli_query($conn, "
          SELECT * FROM pointage
          WHERE employe_id = '$employe_id' 
          AND date = '$date_jour'
          ");

    if (mysqli_num_rows($check) == 0) { 

      mysqli_query($conn, "
           INSERT INTO pointage (employe_id, date, heure_entree, statut)
           VALUES ('$employe_id', '$date_jour', '$heure_actuelle', 'présent')
      ");
      $message = "Entrée enregistrée avec succès";
    } else {
      $message = "Entrée déjà enregistrer aujourd'hui"; 
  }
}

    // Vérifier s'il a pointé l'entrée
    $check = mysqli_query($conn, "
        SELECT * FROM pointage
        WHERE employe_id='$employe_id'
        AND date='$date_jour'
    ");

  // Pointer sortie
  if (isset($_POST['sortie'])) {

      mysqli_query($conn, "
          UPDATE pointage
          SET heure_sortie='$heure_actuelle'
          WHERE employe_id='$employe_id'
          AND date='$date_jour'
      ");
      $message = "Sortie enregistrée avec succès";
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pointage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
        <h2>Pointage des employés</h2>
         
        <?php if (isset($message)) {?>
           <div class="message"><?= $message ?></div>
           <?php } ?>

        <form method="POST">
        <select name="employe_id" required>
          <option value="">Sélectionner un employé</option>
          <?php
          $res = mysqli_query($conn, "SELECT id, nom, prenom FROM employes");
          while ($row = mysqli_fetch_assoc($res)) {
              echo "<option value='{$row['id']}'>
                    {$row['nom']} {$row['prenom']}
                    </option>";
          }
          ?>
        </select><br><br>

       <a href="qr/scan.php?token=ABC123">
        Scanner QR Code
       </a>
    </form>
  </div>  
</body>
</html>