<?php
$host = "localhost";
$user = "root";
$pass = ""; // En MAMP la contraseña por defecto de MySQL suele ser "root"
$db   = "escuela";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>