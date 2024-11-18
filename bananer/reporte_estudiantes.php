<?php
session_start();
include 'db_connection.php'; // Conexión a la base de datos

// Consulta para contar los estudiantes dentro de nivel
$sql_dentro_nivel = "
SELECT COUNT(*) AS estudiantes_dentro_nivel
FROM Estudiantes
WHERE `Cohorte` = '2020-1' AND `Logro` = '2 AÑO' AND `fecha_logro` = '2024-02-01'"; // Asegúrate de que la fecha esté en el formato correcto

$sql_fuera_nivel = "
SELECT COUNT(*) AS estudiantes_fuera_nivel
FROM Estudiantes
WHERE `Cohorte` = '2020-1' AND (`Logro` != '2 AÑO' OR `fecha_logro` != '2024-02-01')"; // Asegúrate de que la fecha esté en el formato correcto

// Ejecutar consultas
$result_dentro = $conn->query($sql_dentro_nivel);
if ($result_dentro === false) {
    echo "Error en la consulta SQL (dentro de nivel): " . $conn->error;
    exit; // Termina el script si hay un error
}

$result_fuera = $conn->query($sql_fuera_nivel);
if ($result_fuera === false) {
    echo "Error en la consulta SQL (fuera de nivel): " . $conn->error;
    exit; // Termina el script si hay un error
}

// Obtener los resultados
$dentro_nivel = $result_dentro->fetch_assoc();
$fuera_nivel = $result_fuera->fetch_assoc();
?>

<h1>Reporte de Estudiantes Vigentes</h1>
<p>Estudiantes dentro de nivel: <?php echo htmlspecialchars($dentro_nivel['estudiantes_dentro_nivel']); ?></p>
<p>Estudiantes fuera de nivel: <?php echo htmlspecialchars($fuera_nivel['estudiantes_fuera_nivel']); ?></p>
