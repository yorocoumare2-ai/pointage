<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

include("db.php");

// Verify if ID exists
if (!isset($_GET['id'])) {
    die("ID manquant");
}
$id = intval($_GET['id']);

// Fetch employee info
$stmt_get = mysqli_prepare($conn, "SELECT * FROM employes WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$employee = mysqli_fetch_assoc($result);

if (!$employee) {
    die("Employé introuvable");
}

$erreur = "";
$succes = "";

// Update form handling
if (isset($_POST['modifier'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $service = $_POST['service'];
    $actif = intval($_POST['actif']);

    // Check if email is already taken by another employee
    $sql_check = "SELECT id FROM employes WHERE email = ? AND id != ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "si", $email, $id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $erreur = "Cette adresse email est déjà utilisée par un autre employé.";
    } else {
        if (!empty($_POST['mot_de_passe'])) {
            // Hash new password if supplied
            $mot_de_passe_hash = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
            $update_sql = "UPDATE employes SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, service = ?, actif = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt, "sssssii", $nom, $prenom, $email, $mot_de_passe_hash, $service, $actif, $id);
        } else {
            // Keep current password
            $update_sql = "UPDATE employes SET nom = ?, prenom = ?, email = ?, service = ?, actif = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt, "ssssii", $nom, $prenom, $email, $service, $actif, $id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $succes = "Les informations de l'employé ont été modifiées avec succès !";
            header("refresh:1.5;url=employes.php");
        } else {
            $erreur = "Une erreur est survenue lors de la modification.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIRA SAS - Modifier Employé</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-brand">CIRA SAS</div>
        <div class="header-nav">
            <a href="index.php">Tableau de bord</a>
            <a href="employes.php">Gestion des employés</a>
            <a href="admin_qr.php">Afficher QR Bureau</a>
            <a href="presence.php">Rapport de présence</a>
            <a href="logout.php" style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 4px; font-weight: bold;">Se déconnecter</a>
        </div>
    </div>

    <!-- MAIN BODY -->
    <div class="form-container">
        
        <div class="premium-card">
            
            <div class="top-bar" style="margin-bottom: 30px;">
                <div>
                    <h2>✏️ Modifier l'employé</h2>
                    <p style="color: #64748b; font-size: 14px; margin-top: 5px;">Modifiez les accès ou les informations de <?= htmlspecialchars($employee['prenom'] . ' ' . $employee['nom']) ?>.</p>
                </div>
                <div>
                    <a href="employes.php" class="btn-retour">⬅ Annuler</a>
                </div>
            </div>

            <!-- NOTIFICATIONS -->
            <?php if (!empty($erreur)) { ?>
                <div class="error">⚠️ <?php echo htmlspecialchars($erreur); ?></div>
            <?php } ?>
            <?php if (!empty($succes)) { ?>
                <div class="success">✅ <?php echo htmlspecialchars($succes); ?></div>
            <?php } ?>

            <!-- FORM -->
            <form method="POST">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label for="nom">Nom de famille</label>
                        <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($employee['nom']) ?>" required>
                    </div>
                    <div>
                        <label for="prenom">Prénom</label>
                        <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($employee['prenom']) ?>" required>
                    </div>
                </div>

                <label for="email">Adresse Email Professionnelle</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($employee['email']) ?>" required>

                <label for="mot_de_passe">Nouveau mot de passe</label>
                <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Laisser vide si inchangé">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label for="service">Service / Département</label>
                        <select name="service" id="service" required>
                            <option value="">-- Choisir le service --</option>
                            <option value="informatique" <?= $employee['service'] === 'informatique' ? 'selected' : '' ?>>Informatique</option>
                            <option value="comptabilite" <?= $employee['service'] === 'comptabilite' ? 'selected' : '' ?>>Comptabilité</option>
                            <option value="rh" <?= $employee['service'] === 'rh' ? 'selected' : '' ?>>Ressources Humaines</option>
                            <option value="ingenieur" <?= $employee['service'] === 'ingenieur' ? 'selected' : '' ?>>Ingénierie & Travaux</option>
                        </select>
                    </div>
                    <div>
                        <label for="actif">Statut du compte</label>
                        <select name="actif" id="actif" required>
                            <option value="1" <?= $employee['actif'] == 1 ? 'selected' : '' ?>>Actif (Accès autorisé)</option>
                            <option value="0" <?= $employee['actif'] == 0 ? 'selected' : '' ?>>Inactif (Accès révoqué)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 15px; display: flex; justify-content: flex-end;">
                    <button type="submit" name="modifier" class="login-btn" style="width: auto; padding: 14px 35px;">💾 Enregistrer les Modifications</button>
                </div>

            </form>
            
        </div>

    </div>

</body>
</html>