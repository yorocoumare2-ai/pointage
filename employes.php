<?php
include("db.php");
$result = mysqli_query($conn, "SELECT * FROM employes");
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
<div class="container">
    <h2>Employés</h2>
    <a href="ajouter_employe.php" class="btn-ajouter">
          Ajouter un employé
    </a>   

    <a href="index.php" class="btn-retour">⬅ Retour</a>
    <table border="1">
        <tr>
            <th>Nom</th>
            <th>Prenom</th>
            <th>Email</th>
            <th>Service</th>
            <th>Actions</th>
        </tr>

       <?php while  ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $row['nom'] ?></td>
            <td><?= $row['prenom'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['service'] ?></td>
         <td>
            
            <a href="modifier.php?id=<?= $row['id'] ?>"class="edit-btn">Modifier</a>

            <a href="supprimer.php?id=<?= $row['id'] ?>"
             class="delete-btn"
            onclick="return confirm('Supprimer cet employé ?');">Supprimer</a>

       </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>