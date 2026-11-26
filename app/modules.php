<?php
declare(strict_types=1);

const META_HORAS = 80;

// Las tablas y columnas interpoladas en SQL se toman de esta lista fija.
function modules(PDO $db): array
{
    return [
        'estudiantes' => [
            'singular' => 'estudiante', 'nuevo' => 'Nuevo estudiante',
            'fields' => [
                'documento' => ['Documento', 'text', 25],
                'nombre' => ['Nombre completo', 'text', 120],
                'grado' => ['Grado', 'select', [10 => 'Décimo (10°)', 11 => 'Undécimo (11°)']],
                'proyecto_id' => ['Proyecto', 'select', static fn () => choices($db, 'proyectos')],
            ],
            'headings' => ['Estudiante', 'Documento', 'Grado', 'Proyecto'],
            'description' => 'Administra los estudiantes de décimo y undécimo y su proyecto asignado.',
        ],
        'docentes' => [
            'singular' => 'docente', 'nuevo' => 'Nuevo docente',
            'fields' => [
                'documento' => ['Documento', 'text', 25],
                'nombre' => ['Nombre completo', 'text', 120],
                'correo' => ['Correo electrónico', 'email', 160],
            ],
            'headings' => ['Docente', 'Documento', 'Correo electrónico'],
            'description' => 'Administra los docentes responsables de supervisar las actividades.',
        ],
        'proyectos' => [
            'singular' => 'proyecto', 'nuevo' => 'Nuevo proyecto',
            'fields' => [
                'nombre' => ['Nombre del proyecto', 'text', 120],
                'descripcion' => ['Descripción', 'textarea', 255],
            ],
            'headings' => ['Proyecto', 'Descripción', 'Estudiantes'],
            'description' => 'Administra las opciones de labor social que pueden elegir los estudiantes.',
        ],
        'actividades' => [
            'singular' => 'actividad', 'nuevo' => 'Registrar actividad',
            'fields' => [
                'estudiante_id' => ['Estudiante', 'select', static fn () => choices($db, 'estudiantes', true)],
                'fecha' => ['Fecha de actividad', 'date', 0],
                'horas' => ['Cantidad de horas', 'number', 0],
                'trabajo' => ['Trabajo realizado', 'textarea', 500],
                'docente_id' => ['Docente responsable', 'select', static fn () => choices($db, 'docentes', true)],
            ],
            'headings' => ['Fecha', 'Estudiante', 'Trabajo realizado', 'Horas', 'Docente'],
            'description' => 'Registra las jornadas de labor social y consulta quién las supervisó.',
        ],
    ];
}

function choices(PDO $db, string $table, bool $includeDocument = false): array
{
    $label = $includeDocument ? "CONCAT(nombre, ' · ', documento)" : 'nombre';
    return $db->query("SELECT id, $label FROM $table ORDER BY nombre")->fetchAll(PDO::FETCH_KEY_PAIR);
}

function moduleRows(PDO $db, string $page): array
{
    $sql = match ($page) {
        'estudiantes' => 'SELECT e.*, p.nombre AS proyecto FROM estudiantes e
                          JOIN proyectos p ON p.id = e.proyecto_id ORDER BY e.nombre, e.id',
        'docentes' => 'SELECT * FROM docentes ORDER BY nombre, id',
        'proyectos' => 'SELECT p.*, (SELECT COUNT(*) FROM estudiantes e WHERE e.proyecto_id = p.id) AS inscritos
                        FROM proyectos p ORDER BY p.nombre, p.id',
        'actividades' => 'SELECT a.*, e.nombre AS estudiante, d.nombre AS docente
                          FROM actividades a JOIN estudiantes e ON e.id = a.estudiante_id
                          JOIN docentes d ON d.id = a.docente_id ORDER BY a.fecha DESC, a.id DESC',
    };
    return $db->query($sql)->fetchAll();
}

function formValues(array $fields, array $source): array
{
    $values = [];
    foreach ($fields as $name => [$label, $type, $limit]) {
        $value = field($source, $name);
        if ($value === '') {
            throw new DomainException("Completa el campo «$label».");
        }

        if ($type === 'select') {
            $options = is_callable($limit) ? $limit() : $limit;
            if (!isset($options[$value])) {
                throw new DomainException("Elige una opción válida en «$label».");
            }
        } elseif ($type === 'date') {
            if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D', $value, $parts)
                || (int) $parts[1] < 1000
                || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
                throw new DomainException('Ingresa una fecha válida.');
            }
        } elseif ($type === 'number') {
            if (!preg_match('/^\d{1,2}(?:\.\d{1,2})?$/D', $value)
                || (float) $value <= 0 || (float) $value > META_HORAS) {
                throw new DomainException('Ingresa entre 0,01 y 80 horas, con máximo dos decimales.');
            }
        } else {
            $length = iconv_strlen($value, 'UTF-8');
            if ($length === false || $length > $limit) {
                throw new DomainException("«$label» admite hasta $limit caracteres.");
            }
            if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new DomainException('Ingresa un correo electrónico válido.');
            }
        }
        $values[$name] = $value;
    }
    return $values;
}

function writeRecord(PDO $db, string $table, int $recordId, array $values): void
{
    $columns = array_keys($values);
    $parameters = array_values($values);
    if ($recordId) {
        $assignments = implode(', ', array_map(static fn ($column) => "$column = ?", $columns));
        query($db, "UPDATE $table SET $assignments WHERE id = ?", [...$parameters, $recordId]);
        return;
    }
    $columnList = implode(', ', $columns);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    query($db, "INSERT INTO $table ($columnList) VALUES ($placeholders)", $parameters);
}

function saveActivity(PDO $db, int $recordId, array $values): void
{
    $db->beginTransaction();
    try {
        // El mismo bloqueo serializa los registros y ediciones de horas de este estudiante.
        $studentId = (int) $values['estudiante_id'];
        $student = query($db, 'SELECT id FROM estudiantes WHERE id = ? FOR UPDATE', [$studentId])->fetchColumn();
        if (!$student) {
            throw new DomainException('El estudiante seleccionado ya no existe.');
        }
        if ($recordId) {
            $current = query($db, 'SELECT estudiante_id FROM actividades WHERE id = ? FOR UPDATE',
                [$recordId])->fetchColumn();
            if ((int) $current !== $studentId) {
                throw new DomainException('La actividad ya no existe o pertenece a otro estudiante.');
            }
        }
        $total = query($db, 'SELECT COALESCE(SUM(horas), 0) FROM actividades
                             WHERE estudiante_id = ? AND id <> ?', [$studentId, $recordId])->fetchColumn();
        $remaining = META_HORAS * 100 - (int) round((float) $total * 100);
        if ((int) round((float) $values['horas'] * 100) > $remaining) {
            throw new DomainException('La actividad superaría las 80 horas. Puedes registrar hasta '
                . hours(max(0, $remaining) / 100) . ' horas para este estudiante.');
        }
        writeRecord($db, 'actividades', $recordId, $values);
        $db->commit();
    } catch (Throwable $exception) {
        $db->rollBack();
        throw $exception;
    }
}
