<?php
session_start();
require_once 'conexion.php';

if (isset($_POST['login'])) {
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    // Buscar al usuario por correo
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        
        // Verificar contraseña encriptada
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El correo no está registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CyberSchool - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; min-height:100vh;">
    <div class="glass-panel" style="width: 350px;">
        <h2 style="text-align:center;">ACCESO PORTAL</h2>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="correo" placeholder="Correo Electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="login">Ingresar</button>
        </form>
    </div>
</body>
</html>