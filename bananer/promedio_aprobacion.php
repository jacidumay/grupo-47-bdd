<?php
session_start();
include 'db_connection.php'; // Conexión a la base de datos

$codigo_asignatura = $_POST['codigo_asignatura']; // Código de la asignatura ingresado

// Escapar el valor para evitar inyección SQL
$codigo_asignatura = $conn->real_escape_string($codigo_asignatura);

// Consulta para calcular el promedio del porcentaje de aprobación agrupado por profesor
$sql = "
SELECT 
    p.nombre_docente AS profesor,
    AVG(CASE WHEN n.nota >= 4.0 THEN 1.0 ELSE 0 END) * 100 AS porcentaje_aprobacion
FROM Notas n
JOIN Planeacion p ON n.`codigo_asignatura` = p.id_asignatura
WHERE n.`codigo_asignatura` = '$codigo_asignatura'
GROUP BY p.nombre_docente";

$result = $conn->query($sql);

// Verificar si la consulta tuvo éxito
if ($result === false) {
    echo "Error en la consulta SQL: " . $conn->error;
    exit; // Termina el script si hay un error
}

// Mostrar resultados
?>

<h1>Promedio de Aprobación para la Asignatura <?php echo htmlspecialchars($codigo_asignatura); ?></h1>
<table>
    <tr>
        <th>Profesor</th>
        <th>Porcentaje de Aprobación</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['profesor']); ?></td>
            <td><?php echo number_format($row['porcentaje_aprobacion'], 2); ?>%</td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="2">No se encontraron resultados para el curso <?php echo htmlspecialchars($codigo_asignatura); ?>.</td>
        </tr>
    <?php endif; ?>
</table>
