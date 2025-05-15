<?php
// header.php
// Inicia sesión solo si no hay una sesión activa
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SISECO</title>
  <!-- Bootstrap CSS -->
  <link href="/siseco/assets/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .header-container {
      text-align: center;
      padding: 10px;
    }

    .header-image img {
      max-width: 100%;
      height: auto;
    }

    :root {
      --navbar-bg: rgba(116, 16, 16, 0.9);
    }

    .navbar {
      background: var(--navbar-bg);
      background-size: cover;
    }

    .dropdown-submenu {
      position: relative;
    }

    .dropdown-submenu>.dropdown-menu {
      top: 0;
      left: 100%;
      margin-top: -1px;
    }
  </style>
</head>

<body>
  <!-- Header con imagen centrada -->
  <div class="header-container">
    <div class="header-image">
      <img src="assets/siseco.jpg" alt="SISECO" class="img-fluid">
    </div>
  </div>

  <!-- Menú de navegación -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Obras</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="capturarObra.php">Capturar obra</a></li>
              <li><a class="dropdown-item" href="buscarObra.php">Buscar obra</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Anexo</a>
            <ul class="dropdown-menu">
              <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Reporte para ejercicio fiscal</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="reporteDesglose.php">Desglose</a></li>
                  <li><a class="dropdown-item" href="reporteResumen.php">Resumen</a></li>
                </ul>
              </li>
              <li class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="#">Reporte para informe de gobierno</a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="informeGobiernoDesglose.php">Desglose</a></li>
                  <li><a class="dropdown-item" href="informeGobiernoResumen.php">Resumen</a></li>
                </ul>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Documentos</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="doctos/verPdf.php" target="_blank">Descargar plan estatal</a></li>
            </ul>
          </li>
          <?php
          // Mostrar el submenú de Administración solo para nivel 1 (administrador)
          if (isset($_SESSION['nivel']) && (int) $_SESSION['nivel'] === 1):
            ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Administración</a>
              <ul class="dropdown-menu">
                <li class="dropdown-submenu">
                  <a class="dropdown-item dropdown-toggle" href="#">Agregar</a>
                  <ul class="dropdown-menu">
                    <li class="dropdown-submenu">
                      <a class="dropdown-item dropdown-toggle" href="#">Usuario</a>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="altaUsuario.php">Alta usuario</a></li>
                        <li><a class="dropdown-item" href="buscarUsuario.php">Buscar usuario</a></li>
                      </ul>
                    </li>
                    <li class="dropdown-submenu">
                      <a class="dropdown-item dropdown-toggle" href="#">Dependencia</a>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="altaDependencia.php">Alta dependencia</a></li>
                        <li><a class="dropdown-item" href="buscarDependencia.php">Buscar dependencia</a></li>
                      </ul>
                    </li>
                    <li class="dropdown-submenu">
                      <a class="dropdown-item dropdown-toggle" href="#">Catálogos</a>
                      <ul class="dropdown-menu">
                        <li class="dropdown-submenu">
                          <a class="dropdown-item dropdown-toggle" href="#">Programa de inversión</a>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="altaProgramaInversion.php">Alta</a></li>
                            <li><a class="dropdown-item" href="buscarProgramaInversion.php">Buscar</a></li>
                          </ul>
                        </li>
                        <!-- ... más catálogos ... -->
                        <!-- Subsubsubmenu: Metas de servicio -->
                        <li class="dropdown-submenu">
                          <a class="dropdown-item dropdown-toggle" href="#">Metas de servicio</a>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="altaMetasServicio.php">Alta</a></li>
                            <li><a class="dropdown-item" href="buscarMetasServicio.php">Buscar</a></li>
                          </ul>
                        </li>
                        <!-- Subsubsubmenu: Metas de beneficio -->
                        <li class="dropdown-submenu">
                          <a class="dropdown-item dropdown-toggle" href="#">Metas de beneficio</a>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="altaMetasBeneficio.php">Alta</a></li>
                            <li><a class="dropdown-item" href="buscarMetasBeneficio.php">Buscar</a></li>
                          </ul>
                        </li>
                        <!-- Subsubsubmenu: Nuevo sector -->
                        <li class="dropdown-submenu">
                          <a class="dropdown-item dropdown-toggle" href="#">Nuevo sector</a>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="altaSector.php">Alta</a></li>
                            <li><a class="dropdown-item" href="buscarSector.php">Buscar</a></li>
                          </ul>
                        </li>
                        <!-- Subsubsubmenu: Nuevo proyecto -->
                        <li class="dropdown-submenu">
                          <a class="dropdown-item dropdown-toggle" href="#">Nuevo proyecto</a>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="altaProyecto.php">Alta</a></li>
                            <li><a class="dropdown-item" href="buscarProyecto.php">Buscar</a></li>
                          </ul>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Salir</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- Bootstrap Bundle JS -->
<script src="/siseco/assets/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.navbar-nav .dropdown').forEach(function (dropdown) {
        dropdown.addEventListener('mouseenter', function () {
          dropdown.classList.add('show');
          let menu = dropdown.querySelector('.dropdown-menu'); if (menu) menu.classList.add('show');
        });
        dropdown.addEventListener('mouseleave', function () {
          dropdown.classList.remove('show');
          let menu = dropdown.querySelector('.dropdown-menu'); if (menu) menu.classList.remove('show');
        });
      });
      document.querySelectorAll('.dropdown-submenu').forEach(function (submenu) {
        submenu.addEventListener('mouseenter', function () {
          let menu = submenu.querySelector('.dropdown-menu'); if (menu) menu.classList.add('show');
        });
        submenu.addEventListener('mouseleave', function () {
          let menu = submenu.querySelector('.dropdown-menu'); if (menu) menu.classList.remove('show');
        });
      });
    });
  </script>
</body>

</html>