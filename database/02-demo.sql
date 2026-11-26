-- Datos ficticios para una base de datos recién creada.
USE labor_social;

INSERT INTO proyectos (id, nombre, descripcion) VALUES
    (1, 'Gestión Documental', 'Organización e inventario del archivo escolar.'),
    (2, 'Embellecimiento Interno', 'Mejora de aulas y espacios comunes.'),
    (3, 'Embellecimiento Externo', 'Cuidado de patios, corredores y zonas deportivas.'),
    (4, 'Jardinería', 'Mantenimiento de las zonas verdes del colegio.'),
    (5, 'Huerta de prueba', 'Proyecto sin estudiantes, preparado para demostrar el borrado.');

INSERT INTO docentes (id, documento, nombre, correo) VALUES
    (1, 'D-101', 'Laura Mejía', 'laura.mejia@example.org'),
    (2, 'D-102', 'Andrés Ruiz', 'andres.ruiz@example.org');

INSERT INTO estudiantes (id, documento, nombre, grado, proyecto_id) VALUES
    (1, 'E-101', 'Valentina Rojas', 11, 1),
    (2, 'E-102', 'Mateo Salazar', 10, 2),
    (3, 'E-103', 'Isabella Torres', 10, 4);

INSERT INTO actividades (estudiante_id, docente_id, fecha, horas, trabajo) VALUES
    (1, 1, '2026-05-04', 8, 'Clasificación de carpetas por año escolar.'),
    (1, 1, '2026-05-05', 8, 'Rotulación de cajas del archivo.'),
    (1, 1, '2026-05-06', 8, 'Inventario de expedientes de matrícula.'),
    (1, 1, '2026-05-07', 8, 'Revisión de registros de préstamos.'),
    (1, 1, '2026-05-11', 8, 'Digitalización de índices de consulta.'),
    (1, 1, '2026-05-12', 8, 'Organización de expedientes recientes.'),
    (1, 1, '2026-05-13', 8, 'Depuración de copias duplicadas del inventario.'),
    (1, 1, '2026-05-14', 8, 'Verificación de cajas y ubicación en estantería.'),
    (1, 1, '2026-05-18', 8, 'Actualización del índice del archivo.'),
    (1, 1, '2026-05-19', 8, 'Entrega del inventario final a coordinación.'),
    (2, 2, '2026-05-07', 4, 'Preparación de carteleras informativas.'),
    (3, 2, '2026-05-08', 3.5, 'Riego y limpieza de jardineras.');
