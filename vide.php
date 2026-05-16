<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>



/* bouton de connexion ===== LOGIN (ADMIN + EMPLOYE) ===== */
.login-btn {
    background: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
}






<style
body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center; /* centre horizontal */
    align-items: center;     /* centre vertical */
    background-color: #f5f5f5;
}

.container {
    text-align: center;
}
</style>


<input type="text" name="service" placeholder="Service" required><br><br>




$sql = "SELECT id FROM employes WHERE qr_token = ? AND actif = 1";

remplacer

$employe_id = $_SESSION['employe_id'];