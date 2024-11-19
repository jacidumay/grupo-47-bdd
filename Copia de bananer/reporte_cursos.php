<?php
session_start();
include 'db_connection.php'; 

$periodo = $_POST['periodo']; // Período ingresado por el usuario

// Escapar el valor para evitar inyección SQL
$periodo = $conn->real_escape_string($periodo);

// Consulta para obtener las asignaturas, profesores y porcentaje de aprobación
$sql = "
SELECT 
    p.id_asignatura AS codigo, 
    p.asignatura AS nombre, 
    p.nombre_docente AS profesor,
    COUNT(CASE WHEN n.nota >= 4.0 THEN 1 END) / COUNT(*) * 100 AS porcentaje_aprobacion
FROM 
    Planeacion p
JOIN 
    Notas n ON p.id_asignatura = n.codigo_asignatura  -- Aquí se usa id_asignatura
WHERE 
    p.periodo = '$periodo'
GROUP BY 
    p.id_asignatura, p.asignatura, p.nombre_docente";

// Ejecutar la consulta
$result = $conn->query($sql);

// Manejar el error si la consulta falla
if ($result === false) {
    echo "Error en la consulta SQL: " . $conn->error;
    exit; // Terminar el script si hay un error
}
?>

<h1>Reporte de Asignaturas para el Periodo <?php echo htmlspecialchars($periodo); ?></h1>
<table>
    <tr>
        <th>Código de la Asignatura</th>
        <th>Nombre de la Asignatura</th>
        <th>Profesor</th>
        <th>Porcentaje de Aprobación</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
            <td><?php echo htmlspecialchars($row['profesor']); ?></td>
            <td><?php echo htmlspecialchars(number_format($row['porcentaje_aprobacion'], 2)); ?>%</td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="4">No se encontraron resultados para el periodo <?php echo htmlspecialchars($periodo); ?>.</td>
        </tr>
    <?php endif; ?>
</table>
