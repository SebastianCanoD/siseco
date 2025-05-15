<?php
// modificarDependenciaProceso.php

session_start();
require __DIR__ . '/includes/conexion.php';

// 1. Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buscarDependencia.php');
    exit;
}

// 2. Recoger y sanear
$id_dependencia = isset($_POST['id_dependencia']) ? (int) $_POST['id_dependencia'] : 0;
$nombre        = trim($_POST['nombre']      ?? '');
$abreviatura   = trim($_POST['abreviatura'] ?? '');

// 3. Validar
if ($id_dependencia <= 0 || $nombre === '' || $abreviatura === '') {
    echo "<script>
            alert('Nombre y abreviatura son obligatorios.');
            history.back();
          </script>";
    exit;
}

// 4. Preparar UPDATE
$sql = "
    UPDATE dependencia
    SET nombre      = ?,
        abreviatura = ?
    WHERE id_dependencia = ?
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<script>
            alert('Error al preparar la consulta: " . addslashes($conn->error) . "');
            history.back();
          </script>";
    exit;
}

// 5. Bind y ejecución
$stmt->bind_param('ssi', $nombre, $abreviatura, $id_dependencia);
if ($stmt->execute()) {
    echo "<script>
            alert('Dependencia actualizada correctamente.');
            window.location.href = 'buscarDependencia.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// 6. Cerrar
$stmt->close();
$conn->close();
exit;
