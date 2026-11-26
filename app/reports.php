<?php
declare(strict_types=1);

function findStudents(PDO $db, string $search): array
{
    return query($db, 'SELECT e.id, e.documento, e.nombre, e.grado, p.nombre AS proyecto,
                             COALESCE(SUM(a.horas), 0) AS total
                      FROM estudiantes e
                      JOIN proyectos p ON p.id = e.proyecto_id
                      LEFT JOIN actividades a ON a.estudiante_id = e.id
                      WHERE LOCATE(?, e.nombre) > 0 OR LOCATE(?, e.documento) > 0 OR ? = \'\'
                      GROUP BY e.id, e.documento, e.nombre, e.grado, p.nombre
                      ORDER BY e.nombre, e.id', [$search, $search, $search])->fetchAll();
}

function studentReport(PDO $db, int $studentId): ?array
{
    $student = query($db, 'SELECT e.*, p.nombre AS proyecto FROM estudiantes e
                           JOIN proyectos p ON p.id = e.proyecto_id WHERE e.id = ?', [$studentId])->fetch();
    if (!$student) {
        return null;
    }
    // id resuelve empates cuando varias actividades se registran el mismo día.
    $rows = query($db, 'SELECT a.fecha, a.horas, a.trabajo, d.nombre AS docente,
                               SUM(a.horas) OVER (
                                   ORDER BY a.fecha, a.id ROWS UNBOUNDED PRECEDING
                               ) AS acumuladas
                        FROM actividades a JOIN docentes d ON d.id = a.docente_id
                        WHERE a.estudiante_id = ? ORDER BY a.fecha, a.id', [$studentId])->fetchAll();
    return [
        'student' => $student,
        'rows' => $rows,
        'total' => $rows ? (float) $rows[array_key_last($rows)]['acumuladas'] : 0.0,
    ];
}
