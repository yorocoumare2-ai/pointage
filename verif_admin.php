<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM employes WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {

        if (password_verify($password, $row['mot_de_passe'])) {

            if ($row['role'] == 'admin') {

                // 🔥 SESSION UNIQUE ET COHERENTE
                $_SESSION['employe_id'] = $row['id'];
                $_SESSION['role'] = 'admin';

                header("Location: presence.php");
                exit();

            } else {
                echo "❌ Vous n'êtes pas administrateur";
            }

        } else {
            echo "❌ Mot de passe incorrect";
        }

    } else {
        echo "❌ Email introuvable";
    }

} else {
    header("Location: login_admin.php");
    exit();
}
?>