-- Tabla para Asignaturas
CREATE TABLE Asignaturas (
    plan VARCHAR(10) NOT NULL,
    asignatura_id VARCHAR(20) NOT NULL,
    asignatura VARCHAR(255) NOT NULL,
    nivel NUMERIC(4, 1) NOT NULL
);

-- Tabla para Docentes Planificados
CREATE TABLE Docentes_Planificados (
    run BIGINT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido_p VARCHAR(50) NOT NULL,
    telefono VARCHAR(15),
    email_personal VARCHAR(255),
    email_institucional VARCHAR(255),
    dedicacion NUMERIC(4, 1),
    contrato VARCHAR(50) NOT NULL,
    diurno VARCHAR(10),
    vespertino VARCHAR(10),
    sede VARCHAR(50),
    carrera VARCHAR(50),
    grado_academico VARCHAR(50) NOT NULL,
    jerarquia VARCHAR(50) NOT NULL,
    cargo VARCHAR(50),
    estamento VARCHAR(50)
);

-- Tabla para Estudiantes
CREATE TABLE Estudiantes (
    codigo_plan VARCHAR(10) NOT NULL,
    carrera VARCHAR(100) NOT NULL,
    cohorte VARCHAR(10) NOT NULL,
    numero_de_alumno INTEGER NOT NULL,
    bloqueado CHAR(1) NOT NULL,
    causal_bloqueo VARCHAR(255),
    run BIGINT NOT NULL,
    nombres VARCHAR(50) NOT NULL,
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50),
    logro VARCHAR(50) NOT NULL,
    fecha_logro DATE NOT NULL,
    ultima_carga DATE
);

-- Tabla para Planeación
CREATE TABLE Planeacion (
    periodo VARCHAR(10) NOT NULL,
    sede VARCHAR(50) NOT NULL,
    facultad VARCHAR(255) NOT NULL,
    codigo_depto VARCHAR(20) NOT NULL,
    departamento VARCHAR(255) NOT NULL,
    id_asignatura VARCHAR(20) NOT NULL,
    asignatura VARCHAR(255) NOT NULL,
    seccion VARCHAR(10) NOT NULL,
    duracion VARCHAR(10) NOT NULL,
    jornada VARCHAR(20) NOT NULL,
    cupo INTEGER NOT NULL,
    inscritos INTEGER NOT NULL,
    dia VARCHAR(20) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    lugar VARCHAR(50),
    edificio VARCHAR(100),
    profesor_principal CHAR(1),
    run BIGINT NOT NULL,
    nombre_docente VARCHAR(50) NOT NULL,
    primer_apellido_docente VARCHAR(50) NOT NULL,
    segundo_apellido_docente VARCHAR(50) NOT NULL,
    jerarquizacion CHAR(1) NOT NULL
);

-- Tabla para Planes
CREATE TABLE Planes (
    codigo_plan VARCHAR(10) NOT NULL,
    facultad VARCHAR(255) NOT NULL,
    carrera VARCHAR(100) NOT NULL,
    plan VARCHAR(255) NOT NULL,
    jornada VARCHAR(50) NOT NULL,
    sede VARCHAR(50) NOT NULL,
    grado VARCHAR(50) NOT NULL,
    modalidad VARCHAR(50) NOT NULL,
    inicio_vigencia DATE NOT NULL
);

-- Tabla para Prerrequisitos
CREATE TABLE Prerequisitos (
    plan VARCHAR(10) NOT NULL,
    asignatura_id VARCHAR(20) NOT NULL,
    asignatura VARCHAR(255) NOT NULL,
    nivel NUMERIC(4, 1) NOT NULL,
    prerequisitos VARCHAR(50),
    prerequisitos_1 VARCHAR(50)
);

-- Crear tabla Notas
CREATE TABLE Notas (
    codigo_plan VARCHAR(10) NOT NULL,
    plan VARCHAR(255) NOT NULL,
    cohorte VARCHAR(10) NOT NULL,
    sede VARCHAR(50) NOT NULL,
    run BIGINT NOT NULL,
    dv CHAR(1) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100),
    numero_alumno INTEGER NOT NULL,
    periodo_asignatura VARCHAR(10) NOT NULL,
    codigo_asignatura VARCHAR(20) NOT NULL,
    asignatura VARCHAR(255) NOT NULL,
    convocatoria VARCHAR(20) NOT NULL,
    calificacion VARCHAR(2) NOT NULL,
    nota NUMERIC(2, 1)
);

