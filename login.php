<?php
session_start();
include ("db.php");


if (isset($_POST['email'], $_POST['password'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT id, mot_de_passe, role FROM employes WHERE email = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_bind_result($stmt, $id, $hash, $role);
    
    if (mysqli_stmt_fetch($stmt)) {
    
    if (password_verify($password, $hash)) {
        $_SESSION['employe_id'] = $id;
        $_SESSION['role'] = $role;

                // REDIRECTION CORRECTE
    if ($role == 'admin') {
        header("Location: presence.php");
        exit();
    } else {
        header("Location: scan.php");
        exit();
    }
        

    } else {
        echo " Mot de passe incorrect";
    }
    
    }else {
        echo "Email incorrect";
    }
    
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion</title>
         <link rel="stylesheet" href="style.css">
    </head>
    <body>
         <a href="index.php" class="btn-retour">← Retour</a>
        <h2>Connexion employé</h2>
    
        <?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
    
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Mot de passe" required><br><br>
            <button type="submit" class="login-btn">Connexion</button>
        </form>
        
    </body>
    </html>
        