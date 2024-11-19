<?php
session_start();
include 'db_connection.php'; // Conexión a la base de datos

// Verificar si se proporciona el archivo a procesar
$nombre_archivo = isset($_GET['archivo']) ? $_GET['archivo'] : null;
if (!$nombre_archivo) {
    die("Error: No se ha especificado un archivo para procesar.");
}

// Verificar si el archivo existe
if (!file_exists($nombre_archivo)) {
    die("Error: El archivo $nombre_archivo no se encuentra.");
}

// Procesar el archivo CSV de notas
$archivo = fopen($nombre_archivo, 'r');
if (!$archivo) {
    die("Error: No se pudo abrir el archivo $nombre_archivo.\n");
}

$delimitador = ';';

// Leer el encabezado del archivo
$encabezado = fgetcsv($archivo, 0, $delimitador);

// Crear la tabla temporal `acta`
$crear_tabla_temporal = "CREATE TEMPORARY TABLE IF NOT EXISTS acta (
    numero_alumno INT,
    run INT,
    asignatura VARCHAR(255),
    seccion VARCHAR(50),
    periodo VARCHAR(10),
    oportunidad_dic FLOAT,
    oportunidad_mar FLOAT
)";
if (!$conn->query($crear_tabla_temporal)) {
    die("Error al crear la tabla temporal: " . $conn->error);
}

// Iniciar la transacción
$conn->begin_transaction();

try {
    // Procesar todas las filas del archivo
    while (($fila = fgetcsv($archivo, 0, $delimitador)) !== false) {
        $numero_alumno = trim($fila[0]);
        $run = trim($fila[1]);
        $asignatura = trim($fila[2]);
        $seccion = trim($fila[3]);
        $periodo = trim($fila[4]);
        $oportunidad_dic = str_replace(',', '.', trim($fila[5])); // Convertir a formato decimal
        $oportunidad_mar = isset($fila[6]) ? str_replace(',', '.', trim($fila[6])) : null;

        // Validar las notas según las reglas de negocio (ejemplo de validación básica)
        if ($oportunidad_dic !== '' && (!is_numeric($oportunidad_dic) || $oportunidad_dic < 1.0 || $oportunidad_dic > 7.0)) {
            throw new Exception("Nota inválida para la oportunidad DIC del número de alumno $numero_alumno.");
        }
        if ($oportunidad_mar !== null && $oportunidad_mar !== '' && (!is_numeric($oportunidad_mar) || $oportunidad_mar < 1.0 || $oportunidad_mar > 7.0)) {
            throw new Exception("Nota inválida para la oportunidad MAR del número de alumno $numero_alumno.");
        }

        // Insertar en la tabla temporal
        $stmt = $conn->prepare("INSERT INTO acta (numero_alumno, run, asignatura, seccion, periodo, oportunidad_dic, oportunidad_mar) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssdd", $numero_alumno, $run, $asignatura, $seccion, $periodo, $oportunidad_dic, $oportunidad_mar);
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar datos: " . $stmt->error);
        }
    }

    // Confirmar la transacción si todo es válido
    $conn->commit();
    echo "Datos procesados e insertados correctamente en la tabla temporal 'acta'.\n";

} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "\n";
}

fclose($archivo);
?>

