<?php
require_once 'conexion.php';

$cedula = "0000000000";
$nombre = "Administrador General";
$correo = "admin@escuela.com";
$pass_plana = "admin123";
$pass_hash = password_hash($pass_plana, PASSWORD_BCRYPT);
$rol = "administrador";

// Limpiar usuario si existe
$conn->query("DELETE FROM usuarios WHERE correo = '$correo'");

// Insertar nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (cedula, nombre_completo, correo, password, rol) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $cedula, $nombre, $correo, $pass_hash, $rol);

if ($stmt->execute()) {
    echo "¡Usuario Administrador creado con éxito!<br>";
    echo "Correo: admin@escuela.com<br>";
    echo "Contraseña: admin123<br>";
    echo "<a href='index.php'>Ir al Login</a>";
} else {
    echo "Error al crear el usuario: " . $conn->error;
}
?>