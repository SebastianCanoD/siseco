<?php
session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar que el id sea válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID no válido.'); window.location.href='buscarMetasServicio.php';</script>";
    exit;
}

$id = (int) $_GET['id'];

// Verificar si la meta de servicio está vinculada en la tabla obra
$sqlCheck = "SELECT COUNT(*) AS total FROM obra WHERE id_servicio = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param('i', $id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result()->fetch_assoc();
$totalRelacionados = (int)$resultCheck['total'];
$stmtCheck->close();

// Si hay registros vinculados, impedir la eliminación
if ($totalRelacionados > 0) {
    echo "<script>alert('No se puede eliminar esta meta de servicio porque está vinculada a $totalRelacionados obra(s).'); window.location.href='buscarMetasServicio.php';</script>";
    exit;
}

// Proceder con la eliminación si no hay relaciones activas
$sql = "DELETE FROM unidad_servicio WHERE id_servicio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo "<script>alert('Meta de servicio eliminada correctamente.'); window.location.href='buscarMetasServicio.php';</script>";
} else {
    echo "<script>alert('Error al eliminar la meta de servicio: " . addslashes($stmt->error) . "'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>
