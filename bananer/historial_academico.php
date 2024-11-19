<?php
session_start();
include 'db_connection.php'; // Conexión a la base de datos

// Verificamos si la conexión es exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificamos si el formulario ha sido enviado
if (isset($_POST['numero_estudiante'])) {
    $numero_estudiante = $_POST['numero_estudiante'];

    // Preparamos la consulta SQL usando el número del estudiante
    $sql = "SELECT curso, nota, calificacion, periodo FROM historial_academico WHERE numero_estudiante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_estudiante); // "i" para entero
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Académico</title>
</head>
<body>
    <h1>Historial Académico del Estudiante</h1>

    <!-- Formulario para ingresar el número del estudiante -->
    <form method="post" action="historial_academico.php">
        <label for="numero_estudiante">Número de Estudiante:</label>
        <input type="text" id="numero_estudiante" name="numero_estudiante" required>
        <input type="submit" value="Ver Historial">
    </form>

    <?php if (isset($result)) { ?>
        <table border="1">
            <tr>
                <th>Curso</th>
                <th>Nota</th>
                <th>Calificación</th>
                <th>Periodo</th>
            </tr>

            <?php
            // Si se encontraron resultados
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['curso']}</td>
                            <td>{$row['nota']}</td>
                            <td>{$row['calificacion']}</td>
                            <td>{$row['periodo']}</td>
                          </tr>";
                }
            } else {
                // Si no se encontraron registros
                echo "<tr><td colspan='4'>No se encontraron registros para el estudiante.</td></tr>";
            }
            ?>
        </table>
    <?php } ?>

</body>
</html>

<?php
// Cerramos la conexión
$conn->close();
?>
