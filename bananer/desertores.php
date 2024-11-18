<?php
session_start();
include 'db_connection.php'; // Conexión a la base de datos

$periodo = $_POST['periodo']; // Periodo ingresado

// Escapar el valor para evitar inyección SQL
$periodo = $conn->real_escape_string($periodo);

// Consulta para obtener la lista de estudiantes desertores
$sql = "
SELECT e.run AS numero_estudiante, CONCAT(e.nombres, ' ', e.primer_apellido, ' ', e.segundo_apellido) AS nombre_completo
FROM Estudiantes e
WHERE e.run NOT IN (
    SELECT n.run FROM Notas n WHERE n.periodo_asignatura = '$periodo'
)
AND e.logro <= (
    SELECT MAX(n2.periodo_asignatura) FROM Notas n2 WHERE n2.run = e.run
    GROUP BY n2.run
    HAVING MAX(n2.periodo_asignatura) < ('$periodo' - 2)
)";

$result = $conn->query($sql);

// Manejar el error si la consulta falla
if ($result === false) {
    echo "Error en la consulta SQL: " . $conn->error;
    exit; // Termina el script si hay un error
}
?>

<h1>Lista de Estudiantes Desertores para el Periodo <?php echo htmlspecialchars($periodo); ?></h1>
<table>
    <tr>
        <th>Número de Estudiante</th>
        <th>Nombre Completo</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['numero_estudiante']); ?></td>
            <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="2">No se encontraron estudiantes desertores para el periodo <?php echo htmlspecialchars($periodo); ?>.</td>
        </tr>
    <?php endif; ?>
</table>

