<?php
include 'db_connection.php'; // Conexión a la base de datos

function cargarCSV($tabla, $archivoCSV, $columnas) {
    global $conn; // Para usar la conexión a la base de datos definida en db_connection.php

    // Abrir el archivo CSV
    if (($handle = fopen($archivoCSV, 'r')) !== FALSE) {
        // Saltar la primera línea (encabezados)
        fgetcsv($handle);

        // Leer cada línea del archivo CSV
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            // Preparar el SQL para insertar en la tabla
            $valores = [];
            for ($i = 0; $i < count($columnas); $i++) {
                $valores[] = "'" . $conn->real_escape_string($data[$i]) . "'"; // Sanitizar los datos y evitar inyección SQL
            }

            // Insertar los datos en la tabla
            $sql = "INSERT INTO $tabla (" . implode(",", $columnas) . ") VALUES (" . implode(",", $valores) . ")";
            if ($conn->query($sql) === TRUE) {
                echo "Datos insertados correctamente en la tabla $tabla<br>";
            } else {
                echo "Error al insertar en $tabla: " . $conn->error . "<br>";
            }
        }

        // Cerrar el archivo CSV
        fclose($handle);
    } else {
        echo "No se pudo abrir el archivo $archivoCSV<br>";
    }
}

function esFechaValida($fecha) {
    $formato = 'Y-m-d'; // Formato esperado de la fecha
    $d = DateTime::createFromFormat($formato, $fecha);
    return $d && $d->format($formato) === $fecha;
}

$csv_file = 'Estudiantes.csv'; 

// Abrir el archivo CSV
if (($handle = fopen($csv_file, 'r')) !== FALSE) {
    // Saltar la primera línea (encabezados)
    fgetcsv($handle);

    // Leer cada línea del archivo CSV
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        // Corregir automáticamente los nombres que están separados en dos columnas
        $nombres = $data[8] . ' ' . $data[9]; // Combina las columnas 8 y 9 (nombre y el "Unnamed")
        $primer_apellido = $data[10];
        $segundo_apellido = $data[11];

        // Asignar el resto de los valores
        $codigo_plan = $data[0];
        $carrera = $data[1];
        $cohorte = $data[2];
        $numero_de_alumno = $data[3];
        $bloqueado = $data[4];
        $causal_bloqueo = $data[5];
        $run = $data[6];
        $dv = $data[7];
        $logro = $data[12];
        $fecha_logro = $data[13];
        $ultima_carga = $data[14];

        // Validar si la fecha es válida antes de la inserción
        if (!esFechaValida($fecha_logro)) {
            echo "Error: Fecha inválida ($fecha_logro) para el alumno $nombres en la fila $numero_de_alumno.<br>";
            continue; // Saltar esta fila si la fecha es inválida
        }

        // Insertar los datos en la tabla Estudiantes
        $sql = "INSERT INTO Estudiantes (codigo_plan, carrera, cohorte, numero_de_alumno, bloqueado, causal_bloqueo, run, dv, nombres, primer_apellido, segundo_apellido, logro, fecha_logro, ultima_carga) 
                VALUES ('$codigo_plan', '$carrera', '$cohorte', '$numero_de_alumno', '$bloqueado', '$causal_bloqueo', '$run', '$dv', '$nombres', '$primer_apellido', '$segundo_apellido', '$logro', '$fecha_logro', '$ultima_carga')";

        if ($conn->query($sql) === TRUE) {
            echo "Datos insertados correctamente para el estudiante: $nombres $primer_apellido.<br>";
        } else {
            echo "Error al insertar en Estudiantes: " . $conn->error . "<br>";
        }
    }

    // Cerrar el archivo CSV
    fclose($handle);
} else {
    echo "No se pudo abrir el archivo $csv_file<br>";
}


// Cargar datos en la tabla Asignaturas
cargarCSV('Asignaturas', 'Asignaturas.csv', ['plan', 'asignatura_id', 'asignatura', 'nivel']);


$csv_file = 'Docentes_Planificados.csv'; 

if (($handle = fopen($csv_file, 'r')) !== FALSE) {
    // Saltar la primera línea (encabezados)
    fgetcsv($handle);

    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        // Reemplazar un valor vacío en 'dedicacion' por NULL
        $dedicacion = !empty($data[6]) ? $data[6] : 'NULL';

        // Asignar el resto de los valores
        $run = $data[0];
        $nombre = $data[1];
        $apellido_p = $data[2];
        $telefono = $data[3];
        $email_personal = $data[4];
        $email_institucional = $data[5];
        $contrato = $data[7];
        $diurno = $data[8];
        $vespertino = $data[9];
        $sede = $data[10];
        $carrera = $data[11];
        $grado_academico = $data[12];
        $jerarquia = $data[13];
        $cargo = $data[14];
        $estamento = $data[15];

        // Insertar los datos en la tabla Docentes_Planificados
        $sql = "INSERT INTO Docentes_Planificados (run, nombre, apellido_p, telefono, email_personal, email_institucional, dedicacion, contrato, diurno, vespertino, sede, carrera, grado_academico, jerarquia, cargo, estamento) 
                VALUES ('$run', '$nombre', '$apellido_p', '$telefono', '$email_personal', '$email_institucional', $dedicacion, '$contrato', '$diurno', '$vespertino', '$sede', '$carrera', '$grado_academico', '$jerarquia', '$cargo', '$estamento')";

        if ($conn->query($sql) === TRUE) {
            echo "Datos insertados correctamente para el docente: $nombre $apellido_p.<br>";
        } else {
            echo "Error al insertar en Docentes_Planificados: " . $conn->error . "<br>";
        }
    }

    // Cerrar el archivo CSV
    fclose($handle);
} else {
    echo "No se pudo abrir el archivo $csv_file<br>";
}



$csv_file = 'Planeacion.csv'; 

if (($handle = fopen($csv_file, 'r')) !== FALSE) {
    // Saltar la primera línea (encabezados)
    fgetcsv($handle);

    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        // Verificar si 'run' está vacío, si es así, saltar la fila
        if (empty($data[20])) {
            echo "Fila omitida: valor de 'run' vacío para la planeación $data[6] - $data[7].<br>";
            continue;
        }

        // Asegurarse de que 'jerarquizacion' tenga solo 1 carácter
        $jerarquizacion = substr($data[24], 0, 1);  // Truncar a 1 carácter

        // Asignar el resto de los valores
        $periodo = $data[0];
        $sede = $data[1];
        $facultad = $data[2];
        $codigo_depto = $data[3];
        $departamento = $data[4];
        $id_asignatura = $data[5];
        $asignatura = $data[6];
        $seccion = $data[7];
        $duracion = $data[8];
        $jornada = $data[9];
        $cupo = $data[10];
        $inscritos = $data[11];
        $dia = $data[12];
        $hora_inicio = $data[13];
        $hora_fin = $data[14];
        $fecha_inicio = $data[15];
        $fecha_fin = $data[16];
        $lugar = $data[17];
        $edificio = $data[18];
        $profesor_principal = $data[19];
        $run = $data[20];
        $nombre_docente = $data[21];
        $primer_apellido_docente = $data[22];
        $segundo_apellido_docente = $data[23];

        // Insertar los datos en la tabla Planeacion
        $sql = "INSERT INTO Planeacion (periodo, sede, facultad, codigo_depto, departamento, id_asignatura, asignatura, seccion, duracion, jornada, cupo, inscritos, dia, hora_inicio, hora_fin, fecha_inicio, fecha_fin, lugar, edificio, profesor_principal, run, nombre_docente, primer_apellido_docente, segundo_apellido_docente, jerarquizacion) 
                VALUES ('$periodo', '$sede', '$facultad', '$codigo_depto', '$departamento', '$id_asignatura', '$asignatura', '$seccion', '$duracion', '$jornada', '$cupo', '$inscritos', '$dia', '$hora_inicio', '$hora_fin', '$fecha_inicio', '$fecha_fin', '$lugar', '$edificio', '$profesor_principal', '$run', '$nombre_docente', '$primer_apellido_docente', '$segundo_apellido_docente', '$jerarquizacion')";

        if ($conn->query($sql) === TRUE) {
            echo "Datos insertados correctamente para la planeación: $asignatura - $seccion.<br>";
        } else {
            echo "Error al insertar en Planeacion: " . $conn->error . "<br>";
        }
    }

    // Cerrar el archivo CSV
    fclose($handle);
} else {
    echo "No se pudo abrir el archivo $csv_file<br>";
}



// Cargar datos en la tabla Planes
cargarCSV('Planes', 'Planes.csv', ['codigo_plan', 'facultad', 'carrera', 'plan', 'jornada', 'sede', 'grado', 'modalidad', 'inicio_vigencia']);

// Cargar datos en la tabla Prerequisitos
cargarCSV('Prerequisitos', 'Prerequisitos.csv', ['plan', 'asignatura_id', 'asignatura', 'nivel', 'prerequisitos', 'prerequisitos_1']);

// Cargar datos en la tabla Notas
cargarCSV('Notas', 'Notas.csv', ['codigo_plan', 'plan', 'cohorte', 'sede', 'run', 'dv', 'nombres', 'apellido_paterno', 'apellido_materno', 'numero_alumno', 'periodo_asignatura', 'codigo_asignatura', 'asignatura', 'convocatoria', 'calificacion', 'nota']);

// Cerrar la conexión
$conn->close();
?>
