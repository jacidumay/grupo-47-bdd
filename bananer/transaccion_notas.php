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
        END
        WHERE nota_final IS NULL;
    END;";
  
    $conn->query($crear_procedimiento);
    

    // Eliminar la vista si existe
    if (!$conn->query("DROP VIEW IF EXISTS vista_acta_notas")) {
        throw new Exception("Error eliminando vista existente: " . $conn->error);
    }
    $conn->query("CALL procesar_acta_notas()");
    // Crear la nueva vista
    $crear_vista = "
    CREATE VIEW vista_acta_notas AS
    SELECT DISTINCT
        a.numero_alumno,
        a.asignatura AS curso,
        a.periodo,
        CONCAT(e.Nombres COLLATE utf8mb4_unicode_ci, ' ', 
               e.Primer_Apellido COLLATE utf8mb4_unicode_ci, ' ', 
               e.Segundo_Apellido COLLATE utf8mb4_unicode_ci) AS nombre_estudiante,
        'Profesor Por Asignar' AS nombre_profesor,
        a.nota_final
    FROM acta a
    JOIN Estudiantes e 
        ON CAST(a.numero_alumno AS CHAR) COLLATE utf8mb4_unicode_ci = e.Número_de_alumno COLLATE utf8mb4_unicode_ci
    LEFT JOIN Planeacion p 
        ON a.asignatura COLLATE utf8mb4_unicode_ci = p.Id_Asignatura COLLATE utf8mb4_unicode_ci
        AND REPLACE(a.periodo, '-2', '-02') COLLATE utf8mb4_unicode_ci = p.Periodo COLLATE utf8mb4_unicode_ci
        AND a.seccion COLLATE utf8mb4_unicode_ci = p.Sección COLLATE utf8mb4_unicode_ci
    WHERE (a.oportunidad_dic >= 1.0 AND a.oportunidad_dic <= 7.0)
        AND (a.oportunidad_mar IS NULL 
             OR (a.oportunidad_mar >= 1.0 AND a.oportunidad_mar <= 7.0))
        AND e.Número_de_alumno IS NOT NULL;
    ";

    if ($conn->query($crear_vista) === TRUE) {
        echo "Vista creada exitosamente\n";
    } else {
        throw new Exception("Error creando vista: " . $conn->error);
    }
    // Crear trigger
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

        -- Eliminar registros anteriores
        DELETE FROM Notas 
        WHERE `Número_de_alumno` = NEW.numero_alumno 
        AND Asignatura = NEW.asignatura
        AND Periodo_Asignatura = NEW.periodo;

        -- Insertar nuevo registro
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

    if ($conn->query($crear_trigger) === TRUE) {
        echo "Notas finales calculadas\n";
    } 

    // Procesar archivo CSV
    // Procesar archivo CSV
    $conn->begin_transaction();
    $archivo = fopen($nombre_archivo, 'r');
    $encabezado = fgetcsv($archivo, 0, ';');
    $count = 0;

    // Limpiar tabla acta antes de insertar
    $conn->query("TRUNCATE TABLE acta");

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

    // Ejecutar procedimiento almacenado para calcular notas finales


    // Confirmar la transacción
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