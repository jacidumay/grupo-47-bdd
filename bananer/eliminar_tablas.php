<?php
function eliminarTodasLasTablas($conexion, $basedatos) {
    // Desactivar restricciones de clave foránea
    $conexion->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Obtener lista de todas las tablas
    $consulta = $conexion->query("SHOW TABLES");
    
    // Eliminar cada tabla
    while ($tabla = $consulta->fetch_array()) {
        $nombreTabla = $tabla[0];
        $conexion->query("DROP TABLE IF EXISTS `$nombreTabla`");
        echo "Tabla $nombreTabla eliminada<br>";
    }
    
    // Reactivar restricciones de clave foránea
    $conexion->query("SET FOREIGN_KEY_CHECKS = 1");
}

// Configuración de conexión
$host = '127.0.0.1';
$usuario = 'root';
$password = 'tu_contraseña';
$basedatos = 'bananer';

// Crear conexión
$conexion = new mysqli($host, $usuario, $password, $basedatos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Eliminar todas las tablas
eliminarTodasLasTablas($conexion, $basedatos);

// Cerrar conexión
$conexion->close();
?>