<?php

// Configuración para mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Ejecutando cargador_asignaturas.php...\n";
include 'cargador_asignaturas.php';
echo "Finalizó ccargador_asignaturas.php.\n\n";

echo "Ejecutando cargador_doc_plan.php...\n";
include 'cargador_doc_plan.php';
echo "Finalizó cargador_doc_plan.php.\n\n";

echo "Ejecutando cargador_estudiantes.php...\n";
include 'cargador_estudiantes.php';
echo "Finalizó cargador_estudiantes.php.\n\n";

echo "Ejecutando cargador_notas.php...\n";
include 'cargador_notas.php';
echo "Finalizó cargador_notas.php.\n\n";

echo "Ejecutando cargador_planeacion.php...\n";
include 'cargador_planeacion.php';
echo "Finalizó cargador_planeacion.php.\n\n";

echo "Ejecutando cargador_planes.php...\n";
include 'cargador_planes.php';
echo "Finalizó cargador_planes.php.\n\n";

echo "Ejecutando cargador_prerequisitos.php...\n";
include 'cargador_prerequisitos.php';
echo "Finalizó cargador_prerequisitos.php.\n\n";

// Mensaje final
echo "Todos los archivos se han procesado correctamente.\n";

?>