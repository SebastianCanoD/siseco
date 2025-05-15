<?php
// login_process.php
// Procesa el formulario de login y guarda nivel e id_dependencia en sesión

session_start();
require __DIR__ . '/includes/conexion.php'; // Asegúrate de que esta ruta es correcta y define $conn

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// 1) Recoger y sanitizar
$usuario = trim($_POST['usuario'] ?? '');
$clave = $_POST['clave'] ?? '';

// 2) Preparar y ejecutar consulta
$sql = "SELECT id_usuario, usuario, contrasena, nombre, paterno, materno, nivel, id_dependencia
         FROM usuario
         WHERE usuario = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en prepare(): " . $conn->error);
}
$stmt->bind_param('s', $usuario);
$stmt->execute();
$result = $stmt->get_result();

// 3) Validar usuario y contraseña
if ($result->num_rows === 1) {
    $fila = $result->fetch_assoc();
    if (password_verify($clave, $fila['contrasena'])) {
        // Guardar datos en sesión
        $_SESSION['id_usuario'] = $fila['id_usuario'];
        $_SESSION['usuario'] = $fila['usuario'];
        $_SESSION['nombre'] = $fila['nombre'] . ' ' . $fila['paterno'] . ' ' . $fila['materno'];
        $_SESSION['nivel'] = (int) $fila['nivel'];
        $_SESSION['id_dependencia'] = (int) $fila['id_dependencia'];  // Se agrega este dato

        header('Location: index.php');
        exit;
    } else {
        $error = 'Clave incorrecta.';
    }
} else {
    $error = 'Usuario no encontrado.';
}

// 4) Cerrar recursos y redirigir con error
$stmt->close();
$conn->close();
header('Location: login.php?error=' . urlencode($error));
exit;
?>