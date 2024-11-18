<?php

// Función para validar y corregir los datos según las reglas especificadas para el archivo Planeación
function validar_y_corregir_datos_planeacion($fila) {
    $errores = [];

    // Normalizar los valores en mayúsculas para los nombres de asignatura y docente
    $campos_mayusculas = ['Asignatura', 'Nombre Docente', '1er Apellido Docente', '2so Apellido Docente'];
    foreach ($campos_mayusculas as $campo) {
        if (isset($fila[$campo])) {
            $fila[$campo] = strtoupper(trim($fila[$campo]));
        }
    }

    // Normalizar el valor de Sede para que solo la primera letra sea mayúscula
    if (isset($fila['Sede'])) {
        $fila['Sede'] = ucfirst(strtolower(trim($fila['Sede'])));
    }

    // Verificar que el "Cupo" sea mayor o igual a "Inscritos"
    if (isset($fila['Cupo']) && isset($fila['Inscritos'])) {
        if ((int)$fila['Cupo'] < (int)$fila['Inscritos']) {
            $errores['Cupo'] = 'El cupo no puede ser menor que los inscritos';
        }
    } else {
        $errores['Cupo'] = 'Cupo o Inscritos no definidos';
    }

    // Si no hay errores, la fila es correcta
    return ['errores' => $errores, 'fila_corregida' => $fila];
}

// Función para procesar el archivo CSV de planeación
function procesar_csv_planeacion($nombre_archivo) {
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

    fputcsv($archivo_correcto, $encabezado);
    fputcsv($archivo_errores, $encabezado);

    // Procesar todas las filas del archivo
    while (($fila = fgetcsv($archivo, 0, $delimitador)) !== false) {
        $columnas_fila = count($fila);

        // Verificar si el número de columnas coincide con el encabezado
        if ($columnas_fila !== $columnas_encabezado) {
            // Guardar como error si el número de columnas no coincide
            fputcsv($archivo_errores, $fila);
            continue;
        }

        // Crear la fila asociativa usando el encabezado normalizado
        $fila_asociativa = array_combine($encabezado_normalizado, $fila);

        // Validar y corregir datos
        $resultado = validar_y_corregir_datos_planeacion($fila_asociativa);
        $errores = $resultado['errores'];

        if (empty($errores)) {
            // Si no hay errores, guardar en el archivo correcto
            fputcsv($archivo_correcto, array_values($resultado['fila_corregida']));
        } else {
            // Si hay errores, guardar en el archivo de errores
            fputcsv($archivo_errores, $fila);
        }
    }

    fclose($archivo);
    fclose($archivo_correcto);
    fclose($archivo_errores);

    echo "Procesamiento de $nombre_archivo completado.\n";
}

// Procesar las filas de 'Planeación.csv'
procesar_csv_planeacion('Planeación.csv');

?>

