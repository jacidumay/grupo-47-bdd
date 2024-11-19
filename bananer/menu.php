<?php
#session_start();
#if (!isset($_SESSION['email'])) {
#    header('Location: login.php'); // Redirigir al login si no ha iniciado sesión
#}
?>

<h1>Menú de Usuario</h1>
<ul>
    <li><a href="reporte_estudiantes.php">Reporte de estudiantes vigentes</a></li>
    <li><a href="reporte_cursos.php">Reporte de cursos por periodo</a></li>
    <li><a href="promedio_aprobacion.php">Promedio histórico de aprobación</a></li>
    <li><a href="propuesta_ramos.php">Propuesta de toma de ramos</a></li>
    <li><a href="historial_academico.php">Historial académico del estudiante</a></li>
    <li><a href="ingresar_notas.php">Subir Notas desde CSV</a></li>
    <li><a href="transaccion_notas.php">Transaccion de Notas</a></li>
    <li><a href="desertores.php">Lista de Desertores</a></li> <!-- Enlace al BONUS -->
</ul>
