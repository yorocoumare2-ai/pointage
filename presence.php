<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login_admin.php");
    exit();
}

include "db.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT p.id, e.nom, e.prenom, e.service,
p.date_presence, p.heure_entree, p.heure_sortie, p.statut
FROM presences p
JOIN employes e ON p.employe_id = e.id";

if (!empty($search)) {
    $sql .= " WHERE e.nom LIKE ? OR e.prenom LIKE ? ORDER BY p.date_presence DESC, p.heure_entree DESC";
    $stmt = mysqli_prepare($conn, $sql);
    $search_param = "%$search%";
    mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql .= " ORDER BY p.date_presence DESC, p.heure_entree DESC";
    $result = mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIRA SAS - Rapport de présence</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-brand">CIRA SAS</div>
        <div class="header-nav">
            <a href="index.php">Tableau de bord</a>
            <a href="employes.php">Gestion des employés</a>
            <a href="admin_qr.php">Afficher QR Bureau</a>
            <a href="presence.php" style="background: rgba(255, 255, 255, 0.2); color: white;">Rapport de présence</a>
            <a href="logout.php" style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 4px; font-weight: bold;">Se déconnecter</a>
        </div>
    </div>

    <!-- MAIN BODY -->
    <div class="container">
        
        <div class="premium-card">
            
            <div class="top-bar">
                <div>
                    <h2>📊 Rapport de présence</h2>
                    <p style="color: #64748b; font-size: 14px; margin-top: 5px;">Consultez, recherchez et archivez les fiches de pointage des employés de l'entreprise.</p>
                </div>
                <div>
                    <a href="index.php" class="btn-retour">⬅ Retour au Dashboard</a>
                </div>
            </div>

            <!-- SEARCH FILTER BAR -->
            <form method="GET" class="search-form-container">
                <input type="text" name="search" class="search-input" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher un employé par nom ou prénom...">
                <button type="submit" class="search-btn">🔍 Rechercher</button>
                <?php if (!empty($search)) { ?>
                    <a href="presence.php" class="btn-retour" style="padding: 12px 18px;">Réinitialiser</a>
                <?php } ?>
            </form>

            <!-- DATA TABLE -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Service / Poste</th>
                            <th>Date présence</th>
                            <th>Statut</th>
                            <th>Heure d'entrée</th>
                            <th>Heure de sortie</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_rows = false;
                        while ($row = mysqli_fetch_assoc($result)) { 
                            $has_rows = true;
                            $statut_clean = strtolower($row['statut']);
                            $badge_class = ($statut_clean === 'present' || $statut_clean === 'présent') ? 'badge-present' : 'badge-absent';
                            $statut_label = ($statut_clean === 'present' || $statut_clean === 'présent') ? 'Présent' : 'Absent';
                        ?>
                        <tr>
                            <td style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($row['nom']) ?></td>
                            <td><?= htmlspecialchars($row['prenom']) ?></td>
                            <td style="text-transform: capitalize; color: #64748b; font-size: 14px;"><?= htmlspecialchars($row['service'] ?? 'Non spécifié') ?></td>
                            <td style="font-weight: 500; color: #475569;"><?= date("d/m/Y", strtotime($row['date_presence'])) ?></td>
                            <td>
                                <span class="badge <?= $badge_class ?>"><?= $statut_label ?></span>
                            </td>
                            <td style="font-family: monospace; font-weight: 600; color: #059669;"><?= $row['heure_entree'] ? date("H:i", strtotime($row['heure_entree'])) : '--:--' ?></td>
                            <td style="font-family: monospace; font-weight: 600; color: #ea580c;"><?= $row['heure_sortie'] ? date("H:i", strtotime($row['heure_sortie'])) : '--:--' ?></td>
                            <td style="text-align: center; white-space: nowrap;">
                                <a href="traitement_rapport.php?id=<?= $row['id'] ?>&action=archiver" class="edit-btn" style="margin-right: 5px;">📥 Archiver</a>
                                <a href="traitement_rapport.php?id=<?= $row['id'] ?>&action=supprimer" class="delete-btn" onclick="return confirm('Voulez-vous vraiment supprimer ce rapport de présence ?');">🗑️ Supprimer</a>
                            </td>
                        </tr>
                        <?php } 
                        if (!$has_rows) {
                        ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 40px;">
                                📭 Aucun enregistrement de présence trouvé.
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>
</html>