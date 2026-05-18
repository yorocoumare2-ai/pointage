<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

include "db.php";

// Helper function to find or create an active token for a given type
function getOrCreateToken($conn, $type) {
    $sql = "SELECT token FROM qr_codes WHERE type = ? AND actif = 1 LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row) {
        return $row['token'];
    }
    
    // Generate secure random token
    $token = bin2hex(random_bytes(16));
    $sql_ins = "INSERT INTO qr_codes (token, type, actif) VALUES (?, ?, 1)";
    $stmt_ins = mysqli_prepare($conn, $sql_ins);
    mysqli_stmt_bind_param($stmt_ins, "ss", $token, $type);
    mysqli_stmt_execute($stmt_ins);
    
    return $token;
}

// Check if a regeneration is requested
if (isset($_GET['regenerate'])) {
    $regen_type = $_GET['regenerate'] === 'ENTREE' ? 'ENTREE' : 'SORTIE';
    
    // Deactivate old active tokens for this type
    $sql_deact = "UPDATE qr_codes SET actif = 0 WHERE type = ?";
    $stmt_deact = mysqli_prepare($conn, $sql_deact);
    mysqli_stmt_bind_param($stmt_deact, "s", $regen_type);
    mysqli_stmt_execute($stmt_deact);
    
    // Redirect to clear URL parameter and force regeneration
    header("Location: admin_qr.php");
    exit();
}

$token_entree = getOrCreateToken($conn, 'ENTREE');
$token_sortie = getOrCreateToken($conn, 'SORTIE');

// Get the actual host & protocol being used to visit the admin panel
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$host/cira_pointage";

// Scanned links contained inside the QR codes
$link_entree = "$base_url/traitement_scan.php?token=" . $token_entree;
$link_sortie = "$base_url/traitement_scan.php?token=" . $token_sortie;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIRA SAS - Gestion des QR Codes de Pointage</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f3f7fa 0%, #e6eef4 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* HEADER MODERN */
        .header {
            background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header-brand {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header-nav a {
            color: rgba(255,255,255,0.9);
            margin-left: 20px;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .header-nav a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.15);
        }

        /* CONTAINER */
        .qr-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .page-intro {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-intro h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 10px;
        }

        .page-intro p {
            color: #64748b;
            font-size: 16px;
            max-width: 600px;
            margin: 0 auto;
        }

        /* GRID CARDS */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        /* PREMIUM GLASS CARD */
        .qr-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(30, 60, 114, 0.08);
            border: 1px solid rgba(255,255,255,0.8);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            position: relative;
        }

        .qr-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(30, 60, 114, 0.15);
        }

        .qr-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
        }

        .card-entree::before {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }

        .card-sortie::before {
            background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
        }

        .card-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .card-entree .card-badge {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .card-sortie .card-badge {
            background-color: #fff7ed;
            color: #9a3412;
        }

        .qr-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 10px 0;
            text-align: center;
        }

        .qr-subtitle {
            font-size: 14px;
            color: #64748b;
            text-align: center;
            margin-bottom: 30px;
        }

        /* QR IMAGE CONTAINER */
        .qr-wrapper {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            transition: all 0.3s;
        }

        .qr-wrapper img {
            width: 250px;
            height: 250px;
            display: block;
        }

        /* ACTIONS */
        .card-actions {
            display: flex;
            gap: 15px;
            width: 100%;
        }

        .qr-btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-print {
            background-color: #1e3c72;
            color: white;
            box-shadow: 0 4px 10px rgba(30, 60, 114, 0.2);
        }

        .btn-print:hover {
            background-color: #2a5298;
            transform: translateY(-2px);
        }

        .btn-regen {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-regen:hover {
            background-color: #e2e8f0;
            color: #1e293b;
            transform: translateY(-2px);
        }

        /* GENERAL BACK BUTTON */
        .back-section {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 50px;
        }

        /* --- PRINT VIEW STYLING --- */
        @media print {
            body {
                background: white;
                color: black;
            }
            .header, .page-intro, .card-actions, .back-section, .btn-regen, .btn-print, .qr-subtitle {
                display: none !important;
            }
            .qr-container {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .cards-grid {
                display: block;
            }
            .qr-card {
                border: none;
                box-shadow: none;
                padding: 0;
                margin: 0;
                page-break-after: always;
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .qr-card::before {
                display: none;
            }
            
            /* Print Poster Content */
            .qr-card .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 40px;
            }
            .qr-card .print-header h1 {
                font-size: 48px;
                color: #1e3c72;
                margin: 0 0 10px 0;
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            .qr-card .print-header h2 {
                font-size: 28px;
                color: #475569;
                margin: 0;
            }
            .qr-wrapper {
                border: none;
                background: white;
                padding: 0;
                margin: 40px 0;
            }
            .qr-wrapper img {
                width: 450px;
                height: 450px;
            }
            .print-instructions {
                display: block !important;
                text-align: center;
                max-width: 600px;
                margin-top: 40px;
                border-top: 2px solid #e2e8f0;
                padding-top: 30px;
            }
            .print-instructions p {
                font-size: 22px;
                margin: 10px 0;
                color: #334155;
                font-weight: 500;
            }
            .print-instructions span {
                font-size: 16px;
                color: #64748b;
            }
        }

        /* Hidden in web view, active in print */
        .print-header, .print-instructions {
            display: none;
        }

        @media (max-width: 992px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }
            .qr-card {
                padding: 30px;
            }
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
            <a href="admin_qr.php" style="background: rgba(255, 255, 255, 0.2); color: white;">Afficher QR Bureau</a>
            <a href="presence.php">Rapport de présence</a>
            <a href="logout.php" style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 4px; font-weight: bold;">Se déconnecter</a>
        </div>
    </div>

    <!-- MAIN BODY -->
    <div class="qr-container">
        
        <div class="page-intro">
            <h1>QR Codes de Pointage du Bureau</h1>
            <p>Affichez ces QR Codes sur une tablette à l'accueil ou imprimez-les pour les coller sur le mur à l'entrée et à la sortie des locaux. Les employés les scanneront avec leur smartphone.</p>
        </div>

        <div class="cards-grid">
            
            <!-- CARTE ENTREE -->
            <div class="qr-card card-entree" id="print-area-entree">
                <!-- PRINT HEADER (ONLY VISIBLE ON PRINT) -->
                <div class="print-header">
                    <h1>CIRA SAS</h1>
                    <h2>POINTAGE : ENTRÉE 📥</h2>
                </div>

                <div class="card-badge">POINTAGE ENTRÉE</div>
                <h2 class="qr-title">QR Code d'Entrée</h2>
                <div class="qr-subtitle">Scannez ce code pour enregistrer votre arrivée au travail.</div>

                <div class="qr-wrapper">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=<?php echo urlencode($link_entree); ?>" alt="QR Code Entrée">
                </div>

                <!-- PRINT INSTRUCTIONS (ONLY VISIBLE ON PRINT) -->
                <div class="print-instructions">
                    <p>👉 Connectez-vous sur votre téléphone.</p>
                    <p>👉 Cliquez sur "Scanner" et scannez ce code.</p>
                    <span>ID Unique : <?php echo htmlspecialchars($token_entree); ?></span>
                </div>

                <div class="card-actions">
                    <button onclick="printCard('print-area-entree')" class="qr-btn btn-print">
                        🖨️ Imprimer l'affiche
                    </button>
                    <a href="admin_qr.php?regenerate=ENTREE" class="qr-btn btn-regen" onclick="return confirm('Sécurité : Voulez-vous vraiment régénérer ce QR Code ? L\'ancien code ne fonctionnera plus du tout.');">
                        🔄 Régénérer le code
                    </a>
                </div>
            </div>

            <!-- CARTE SORTIE -->
            <div class="qr-card card-sortie" id="print-area-sortie">
                <!-- PRINT HEADER (ONLY VISIBLE ON PRINT) -->
                <div class="print-header">
                    <h1>CIRA SAS</h1>
                    <h2>POINTAGE : SORTIE 📤</h2>
                </div>

                <div class="card-badge">POINTAGE SORTIE</div>
                <h2 class="qr-title">QR Code de Sortie</h2>
                <div class="qr-subtitle">Scannez ce code pour enregistrer votre départ du travail.</div>

                <div class="qr-wrapper">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=<?php echo urlencode($link_sortie); ?>" alt="QR Code Sortie">
                </div>

                <!-- PRINT INSTRUCTIONS (ONLY VISIBLE ON PRINT) -->
                <div class="print-instructions">
                    <p>👉 Connectez-vous sur votre téléphone.</p>
                    <p>👉 Cliquez sur "Scanner" et scannez ce code.</p>
                    <span>ID Unique : <?php echo htmlspecialchars($token_sortie); ?></span>
                </div>

                <div class="card-actions">
                    <button onclick="printCard('print-area-sortie')" class="qr-btn btn-print">
                        🖨️ Imprimer l'affiche
                    </button>
                    <a href="admin_qr.php?regenerate=SORTIE" class="qr-btn btn-regen" onclick="return confirm('Sécurité : Voulez-vous vraiment régénérer ce QR Code ? L\'ancien code ne fonctionnera plus du tout.');">
                        🔄 Régénérer le code
                    </a>
                </div>
            </div>

        </div>

        <div class="back-section">
            <a href="index.php" class="btn-retour">⬅ Retour au Tableau de bord Admin</a>
        </div>

    </div>

    <script>
        function printCard(cardId) {
            // Store original body classes/styles
            var originalContent = document.body.innerHTML;
            var cardContent = document.getElementById(cardId).innerHTML;
            
            // To ensure we get perfect print sizes, temporarily swap the body content to ONLY contain the card
            document.body.innerHTML = '<div class="qr-container"><div class="qr-card ' + 
                (cardId === "print-area-entree" ? "card-entree" : "card-sortie") + 
                '" style="height:100vh; justify-content:center; border:none; box-shadow:none;">' + 
                cardContent + '</div></div>';
            
            // Trigger print dialog
            window.print();
            
            // Restore page immediately
            document.body.innerHTML = originalContent;
            
            // Reload page to restore JS bindings
            window.location.reload();
        }
    </script>
</body>
</html>
