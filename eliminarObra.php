<?php
session_start();
require 'includes/conexion.php';

// Validar que se reciba un id válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
            alert('ID de obra no válido.');
            window.location.href='buscarObra.php';
          </script>";
    exit;
}

$id_obra = (int) $_GET['id'];

// Si tienes que eliminar primero los datos relacionados en la tabla `inversion` (en caso de haber FK sin ON DELETE CASCADE),
// podrías hacerlo con una consulta adicional. Por ejemplo:
// $sql_inversion = "DELETE FROM inversion WHERE id_obra = ?";
// $stmt_inversion = $conn->prepare($sql_inversion);
// $stmt_inversion->bind_param("i", $id_obra);
// $stmt_inversion->execute();
// $stmt_inversion->close();

// Preparar consulta DELETE
$sql = "DELETE FROM obra WHERE id_obra = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<script>
            alert('Error preparando la consulta: " . addslashes($conn->error) . "');
            window.history.back();
          </script>";
    exit;
}

$stmt->bind_param("i", $id_obra);

if ($stmt->execute()) {
    echo "<script>
            alert('Obra eliminada correctamente.');
            window.location.href='buscarObra.php';
          </script>";
} else {
    echo "<script>
            alert('Error al eliminar la obra: " . addslashes($stmt->error) . "');
            window.history.back();
          </script>";
}

$stmt->close();
$conn->close();
?>
