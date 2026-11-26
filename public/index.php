<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/modules.php';
require __DIR__ . '/../app/reports.php';
$config = require __DIR__ . '/../app/config.php';

session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);

$navigation = [
    'inicio' => ['Inicio', 'home'],
    'estudiantes' => ['Estudiantes', 'students'],
    'docentes' => ['Docentes', 'teacher'],
    'proyectos' => ['Proyectos', 'folder'],
    'actividades' => ['Actividades', 'clock'],
    'informe' => ['Informes', 'report'],
];
$page = field($_GET, 'page') ?: 'inicio';
if (!isset($navigation[$page])) {
    http_response_code(404);
    $page = 'inicio';
    $error = 'La sección solicitada no existe.';
}

try {
    $db = db();
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    $error = 'La base de datos no está disponible. Comprueba que el servicio esté iniciado y que se haya importado 01-schema.sql.';
}

$module = null;
$editing = false;
$recordId = 0;
$values = [];
$rows = [];
$search = field($_GET, 'q');
$studentId = id(field($_GET, 'estudiante'));
$report = null;
$stats = [];
$recent = [];

if (isset($db)) {
    $module = modules($db)[$page] ?? null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$module || !hash_equals($_SESSION['csrf'], field($_POST, 'csrf'))) {
            http_response_code(400);
            exit('Solicitud inválida. Recarga la página y vuelve a intentarlo.');
        }
        $recordId = id(field($_POST, 'id'));
        $action = field($_POST, 'action');
        $editing = $action === 'save';
        $values = $_POST;
        try {
            if ($action === 'delete' && $recordId) {
                $deleted = query($db, "DELETE FROM $page WHERE id = ?", [$recordId])->rowCount();
                $_SESSION['notice'] = $deleted ? 'Registro eliminado.' : 'El registro ya no existe.';
            } elseif ($action === 'save') {
                if ($recordId && !query($db, "SELECT 1 FROM $page WHERE id = ?", [$recordId])->fetchColumn()) {
                    throw new DomainException('El registro que intentas editar ya no existe.');
                }
                $validated = formValues($module['fields'], $_POST);
                if ($page === 'actividades') {
                    saveActivity($db, $recordId, $validated);
                } else {
                    writeRecord($db, $page, $recordId, $validated);
                }
                $_SESSION['notice'] = $recordId ? 'Cambios guardados.' : 'Registro creado.';
            } else {
                throw new DomainException('La acción solicitada no es válida.');
            }
            redirect($page);
        } catch (DomainException $exception) {
            $error = $exception->getMessage();
        } catch (PDOException $exception) {
            $error = match ((int) ($exception->errorInfo[1] ?? 0)) {
                1062 => 'Ya existe un registro con ese documento o nombre de proyecto.',
                1451 => 'Este registro está asociado a otros datos. Quita o reasigna esas referencias antes de eliminarlo.',
                1452 => 'Un registro asociado ya no existe. Selecciona una opción vigente e inténtalo de nuevo.',
                default => 'La operación no pudo guardarse. Recarga la página e inténtalo de nuevo.',
            };
            if (!in_array((int) ($exception->errorInfo[1] ?? 0), [1062, 1451, 1452], true)) {
                error_log($exception->getMessage());
            }
        }
    } elseif ($module && array_key_exists('edit', $_GET)) {
        $editing = true;
        $recordId = id(field($_GET, 'edit'));
        if ($recordId) {
            $values = query($db, "SELECT * FROM $page WHERE id = ?", [$recordId])->fetch() ?: [];
            if (!$values) {
                http_response_code(404);
                $editing = false;
                $error = 'El registro seleccionado no existe.';
            }
        } elseif ($page === 'actividades') {
            $values = ['estudiante_id' => $studentId, 'fecha' => date('Y-m-d')];
        }
    }

    if ($module && !$editing) {
        $rows = moduleRows($db, $page);
    } elseif ($page === 'informe') {
        $report = $studentId ? studentReport($db, $studentId) : null;
        if ($studentId && !$report) {
            http_response_code(404);
            $error = 'El estudiante seleccionado no existe.';
        }
        if (!$report) {
            $rows = findStudents($db, $search);
        }
    } elseif ($page === 'inicio') {
        $stats = $db->query('SELECT
            (SELECT COUNT(*) FROM estudiantes) AS estudiantes,
            (SELECT COUNT(*) FROM docentes) AS docentes,
            (SELECT COUNT(*) FROM proyectos) AS proyectos,
            (SELECT COUNT(*) FROM (SELECT estudiante_id FROM actividades
                                   GROUP BY estudiante_id HAVING SUM(horas) >= 80) completos) AS completos')->fetch();
        $recent = $db->query('SELECT a.fecha, a.horas, a.trabajo, e.nombre, e.id
                             FROM actividades a JOIN estudiantes e ON e.id = a.estudiante_id
                             ORDER BY a.id DESC LIMIT 4')->fetchAll();
    }
}

require __DIR__ . '/../app/views.php';
