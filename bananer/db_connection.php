<?php
// Configuración de la conexión a la base de datos
$host = '127.0.0.1'; // Usé 127.0.0.1 en vez de localhost para evitar problemas de socket
$db = 'bananer';     // Nombre de la base de datos 
$user = 'root';      // Usuario predeterminado de MySQL
$password = 'tu_contraseña';    

// Crear la conexión
$conn = new mysqli($host, $user, $password, $db);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
