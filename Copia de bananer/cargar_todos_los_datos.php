
<?php
include 'db_connection.php';


function cargarCSV($tabla, $archivoCSV, $columnas) {
    global $conn;

    if (($handle = fopen($archivoCSV, 'r')) !== FALSE) {
        fgetcsv($handle);

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $valores = [];
            for ($i = 0; $i < count($columnas); $i++) {
                $valores[] = convertEmptyToNull($data[$i]);
            }

            $sql = "INSERT INTO $tabla (" . implode(",", $columnas) . ") VALUES (" . implode(",", $valores) . ")";
            if ($conn->query($sql) === TRUE) {
                echo "Datos insertados correctamente en la tabla $tabla<br>";
            } else {
                echo "Error al insertar en $tabla: " . $conn->error . "<br>";
            }
        }
        fclose($handle);
    } else {
        echo "No se pudo abrir el archivo $archivoCSV<br>";
    }
}

function esFechaValida($fecha) {
    if (trim($fecha) === '') return false;
    $formato = 'Y-m-d';
    $d = DateTime::createFromFormat($formato, $fecha);
    return $d && $d->format($formato) === $fecha;
}

function convertEmptyToNull($value) {
    return (trim($value) === '') ? 'NULL' : "'" . $GLOBALS['conn']->real_escape_string(trim($value)) . "'";
}

$csv_file = 'Estudiantes.csv';

if (($handle = fopen($csv_file, 'r')) !== FALSE) {
    fgetcsv($handle); // Saltar la primera línea (encabezados)

    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        // Combinar nombres si hay datos en "Unnamed: 9"
        $nombres = trim($data[8] . ' ' . $data[9]);
        
        // Validar campos NOT NULL
        if (empty($data[0]) || empty($data[1]) || empty($data[2]) || empty($data[3]) || 
            empty($data[4]) || empty($data[6]) || empty($data[7]) || empty($nombres) || 
            empty($data[10]) || empty($data[12]) || empty($data[13])) {
            echo "Error: Datos obligatorios faltantes en la fila para el estudiante con RUN: {$data[6]}<br>";
            continue;
        }

        $valores = [
            convertEmptyToNull($data[0]),   // codigo_plan
            convertEmptyToNull($data[1]),   // carrera
            convertEmptyToNull($data[2]),   // cohorte
            $data[3],                       // numero_de_alumno (INTEGER)
            convertEmptyToNull($data[4]),   // bloqueado
            convertEmptyToNull($data[5]),   // causal_bloqueo
            $data[6],                       // run (BIGINT)
            convertEmptyToNull($data[7]),   // dv
            convertEmptyToNull($nombres),   // nombres (combinados)
            convertEmptyToNull($data[10]),  // primer_apellido
            convertEmptyToNull($data[11]),  // segundo_apellido
            convertEmptyToNull($data[12]),  // logro
            convertEmptyToNull($data[13]),  // fecha_logro
            convertEmptyToNull($data[14])   // ultima_carga
        ];

        $sql = "INSERT INTO Estudiantes (
            codigo_plan, carrera, cohorte, numero_de_alumno, bloqueado, 
            causal_bloqueo, run, dv, nombres, primer_apellido, 
            segundo_apellido, logro, fecha_logro, ultima_carga
        ) VALUES (" . implode(",", $valores) . ")";

        if ($conn->query($sql) === TRUE) {
            echo "Datos insertados correctamente para el estudiante: $nombres {$data[10]}<br>";
        } else {
            echo "Error al insertar en Estudiantes: " . $conn->error . "<br>";
            echo "SQL: " . $sql . "<br>";
        }
    }
    fclose($handle);
} else {
    echo "No se pudo abrir el archivo $csv_file<br>";
}


$csv_file = 'Docentes_Planificados.csv';

if (($handle = fopen($csv_file, 'r')) !== FALSE) {
    fgetcsv($handle);

    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $valores = [
            convertEmptyToNull($data[0]),  // run
            convertEmptyToNull($data[1]),  // nombre
            convertEmptyToNull($data[2]),  // apellido_p
            convertEmptyToNull($data[3]),  // telefono
            convertEmptyToNull($data[4]),  // email_personal
            convertEmptyToNull($data[5]),  // email_institucional
            trim($data[6]) === '' ? 'NULL' : $data[6], // dedicacion (número)
            convertEmptyToNull($data[7]),  // contrato
            convertEmptyToNull($data[8]),  // diurno
            convertEmptyToNull($data[9]),  // vespertino
            convertEmptyToNull($data[10]), // sede
            convertEmptyToNull($data[11]), // carrera
            convertEmptyToNull($data[12]), // grado_academico
            convertEmptyToNull($data[13]), // jerarquia
            convertEmptyToNull($data[14]), // cargo
            convertEmptyToNull($data[15])  // estamento
        ];

        $sql = "INSERT INTO Docentes_Planificados (run, nombre, apellido_p, telefono, email_personal, email_institucional, dedicacion, contrato, diurno, vespertino, sede, carrera, grado_academico, jerarquia, cargo, estamento) 
                VALUES (" . implode(",", $valores) . ")";

        if ($conn->query($sql) === TRUE) {
            echo "Datos insertados correctamente para el docente: {$data[1]} {$data[2]}<br>";
        } else {
            echo "Error al insertar en Docentes_Planificados: " . $conn->error . "<br>";
        }
    }
    fclose($handle);
} else {
    echo "No se pudo abrir el archivo $csv_file<br>";
}

$csv_file = 'Planeacion.csv';

// Función para convertir la cadena 'NULL' a NULL en SQL
function convertNullStringToNull($value) {
    return (strtoupper($value) == 'NULL') ? 'NULL' : "'" . $value . "'";
}

if (($handle = fopen($csv_file, 'r')) !== FALSE) {
    fgetcsv($handle); // Saltamos el encabezado

    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        
        // Convertimos los valores de cada columna que sean 'NULL' a NULL
        $jerarquizacion = substr($data[24], 0, 1);

        $valores = array_map('convertNullStringToNull', [
            $data[0],  // periodo
            $data[1],  // sede
            $data[2],  // facultad
            $data[3],  // codigo_depto
            $data[4],  // departamento
            $data[5],  // id_asignatura
            $data[6],  // asignatura
            $data[7],  // seccion
            $data[8],  // duracion
            $data[9],  // jornada
            $data[10], // cupo
            $data[11], // inscritos
            $data[12], // dia
            $data[13], // hora_inicio
            $data[14], // hora_fin
            $data[15], // fecha_inicio
            $data[16], // fecha_fin
            $data[17], // lugar
            $data[18], // edificio
            $data[19], // profesor_principal
            $data[20], // run
            $data[21], // nombre_docente
            $data[22], // primer_apellido_docente
            $data[23], // segundo_apellido_docente
            $jerarquizacion // jerarquización
        ]);

        // Insertar los datos en la base de datos
        $sql = "INSERT INTO Planeacion (periodo, sede, facultad, codigo_depto, departamento, id_asignatura, asignatura, seccion, duracion, jornada, cupo, inscritos, dia, hora_inicio, hora_fin, fecha_inicio, fecha_fin, lugar, edificio, profesor_principal, run, nombre_docente, primer_apellido_docente, segundo_apellido_docente, jerarquizacion) 
                VALUES (" . implode(",", $valores) . ")";

        if ($conn->query($sql) === TRUE) {
            echo "Datos insertados correctamente para la planeación: {$data[6]} - {$data[7]}.<br>";
        } else {
            echo "Error al insertar en Planeacion: " . $conn->error . "<br>";
        }
    }

    fclose($handle);
} else {
    echo "No se pudo abrir el archivo $csv_file<br>";
}

// Cargar datos en las tablas restantes
cargarCSV('Planes', 'Planes.csv', ['codigo_plan', 'facultad', 'carrera', 'plan', 'jornada', 'sede', 'grado', 'modalidad', 'inicio_vigencia']);
cargarCSV('Prerequisitos', 'Prerequisitos.csv', ['plan', 'asignatura_id', 'asignatura', 'nivel', 'prerequisitos', 'prerequisitos_1']);
cargarCSV('Notas', 'Notas.csv', ['codigo_plan', 'plan', 'cohorte', 'sede', 'run', 'dv', 'nombres', 'apellido_paterno', 'apellido_materno', 'numero_alumno', 'periodo_asignatura', 'codigo_asignatura', 'asignatura', 'convocatoria', 'calificacion', 'nota']);

$conn->close();
?>