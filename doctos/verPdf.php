<?php
// verPdf.php

// Ruta al archivo PDF (exactamente donde lo guardas)
$file = __DIR__ . '/plan2127.pdf';

if (!is_readable($file)) {
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    exit('Archivo no encontrado.');
}

// Limpia cualquier buffer abierto
while (ob_get_level()) ob_end_clean();

// Cabeceras para PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="plan2127.pdf"');
header('Content-Length: '.filesize($file));

// Streaming
readfile($file);
exit;
