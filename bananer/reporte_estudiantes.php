<?php
session_start();
include 'db_connection.php';

// Consulta para estudiantes dentro de nivel
$sql_dentro_nivel = "
WITH SemestresEsperados AS (
    SELECT 
        Cohorte,
        TIMESTAMPDIFF(
            MONTH, 
            STR_TO_DATE(Cohorte, '%Y-%m'),
            STR_TO_DATE('2024-2', '%Y-%m')
        ) / 6 + 1 as semestre_esperado
    FROM Estudiantes
    WHERE ultima_carga = '2024-2'
    GROUP BY Cohorte
)
SELECT COUNT(*) AS estudiantes_dentro_nivel
FROM Estudiantes e
JOIN SemestresEsperados se ON e.Cohorte = se.Cohorte
WHERE ultima_carga = '2024-2'
AND (
    (se.semestre_esperado <= 1 AND e.Logro = 'INGRESO') OR
    (se.semestre_esperado = 2 AND e.Logro = '1° semestre') OR
    (se.semestre_esperado = 3 AND e.Logro = '2° semestre') OR
    (se.semestre_esperado = 4 AND e.Logro = '3° semestre') OR
    (se.semestre_esperado = 5 AND e.Logro = '4° semestre') OR
    (se.semestre_esperado = 6 AND e.Logro = '5° semestre') OR
    (se.semestre_esperado = 7 AND e.Logro = '6° semestre') OR
    (se.semestre_esperado = 8 AND e.Logro = '7° semestre') OR
    (se.semestre_esperado = 9 AND e.Logro = '8° semestre') OR
    (se.semestre_esperado = 10 AND e.Logro = '9° semestre') OR
    (se.semestre_esperado > 10 AND e.Logro = '10° semestre')
)";

// Consulta para estudiantes fuera de nivel
$sql_fuera_nivel = "
WITH SemestresEsperados AS (
    SELECT 
        Cohorte,
        TIMESTAMPDIFF(
            MONTH, 
            STR_TO_DATE(Cohorte, '%Y-%m'),
            STR_TO_DATE('2024-2', '%Y-%m')
        ) / 6 + 1 as semestre_esperado
    FROM Estudiantes
    WHERE ultima_carga = '2024-2'
    GROUP BY Cohorte
)
SELECT COUNT(*) AS estudiantes_fuera_nivel
FROM Estudiantes e
JOIN SemestresEsperados se ON e.Cohorte = se.Cohorte
WHERE ultima_carga = '2024-2'
AND NOT (
    (se.semestre_esperado <= 1 AND e.Logro = 'INGRESO') OR
    (se.semestre_esperado = 2 AND e.Logro = '1° semestre') OR
    (se.semestre_esperado = 3 AND e.Logro = '2° semestre') OR
    (se.semestre_esperado = 4 AND e.Logro = '3° semestre') OR
    (se.semestre_esperado = 5 AND e.Logro = '4° semestre') OR
    (se.semestre_esperado = 6 AND e.Logro = '5° semestre') OR
    (se.semestre_esperado = 7 AND e.Logro = '6° semestre') OR
    (se.semestre_esperado = 8 AND e.Logro = '7° semestre') OR
    (se.semestre_esperado = 9 AND e.Logro = '8° semestre') OR
    (se.semestre_esperado = 10 AND e.Logro = '9° semestre') OR
    (se.semestre_esperado > 10 AND e.Logro = '10° semestre')
)";

// Ejecutar consultas
$result_dentro = $conn->query($sql_dentro_nivel);
if ($result_dentro === false) {
    echo "Error en la consulta SQL (dentro de nivel): " . $conn->error;
    exit;
}

$result_fuera = $conn->query($sql_fuera_nivel);
if ($result_fuera === false) {
    echo "Error en la consulta SQL (fuera de nivel): " . $conn->error;
    exit;
}

// Obtener los resultados
$dentro_nivel = $result_dentro->fetch_assoc();
$fuera_nivel = $result_fuera->fetch_assoc();
?>

<h1>Reporte de Estudiantes Vigentes (2024-2)</h1>
<p>Estudiantes dentro de nivel: <?php echo htmlspecialchars($dentro_nivel['estudiantes_dentro_nivel']); ?></p>
<p>Estudiantes fuera de nivel: <?php echo htmlspecialchars($fuera_nivel['estudiantes_fuera_nivel']); ?></p>