<?php
session_start();

include "db.php";

if (!isset($_SESSION['employe_id'])) {
    header("Location: login.php");
    exit();
}

// empêcher admin d'aller ici (optionnel)
if ($_SESSION['role'] === 'admin') {
    header("Location: presence.php");
    exit();
}



$id = $_SESSION['employe_id'];

// Mets ton IP ici
$ip = "192.168.1.5";

// Lien du scan
$lien = "http://$ip/cira_pointage/traitement_scan.php?token=$id";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner QR</title>
      <link rel="stylesheet" href="style.css">
    
      <style>
body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f5f5f5;
    position: relative; /* important pour le bouton */
}

/* bouton logout en haut */
.logout-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    background: #dc3545;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
}

/* conteneur principal */
.container {
    width: 100%;
    height: 100%;
    text-align: center;
}

/* cadre QR */
.box {
    width: 80%;
    max-width: 600px;
    margin: 30px auto;
    padding: 40px;
    background: #e9ecef;
    border-radius: 15px;
}

/* image QR */
.box img {
    width: 250px;
}

/* texte */
.status {
    margin-top: 10px;
}
</style>
   
</head>
<body>
    <a href="logout.php" class="logout-btn">Se déconnecter</a>
    <div class="container">
        <h2>Scanner le QR code</h2>

        <div class="box">
            
<img 
    src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode($lien); ?>" 
    alt="QR Code"
/>
            <div class="status">
                En attente du scan...
            </div>
        </div>

        <p>Utilisez votre téléphone pour scanner</p>
    </div>
</body>
</html>