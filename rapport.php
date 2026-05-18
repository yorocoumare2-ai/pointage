<?php
include ("db.php");

$sql = "
SELECT p.id, e.nom, p.date_presence, p.heure_entree, p.heure_sortie
FROM presences p
JOIN employes e ON e.id = p.employe_id
ORDER BY p.date_presence DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Présence</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
<div class="container">
    <h2>Rapports de présence</h2>
    <a href="index.php" class="btn-retour">⬅ Retour</a>

    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>Employé</th>
            <th>Date</th>
            <th>Entrée</th>
            <th>Sortie</th>
            <th>Actions</th>
        </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?= htmlspecialchars($row['nom']) ?></td>
        <td><?= htmlspecialchars($row['date_presence']) ?></td>
        <td><?= htmlspecialchars($row['heure_entree'] ?? '--:--:--') ?></td>
        <td><?= htmlspecialchars($row['heure_sortie'] ?? '--:--:--') ?></td>
        <td>
            <a href="traitement_rapport.php?id=<?= $row['id'] ?>&action=archiver" class="edit-btn">Archiver</a>
            <a href="traitement_rapport.php?id=<?= $row['id'] ?>&action=supprimer" class="delete-btn" onclick="return confirm('Supprimer ce rapport ?');">Supprimer</a>
        </td>
    </tr>
    <?php } ?>   
    </table>
</div>
</body>
</html>