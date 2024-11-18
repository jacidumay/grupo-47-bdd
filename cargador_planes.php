<?php

require 'vendor/autoload.php'; // Para manejar archivos Excel con PHPSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

// Función para validar y corregir los datos según las reglas especificadas
function validar_y_corregir_planes($fila, $formato) {
    $errores = [];
    $correcciones = [];

    foreach ($formato as $campo => $regla) {
        $no_nulo = strpos($regla, 'no nulo') !== false;

        // Normalizar el valor eliminando espacios en blanco
        $valor = trim($fila[$campo] ?? '');

        // Validar campos que no permiten nulos
        if ($no_nulo && $valor === '') {
            $errores[$campo] = "Valor nulo en el campo '$campo'";
        }
    }

    // Convertir el campo 'plan' a mayúsculas si está presente
    if (isset($fila['plan'])) {
        $fila['plan'] = strtoupper(trim($fila['plan']));
        $correcciones['plan'] = "Campo 'plan' convertido a mayúsculas.";
    }

    return ['errores' => $errores, 'correcciones' => $correcciones, 'fila_corregida' => $fila];
}

// Función para procesar el archivo CSV
function procesar_csv_planes($nombre_archivo, $formato) {
    $archivo = fopen($nombre_archivo, 'r');
    if (!$archivo) {
        echo "Error: No se pudo abrir el archivo $nombre_archivo.\n";
        return;
    }

    $delimitador = ',';

    $archivo_correcto = fopen('correcto_' . $nombre_archivo, 'w');
    $archivo_errores = fopen('errores_' . $nombre_archivo, 'w');

    // Leer encabezado
    $encabezado = fgetcsv($archivo, 0, $delimitador);
    $columnas_encabezado = count($encabezado);

    // Normalizar el encabezado eliminando espacios en blanco y haciendo todo en minúsculas
    $encabezado_normalizado = array_map(function($campo) {
        return strtolower(trim($campo));
    }, $encabezado);

    fputcsv($archivo_correcto, $encabezado);
    fputcsv($archivo_errores, $encabezado);

    // Procesar todas las filas del archivo
    $fila_numero = 1;
    while (($fila = fgetcsv($archivo, 0, $delimitador)) !== false) {
        $columnas_fila = count($fila);

        if ($columnas_fila !== $columnas_encabezado) {
            fputcsv($archivo_errores, $fila);
            $fila_numero++;
            continue;
        }

        // Crear la fila asociativa usando el encabezado normalizado
        $fila_asociativa = array_combine($encabezado_normalizado, $fila);

        // Validar y corregir datos
        $resultado = validar_y_corregir_planes($fila_asociativa, $formato);
        $errores = $resultado['errores'];
        $fila_corregida = $resultado['fila_corregida'];

        // Si no hay errores, guardar la fila corregida en el archivo correcto
        if (empty($errores)) {
            fputcsv($archivo_correcto, array_values($fila_corregida));
        } else {
            fputcsv($archivo_errores, $fila);
        }

        $fila_numero++;
    }

    fclose($archivo);
    fclose($archivo_correcto);
    fclose($archivo_errores);

    echo "Procesamiento de $nombre_archivo completado.\n";
}

// Formato específico para el archivo Planes.csv
$formato_archivo = [
    'código plan' => 'string, no nulo',
    'facultad' => 'string, no nulo',
    'carrera' => 'string, no nulo',
    'plan' => 'string, no nulo',
    'jornada' => 'string, no nulo',
    'sede' => 'string, no nulo',
    'grado' => 'string, no nulo',
    'modalidad' => 'string, no nulo',
    'inicio vigencia' => 'string, no nulo',
];

// Procesar el archivo CSV
procesar_csv_planes('Planes.csv', $formato_archivo);

?>
