<?php
session_start();
include "db.php";

// Set timezone just in case
date_default_timezone_set('Africa/Bamako');

// Detect if this is an AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Unified helper function to respond (JSON or beautiful HTML page)
function respond($status, $message, $extra = []) {
    global $is_ajax;
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'status' => $status, 
            'message' => $message
        ], $extra));
        exit();
    } else {
        // Fallback for direct browser visits (generic camera scan)
        $_SESSION['scan_feedback'] = [
            'status' => $status,
            'message' => $message,
            'extra' => $extra
        ];
        // Render a premium validation page below
    }
}

// 1. Authentication Check
if (!isset($_SESSION['employe_id'])) {
    respond("error", "Accès refusé. Vous devez être connecté sur votre espace pour effectuer un pointage.");
}

// 2. Token Check
if (!isset($_REQUEST['token']) || empty(trim($_REQUEST['token']))) {
    respond("error", "Jeton QR Code manquant ou invalide.");
}

$token = trim($_REQUEST['token']);
$date_today = date("Y-m-d");
$heure_actuelle = date("H:i:s");
$employe_id = $_SESSION['employe_id'];

// Get current employee details
$sql_emp = "SELECT nom, prenom, service FROM employes WHERE id = ?";
$stmt_emp = mysqli_prepare($conn, $sql_emp);
mysqli_stmt_bind_param($stmt_emp, "i", $employe_id);
mysqli_stmt_execute($stmt_emp);
$res_emp = mysqli_stmt_get_result($stmt_emp);
$current_employe = mysqli_fetch_assoc($res_emp);

if (!$current_employe) {
    respond("error", "Compte employé introuvable.");
}
$nom_complet = $current_employe['prenom'] . " " . $current_employe['nom'];

// 3. Process Pointage Logic
// First check: Is this token an Office Entrance/Exit QR Code?
$sql_qr = "SELECT type, actif FROM qr_codes WHERE token = ?";
$stmt_qr = mysqli_prepare($conn, $sql_qr);
mysqli_stmt_bind_param($stmt_qr, "s", $token);
mysqli_stmt_execute($stmt_qr);
$res_qr = mysqli_stmt_get_result($stmt_qr);
$office_qr = mysqli_fetch_assoc($res_qr);

if ($office_qr) {
    // Flow A: Employee scanned the office's QR Code
    if (!$office_qr['actif']) {
        respond("error", "Ce QR Code de bureau a été désactivé par l'administrateur.");
    }
    
    $type_pointage = $office_qr['type']; // 'ENTREE' or 'SORTIE'
    
    // Check if a presence record already exists for today
    $sql_pres = "SELECT id, heure_entree, heure_sortie FROM presences WHERE employe_id = ? AND date_presence = ?";
    $stmt_pres = mysqli_prepare($conn, $sql_pres);
    mysqli_stmt_bind_param($stmt_pres, "is", $employe_id, $date_today);
    mysqli_stmt_execute($stmt_pres);
    $res_pres = mysqli_stmt_get_result($stmt_pres);
    $presence_today = mysqli_fetch_assoc($res_pres);
    
    if ($type_pointage === 'ENTREE') {
        if ($presence_today) {
            respond("error", "Vous avez déjà enregistré votre arrivée (entrée) aujourd'hui à " . date("H:i", strtotime($presence_today['heure_entree'])) . ".");
        }
        
        // Register Entry
        $sql_ins = "INSERT INTO presences (employe_id, date_presence, heure_entree, statut) VALUES (?, ?, ?, 'present')";
        $stmt_ins = mysqli_prepare($conn, $sql_ins);
        mysqli_stmt_bind_param($stmt_ins, "iss", $employe_id, $date_today, $heure_actuelle);
        mysqli_stmt_execute($stmt_ins);
        
        respond("success", "Entrée enregistrée avec succès !", [
            "time" => $heure_actuelle, 
            "type" => "ENTREE", 
            "employee" => $nom_complet
        ]);
        
    } else {
        // SORTIE (Exit)
        if (!$presence_today) {
            // Edge case: Employee forgot to check in but scans exit. Create record with blank entry.
            $sql_ins = "INSERT INTO presences (employe_id, date_presence, heure_sortie, statut) VALUES (?, ?, ?, 'present')";
            $stmt_ins = mysqli_prepare($conn, $sql_ins);
            mysqli_stmt_bind_param($stmt_ins, "iss", $employe_id, $date_today, $heure_actuelle);
            mysqli_stmt_execute($stmt_ins);
        } else {
            if ($presence_today['heure_sortie'] !== null) {
                respond("error", "Vous avez déjà enregistré votre départ (sortie) aujourd'hui à " . date("H:i", strtotime($presence_today['heure_sortie'])) . ".");
            }
            
            // Register Exit
            $sql_upd = "UPDATE presences SET heure_sortie = ? WHERE id = ?";
            $stmt_upd = mysqli_prepare($conn, $sql_upd);
            mysqli_stmt_bind_param($stmt_upd, "si", $heure_actuelle, $presence_today['id']);
            mysqli_stmt_execute($stmt_upd);
        }
        
        respond("success", "Sortie enregistrée avec succès !", [
            "time" => $heure_actuelle, 
            "type" => "SORTIE", 
            "employee" => $nom_complet
        ]);
    }
    
} else {
    // Flow B: Fallback Kiosk Mode / Backward compatibility
    // Check if the scanned token is actually another employee's personal qr_token (Kiosk mode)
    $sql_kiosk = "SELECT id, nom, prenom FROM employes WHERE qr_token = ? OR id = ?";
    $stmt_kiosk = mysqli_prepare($conn, $sql_kiosk);
    mysqli_stmt_bind_param($stmt_kiosk, "ss", $token, $token);
    mysqli_stmt_execute($stmt_kiosk);
    $res_kiosk = mysqli_stmt_get_result($stmt_kiosk);
    $scanned_employee = mysqli_fetch_assoc($res_kiosk);
    
    if (!$scanned_employee) {
        respond("error", "QR Code invalide ou non reconnu.");
    }
    
    $scanned_emp_id = $scanned_employee['id'];
    $scanned_nom_complet = $scanned_employee['prenom'] . " " . $scanned_employee['nom'];
    
    // Check if they have checked in today
    $sql_pres = "SELECT id, heure_entree, heure_sortie FROM presences WHERE employe_id = ? AND date_presence = ?";
    $stmt_pres = mysqli_prepare($conn, $sql_pres);
    mysqli_stmt_bind_param($stmt_pres, "is", $scanned_emp_id, $date_today);
    mysqli_stmt_execute($stmt_pres);
    $res_pres = mysqli_stmt_get_result($stmt_pres);
    $presence_today = mysqli_fetch_assoc($res_pres);
    
    if (!$presence_today) {
        // Register Entry
        $sql_ins = "INSERT INTO presences (employe_id, date_presence, heure_entree, statut) VALUES (?, ?, ?, 'present')";
        $stmt_ins = mysqli_prepare($conn, $sql_ins);
        mysqli_stmt_bind_param($stmt_ins, "iss", $scanned_emp_id, $date_today, $heure_actuelle);
        mysqli_stmt_execute($stmt_ins);
        
        respond("success", "Arrivée enregistrée (Kiosk) !", [
            "time" => $heure_actuelle, 
            "type" => "ENTREE", 
            "employee" => $scanned_nom_complet
        ]);
    } else {
        if ($presence_today['heure_sortie'] !== null) {
            respond("error", "Cet employé a déjà enregistré sa sortie aujourd'hui à " . date("H:i", strtotime($presence_today['heure_sortie'])) . ".");
        }
        
        // Register Exit
        $sql_upd = "UPDATE presences SET heure_sortie = ? WHERE id = ?";
        $stmt_upd = mysqli_prepare($conn, $sql_upd);
        mysqli_stmt_bind_param($stmt_upd, "si", $heure_actuelle, $presence_today['id']);
        mysqli_stmt_execute($stmt_upd);
        
        respond("success", "Départ enregistré (Kiosk) !", [
            "time" => $heure_actuelle, 
            "type" => "SORTIE", 
            "employee" => $scanned_nom_complet
        ]);
    }
}

// -------------------------------------------------------------
// RENDER HTML FOR DIRECT SCAN VISITS (NON-AJAX / GENERIC CAMERA)
// -------------------------------------------------------------
$feedback = $_SESSION['scan_feedback'] ?? [
    'status' => 'error', 
    'message' => 'Une erreur inconnue est survenue.',
    'extra' => []
];

$is_success = ($feedback['status'] === 'success');
$title = $is_success ? "Pointage Validé !" : "Échec du Pointage";
$message = $feedback['message'];
$time = isset($feedback['extra']['time']) ? date("H:i", strtotime($feedback['extra']['time'])) : null;
$type = $feedback['extra']['type'] ?? null;
$employee = $feedback['extra']['employee'] ?? $nom_complet;

// Clear the feedback from session
unset($_SESSION['scan_feedback']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation Pointage - CIRA SAS</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #f1f5f9;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            box-sizing: border-box;
        }

        .card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
        }

        .icon-container {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 25px auto;
        }

        .success-theme .icon-container {
            background: rgba(16, 185, 129, 0.15);
            border: 2px solid #10b981;
            color: #10b981;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .error-theme .icon-container {
            background: rgba(239, 68, 68, 0.15);
            border: 2px solid #ef4444;
            color: #ef4444;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }

        .icon-container svg {
            width: 42px;
            height: 42px;
            fill: currentColor;
        }

        .feedback-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .success-theme .feedback-title { color: #34d399; }
        .error-theme .feedback-title { color: #f87171; }

        .employee-name {
            font-size: 16px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .feedback-msg {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 30px 0;
        }

        .info-pill {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 12px 18px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 700;
            color: white;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .type-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .type-entree { background-color: #10b981; box-shadow: 0 0 8px #10b981; }
        .type-sortie { background-color: #f97316; box-shadow: 0 0 8px #f97316; }

        .btn-action-back {
            background: linear-gradient(90deg, #6366f1, #4f46e5);
            color: white !important;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            width: 100%;
            display: block;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .btn-action-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
    </style>
</head>
<body>

    <div class="container <?php echo $is_success ? 'success-theme' : 'error-theme'; ?>">
        <div class="card">
            
            <div class="icon-container">
                <?php if ($is_success) { ?>
                    <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                <?php } else { ?>
                    <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>
                <?php } ?>
            </div>

            <h2 class="feedback-title"><?php echo $title; ?></h2>
            <div class="employee-name"><?php echo htmlspecialchars($employee); ?></div>
            
            <p class="feedback-msg"><?php echo htmlspecialchars($message); ?></p>

            <?php if ($is_success && $time) { ?>
                <div class="info-pill">
                    <div class="type-indicator <?php echo ($type === 'ENTREE') ? 'type-entree' : 'type-sortie'; ?>"></div>
                    <span><?php echo ($type === 'ENTREE' ? 'Entrée' : 'Sortie') . " à " . $time; ?></span>
                </div>
            <?php } ?>

            <a href="scan.php" class="btn-action-back">Retour à mon Espace</a>

        </div>
    </div>

</body>
</html>