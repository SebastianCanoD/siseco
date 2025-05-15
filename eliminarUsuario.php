<?php
session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar que el id sea válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID no válido.'); window.location.href='buscarUsuario.php';</script>";
    exit;
}

$id = (int) $_GET['id'];

// Proceder con la eliminación del usuario
$sql = "DELETE FROM usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo "<script>alert('Usuario eliminado correctamente.'); window.location.href='buscarUsuario.php';</script>";
} else {
    echo "<script>alert('Error al eliminar el usuario: " . addslashes($stmt->error) . "'); history.back();</script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>
