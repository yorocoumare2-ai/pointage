<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

include("db.php");

$erreur = "";
$succes = "";

if (isset($_POST['Enregistrer'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $service = $_POST['service'];

    // Verify if email is already taken
    $sql_check = "SELECT id FROM employes WHERE email = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $erreur = "Cette adresse email est déjà enregistrée pour un autre employé.";
    } else {
        // Hash the password securely
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO employes (nom, prenom, email, mot_de_passe, service, role, actif, premiere_connexion)
                VALUES (?, ?, ?, ?, ?, 'employe', 1, 1)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $nom, $prenom, $email, $mot_de_passe_hash, $service);
        
        if (mysqli_stmt_execute($stmt)) {
            $succes = "L'employé a été enregistré avec succès !";
            header("refresh:1.5;url=employes.php");
        } else {
            $erreur = "Une erreur est survenue lors de l'enregistrement de l'employé.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIRA SAS - Ajouter un Employé</title>
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
                    <h2>➕ Ajouter un employé</h2>
                    <p style="color: #64748b; font-size: 14px; margin-top: 5px;">Remplissez la fiche d'inscription pour créer un accès employé.</p>
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
                        <input type="text" name="nom" id="nom" placeholder="Ex: COULIBALY" required value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
                    </div>
                    <div>
                        <label for="prenom">Prénom</label>
                        <input type="text" name="prenom" id="prenom" placeholder="Ex: Adama" required value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>">
                    </div>
                </div>

                <label for="email">Adresse Email Professionnelle</label>
                <input type="email" name="email" id="email" placeholder="Ex: a.coulibaly@cira.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

                <label for="mot_de_passe">Mot de passe temporaire</label>
                <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Mot de passe de première connexion" required>

                <label for="service">Service / Département</label>
                <select name="service" id="service" required>
                    <option value="">-- Choisir le service --</option>
                    <option value="informatique" <?= (isset($_POST['service']) && $_POST['service'] === 'informatique') ? 'selected' : '' ?>>Informatique</option>
                    <option value="comptabilite" <?= (isset($_POST['service']) && $_POST['service'] === 'comptabilite') ? 'selected' : '' ?>>Comptabilité</option>
                    <option value="rh" <?= (isset($_POST['service']) && $_POST['service'] === 'rh') ? 'selected' : '' ?>>Ressources Humaines</option>
                    <option value="ingenieur" <?= (isset($_POST['service']) && $_POST['service'] === 'ingenieur') ? 'selected' : '' ?>>Ingénierie & Travaux</option>
                </select>

                <div style="margin-top: 15px; display: flex; justify-content: flex-end;">
                    <button type="submit" name="Enregistrer" class="login-btn" style="width: auto; padding: 14px 35px;">💾 Enregistrer l'Employé</button>
                </div>

            </form>
            
        </div>

    </div>

</body>
</html>