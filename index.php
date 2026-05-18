<?php
session_start();

// Ensure admin authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

include "db.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIRA SAS - Administration Pointage</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .qr-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(30, 60, 114, 0.04);
            border: 1px solid rgba(255,255,255,0.8);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            position: relative;
            cursor: pointer;
            text-align: center;
        }

        .qr-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(30, 60, 114, 0.12);
        }

        .qr-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
        }

        .card-emp::before {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }

        .card-qr::before {
            background: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%);
        }

        .card-pres::before {
            background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
        }

        .card-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .card-emp .card-badge {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .card-qr .card-badge {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .card-pres .card-badge {
            background-color: #fff7ed;
            color: #9a3412;
        }

        .qr-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 10px 0;
        }

        .number {
            font-size: 56px;
            font-weight: 700;
            margin: 15px 0;
            background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .qr-subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 0;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-brand">CIRA SAS</div>
        <div class="header-nav">
            <a href="index.php" style="background: rgba(255, 255, 255, 0.2); color: white;">Tableau de bord</a>
            <a href="employes.php">Gestion des employés</a>
            <a href="admin_qr.php">Afficher QR Bureau</a>
            <a href="presence.php">Rapport de présence</a>
            <a href="logout.php" style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 4px; font-weight: bold;">Se déconnecter</a>
        </div>
    </div>

    <!-- MAIN BODY -->
    <div class="container">
        
        <div style="margin-bottom: 40px; text-align: center; margin-top: 20px;">
            <h1 style="font-size: 32px; color: #1e3c72; font-weight: 700;">Tableau de bord Administration</h1>
            <p style="color: #64748b; font-size: 16px; margin-top: 5px;">Bienvenue sur votre espace de pilotage du système de pointage CIRA SAS.</p>
        </div>

        <!-- CARDS GRID -->
        <div class="cards-grid">
            
            <!-- CARD EMPLOYES -->
            <div class="qr-card card-emp" onclick="window.location='employes.php'">
                <div class="card-badge">Base de données</div>
                <h2 class="qr-title">👥 Employés</h2>
                <div class="number">
                    <?php
                    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM employes WHERE role = 'employe'");
                    $data = mysqli_fetch_assoc($res);
                    echo $data['total'];
                    ?>
                </div>
                <p class="qr-subtitle">Gérer les comptes, ajouter de nouveaux employés et administrer les accès.</p>
            </div>

            <!-- CARD QR bureau -->
            <div class="qr-card card-qr" onclick="window.location='admin_qr.php'">
                <div class="card-badge">Affiches Murales</div>
                <h2 class="qr-title">🖨️ QR Codes Bureau</h2>
                <div class="number" style="font-size: 40px; margin: 26px 0;">Entrée / Sortie</div>
                <p class="qr-subtitle">Générer, afficher et imprimer les QR codes de pointage pour l'accueil de l'entreprise.</p>
            </div>

            <!-- CARD PRESENCES -->
            <div class="qr-card card-pres" onclick="window.location='presence.php'">
                <div class="card-badge">Aujourd'hui</div>
                <h2 class="qr-title">📊 Présences</h2>
                <div class="number">
                    <?php
                    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM presences WHERE date_presence = CURDATE()");
                    $data = mysqli_fetch_assoc($res);
                    echo $data['total'];
                    ?>
                </div>
                <p class="qr-subtitle">Consulter les heures d'arrivée, de départ et archiver les rapports de présence journaliers.</p>
            </div>

        </div>

    </div>

</body>
</html>