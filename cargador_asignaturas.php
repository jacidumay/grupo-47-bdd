<?php

require 'vendor/autoload.php'; // Para manejar archivos Excel con PHPSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

// Función para validar y corregir los datos según las reglas especificadas
function validar_y_corregir_asignaturas($fila, $formato) {
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

    // Convertir el campo 'Asignatura' a mayúsculas si está presente
    if (isset($fila['asignatura'])) {
        $fila['asignatura'] = strtoupper(trim($fila['asignatura']));
        $correcciones['asignatura'] = "Campo 'Asignatura' convertido a mayúsculas.";
    }

    // Validar que los primeros caracteres de 'Plan' coincidan con 'Asignatura id'
    if (isset($fila['plan']) && isset($fila['asignatura id'])) {
        $plan_prefix = substr($fila['plan'], 0, 2); // Obtener los primeros dos caracteres del plan
        $asignatura_id_prefix = substr($fila['asignatura id'], 0, 2); // Obtener los primeros dos caracteres del ID de la asignatura
        if ($plan_prefix !== $asignatura_id_prefix) {
            $errores['plan_asignatura'] = "Los primeros caracteres de 'Plan' no coinciden con los de 'Asignatura id'.";
        }
    }

    // Verificar que el campo 'Nivel' no sea nulo
    if (isset($fila['nivel']) && trim($fila['nivel']) === '') {
        $errores['nivel'] = "El campo 'Nivel' no puede estar vacío.";
    }

    return ['errores' => $errores, 'correcciones' => $correcciones, 'fila_corregida' => $fila];
}

// Función para procesar el archivo CSV
function procesar_csv_asignaturas($nombre_archivo, $formato) {
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
        $resultado = validar_y_corregir_asignaturas($fila_asociativa, $formato);
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

// Formato específico para el archivo Asignaturas.csv
$formato_archivo = [
    'plan' => 'string, no nulo',
    'asignatura id' => 'string, no nulo',
    'asignatura' => 'string, no nulo',
    'nivel' => 'string, no nulo',
];

// Procesar el archivo CSV
procesar_csv_asignaturas('Asignaturas.csv', $formato_archivo);

?>
