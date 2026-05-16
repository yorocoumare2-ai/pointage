<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login_admin.php");
    exit();
}

include "db.php";

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT p.id, e.nom, e.prenom, e.service,
p.date_presence, p.heure_entree, p.heure_sortie, p.statut
FROM presences p
JOIN employes e ON p.employe_id = e.id";



if (!empty($search)) {
    $sql .= " WHERE e.nom LIKE '%$search%' 
              OR e.prenom LIKE '%$search%'";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de présence</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>Rapport de présence</h2>

    <a href="logout.php" class="logout-btn">Se déconnecter</a>


    <form method="GET">
    <input type="text" name="search" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>" placeholder="Rechercher un employé">
    <button type="submit" class="search-btn">Rechercher</button>
</form>

    <table>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>date_presence</th>
            <th>Statut</th>
            <th>Heure entrée</th>
            <th>Heure sortie</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $row['nom'] ?></td>
            <td><?= $row['prenom'] ?></td>
            <td><?= $row['date_presence'] ?></td>
            <td><?= $row['statut'] ?></td>
            <td><?= $row['heure_entree'] ?></td>
            <td><?= $row['heure_sortie'] ?></td>
        </tr>
        <?php } ?>

    </table>

</div>

</body>
</html>