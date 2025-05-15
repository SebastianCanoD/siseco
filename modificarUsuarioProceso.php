<?php
// procesarModificarUsuario.php

session_start();
require __DIR__ . '/includes/conexion.php';

// 1. Verificar petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buscarUsuario.php');
    exit;
}

// 2. Obtener y sanear datos
$id_usuario     = isset($_POST['id_usuario'])     ? (int) $_POST['id_usuario']     : 0;
$nombre         = isset($_POST['nombre'])         ? trim($_POST['nombre'])        : '';
$paterno        = isset($_POST['paterno'])        ? trim($_POST['paterno'])       : '';
$materno        = isset($_POST['materno'])        ? trim($_POST['materno'])       : '';
$notrabaja      = isset($_POST['notrabaja'])      ? trim($_POST['notrabaja'])     : '';
$contrasena     = isset($_POST['contrasena'])     ? trim($_POST['contrasena'])    : '';
$cargo          = isset($_POST['cargo'])          ? trim($_POST['cargo'])         : '';
$mail           = isset($_POST['mail'])           ? trim($_POST['mail'])          : '';
$nivel          = isset($_POST['nivel'])          ? (int) $_POST['nivel']         : 0;
$id_dependencia = isset($_POST['id_dependencia']) ? (int) $_POST['id_dependencia']: 0;

// 3. Construir consulta de actualización
if (!empty($contrasena)) {
    // Incluye cambio de contraseña
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $sql = "UPDATE usuario SET
                nombre         = ?,
                paterno        = ?,
                materno        = ?,
                notrabaja      = ?,
                contrasena     = ?,
                cargo          = ?,
                mail           = ?,
                nivel          = ?,
                id_dependencia = ?
            WHERE id_usuario     = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'sssssssiii',
        $nombre,
        $paterno,
        $materno,
        $notrabaja,
        $hash,
        $cargo,
        $mail,
        $nivel,
        $id_dependencia,
        $id_usuario
    );
} else {
    // Sin cambiar contraseña
    $sql = "UPDATE usuario SET
                nombre         = ?,
                paterno        = ?,
                materno        = ?,
                notrabaja      = ?,
                cargo          = ?,
                mail           = ?,
                nivel          = ?,
                id_dependencia = ?
            WHERE id_usuario     = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'ssssssiii',
        $nombre,
        $paterno,
        $materno,
        $notrabaja,
        $cargo,
        $mail,
        $nivel,
        $id_dependencia,
        $id_usuario
    );
}

// 4. Ejecutar y verificar
if ($stmt->execute()) {
    echo "<script>
            alert('Usuario actualizado correctamente.');
            window.location.href = 'buscarUsuario.php';
          </script>";
} else {
    echo "<script>
            alert('Error al actualizar usuario: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// 5. Cerrar recursos
$stmt->close();
$conn->close();
exit;
