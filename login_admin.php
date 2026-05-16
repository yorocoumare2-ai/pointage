<?php
include ("db.php");
?>


<!DOCTYPE html>
<html>
<head>
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
     <a href="index.php" class="btn-retour">← Retour</a>

<h2>Connexion Admin</h2>

<form method="POST" action="verif_admin.php">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br><br>

    <button type="submit" class="login-btn">Connexion</button>
</form>

</body>
</html>

