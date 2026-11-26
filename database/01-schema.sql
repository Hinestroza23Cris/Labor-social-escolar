CREATE DATABASE IF NOT EXISTS labor_social
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE labor_social;

CREATE TABLE proyectos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE docentes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    documento VARCHAR(25) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(160) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE estudiantes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    documento VARCHAR(25) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    grado TINYINT UNSIGNED NOT NULL,
    proyecto_id INT UNSIGNED NOT NULL,
    CONSTRAINT chk_grado CHECK (grado IN (10, 11)),
    CONSTRAINT fk_estudiante_proyecto FOREIGN KEY (proyecto_id)
        REFERENCES proyectos (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE actividades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT UNSIGNED NOT NULL,
    docente_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    horas DECIMAL(4,2) NOT NULL,
    trabajo VARCHAR(500) NOT NULL,
    CONSTRAINT chk_horas CHECK (horas > 0 AND horas <= 80),
    CONSTRAINT fk_actividad_estudiante FOREIGN KEY (estudiante_id)
        REFERENCES estudiantes (id) ON DELETE RESTRICT,
    CONSTRAINT fk_actividad_docente FOREIGN KEY (docente_id)
        REFERENCES docentes (id) ON DELETE RESTRICT,
    INDEX idx_actividad_informe (estudiante_id, fecha, id)
) ENGINE=InnoDB;
