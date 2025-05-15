<?php
session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar que el id sea válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID no válido.'); window.location.href='buscarMetasBeneficio.php';</script>";
    exit;
}

$id = (int) $_GET['id'];

// Verificar si la meta de beneficio está vinculada en la tabla obra
$sqlCheck = "SELECT COUNT(*) AS total FROM obra WHERE id_beneficiarios = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param('i', $id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result()->fetch_assoc();
$totalRelacionados = (int)$resultCheck['total'];

$stmtCheck->close();

// Si hay registros vinculados, impedir la eliminación
if ($totalRelacionados > 0) {
    echo "<script>alert('No se puede eliminar esta meta de beneficio porque está vinculada a $totalRelacionados obra(s).'); window.location.href='buscarMetasBeneficio.php';</script>";
    exit;
}

// Proceder con la eliminación si no hay relaciones activas
$sql = "DELETE FROM unidad_beneficiarios WHERE id_beneficiarios = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo "<script>alert('Meta de beneficio eliminada correctamente.'); window.location.href='buscarMetasBeneficio.php';</script>";
} else {
    echo "<script>alert('Error al eliminar la meta de beneficio: " . addslashes($stmt->error) . "'); history.back();</script>";
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>
