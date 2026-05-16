<?php
include ("db.php");

$sql = "
SELECT p.id, e.nom, p.date_presence, p.heure_entree, p.heure_sortie
FROM presences p
JOIN employe e ON e.id = p.employe_id
ORDER BY p.date_presence DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="stylesheet" rel="style.css"> 
</head>
<body>
<h2>Rapports de présence</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>Employé</th>
        <th>Date</th>
        <th>entrée</th>
        <th>Sortie</th>
        <th>Actions</th>
    </tr>

<?php while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?= $row['nom'] ?></td>
    <td><?= $row['date _presence'] ?></td>
    <td><?= $row['Heure_entree'] ?></td>
    <td><?= $row['Heure_sortie'] ?></td>
 
</tr>
<?php } ?>   
</table>

    
</body>
</html>