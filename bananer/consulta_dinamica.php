<?php
// Configuración de conexión a la base de datos
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

function sanitizarEntrada($entrada) {
    $entrada = trim($entrada);
    $entrada = strip_tags($entrada);
    $entrada = htmlspecialchars($entrada, ENT_QUOTES, 'UTF-8');
    return $entrada;
}

// Función para obtener las tablas de la base de datos
function obtenerTablas($conexion) {
    $tablas = [];
    $consulta = $conexion->query("SHOW TABLES");
    while ($tabla = $consulta->fetch_array()) {
        $tablas[] = $tabla[0];
    }
    return $tablas;
}

// Función para obtener columnas de una tabla
function obtenerColumnas($conexion, $tabla) {
    $columnas = [];
    $consulta = $conexion->query("SHOW COLUMNS FROM $tabla");
    while ($columna = $consulta->fetch_assoc()) {
        $columnas[] = $columna['Field'];
    }
    return $columnas;
}

// Conexión a la base de datos
$conexion = new mysqli($host, $usuario, $password, $basedatos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consulta SQL Segura</title>
</head>
<body>
    <h2>Consulta SQL Dinámica</h2>
    
    <form method="POST" action="">
        <label>Seleccionar Tabla:</label>
        <select name="tabla" required>
            <?php 
            $tablas = obtenerTablas($conexion);
            foreach ($tablas as $tabla) {
                echo "<option value='$tabla'>$tabla</option>";
            }
            ?>
        </select>
        
        <label>Atributos (separados por coma):</label>
        <input type="text" name="atributos" required placeholder="columna1, columna2">
        
        <label>Condición WHERE:</label>
        <input type="text" name="condicion" placeholder="columna = valor">
        
        <input type="submit" name="ejecutar_consulta" value="Ejecutar Consulta">
    </form>

    <?php
    if (isset($_POST['ejecutar_consulta'])) {
        try {
            // Sanitizar y validar entradas
            $tabla = sanitizarEntrada($_POST['tabla']);
            $atributos = sanitizarEntrada($_POST['atributos']);
            $condicion = sanitizarEntrada($_POST['condicion']);

            // Verificar que la tabla exista
            if (!in_array($tabla, $tablas)) {
                throw new Exception("Tabla no válida");
            }

            // Verificar columnas
            $columnasTabla = obtenerColumnas($conexion, $tabla);
            $atributosArray = array_map('trim', explode(',', $atributos));
            
            foreach ($atributosArray as $atributo) {
                if (!in_array($atributo, $columnasTabla)) {
                    throw new Exception("Columna '$atributo' no existe en la tabla");
                }
            }

            $consultaSQL = "SELECT " . implode(", ", $atributosArray) . " FROM $tabla";
            $condicionesArray = array();
            
            if (!empty($condicion)) {
                $condicionesArray = explode(" AND ", $condicion);
                $consultaSQL .= " WHERE ";
                $i = 0;
                $whereConditions = array();
                foreach ($condicionesArray as $cond) {
                    $whereConditions[] = "?";
                    $i++;
                }
                $consultaSQL .= implode(" AND ", $whereConditions);
                $stmt = $conexion->prepare($consultaSQL);
                $stmt->bind_param(str_repeat("s", count($condicionesArray)), ...$condicionesArray);
                $stmt->execute();
                $resultado = $stmt->get_result();
            } else {
                $resultado = $conexion->query($consultaSQL);
            }

            if ($resultado) {
                echo "<h3>Resultados:</h3>";
                echo "<table border='1'>";
                
                // Encabezados
                echo "<tr>";
                foreach ($atributosArray as $atributo) {
                    echo "<th>$atributo</th>";
                }
                echo "</tr>";

                // Datos
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($atributosArray as $atributo) {
                        echo "<td>" . htmlspecialchars($fila[$atributo]) . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                throw new Exception("Error en la consulta: " . $conexion->error);
            }

        } catch (Exception $e) {
            echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
        }
    }
    ?>
</body>
</html>
<?php
// Cerrar conexión
$conexion->close();
?>