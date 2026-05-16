
<?php
include "db.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
  <link rel="stylesheet" href="style.css">

<style>
body {
    margin: 0;
    font-family: Arial;
    background: #f5f5f5;
}

/* HEADER */
.header {
    background: #2f80ed;
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
}

.header a {
    color: white;
    margin-left: 15px;
    text-decoration: none;
}

/* CONTAINER */
.container {
    padding: 40px;
}

/* CARTES */
.cards {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 60px;
}

/* CARTE */
.card {
    width: 280px;
    height: 150px;
    border-radius: 15px;
    color: white;
    text-align: center;
    padding: 20px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    transition: 0.3s;

    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* TEXTE */
.card h2 {
    margin: 0;
    font-size: 22px;
}

.number {
    font-size: 40px;
    margin-top: 10px;
    font-weight: bold;
}

/* COULEURS */
.green {
    background: linear-gradient(135deg, #43a047, #2e7d32);
}

.blue {
    background: linear-gradient(135deg, #42a5f5, #1e88e5);
}

.orange {
    background: linear-gradient(135deg, #ffa726, #fb8c00);
}

/* HOVER */
.card:hover {
    transform: translateY(-8px);
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div>CIRA SAS</div>
    <div>
        Bienvenue Admin |
        <a href="#">Gestion des employes</a>
        <a href="scan.php">Pointage</a>
        <a href="presence.php">Rapport de presence</a>
    </div>
</div>

<!-- CONTENT -->
<div class="container">
    <div class="cards">

        <!-- EMPLOYES -->
        <div class="card green" onclick="window.location='employes.php'">
            <h2>Employés</h2>
            <div class="number">
                <?php
                $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM employes");
                $data = mysqli_fetch_assoc($res);
                echo $data['total'];
                ?>
            </div>
        </div>

        <!-- POINTAGE -->
        <div class="card blue" onclick="window.location='scan.php'">
            <h2>Pointage</h2>
            <div class="number">--</div>
        </div>

        <!-- PRESENCE -->
        <div class="card orange" onclick="window.location='presence.php'">
            <h2>Présences</h2>
            <div class="number">
                <?php
                $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM presences");
                $data = mysqli_fetch_assoc($res);
                echo $data['total'];
                ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>