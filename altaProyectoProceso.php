<?php
session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: altaProyecto.php');
    exit;
}

// Obtener y validar el nombre del proyecto
$nombre = isset($_POST['proyectoNombre']) ? trim($_POST['proyectoNombre']) : '';

if (empty($nombre)) {
    echo "<script>
            alert('El nombre del proyecto no puede estar vacío.');
            history.back();
          </script>";
    exit;
}

// Insertar en la tabla 'proyecto'
$sql = "INSERT INTO proyecto (proyecto_nombre) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $nombre);

if ($stmt->execute()) {
    echo "<script>
            alert('Proyecto registrado correctamente.');
            window.location.href = 'buscarProyecto.php';
          </script>";
} else {
    echo "<script>
            alert('Error al registrar el proyecto: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

$stmt->close();
$conn->close();
