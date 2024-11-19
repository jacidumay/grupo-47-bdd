<?php

require 'vendor/autoload.php'; // Para manejar archivos Excel con PHPSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

// Función para validar y corregir los datos según las reglas especificadas
function validar_y_corregir_prerequisitos($fila, $formato) {
    $errores = [];
    $correcciones = [];

    foreach ($formato as $campo => $regla) {
        $permite_nulo = strpos($regla, 'admite nulos') !== false;
        $no_nulo = strpos($regla, 'no nulo') !== false;

        // Normalizar el valor eliminando espacios en blanco
        $valor = trim($fila[$campo] ?? '');

        // Si el campo permite nulos y el valor es una cadena vacía, asignar "null"
        if ($permite_nulo && $valor === '') {
            $fila[$campo] = 'null';
            continue;
        }

        // Validar campos que no permiten nulos
        if ($no_nulo && $valor === '') {
            // Corregir si el campo es "Nivel" y está vacío
            if ($campo === 'nivel') {
                $fila[$campo] = 'X'; // Asignar "X" si "Nivel" está vacío
                $correcciones[$campo] = 'Nivel estaba vacío, se asignó "X".';
            } else {
                $errores[$campo] = 'Valor nulo en campo no nulo';
            }
        }

        // Convertir "ingreso" y "egreso" a "Ingreso" y "Egreso" respectivamente
        if ($campo === 'prerequisitos' && strtolower($valor) === 'ingreso') {
            $fila[$campo] = 'Ingreso';
            $correcciones[$campo] = 'Se corrigió "ingreso" a "Ingreso".';
        } elseif ($campo === 'prerequisitos' && strtolower($valor) === 'egreso') {
            $fila[$campo] = 'Egreso';
            $correcciones[$campo] = 'Se corrigió "egreso" a "Egreso".';
        }
    }

    return ['errores' => $errores, 'correcciones' => $correcciones, 'fila_corregida' => $fila];
}

// Función para procesar el archivo CSV
function procesar_csv_prerequisitos($nombre_archivo, $formato) {
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
        $resultado = validar_y_corregir_prerequisitos($fila_asociativa, $formato);
        $errores = $resultado['errores'];
        $fila_corregida = $resultado['fila_corregida'];

        if (empty($errores)) {
            fputcsv($archivo_correcto, array_values($fila_corregida));  // Guardar la fila corregida en el archivo correcto
        } else {
            fputcsv($archivo_errores, $fila);  // Si hay errores, se guarda en el archivo de errores
        }

        $fila_numero++;
    }

    fclose($archivo);
    fclose($archivo_correcto);
    fclose($archivo_errores);

    echo "Procesamiento de $nombre_archivo completado.\n";
}

// Formato específico para 'prerequisitos.csv'
$formato_prerequisitos = [
    'plan' => 'string, no nulo',
    'asignatura id' => 'string, no nulo',
    'asignatura' => 'string, no nulo',
    'nivel' => 'string, no nulo', // Aquí no permite nulos, pero corregimos con "X" si está vacío
    'prerequisitos' => 'string, admite nulos',
    'prerequisitos.1' => 'string, admite nulos',
];

// Procesar las filas de 'prerequisitos.csv'
procesar_csv_prerequisitos('prerequisitos.csv', $formato_prerequisitos);

?>


