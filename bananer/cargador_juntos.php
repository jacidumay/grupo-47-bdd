<?php
function esVacio($valor) {
    // Considera como vacío: cadenas en blanco, espacios, null, false
    return $valor === null || trim($valor) === '';
}

function crearTablaDesdeCSV($conexion, $rutaCSV, $nombreTabla) {
    // Abrir el archivo CSV
    $archivo = fopen($rutaCSV, 'r');
    
    // Leer la primera línea para obtener los encabezados
    $encabezados = fgetcsv($archivo);
    
    // Construir la consulta de creación de tabla
    $consultaCreacion = "CREATE TABLE IF NOT EXISTS $nombreTabla (";
    
    // Agregar columnas permitiendo NULL
    foreach ($encabezados as $columna) {
        $nombreColumna = str_replace(' ', '_', $columna);
        $consultaCreacion .= "`$nombreColumna` VARCHAR(255) NULL, ";
    }
    
    // Eliminar última coma y cerrar paréntesis
    $consultaCreacion = rtrim($consultaCreacion, ', ') . ")";
    
    // Ejecutar creación de tabla
    if ($conexion->query($consultaCreacion) === TRUE) {
        echo "Tabla $nombreTabla creada exitosamente<br>";
    } else {
        echo "Error creando tabla: " . $conexion->error . "<br>";
    }
    
    // Preparar consulta de inserción
    $campos = implode(',', array_map(function($col) { 
        return "`" . str_replace(' ', '_', $col) . "`"; 
    }, $encabezados));
    
    $placeholders = implode(',', array_fill(0, count($encabezados), '?'));
    
    $consultaInsercion = "INSERT INTO $nombreTabla ($campos) VALUES ($placeholders)";
    $stmt = $conexion->prepare($consultaInsercion);
    
    // Insertar datos
    $filasInsertadas = 0;
    $filasOmitidas = 0;
    
    while (($fila = fgetcsv($archivo)) !== FALSE) {
        // Mapear valores vacíos a NULL
        $filaFormateada = array_map(function($valor) {
            return esVacio($valor) ? NULL : trim($valor);
        }, $fila);
        
        // Omitir filas completamente vacías
        if (count(array_filter($filaFormateada)) === 0) {
            $filasOmitidas++;
            continue;
        }
        
        $tipos = str_repeat('s', count($filaFormateada));
        $stmt->bind_param($tipos, ...$filaFormateada);
        
        if ($stmt->execute()) {
            $filasInsertadas++;
        } else {
            echo "Error insertando fila: " . $stmt->error . "<br>";
        }
    }
    
    echo "Resumen de importación para $nombreTabla:<br>";
    echo "- Filas insertadas: $filasInsertadas<br>";
    echo "- Filas omitidas: $filasOmitidas<br>";
    
    fclose($archivo);
    $stmt->close();
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

// Archivos CSV a importar
$archivosCSV = [
    'Asignaturas' => 'Asignaturas.csv',
    'Docentes_Planificados' => 'Docentes_Planificados.csv',
    'Estudiantes' => 'Estudiantes.csv',
    'Planeacion' => 'Planeacion.csv',
    'Planes' => 'Planes.csv',
    'Prerequisitos' => 'Prerequisitos.csv',
];

// Importar cada archivo
foreach ($archivosCSV as $nombreTabla => $rutaCSV) {
    crearTablaDesdeCSV($conexion, $rutaCSV, $nombreTabla);
}

$conexion->close();
?>