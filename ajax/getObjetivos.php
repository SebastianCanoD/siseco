<?php
include '../includes/conexion.php';
$ideje = (int)$_POST['ideje'];
$stmt = $conn->prepare("SELECT id_objetivo, nombre FROM objetivo WHERE id_eje=? ORDER BY id_objetivo");
$stmt->bind_param('i',$ideje);
$stmt->execute();
$res = $stmt->get_result();
echo '<option value="">Seleccione...</option>';
while($r=$res->fetch_assoc()){
  echo "<option value=\"{$r['id_objetivo']}\">".htmlspecialchars($r['nombre'])."</option>";
}
