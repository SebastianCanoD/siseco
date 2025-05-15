<?php
$host = "localhost";
$usuario = "root";
$password = "1123";
$base_de_datos = "sisecoprueba"; // cambia si tu base tiene otro nombre

$conn = new mysqli($host, $usuario, $password, $base_de_datos);

if ($conn->connect_error) {
    die("❌ Conexión fallida: " . $conn->connect_error);
}
?>
