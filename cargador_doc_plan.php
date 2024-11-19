<?php

// Función para validar y corregir los datos según las reglas especificadas para Docentes Planificados
function validar_y_corregir_datos_docentes($fila, $formato) {
    $errores = [];

    foreach ($formato as $campo => $regla) {
        $permite_nulo = strpos($regla, 'admite nulos') !== false;
        $no_nulo = strpos($regla, 'no nulo') !== false;

        // Normalizar el valor eliminando espacios en blanco
        $valor = trim($fila[$campo] ?? '');

        // Si la columna admite nulos y el valor está vacío, asignar "null"
        if ($permite_nulo && array_key_exists($campo, $fila) && $valor === '') {
            $fila[$campo] = 'null';
        }

        // Validar campos que no permiten nulos
        if ($no_nulo && $valor === '') {
            $errores[$campo] = 'Valor nulo en campo no nulo';
        }

        // Validar el campo 'telefono'
        if ($campo === 'telefono' && $valor !== '' && $valor !== 'null') {
            $telefono = preg_replace('/\D/', '', $valor); // Eliminar caracteres no numéricos
            if (strlen($telefono) !== 9) {
                $errores[$campo] = 'El teléfono debe tener exactamente 9 dígitos y no contener letras';
            } else {
                $fila[$campo] = $telefono;
            }
        }

        // Convertir los nombres y apellidos a mayúsculas si no están vacíos
        if (in_array($campo, ['Nombre', 'Apellido P']) && $valor !== '') {
            $fila[$campo] = strtoupper($valor);
        }

        // Validar y corregir correos electrónicos personales
        if (strpos($campo, 'email personal') !== false && $valor !== '') {
            // Si el correo tiene más de un '@', eliminamos los adicionales
            if (substr_count($valor, '@') > 1) {
                $partes = explode('@', $valor);
                $nuevo_email = $partes[0] . '@' . $partes[1];
                $fila[$campo] = $nuevo_email;
            }
        }

        // Validar y corregir correos electrónicos institucionales
        if (strpos($campo, 'email institucional') !== false && $valor !== '') {
            // Si el correo no termina en '@lamejor.com' o tiene más de un '@', corregimos
            if (!str_ends_with($valor, '@lamejor.com') || substr_count($valor, '@') > 1) {
                $partes = explode('@', $valor);
                $nuevo_email = $partes[0] . '@lamejor.com';
                $fila[$campo] = $nuevo_email;
            }
        }
    }

    return ['errores' => $errores, 'fila_corregida' => $fila];
}

// Función para procesar el archivo CSV de docentes planificados
function procesar_csv_docentes($nombre_archivo, $formato) {
    $archivo = fopen($nombre_archivo, 'r');
    if (!$archivo) {
        echo "Error: No se pudo abrir el archivo $nombre_archivo.\n";
        return;
    }

    $delimitador = ',';

    $archivo_correcto = fopen('correcto_' . $nombre_archivo, 'w');
    $archivo_errores = fopen('errores_' . $nombre_archivo, 'w');

    // Leer encabezado y normalizar eliminando espacios adicionales
    $encabezado = fgetcsv($archivo, 0, $delimitador);
    if (!$encabezado) {
        echo "Error: El archivo $nombre_archivo está vacío o tiene un encabezado no válido.\n";
        fclose($archivo);
        fclose($archivo_correcto);
        fclose($archivo_errores);
        return;
    }
    $encabezado_normalizado = array_map('trim', $encabezado);
    $columnas_encabezado = count($encabezado_normalizado);

    fputcsv($archivo_correcto, $encabezado_normalizado);
    fputcsv($archivo_errores, $encabezado_normalizado);

    // Procesar todas las filas del archivo
    while (($fila = fgetcsv($archivo, 0, $delimitador)) !== false) {
        $columnas_fila = count($fila);

        // Verificar si el número de columnas coincide con el encabezado
        if ($columnas_fila !== $columnas_encabezado) {
            fputcsv($archivo_errores, $fila);
            continue;
        }

        // Crear la fila asociativa usando el encabezado normalizado
        $fila_asociativa = array_combine($encabezado_normalizado, $fila);

        // Validar y corregir datos
        $resultado = validar_y_corregir_datos_docentes($fila_asociativa, $formato);
        $errores = $resultado['errores'];

        // Asegurar que la fila corregida tiene la misma cantidad de columnas que el encabezado
        if (empty($errores)) {
            $fila_corregida = array_values($resultado['fila_corregida']);
            if (count($fila_corregida) === $columnas_encabezado) {
                fputcsv($archivo_correcto, $fila_corregida);
            } else {
                fputcsv($archivo_errores, $fila);
            }
        } else {
            fputcsv($archivo_errores, $fila);
        }
    }

    fclose($archivo);
    fclose($archivo_correcto);
    fclose($archivo_errores);

    echo "Procesamiento de $nombre_archivo completado.\n";
}

// Formato específico para 'docentes planificados.csv'
$formato_docentes = [
    'RUN' => 'int, no nulo',
    'Nombre' => 'string, no nulo',
    'Apellido P' => 'string, no nulo',
    'telefono' => 'int, admite nulos',
    'email personal' => 'string, admite nulos',
    'email institucional' => 'string, admite nulos',
    'DEDICACIÓN' => 'float, admite nulos',
    'CONTRATO' => 'string, no nulo',
    'DIURNO' => 'string, admite nulos',
    'VESPERTINO' => 'string, admite nulos',
    'SEDE' => 'string, admite nulos',
    'CARRERA' => 'string, admite nulos',
    'GRADO ACADÉMICO' => 'string, no nulo',
    'JERARQUÍA' => 'string, no nulo',
    'CARGO' => 'string, admite nulos',
    'ESTAMENTO' => 'string, admite nulos'
];

// Procesar las filas de 'docentes planificados.csv'
procesar_csv_docentes('docentes planificados.csv', $formato_docentes);

?>

