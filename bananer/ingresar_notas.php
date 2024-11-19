<?php
session_start();
include 'db_connection.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica si el archivo ha sido subido
    if (isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        
        // Abrir el archivo
        $handle = fopen($file, 'r');
        
        if ($handle !== FALSE) {
            // Leer los datos del CSV y procesarlos
            // Saltar la primera fila si es necesario (encabezados)
            fgetcsv($handle, 1000, ','); // Asumiendo que la primera fila son los encabezados
            
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Asegúrate de que los índices coincidan con las columnas del CSV
                $codigo_plan = $data[0];
                $plan = $data[1];
                $cohorte = $data[2];
                $sede = $data[3];
                $run = $data[4]; // RUN
                $dv = $data[5]; // DV
                $nombres = $data[6]; // Nombres
                $apellido_paterno = $data[7]; // Apellido Paterno
                $apellido_materno = $data[8]; // Apellido Materno
                $numero_alumno = $data[9]; // Número de alumno
                $periodo_asignatura = $data[10]; // Periodo Asignatura
                $codigo_asignatura = $data[11]; // Código Asignatura
                $asignatura = $data[12]; // Asignatura
                $convocatoria = $data[13]; // Convocatoria
                $calificacion = $data[14]; // Calificación
                $nota = $data[15]; // Nota
                
                // Escapar valores para evitar inyección SQL
                $codigo_plan = $conn->real_escape_string($codigo_plan);
                $plan = $conn->real_escape_string($plan);
                $cohorte = $conn->real_escape_string($cohorte);
                $sede = $conn->real_escape_string($sede);
                $run = $conn->real_escape_string($run);
                $dv = $conn->real_escape_string($dv);
                $nombres = $conn->real_escape_string($nombres);
                $apellido_paterno = $conn->real_escape_string($apellido_paterno);
                $apellido_materno = $conn->real_escape_string($apellido_materno);
                $numero_alumno = $conn->real_escape_string($numero_alumno);
                $periodo_asignatura = $conn->real_escape_string($periodo_asignatura);
                $codigo_asignatura = $conn->real_escape_string($codigo_asignatura);
                $asignatura = $conn->real_escape_string($asignatura);
                $convocatoria = $conn->real_escape_string($convocatoria);
                $calificacion = $conn->real_escape_string($calificacion);
                $nota = $conn->real_escape_string($nota);
                
                // Consulta para insertar la nota en la base de datos
                $sql = "INSERT INTO Notas (`Código Plan`, `plan`, `cohorte`, `sede`, `run`, `dv`, `nombres`, 
                        `apellido_paterno`, `apellido_materno`, `numero_alumno`, 
                        `periodo_asignatura`, `Código Asignatura`, `asignatura`, 
                        `convocatoria`, `calificacion`, `nota`) 
                        VALUES ('$codigo_plan', '$plan', '$cohorte', '$sede', '$run', '$dv', '$nombres', 
                        '$apellido_paterno', '$apellido_materno', '$numero_alumno', 
                        '$periodo_asignatura', '$codigo_asignatura', '$asignatura', 
                        '$convocatoria', '$calificacion', '$nota')";
                
                // Ejecutar la consulta
                if ($conn->query($sql) === TRUE) {
                    echo "Nota para el estudiante $nombres insertada correctamente.<br>";
                } else {
                    echo "Error al insertar la nota para el estudiante $nombres: " . $conn->error . "<br>";
                }
            }
            fclose($handle);
        } else {
            echo "Error al abrir el archivo.";
        }
    } else {
        echo "No se subió ningún archivo.";
    }
}
?>

<!-- Formulario para subir el archivo CSV -->
<h1>Subir Notas desde un CSV</h1>
<form enctype="multipart/form-data" action="ingresar_notas.php" method="POST">
    <input type="file" name="csv_file" accept=".csv" required>
    <br>
    <input type="submit" value="Subir CSV">
</form>
