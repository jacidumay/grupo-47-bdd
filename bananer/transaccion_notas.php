<?php
session_start();
include 'db_connection.php';

if ($argc < 2) {
    die("Error: No se ha especificado un archivo para procesar.\n");
}

$nombre_archivo = $argv[1];
if (!file_exists($nombre_archivo)) {
    die("Error: El archivo $nombre_archivo no se encuentra.\n");
}

try {
    // Crear procedimiento almacenado
    $conn->query("DROP PROCEDURE IF EXISTS procesar_acta_notas");
    $crear_procedimiento = "
    CREATE PROCEDURE procesar_acta_notas()
    BEGIN
        UPDATE acta
        SET nota_final = CASE
            WHEN oportunidad_mar IS NULL OR oportunidad_mar = 0 THEN oportunidad_dic
            WHEN oportunidad_dic >= 4.0 AND oportunidad_dic <= 7.0 THEN oportunidad_dic
            ELSE oportunidad_mar
        END;
    END;";
    $conn->query($crear_procedimiento);

    // Crear trigger
    $conn->query("DROP TRIGGER IF EXISTS calcular_calificacion");
    $crear_trigger = "
    CREATE TRIGGER calcular_calificacion
    AFTER UPDATE ON acta
    FOR EACH ROW
    BEGIN
        DECLARE calificacion_final VARCHAR(2);
        
        IF NEW.nota_final >= 6.6 THEN SET calificacion_final = 'SO';
        ELSEIF NEW.nota_final >= 6.0 THEN SET calificacion_final = 'MB';
        ELSEIF NEW.nota_final >= 5.0 THEN SET calificacion_final = 'B';
        ELSEIF NEW.nota_final >= 4.0 THEN SET calificacion_final = 'SU';
        ELSEIF NEW.nota_final >= 3.0 THEN SET calificacion_final = 'I';
        ELSEIF NEW.nota_final >= 2.0 THEN SET calificacion_final = 'M';
        ELSEIF NEW.nota_final >= 1.0 THEN SET calificacion_final = 'MM';
        ELSE SET calificacion_final = 'R';
        END IF;

        INSERT INTO Notas (
            `Número_de_alumno`, 
            RUN, 
            Asignatura, 
            Periodo_Asignatura, 
            `Calificación`, 
            Nota
        )
        VALUES (
            NEW.numero_alumno, 
            NEW.run, 
            NEW.asignatura, 
            NEW.periodo, 
            calificacion_final, 
            NEW.nota_final
        );
    END;";
    $conn->query($crear_trigger);

    // Procesar archivo CSV
    $conn->begin_transaction();
    $archivo = fopen($nombre_archivo, 'r');
    $encabezado = fgetcsv($archivo, 0, ';');
    $count = 0;

    while (($fila = fgetcsv($archivo, 0, ';')) !== false) {
        if (!empty($fila[0])) {
            $sql = "INSERT INTO acta (numero_alumno, run, asignatura, seccion, periodo, oportunidad_dic, oportunidad_mar, nota_final) VALUES (?, ?, ?, ?, ?, ?, ?, NULL)";
            $stmt = $conn->prepare($sql);
            
            $numero_alumno = (int)trim($fila[0]);
            $run = trim($fila[1]);
            $asignatura = trim($fila[2]);
            $seccion = trim($fila[3]);
            $periodo = trim($fila[4]);
            $oportunidad_dic = str_replace(',', '.', trim($fila[5]));
            $oportunidad_mar = isset($fila[6]) ? str_replace(',', '.', trim($fila[6])) : null;
            
            $stmt->bind_param("iisssdd", $numero_alumno, $run, $asignatura, $seccion, $periodo, $oportunidad_dic, $oportunidad_mar);
            $stmt->execute();
            $count++;
        }
    }
    
    // Ejecutar procedimiento almacenado
    $conn->query("CALL procesar_acta_notas()");
    
    $conn->commit();
    echo "Insertadas $count filas exitosamente\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $conn->rollback();
} finally {
    if (isset($archivo)) fclose($archivo);
    $conn->close();
}
?>