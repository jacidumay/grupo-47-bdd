<?php
// Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "tu_contraseña";
$dbname = "bananer";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consultar la vista 'vista_acta'
$sql = "SELECT * FROM vista_acta";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Número de Estudiante</th>
                <th>Curso</th>
                <th>Periodo</th>
                <th>Nombre del Estudiante</th>
                <th>Nombre del Profesor</th>
                <th>Nota Final</th>
            </tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["num_estudiante"]. "</td>
                <td>" . $row["curso"]. "</td>
                <td>" . $row["periodo"]. "</td>
                <td>" . $row["nombre_estudiante"]. "</td>
                <td>" . $row["profesor"]. "</td>
                <td>" . $row["nota_final"]. "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 resultados";
}

$conn->close();
?>
