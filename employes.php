<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

include "db.php";

// Fetch employees (non-admins for normal management)
$sql = "SELECT id, nom, prenom, service, email, actif FROM employes WHERE role = 'employe' ORDER BY nom ASC, prenom ASC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIRA SAS - Gestion des Employés</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-brand">CIRA SAS</div>
        <div class="header-nav">
            <a href="index.php">Tableau de bord</a>
            <a href="employes.php" style="background: rgba(255, 255, 255, 0.2); color: white;">Gestion des employés</a>
            <a href="admin_qr.php">Afficher QR Bureau</a>
            <a href="presence.php">Rapport de présence</a>
            <a href="logout.php" style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 4px; font-weight: bold;">Se déconnecter</a>
        </div>
    </div>

    <!-- MAIN BODY -->
    <div class="container">
        
        <div class="premium-card">
            
            <div class="top-bar">
                <div>
                    <h2>👥 Gestion des employés</h2>
                    <p style="color: #64748b; font-size: 14px; margin-top: 5px;">Gérez l'ensemble des fiches employés, contrôlez l'activation de leurs comptes et modifiez leurs informations d'accès.</p>
                </div>
                <div>
                    <a href="ajouter_employe.php" class="btn-primary">➕ Ajouter un employé</a>
                </div>
            </div>

            <!-- DATA TABLE -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">Profil</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Service / Poste</th>
                            <th>Email</th>
                            <th style="text-align: center; width: 100px;">Statut</th>
                            <th style="text-align: center; width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_rows = false;
                        while ($row = mysqli_fetch_assoc($result)) { 
                            $has_rows = true;
                            
                            // Generate Avatar Initials
                            $initials = strtoupper(substr($row['prenom'] ?? '', 0, 1) . substr($row['nom'] ?? '', 0, 1));
                            
                            // Simple dynamic soft colors for avatars
                            $colors = ['#ecfdf5', '#e0e7ff', '#fff7ed', '#f0fdf4', '#fef2f2', '#f0f9ff'];
                            $text_colors = ['#047857', '#4338ca', '#c2410c', '#15803d', '#b91c1c', '#0369a1'];
                            $color_idx = $row['id'] % count($colors);
                            
                            $avatar_bg = $colors[$color_idx];
                            $avatar_text = $text_colors[$color_idx];
                            
                            // Active status badge
                            $is_active = $row['actif'] == 1;
                            $badge_class = $is_active ? 'badge-present' : 'badge-absent';
                            $badge_label = $is_active ? 'Actif' : 'Inactif';
                        ?>
                        <tr>
                            <td>
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: <?= $avatar_bg ?>; color: <?= $avatar_text ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; letter-spacing: 0.5px; box-shadow: inset 0 0 4px rgba(0,0,0,0.05);">
                                    <?= $initials ?>
                                </div>
                            </td>
                            <td style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($row['nom']) ?></td>
                            <td style="font-weight: 500;"><?= htmlspecialchars($row['prenom']) ?></td>
                            <td style="text-transform: capitalize; color: #64748b; font-size: 14px; font-weight: 500;"><?= htmlspecialchars($row['service'] ?? 'Non spécifié') ?></td>
                            <td style="color: #475569;"><?= htmlspecialchars($row['email']) ?></td>
                            <td style="text-align: center;">
                                <span class="badge <?= $badge_class ?>"><?= $badge_label ?></span>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <a href="modifier.php?id=<?= $row['id'] ?>" class="edit-btn" style="margin-right: 5px;">✏️ Modifier</a>
                                <a href="supprimer.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Voulez-vous vraiment supprimer cet employé ? Son historique de pointage sera également affecté.');">🗑️ Supprimer</a>
                            </td>
                        </tr>
                        <?php } 
                        if (!$has_rows) {
                        ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 40px;">
                                📭 Aucun employé n'est actuellement inscrit dans le système.
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn-retour">⬅ Retour au Tableau de bord</a>
            </div>

        </div>

    </div>

</body>
</html>