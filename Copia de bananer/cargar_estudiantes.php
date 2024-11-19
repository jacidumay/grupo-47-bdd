<?php
include 'db_connection.php';

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

$conn->close();
?>