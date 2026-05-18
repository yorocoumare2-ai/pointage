<?php
session_start();
include("db.php");

// ✅ Vérifier si utilisateur connecté
if (!isset($_SESSION['employe_id'])) {
    die("Accès refusé");
}

$id = $_SESSION['employe_id'];

if (isset($_POST['modifier'])) {
    $new_email = $_POST['email'];
    $new_password = $_POST['mot_de_passe'];
    $confirm_password = $_POST['confirm_mot_de_passe'];

    // ✅ Vérifier email valide
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo "Email invalide";
        exit();
    }

    // ✅ Vérifier mot de passe
    if (strlen($new_password) < 4) {
        echo "Le mot de passe doit contenir au moins 4 caractères";
        exit();
    }

    // ✅ Vérifier confirmation
    if ($new_password !== $confirm_password) {
        echo "Les mots de passe ne correspondent pas";
        exit();
    }

    // ✅ Vérifier email déjà utilisé
    $check = "SELECT id FROM employes WHERE email = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $check);
    mysqli_stmt_bind_param($stmt, "si", $new_email, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "Cet email est déjà utilisé";
        exit();
    }

    // ✅ Hash mot de passe
    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    // ✅ Mise à jour
    $sql = "UPDATE employes 
            SET email = ?, mot_de_passe = ?, premiere_connexion = 1 
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $new_email, $hash, $id);
    mysqli_stmt_execute($stmt);

    header("Location: employes.php");
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
   <form method="POST">
    <input type="email" name="email" placeholder="Nouvel email" required><br><br>

    <input type="password" name="mot_de_passe" placeholder="Nouveau mot de passe" required><br><br>

    <input type="password" name="confirm_mot_de_passe" placeholder="Confirmer mot de passe" required><br><br>

    <button type="submit" name="modifier">Modifier</button>
</form> 
</body>
</html>