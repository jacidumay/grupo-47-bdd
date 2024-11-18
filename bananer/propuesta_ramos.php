<?php
session_start();
include 'db_connection.php'; // Conexión a la base de datos

$numero_estudiante = $_POST['numero_estudiante']; // Número de estudiante ingresado

// Escapar el valor para evitar inyección SQL
$numero_estudiante = $conn->real_escape_string($numero_estudiante);

// Verificar si el estudiante está vigente en 2024-2
$sql_verificar = "
SELECT COUNT(*) AS vigente
FROM Estudiantes
WHERE `numero_de_alumno` = '$numero_estudiante' AND `Cohorte` = '2024-2'";

$result_verificar = $conn->query($sql_verificar);
if ($result_verificar === false) {
    echo "Error en la consulta SQL (verificación de estudiante): " . $conn->error;
    exit; // Terminar el script si hay un error
}

$vigente = $result_verificar->fetch_assoc();

if ($vigente['vigente'] == 0) {
    echo "El estudiante no está vigente en el periodo 2024-2.";
    exit();
}

// Propuesta de cursos basados en cursos aprobados
$sql_cursos_propuesta = "
SELECT p.id_asignatura AS codigo
FROM Planeacion p
WHERE p.id_asignatura NOT IN (
    SELECT n.`Código Asignatura` FROM Notas n 
    WHERE n.`numero_de_alumno` = '$numero_estudiante' AND n.nota >= 4.0
)
AND p.nivel > (
    SELECT MAX(p2.nivel) FROM Planeacion p2
    JOIN Notas n2 ON p2.id_asignatura = n2.`Código Asignatura`
    WHERE n2.`numero_de_alumno` = '$numero_estudiante'
)";

// Ejecutar la consulta de propuesta de cursos
$result = $conn->query($sql_cursos_propuesta);
if ($result === false) {
    echo "Error en la consulta SQL (propuesta de cursos): " . $conn->error;
    exit; // Terminar el script si hay un error
}
?>

<h1>Propuesta de Toma de Ramos</h1>
<ul>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li><?php echo htmlspecialchars($row['codigo']); ?></li>
        <?php endwhile; ?>
    <?php else: ?>
        <li>No hay cursos disponibles para este estudiante.</li>
    <?php endif; ?>
</ul>
