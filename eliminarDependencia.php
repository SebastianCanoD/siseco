<?php
session_start();
require __DIR__ . '/includes/conexion.php';

// Verificar que el id sea válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID no válido.'); window.location.href='buscarDependencia.php';</script>";
    exit;
}

$id = (int) $_GET['id'];

// Verificar si la dependencia está vinculada en otras tablas (obra, usuario)
$sqlCheckObra = "SELECT COUNT(*) AS total FROM obra WHERE id_dependencia = ?";
$sqlCheckUsuario = "SELECT COUNT(*) AS total FROM usuario WHERE id_dependencia = ?";

$stmtCheckObra = $conn->prepare($sqlCheckObra);
$stmtCheckObra->bind_param('i', $id);
$stmtCheckObra->execute();
$resultCheckObra = $stmtCheckObra->get_result()->fetch_assoc();
$totalRelacionadosObra = (int)$resultCheckObra['total'];
$stmtCheckObra->close();

$stmtCheckUsuario = $conn->prepare($sqlCheckUsuario);
$stmtCheckUsuario->bind_param('i', $id);
$stmtCheckUsuario->execute();
$resultCheckUsuario = $stmtCheckUsuario->get_result()->fetch_assoc();
$totalRelacionadosUsuario = (int)$resultCheckUsuario['total'];
$stmtCheckUsuario->close();

// Si hay registros vinculados, impedir la eliminación
if ($totalRelacionadosObra > 0 || $totalRelacionadosUsuario > 0) {
    echo "<script>alert('No se puede eliminar esta dependencia porque está vinculada a $totalRelacionadosObra obra(s) y $totalRelacionadosUsuario usuario(s).'); window.location.href='buscarDependencia.php';</script>";
    exit;
}

// Proceder con la eliminación si no hay relaciones activas
$sql = "DELETE FROM dependencia WHERE id_dependencia = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo "<script>alert('Dependencia eliminada correctamente.'); window.location.href='buscarDependencia.php';</script>";
} else {
    echo "<script>alert('Error al eliminar la dependencia: " . addslashes($stmt->error) . "'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>
