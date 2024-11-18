<?php
session_start();
include 'db_connection.php'; // Archivo que manejará la conexión a la base de datos.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Consulta para verificar el email y la contraseña
    $sql = "SELECT * FROM usuarios WHERE email_institucional = '$email' AND clave = '$password'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Si la consulta devuelve resultados, significa que el usuario existe
        $_SESSION['email'] = $email; // Guarda el email en la sesión
        header('Location: menu.php'); // Redirige al menú
    } else {
        echo "ID o clave incorrecta";
    }
}
?>

<!-- HTML para el formulario de login -->
<form action="login.php" method="POST">
    <label for="email">ID (Email Institucional):</label>
    <input type="text" id="email" name="email" required><br>
    <label for="password">Clave:</label>
    <input type="password" id="password" name="password" required><br>
    <input type="submit" value="Ingresar">
</form>
