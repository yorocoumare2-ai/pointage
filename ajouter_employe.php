<?php
include ("db.php");

if (isset($_POST['Enregistrer'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $service = $_POST['service'];

$mot_de_passe_clair = $_POST['mot_de_passe']; 
$mot_de_passe_hash = password_hash($mot_de_passe_clair, PASSWORD_DEFAULT);



$sql = "INSERT INTO employes (nom, prenom, email, mot_de_passe, service)
        VALUES (?, ?, ?, ?, ?)";

    // Récuperation de l'id du nouvel employé
    $employe_id = mysqli_insert_id($conn);

   
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss",
         $nom,
         $prenom,
         $email,
         $mot_de_passe_hash,
         $service
);
    mysqli_stmt_execute($stmt);
    header("Location: employes.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un employé</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Ajouter un employé</h2>
        <form method="POST">
            <input type="text" name="nom" placeholder="Nom" required><br><br>
            <input type="text" name="prenom" placeholder="Prenom" required><br><br>
            <input type="text" name="email" placeholder="Email" required><br><br>
            <input type="password" name="mot_de_passe" placeholder="mot_de_passe" required><br><br>
                     <!-- 🔹 SERVICE -->

            <select name="service" id="service" required>
    <option value="">-- Choisir le service --</option>
    <option value="informatique">Informatique</option>
    <option value="comptabilite">Comptabilité</option>
    <option value="rh">Ressources humaines</option>
     <option value="ingenieur">ingenieur</option>
</select>

            <button type="submit" name="Enregistrer" class="login-btn">Enregistrer</button>
        </form>
</body>
</html>