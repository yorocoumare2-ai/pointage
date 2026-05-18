<?php
session_start();
include "db.php";

if (!isset($_SESSION['employe_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect admin to presence/dashboard
if ($_SESSION['role'] === 'admin') {
    header("Location: presence.php");
    exit();
}

$employe_id = $_SESSION['employe_id'];

// Get employee info
$sql_emp = "SELECT nom, prenom, email, service FROM employes WHERE id = ?";
$stmt_emp = mysqli_prepare($conn, $sql_emp);
mysqli_stmt_bind_param($stmt_emp, "i", $employe_id);
mysqli_stmt_execute($stmt_emp);
$res_emp = mysqli_stmt_get_result($stmt_emp);
$employe = mysqli_fetch_assoc($res_emp);

// Get today's attendance times
$date_today = date("Y-m-d");
$sql_pres = "SELECT heure_entree, heure_sortie FROM presences WHERE employe_id = ? AND date_presence = ? LIMIT 1";
$stmt_pres = mysqli_prepare($conn, $sql_pres);
mysqli_stmt_bind_param($stmt_pres, "is", $employe_id, $date_today);
mysqli_stmt_execute($stmt_pres);
$res_pres = mysqli_stmt_get_result($stmt_pres);
$presence = mysqli_fetch_assoc($res_pres);

$heure_entree = $presence['heure_entree'] ?? null;
$heure_sortie = $presence['heure_sortie'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIRA SAS - Espace Employé & Pointage</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- QR Code Scanner Library via CDN -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
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

        .app-wrapper {
            width: 100%;
            max-width: 480px;
            padding: 20px;
            box-sizing: border-box;
        }

        /* HEADER MOBILE */
        .mobile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .brand-logo {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-logout {
            background: rgba(220, 38, 38, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(220, 38, 38, 0.3);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: rgba(220, 38, 38, 0.4);
            color: white;
        }

        /* USER INFO */
        .user-welcome-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .user-welcome-card h2 {
            font-size: 20px;
            font-weight: 600;
            color: white;
            text-align: left;
            margin: 0 0 5px 0;
        }

        .user-welcome-card p {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ATTENDANCE STATUS CARD */
        .status-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }

        .status-card-title {
            font-size: 14px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
        }

        .status-date {
            color: #6366f1;
        }

        .status-rows {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.03);
        }

        .status-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            font-weight: 500;
            color: #cbd5e1;
        }

        .status-icon {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #475569;
        }

        .status-icon.active {
            box-shadow: 0 0 10px currentColor;
        }

        .status-val {
            font-size: 16px;
            font-weight: 700;
            color: white;
        }

        .status-val.empty {
            color: #475569;
            font-weight: 400;
        }

        /* BIG SCAN TRIGGER BUTTON */
        .scan-action-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .btn-scan-trigger {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #8b5cf6 100%);
            border: 8px solid rgba(99, 102, 241, 0.15);
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.4);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .btn-scan-trigger:hover {
            transform: scale(1.06);
            box-shadow: 0 0 45px rgba(99, 102, 241, 0.6);
            border-color: rgba(99, 102, 241, 0.25);
        }

        .btn-scan-trigger:active {
            transform: scale(0.96);
        }

        .btn-scan-trigger svg {
            width: 44px;
            height: 44px;
            margin-bottom: 8px;
            fill: white;
        }

        .btn-scan-trigger span {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .scan-helper-text {
            font-size: 13px;
            color: #64748b;
            text-align: center;
            max-width: 250px;
            line-height: 1.5;
        }

        /* MODAL FULLSCREEN FOR CAMERA SCANNER */
        .scanner-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #09090b;
            z-index: 1000;
            display: none; /* Controlled by JS */
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 30px 20px;
            box-sizing: border-box;
        }

        .scanner-modal-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .scanner-title {
            font-size: 18px;
            font-weight: 600;
            color: white;
            margin: 0;
        }

        .btn-close-scanner {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 16px;
            transition: 0.2s;
        }

        .btn-close-scanner:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* CAMERA VIEWPORT */
        .camera-viewport-wrapper {
            position: relative;
            width: 100%;
            max-width: 380px;
            aspect-ratio: 1;
            border-radius: 24px;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.1);
            background: #18181b;
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
        }

        #reader {
            width: 100% !important;
            height: 100% !important;
        }

        /* Hide library ugly UI elements */
        #reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
        }
        #reader__dashboard, #reader img {
            display: none !important;
        }

        /* SCANNING LASER AND OVERLAYS */
        .scanner-laser-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            box-sizing: border-box;
            border: 40px solid rgba(9, 9, 11, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 5;
        }

        .scan-frame-corner {
            position: absolute;
            width: 25px;
            height: 25px;
            border-color: #6366f1;
            border-style: solid;
            pointer-events: none;
        }

        /* Border box size of the scan window: ~250px inside 380px (approx) */
        .scan-frame-box {
            position: relative;
            width: 240px;
            height: 240px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            overflow: hidden;
        }

        .corner-tl { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-top-left-radius: 8px;}
        .corner-tr { top: -2px; right: -2px; border-width: 4px 4px 0 0; border-top-right-radius: 8px;}
        .corner-bl { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; border-bottom-left-radius: 8px;}
        .corner-br { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-bottom-right-radius: 8px;}

        /* Moving laser line */
        .laser-line {
            position: absolute;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent 10%, #6366f1 50%, transparent 90%);
            box-shadow: 0 0 10px #6366f1, 0 0 20px #6366f1;
            top: 0;
            animation: laserMove 2.5s infinite linear;
        }

        @keyframes laserMove {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }

        .scanner-footer {
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
            max-width: 280px;
            line-height: 1.5;
            z-index: 10;
        }

        /* SCAN RESPONSE OVERLAYS (MODALS) */
        .response-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(9, 9, 11, 0.95);
            z-index: 1100;
            display: none; /* Controlled by JS */
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .response-card {
            width: 100%;
            max-width: 380px;
            background: #18181b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            transform: scale(0.9);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .response-overlay.active .response-card {
            transform: scale(1);
        }

        .response-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 25px auto;
            position: relative;
        }

        .success-theme .response-icon-wrapper {
            background-color: rgba(16, 185, 129, 0.15);
            border: 2px solid #10b981;
            color: #10b981;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .error-theme .response-icon-wrapper {
            background-color: rgba(239, 68, 68, 0.15);
            border: 2px solid #ef4444;
            color: #ef4444;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }

        .response-icon-wrapper svg {
            width: 40px;
            height: 40px;
            fill: currentColor;
        }

        .response-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .success-theme .response-title { color: #34d399; }
        .error-theme .response-title { color: #f87171; }

        .response-text {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 30px 0;
        }

        .btn-dismiss {
            width: 100%;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .success-theme .btn-dismiss {
            background-color: #10b981;
            color: white;
        }
        .success-theme .btn-dismiss:hover { background-color: #059669; }

        .error-theme .btn-dismiss {
            background-color: #ef4444;
            color: white;
        }
        .error-theme .btn-dismiss:hover { background-color: #dc2626; }
    </style>
</head>
<body>

    <div class="app-wrapper">

        <!-- MOBILE HEADER -->
        <div class="mobile-header">
            <div class="brand-logo">CIRA SAS</div>
            <a href="logout.php" class="btn-logout">Déconnexion</a>
        </div>

        <!-- WELCOME CARD -->
        <div class="user-welcome-card">
            <p><?php echo htmlspecialchars($employe['service']); ?></p>
            <h2>Bonjour, <?php echo htmlspecialchars($employe['prenom'] . ' ' . $employe['nom']); ?> 👋</h2>
        </div>

        <!-- ATTENDANCE CARD -->
        <div class="status-card">
            <div class="status-card-title">
                <span>Pointage du Jour</span>
                <span class="status-date"><?php echo date("d/m/Y"); ?></span>
            </div>

            <div class="status-rows">
                <!-- ROW ENTREE -->
                <div class="status-row">
                    <div class="status-label">
                        <div class="status-icon <?php echo $heure_entree ? 'active' : ''; ?>" style="color:#10b981; background-color: <?php echo $heure_entree ? '#10b981' : '#475569'; ?>"></div>
                        <span>Arrivée (Entrée)</span>
                    </div>
                    <div class="status-val <?php echo !$heure_entree ? 'empty' : ''; ?>" id="display-entree">
                        <?php echo $heure_entree ? date("H:i", strtotime($heure_entree)) : '--:--'; ?>
                    </div>
                </div>

                <!-- ROW SORTIE -->
                <div class="status-row">
                    <div class="status-label">
                        <div class="status-icon <?php echo $heure_sortie ? 'active' : ''; ?>" style="color:#f97316; background-color: <?php echo $heure_sortie ? '#f97316' : '#475569'; ?>"></div>
                        <span>Départ (Sortie)</span>
                    </div>
                    <div class="status-val <?php echo !$heure_sortie ? 'empty' : ''; ?>" id="display-sortie">
                        <?php echo $heure_sortie ? date("H:i", strtotime($heure_sortie)) : '--:--'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SCAN BUTTON ACTION -->
        <div class="scan-action-container">
            <button onclick="openScanner()" class="btn-scan-trigger" id="btn-scan-start">
                <svg viewBox="0 0 24 24">
                    <path d="M4 4h4.5V2H4C2.9 2 2 2.9 2 4v4.5h2V4zm11.5 2H20v4.5h2V4c0-1.1-.9-2-2-2h-4.5v2zM4 15.5H2V20c0 1.1.9 2 2 2h4.5v-2H4v-4.5zM20 15.5h2V20c0 1.1-.9 2-2 2h-4.5v-2H20v-4.5zM7 7h10v10H7V7zm2 2v6h6V9H9z"/>
                </svg>
                <span>Scanner</span>
            </button>
            <div class="scan-helper-text">
                Touchez le bouton pour activer l'appareil photo et scanner le QR Code affiché à l'entrée ou à la sortie.
            </div>
        </div>

    </div>

    <!-- FULLSCREEN CAMERA MODAL -->
    <div class="scanner-modal" id="scanner-modal">
        <div class="scanner-modal-header">
            <h3 class="scanner-title">Scanner un QR Code</h3>
            <button onclick="closeScanner()" class="btn-close-scanner">✕</button>
        </div>

        <div class="camera-viewport-wrapper">
            <div id="reader"></div>
            <!-- Scanning Neon Target Frame -->
            <div class="scanner-laser-overlay">
                <div class="scan-frame-box">
                    <div class="scan-frame-corner corner-tl"></div>
                    <div class="scan-frame-corner corner-tr"></div>
                    <div class="scan-frame-corner corner-bl"></div>
                    <div class="scan-frame-corner corner-br"></div>
                    <div class="laser-line"></div>
                </div>
            </div>
        </div>

        <div class="scanner-footer">
            Cadrez le QR Code de l'entrée ou de la sortie à l'intérieur du repère pour le scanner automatiquement.
        </div>
    </div>

    <!-- SUCCESS / ERROR RESPONSE MODAL -->
    <div class="response-overlay" id="response-modal">
        <div class="response-card" id="response-card">
            <div class="response-icon-wrapper" id="response-icon">
                <!-- SVG Icon injected by JS -->
            </div>
            <h3 class="response-title" id="response-title">Titre</h3>
            <p class="response-text" id="response-text">Message détaillé de réponse.</p>
            <button onclick="closeResponseModal()" class="btn-dismiss">Fermer</button>
        </div>
    </div>

    <script>
        let html5QrCode = null;
        let isScannerRunning = false;

        // --- WEB AUDIO API FOR RETRO BEEPS ---
        function playSuccessSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                
                // Double high pitched beep (bip-bip)
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(600, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.005, audioCtx.currentTime + 0.1);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.1);
                
                setTimeout(() => {
                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(800, audioCtx.currentTime);
                    gain2.gain.setValueAtTime(0.08, audioCtx.currentTime);
                    gain2.gain.exponentialRampToValueAtTime(0.005, audioCtx.currentTime + 0.15);
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.start();
                    osc2.stop(audioCtx.currentTime + 0.15);
                }, 80);
            } catch(e) {
                console.log("Audio non disponible ou bloqué :", e);
            }
        }

        function playErrorSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                
                // Low frequency buzzy warning sound (bzzz)
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(150, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                gain.gain.linearRampToValueAtTime(0.005, audioCtx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.35);
            } catch(e) {
                console.log("Audio non disponible ou bloqué :", e);
            }
        }

        // --- SCANNER MODAL CONTROLS ---
        function openScanner() {
            document.getElementById('scanner-modal').style.display = 'flex';
            
            // Initialize reader
            html5QrCode = new Html5Qrcode("reader");
            const config = { 
                fps: 15, 
                qrbox: function(width, height) {
                    const minSize = Math.min(width, height);
                    const boxSize = Math.floor(minSize * 0.65);
                    return { width: boxSize, height: boxSize };
                }
            };
            
            isScannerRunning = true;
            
            // Launch camera (facing environment = back camera)
            html5QrCode.start(
                { facingMode: "environment" }, 
                config, 
                onScanSuccess, 
                onScanFailure
            ).catch(err => {
                console.error("Camera access error:", err);
                alert("Impossible d'accéder à l'appareil photo. Assurez-vous d'avoir accordé les permissions d'accès dans votre navigateur.");
                closeScanner();
            });
        }

        function closeScanner() {
            if (html5QrCode && isScannerRunning) {
                html5QrCode.stop().then(() => {
                    isScannerRunning = false;
                    document.getElementById('scanner-modal').style.display = 'none';
                }).catch(err => {
                    console.error("Failed to stop scanner:", err);
                    document.getElementById('scanner-modal').style.display = 'none';
                });
            } else {
                document.getElementById('scanner-modal').style.display = 'none';
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Success! Immediately stop camera
            closeScanner();
            
            // Extract the token parameter if the QR Code contains a full URL
            let token = decodedText;
            try {
                if (decodedText.startsWith('http://') || decodedText.startsWith('https://')) {
                    const url = new URL(decodedText);
                    const urlParams = new URLSearchParams(url.search);
                    if (urlParams.has('token')) {
                        token = urlParams.get('token');
                    }
                }
            } catch (e) {
                console.log("Could not parse scanned text as URL, using raw string.", e);
            }
            
            // Submit token to validation script via AJAX
            sendPointage(token);
        }

        function onScanFailure(error) {
            // Silently ignore typical frame scan misses to avoid flooding console log
        }

        // --- SUBMIT SCAN VIA AJAX ---
        function sendPointage(token) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "traitement_scan.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.status === "success") {
                            playSuccessSound();
                            showResultModal(true, response.message, "Heure enregistrée : " + formatTime(response.time));
                            
                            // Dynamically update the times on screen!
                            const formattedTime = formatTime(response.time);
                            if (response.type === "ENTREE") {
                                const el = document.getElementById("display-entree");
                                el.innerText = formattedTime;
                                el.classList.remove("empty");
                                // activate green circle
                                el.previousElementSibling.firstElementChild.classList.add("active");
                                el.previousElementSibling.firstElementChild.style.backgroundColor = "#10b981";
                            } else if (response.type === "SORTIE") {
                                const el = document.getElementById("display-sortie");
                                el.innerText = formattedTime;
                                el.classList.remove("empty");
                                // activate orange circle
                                el.previousElementSibling.firstElementChild.classList.add("active");
                                el.previousElementSibling.firstElementChild.style.backgroundColor = "#f97316";
                            }
                        } else {
                            playErrorSound();
                            showResultModal(false, "Échec du pointage", response.message);
                        }
                    } catch(e) {
                        console.error("JSON parsing error", e, xhr.responseText);
                        playErrorSound();
                        showResultModal(false, "Erreur Serveur", "Une erreur inattendue est survenue sur le serveur.");
                    }
                } else {
                    playErrorSound();
                    showResultModal(false, "Erreur Réseau", "Impossible de contacter le serveur local.");
                }
            };
            
            xhr.send("token=" + encodeURIComponent(token));
        }

        // Helper to format H:i:s -> H:i
        function formatTime(timeStr) {
            if (!timeStr) return '--:--';
            const parts = timeStr.split(':');
            if (parts.length >= 2) {
                return parts[0] + ":" + parts[1];
            }
            return timeStr;
        }

        // --- RESULTS MODAL POPUP ---
        function showResultModal(isSuccess, title, text) {
            const modal = document.getElementById("response-modal");
            const card = document.getElementById("response-card");
            const iconContainer = document.getElementById("response-icon");
            const titleEl = document.getElementById("response-title");
            const textEl = document.getElementById("response-text");
            
            // Clean modal classes
            card.className = "response-card " + (isSuccess ? "success-theme" : "error-theme");
            
            // Setup Icon
            if (isSuccess) {
                iconContainer.innerHTML = `<svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>`;
            } else {
                iconContainer.innerHTML = `<svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>`;
            }
            
            titleEl.innerText = title;
            textEl.innerText = text;
            
            // Open modal with smooth transition
            modal.style.display = "flex";
            setTimeout(() => {
                modal.classList.add("active");
            }, 10);
        }

        function closeResponseModal() {
            const modal = document.getElementById("response-modal");
            modal.classList.remove("active");
            setTimeout(() => {
                modal.style.display = "none";
            }, 250);
        }
    </script>
</body>
</html>