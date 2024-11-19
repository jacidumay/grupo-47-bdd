<?php
// Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "tu_contraseña";
$dbname = "bananer";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Crear el Stored Procedure
$sql = "
DELIMITER //
CREATE PROCEDURE cargar_notas()
BEGIN
    DECLARE v_existe_curso INT;
    DECLARE v_existe_alumno INT;
    DECLARE v_existe_profesor INT;
    DECLARE v_nota1 FLOAT;
    DECLARE v_nota2 FLOAT;
    DECLARE v_nota_final FLOAT;

    DECLARE done INT DEFAULT 0;
    DECLARE cur CURSOR FOR
        SELECT
            a.num_estudiante, a.cod_curso, a.periodo, a.nota1, a.nota2, p.nombre, p.profesor
        FROM
            notas a
        JOIN estudiantes e ON a.num_estudiante = e.num_estudiante
        JOIN profesores p ON a.profesor_id = p.id
        WHERE a.nota1 IS NOT NULL AND a.nota2 IS NOT NULL;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_num_estudiante, v_cod_curso, v_periodo, v_nota1, v_nota2, v_nombre_estudiante, v_nombre_profesor;
        
        IF done THEN
            LEAVE read_loop;
        END IF;

        SELECT COUNT(*) INTO v_existe_curso FROM cursos WHERE cod_curso = v_cod_curso;
        IF v_existe_curso = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El curso no existe';
        END IF;

        SELECT COUNT(*) INTO v_existe_alumno FROM estudiantes WHERE num_estudiante = v_num_estudiante;
        IF v_existe_alumno = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El alumno no existe';
        END IF;

        SELECT COUNT(*) INTO v_existe_profesor FROM profesores WHERE id = v_profesor_id;
        IF v_existe_profesor = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El profesor no existe';
        END IF;

        IF v_nota1 < 1 OR v_nota1 > 7 OR v_nota2 < 1 OR v_nota2 > 7 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Las notas deben estar entre 1 y 7';
        END IF;

        SET v_nota_final = (v_nota1 + v_nota2) / 2;

        INSERT INTO acta (num_estudiante, cod_curso, periodo, nombre_estudiante, nombre_profesor, nota_final)
        VALUES (v_num_estudiante, v_cod_curso, v_periodo, v_nombre_estudiante, v_nombre_profesor, v_nota_final);

    END LOOP;

    CLOSE cur;
END //
DELIMITER ;
";

// Ejecutar la creación del stored procedure
if ($conn->query($sql) === TRUE) {
    echo "Stored Procedure creado con éxito";
} else {
    echo "Error al crear Stored Procedure: " . $conn->error;
}

// Crear la Vista
$sql = "
CREATE VIEW vista_acta AS
SELECT
    a.num_estudiante,
    c.nombre AS curso,
    a.periodo,
    a.nombre_estudiante,
    p.nombre AS profesor,
    a.nota_final
FROM
    acta a
JOIN cursos c ON a.cod_curso = c.cod_curso
JOIN profesores p ON a.profesor_id = p.id;
";

// Ejecutar la creación de la vista
if ($conn->query($sql) === TRUE) {
    echo "Vista 'vista_acta' creada con éxito";
} else {
    echo "Error al crear vista: " . $conn->error;
}

$conn->close();
?>
