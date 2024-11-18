<?php

require 'vendor/autoload.php'; // Para manejar archivos Excel con PHPSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

// Función para validar y corregir los datos según las reglas especificadas
function validar_y_corregir_notas($fila, $formato, $tabla_calificaciones) {
    $errores = [];
    $correcciones = [];

    foreach ($formato as $campo => $regla) {
        $permite_nulo = strpos($regla, 'admite nulos') !== false;
        $no_nulo = strpos($regla, 'no nulo') !== false;

        // Normalizar el valor eliminando espacios en blanco
        $valor = trim($fila[$campo] ?? '');

        // Si el campo permite nulos y el valor es una cadena vacía, no es un error
        if ($permite_nulo && $valor === '') {
            continue;
        }

        // Validar campos que no permiten nulos
        if ($no_nulo && $valor === '') {
            $errores[$campo] = 'Valor nulo en campo no nulo';
        }
    }

    // Validar y corregir la calificación en base a la nota
    $nota = isset($fila['nota']) ? str_replace(',', '.', trim($fila['nota'])) : null;
    $calificacion = trim($fila['calificación'] ?? '');
    $periodo_asignatura = trim($fila['periodo asignatura'] ?? '');

    // Limpiar espacios y convertir la asignatura a mayúsculas
    if (isset($fila['asignatura'])) {
        $fila['asignatura'] = strtoupper(trim($fila['asignatura']));
        $correcciones['asignatura'] = "Espacio inicial eliminado y convertido a mayúsculas en 'asignatura'.";
    }

    // Si el periodo asignatura es '2024-02', calificación y nota pueden estar vacías y se asigna 'X'
    if ($periodo_asignatura === '2024-02') {
        if ($calificacion === '' && ($nota === '' || $nota === null)) {
            $fila['calificación'] = 'X';
            $fila['nota'] = 'X';
            $correcciones['calificación'] = "Calificación y nota establecidas como 'X' para el periodo '2024-02'.";
        }
    } else {
        // Si tanto la calificación como la nota están vacías o nulas, enviar la fila a errores
        if (($calificacion === '' || $calificacion === null) && ($nota === '' || $nota === null)) {
            $errores['calificación'] = 'Calificación y nota no pueden ser ambas nulas';
        } else {
            // Si la calificación es nula pero hay nota, asignar la calificación basada en la nota
            if (($calificacion === '' || $calificacion === null) && $nota !== '') {
                $nota = (float)$nota;
                $calificacion_correcta = null;
                foreach ($tabla_calificaciones as $rango => $calif) {
                    [$min, $max] = explode('-', $rango);
                    if ($nota >= (float)$min && $nota <= (float)$max) {
                        $calificacion_correcta = $calif;
                        break;
                    }
                }

                // Asignar la calificación correcta según la nota
                if ($calificacion_correcta !== null) {
                    $fila['calificación'] = $calificacion_correcta;
                    $correcciones['calificación'] = "Calificación asignada como '$calificacion_correcta' basada en la nota.";
                } else {
                    $errores['calificación'] = 'La nota no corresponde a ninguna calificación válida';
                }
            } elseif ($nota !== '') {
                // Convertir a float para validar
                $nota = (float)$nota;
                $calificacion_correcta = null;
                foreach ($tabla_calificaciones as $rango => $calif) {
                    [$min, $max] = explode('-', $rango);
                    if ($nota >= (float)$min && $nota <= (float)$max) {
                        $calificacion_correcta = $calif;
                        break;
                    }
                }

                // Verificar si la calificación coincide con la esperada según la nota y corregir si es necesario
                if ($calificacion_correcta !== null && $calificacion_correcta !== $calificacion) {
                    $fila['calificación'] = $calificacion_correcta;
                    $correcciones['calificación'] = "Calificación corregida a '$calificacion_correcta' basada en la nota.";
                }
            }

            // Si la nota está vacía o nula, asignar 'X'
            if ($nota === '' || $nota === null) {
                $fila['nota'] = 'X';
                $correcciones['nota'] = "Nota nula reemplazada por 'X'.";
            }
        }
    }

    return ['errores' => $errores, 'correcciones' => $correcciones, 'fila_corregida' => $fila];
}

// Función para procesar el archivo CSV
function procesar_csv($nombre_archivo, $formato, $tabla_calificaciones) {
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
        $resultado = validar_y_corregir_notas($fila_asociativa, $formato, $tabla_calificaciones);
        $errores = $resultado['errores'];
        $fila_corregida = $resultado['fila_corregida'];

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

// Tabla de calificaciones con rangos y sus correspondientes etiquetas
$tabla_calificaciones = [
    '6.6-7.0' => 'SO',
    '6.0-6.5' => 'MB',
    '5.0-5.9' => 'B',
    '4.0-4.9' => 'SU',
    '3.0-3.9' => 'I',
    '2.0-2.9' => 'M',
    '1.0-1.9' => 'MM'
    // Agrega aquí cualquier otro rango necesario según tu enunciado
];

// Formato específico para el archivo
$formato_archivo = [
    'código plan' => 'string, no nulo',
    'plan' => 'string, no nulo',
    'cohorte' => 'string, no nulo',
    'sede' => 'string, no nulo',
    'run' => 'int, no nulo',
    'dv' => 'char, no nulo',
    'nombres' => 'string, no nulo',
    'apellido paterno' => 'string, no nulo',
    'apellido materno' => 'string, admite nulos',
    'número de alumno' => 'int, no nulo',
    'periodo asignatura' => 'string, no nulo',
    'código asignatura' => 'string, no nulo',
    'asignatura' => 'string, no nulo',
    'convocatoria' => 'string, no nulo',
    'calificación' => 'string, admite nulos',
    'nota' => 'float, admite nulos',
];

// Procesar las filas de 'notas.csv'
procesar_csv('Notas.csv', $formato_archivo, $tabla_calificaciones);

?>


