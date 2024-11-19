<?php
session_start();
include 'db_connection.php'; // Conexión a la base de datos

$numero_estudiante = $_POST['numero_estudiante']; // Número de estudiante ingresado

// Escapar el valor para evitar inyección SQL
$numero_estudiante = $conn->real_escape_string($numero_estudiante);

// Consulta para obtener el historial académico
$sql = "
SELECT p.asignatura, n.nota, n.calificacion, p.periodo
FROM Notas n
JOIN Planeacion p ON n.`codigo_asignatura` = p.id_asignatura
WHERE n.`numero_alumno` = '$numero_estudiante'
ORDER BY p.periodo ASC";

$result = $conn->query($sql);

// Manejar el error si la consulta falla
if ($result === false) {
    echo "Error en la consulta SQL: " . $conn->error;
    exit; // Terminar el script si hay un error
}
?>

<h1>Historial Académico del Estudiante</h1>
<table>
    <tr>
        <th>Curso</th>
        <th>Nota</th>
        <th>Calificación</th>
        <th>Periodo</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['asignatura']); ?></td>
            <td><?php echo htmlspecialchars($row['nota']); ?></td>
            <td><?php echo htmlspecialchars($row['calificacion']); ?></td>
            <td><?php echo htmlspecialchars($row['periodo']); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="4">No se encontraron registros para el estudiante.</td>
        </tr>
    <?php endif; ?>
</table>

