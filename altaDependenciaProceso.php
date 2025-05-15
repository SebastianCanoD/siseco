<?php
session_start();
require 'includes/conexion.php';

// 1. Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: altaDependencia.php');
    exit;
}

// 2. Recoger y sanear
$nombre      = trim($_POST['dependencia']  ?? '');
$abreviatura = trim($_POST['abreviatura'] ?? '');

if ($nombre === '' || $abreviatura === '') {
    echo "<script>
            alert('Nombre y abreviatura no pueden quedar vacíos.');
            history.back();
          </script>";
    exit;
}

// 3. Preparar INSERT
$sql = "INSERT INTO dependencia (nombre, abreviatura) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<script>
            alert('Error preparando consulta: ". addslashes($conn->error) ."');
            history.back();
          </script>";
    exit;
}

// 4. Ejecutar
$stmt->bind_param('ss', $nombre, $abreviatura);
if ($stmt->execute()) {
    echo "<script>
            alert('Dependencia registrada correctamente.');
            window.location.href = 'buscarDependencia.php';
          </script>";
} else {
    echo "<script>
            alert('Error al registrar la dependencia: ". addslashes($stmt->error) ."');
            history.back();
          </script>";
}

// 5. Cerrar
$stmt->close();
$conn->close();
exit;
