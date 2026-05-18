<?php
session_start();
include("db.php");

$erreur = "";

if (isset($_POST['email'], $_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, mot_de_passe, role FROM employes WHERE email = ? AND actif = 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $hash, $role);
    
    if (mysqli_stmt_fetch($stmt)) {
        if (password_verify($password, $hash)) {
            if ($role === 'admin') {
                $_SESSION['employe_id'] = $id;
                $_SESSION['role'] = 'admin';
                header("Location: index.php");
                exit();
            } else {
                $erreur = "Accès refusé. Ce compte n'a pas les droits d'administration.";
            }
        } else {
            $erreur = "Mot de passe incorrect.";
        }
    } else {
        $erreur = "Adresse email incorrecte ou compte inactif.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIRA SAS - Connexion Admin</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 40px 30px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            text-align: center;
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
        }

        .login-brand {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: white;
            margin-bottom: 5px;
            margin-top: 10px;
        }

        .login-subtitle {
            color: #818cf8;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .login-card input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 0;
        }

        .login-card input:focus {
            border-color: #a855f7;
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.25);
        }

        .login-card button {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            margin-top: 10px;
        }

        .login-card button:hover {
            box-shadow: 0 6px 20px rgba(168, 85, 247, 0.45);
        }

        .switch-login {
            margin-top: 25px;
            font-size: 14px;
            color: #64748b;
        }

        .switch-login a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .switch-login a:hover {
            color: #a5b4fc;
            text-decoration: underline;
        }
        
        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: left;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="login-brand">CIRA SAS</div>
            <p class="login-subtitle">🔐 Espace Administration</p>

            <?php if (!empty($erreur)) { ?>
                <div class="error">⚠️ <?php echo htmlspecialchars($erreur); ?></div>
            <?php } ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Adresse Email Administrateur</label>
                    <input type="email" name="email" id="email" placeholder="admin@cira.com" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-btn">Accéder au Dashboard</button>
            </form>

            <div class="switch-login">
                Vous êtes un employé ? <a href="login.php">Espace Employé</a>
            </div>
            
        </div>
    </div>

</body>
</html>
