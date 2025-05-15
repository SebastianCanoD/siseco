<?php
// altaUsuarioProceso.php
session_start();
require __DIR__ . '/includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: altaUsuario.php');
    exit;
}

// 1) Captura y sanea campos; usuario viene indefinido, así que lo inicializamos a cadena vacía
$usuarioInput    = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$nombre          = trim($_POST['nombre']);
$paterno         = trim($_POST['paterno']);
$materno         = trim($_POST['materno']);
$notrabaja       = trim($_POST['notrabaja']);
$cargo           = trim($_POST['cargo']);
$mail            = trim($_POST['mail']);
$nivel           = (int) $_POST['nivel'];
$id_dependencia  = (int) $_POST['id_dependencia'];

// 2) Generar nombre de usuario si no lo ingresaron
function usuarioExiste($conn, $u) {
    $st = $conn->prepare("SELECT 1 FROM usuario WHERE usuario = ?");
    $st->bind_param("s", $u);
    $st->execute();
    $res = $st->get_result();
    $st->close();
    return $res->num_rows > 0;
}
if ($usuarioInput === '') {
    $base = strtolower(substr($nombre, 0, 1) . $paterno);
    $usuario = $base;
    $i = 1;
    while (usuarioExiste($conn, $usuario)) {
        $usuario = $base . $i++;
    }
} else {
    $usuario = $usuarioInput;
}

// 3) Definir contraseña en texto plano (igual al número de trabajador)
$plainPass = $notrabaja ?: bin2hex(random_bytes(4));

// 4) Hashear contraseña
$hashPass = password_hash($plainPass, PASSWORD_DEFAULT);

// 5) Preparar e insertar
$sql = "INSERT INTO usuario
        (usuario, contrasena, nombre, paterno, materno, notrabaja, cargo, mail, nivel, id_dependencia)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssii",
    $usuario,
    $hashPass,
    $nombre,
    $paterno,
    $materno,
    $notrabaja,
    $cargo,
    $mail,
    $nivel,
    $id_dependencia
);

// 6) Ejecutar y redireccionar
if ($stmt->execute()) {
    echo "<script>
            alert('Usuario creado con éxito!\\nUsuario: {$usuario}\\nContraseña: {$plainPass}');
            window.location.href = 'buscarUsuario.php';
          </script>";
} else {
    echo "<script>
            alert('Error al crear usuario: " . addslashes($stmt->error) . "');
            history.back();
          </script>";
}

// 7) Cerrar
$stmt->close();
$conn->close();
exit;
