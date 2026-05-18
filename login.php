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
            $_SESSION['employe_id'] = $id;
            $_SESSION['role'] = $role;

            // Direct route based on role
            if ($role === 'admin') {
                header("Location: presence.php");
                exit();
            } else {
                header("Location: scan.php");
                exit();
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
    <title>CIRA SAS - Connexion Employé</title>
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
        }

        .login-brand {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .login-subtitle {
            color: #94a3b8;
            font-size: 14px;
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
            border-color: #6366f1;
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.25);
        }

        .login-card button {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            margin-top: 10px;
        }

        .login-card button:hover {
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
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
            <p class="login-subtitle">Connexion Espace Employé & Pointage</p>

            <?php if (!empty($erreur)) { ?>
                <div class="error">⚠️ <?php echo htmlspecialchars($erreur); ?></div>
            <?php } ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <input type="email" name="email" id="email" placeholder="exemple@cira.com" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-btn">Se connecter</button>
            </form>

            <div class="switch-login">
                Vous êtes administrateur ? <a href="login_admin.php">Connexion Admin</a>
            </div>
            
        </div>
    </div>

</body>
</html>