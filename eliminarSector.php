<?php
session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar que el id sea válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID no válido.'); window.location.href='buscarSector.php';</script>";
    exit;
}

$id = (int) $_GET['id'];

// Verificar si el sector está vinculado en la tabla obra
$sqlCheck = "SELECT COUNT(*) AS total FROM obra WHERE id_sector = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param('i', $id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result()->fetch_assoc();
$totalRelacionados = (int)$resultCheck['total'];
$stmtCheck->close();

// Si hay registros vinculados, impedir la eliminación
if ($totalRelacionados > 0) {
    echo "<script>alert('No se puede eliminar este sector porque está vinculado a $totalRelacionados obra(s).'); window.location.href='buscarSector.php';</script>";
    exit;
}

// Proceder con la eliminación si no hay relaciones activas
$sql = "DELETE FROM sector WHERE id_sector = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo "<script>alert('Sector eliminado correctamente.'); window.location.href='buscarSector.php';</script>";
} else {
    echo "<script>alert('Error al eliminar el sector: " . addslashes($stmt->error) . "'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>
