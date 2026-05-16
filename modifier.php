<?php

include("db.php");

// Vérifier si l'ID existe dans l'URL
if (!isset ($_GET['id'])) {
    die("ID manquant");
}
$id =$_GET['id'];
// Récupérer les info de l'employé
$sql =mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM employes WHERE id=$id"));

// Modifier les données
if (isset($_POST['modifier'])) {
    mysqli_query($conn, "UPDATE employes SET
    nom='$_POST[nom]', prenom='$_POST[prenom]', email='$_POST[email]', mot_de_passe='$_POST[mot_de_passe]', service='$_POST[service]'
    WHERE id=$id");
    header("Location: employes.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier employé</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <form method="POST">
            <input type="text" name="nom" value="<?= $sql['nom'] ?>">
            <input type="text" name="prenom" value="<?= $sql['prenom'] ?>">
            <input type="text" name="service" value="<?= $sql['service'] ?>">
            <input type="text" name="email" value="<?= $sql['email'] ?>">
            <input type="password" name="mot_de_passe" value="<?= $sql['mot_de_passe'] ?>">

            <!-- 🔹 SERVICE -->

            <select name="service" id="service" required>
    <option value="">-- Choisir le service --</option>
    <option value="informatique">Informatique</option>
    <option value="comptabilite">Comptabilité</option>
    <option value="rh">Ressources humaines</option>
     <option value="ingenieur">ingenieur</option>
</select>



            <button type="submit" name="modifier" class="login-btn" >Modifier</button>
        </form>
    </div>
</body>
</html>