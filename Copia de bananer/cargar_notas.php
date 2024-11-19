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

// Llamar al Stored Procedure 'cargar_notas'
$sql = "CALL cargar_notas()";

// Ejecutar el procedimiento almacenado
if ($conn->query($sql) === TRUE) {
    echo "Notas cargadas correctamente";
} else {
    echo "Error al cargar las notas: " . $conn->error;
}

$conn->close();
?>
