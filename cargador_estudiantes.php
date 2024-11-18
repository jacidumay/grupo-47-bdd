<?php

// Función para validar y corregir los datos según las reglas especificadas para Estudiantes
function validar_y_corregir_datos_estudiantes($fila, $formato) {
    $errores = [];

    foreach ($formato as $campo => $regla) {
        $permite_nulo = strpos($regla, 'admite nulos') !== false;
        $no_nulo = strpos($regla, 'no nulo') !== false;

        // Normalizar el valor eliminando espacios en blanco
        $valor = trim($fila[$campo] ?? '');

        // Validar campos que no permiten nulos
        if ($no_nulo && $valor === '') {
            $errores[$campo] = 'Valor nulo en campo no nulo';
        }
    }

    return ['errores' => $errores, 'fila_corregida' => $fila];
}

// Función para procesar el archivo CSV de estudiantes
function procesar_csv_estudiantes($nombre_archivo, $formato) {
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

    // Normalizar el encabezado eliminando espacios en blanco
    $encabezado_normalizado = array_map(function($campo) {
        return trim($campo);
    }, $encabezado);

    // Combinar "Nombres" y la columna a su derecha
    $pos_nombres = array_search('Nombres', $encabezado_normalizado);
    if ($pos_nombres !== false && isset($encabezado_normalizado[$pos_nombres + 1])) {
        $encabezado_normalizado[$pos_nombres] = 'Nombre Completo';
        // Eliminar la columna siguiente (segundo nombre)
        array_splice($encabezado_normalizado, $pos_nombres + 1, 1);
    }

    // Escribir el encabezado ajustado en los archivos de salida
    fputcsv($archivo_correcto, $encabezado_normalizado);
    fputcsv($archivo_errores, $encabezado_normalizado);

    // Procesar todas las filas del archivo
    while (($fila = fgetcsv($archivo, 0, $delimitador)) !== false) {
        $columnas_fila = count($fila);

        // Verificar si el número de columnas coincide con el encabezado ajustado
        if ($columnas_fila !== $columnas_encabezado) {
            fputcsv($archivo_errores, $fila);
            continue;
        }

        // Combinar el nombre y el segundo nombre en "Nombre Completo"
        $fila[$pos_nombres] = trim($fila[$pos_nombres]) . ' ' . trim($fila[$pos_nombres + 1] ?? '');
        // Eliminar la columna siguiente (segundo nombre)
        array_splice($fila, $pos_nombres + 1, 1);

        // Crear la fila asociativa usando el encabezado normalizado
        $fila_asociativa = array_combine($encabezado_normalizado, $fila);

        // Validar y corregir datos
        $resultado = validar_y_corregir_datos_estudiantes($fila_asociativa, $formato);
        $errores = $resultado['errores'];

        if (empty($errores)) {
            fputcsv($archivo_correcto, array_values($resultado['fila_corregida']));
        } else {
            fputcsv($archivo_errores, $fila);
        }
    }

    fclose($archivo);
    fclose($archivo_correcto);
    fclose($archivo_errores);

    echo "Procesamiento de $nombre_archivo completado.\n";
}

// Formato específico para 'Estudiantes.csv'
$formato_estudiantes = [
    'Código Plan' => 'string, no nulo',
    'Carrera' => 'string, no nulo',
    'Cohorte' => 'string, no nulo',
    'Número de alumno' => 'int, no nulo',
    'Bloqueo' => 'string, no nulo',
    'Causal Bloqueo' => 'string, no nulo',
    'RUN' => 'int, no nulo',
    'Nombre Completo' => 'string, no nulo',
    'Primer Apellido' => 'string, no nulo',
    'Segundo Apellido' => 'string, admite nulos',
    'Logro' => 'string, no nulo',
    'Fecha Logro' => 'string, no nulo',
    'Última Carrera' => 'string, admite nulos'
];

// Procesar las filas de 'Estudiantes.csv'
procesar_csv_estudiantes('Estudiantes.csv', $formato_estudiantes);

?>
