<?php
$host = "localhost";
$user = "root"; // Usuario por defecto en XAMPP
$pass = ""; // Dejar vacío si no tienes contraseña
$db = "siseco"; // Nombre de tu base de datos

$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
