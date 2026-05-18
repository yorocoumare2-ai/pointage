<?php
// Démarrer la session
session_start();
include "db.php";

// 🔒 Supprimer toutes les variables de session
$_SESSION = [];

// 🔒 Détruire la session
session_destroy();

// 🔒 Supprimer le cookie de session (très important)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 🔄 Redirection vers la page de connexion admin
header("Location: login_admin.php");
exit();
?>