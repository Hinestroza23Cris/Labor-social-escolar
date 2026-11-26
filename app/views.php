<?php
$icons = ['home' => '⌂', 'students' => '♙', 'teacher' => '◉', 'folder' => '▤', 'clock' => '◷', 'report' => '▥'];
$title = $navigation[$page][0];

if ($module && $editing) {
    $title = $recordId ? 'Editar ' . $module['singular'] : $module['nuevo'];
}

function moduleCells(string $page, array $row): array
{
    return match ($page) {
        'estudiantes' => [$row['nombre'], $row['documento'], $row['grado'] . '°', $row['proyecto']],
        'docentes' => [$row['nombre'], $row['documento'], $row['correo']],
        'proyectos' => [$row['nombre'], $row['descripcion'], $row['inscritos']],
        'actividades' => [dateLabel($row['fecha']), $row['estudiante'], $row['trabajo'],
            hours($row['horas']) . ' h', $row['docente']],
    };
}
?>
<!doctype html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?= e($title) ?> · Labor social escolar</title>
    <link rel="stylesheet" href="style.css">
    <script src="actions.js" defer></script>
</head>
<body>
<a class="skip" href="#contenido">Saltar al contenido</a>
<div class="shell">
    <aside class="sidebar" aria-label="Menú de la aplicación">
        <a class="brand" href="<?= e(url('inicio')) ?>">
            <span class="school-mark" aria-hidden="true">XYZ</span>
            <span>Labor social<small>Institución Educativa XYZ</small></span>
        </a>
        <nav aria-label="Secciones">
            <?php foreach ($navigation as $key => [$label, $icon]): ?>
                <a href="<?= e(url($key)) ?>" <?= $key === $page ? 'aria-current="page"' : '' ?>>
                    <span class="nav-icon" aria-hidden="true"><?= e($icons[$icon]) ?></span>
                    <?= e($label) ?>
                </a>
            <?php endforeach ?>
        </nav>
        <div class="sidebar-footer">
            <a href="ayuda.html">Ayuda de uso <span aria-hidden="true">↗</span></a>
            <small>Servicio social estudiantil</small>
        </div>
    </aside>

    <main id="contenido">
        <div class="topline">
            <span><?= e($navigation[$page][0]) ?></span>
            <span class="grade-label">Grados 10° y 11°</span>
        </div>
        <?php if (isset($error)): ?><p class="alert error" role="alert"><?= e($error) ?></p><?php endif ?>
        <?php if ($notice): ?><p class="alert success" role="status"><?= e($notice) ?></p><?php endif ?>

        <?php if (!isset($db)): ?>
            <h1><?= e($title) ?></h1>
        <?php elseif ($page === 'inicio'): ?>
            <section class="hero" aria-labelledby="titulo">
                <div>
                    <p class="eyebrow">Servicio social estudiantil</p>
                    <h1 id="titulo">Labor social<br>escolar</h1>
                    <p>Registra el trabajo de los estudiantes y consulta su avance hasta completar el servicio social.</p>
                    <div class="actions">
                        <a class="button primary" href="<?= e(url('actividades', ['edit' => 0])) ?>">Registrar actividad <span aria-hidden="true">→</span></a>
                        <a class="button inverse" href="<?= e(url('informe')) ?>">Consultar informes</a>
                    </div>
                </div>
                <div class="hero-target">
                    <span class="target-number">80</span>
                    <span class="target-unit">horas</span>
                    <span class="target-caption">Meta por estudiante</span>
                </div>
            </section>
            <section class="metrics" aria-label="Resumen general">
                <?php foreach ([
                    ['estudiantes', 'Estudiantes', 'students'],
                    ['docentes', 'Docentes', 'teacher'],
                    ['proyectos', 'Proyectos', 'folder'],
                    ['completos', 'Meta cumplida', 'report'],
                ] as [$key, $label, $icon]): ?>
                    <a class="metric" href="<?= e(url($key === 'completos' ? 'informe' : $key)) ?>">
                        <span class="tile-icon" aria-hidden="true"><?= e($icons[$icon]) ?></span>
                        <strong><?= e($stats[$key]) ?></strong>
                        <span><?= e($label) ?></span>
                    </a>
                <?php endforeach ?>
            </section>
            <section class="panel">
                <div class="panel-title">
                    <h2>Últimos registros</h2>
                    <a class="text-link" href="<?= e(url('actividades')) ?>">Ver actividades <span aria-hidden="true">→</span></a>
                </div>
                <?php if ($recent): ?>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Estudiante</th><th>Trabajo realizado</th><th>Fecha</th><th>Horas</th></tr></thead>
                            <tbody>
                            <?php foreach ($recent as $row): ?>
                                <tr>
                                    <td><a class="text-link" href="<?= e(url('informe', ['estudiante' => $row['id']])) ?>"><?= e($row['nombre']) ?></a></td>
                                    <td><?= e($row['trabajo']) ?></td>
                                    <td class="nowrap"><?= e(dateLabel($row['fecha'])) ?></td>
                                    <td class="nowrap"><?= e(hours($row['horas'])) ?> h</td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?><p class="empty">Aún no se han registrado actividades.</p><?php endif ?>
            </section>
            <section class="panel evidence" id="videos">
                <div>
                    <h2>Presentación del proyecto</h2>
                    <dl class="author">
                        <div><dt>Nombre completo</dt><dd><?= e($config['autor'] ?: 'Por completar') ?></dd></div>
                        <div><dt>Programa académico</dt><dd><?= e($config['programa'] ?: 'Por completar') ?></dd></div>
                    </dl>
                </div>
                <div>
                    <h3>Videos de exposición</h3>
                    <ol class="video-list">
                        <?php foreach ($config['videos'] as $label => $link): ?>
                            <li>
                                <?php if (filter_var($link, FILTER_VALIDATE_URL) && parse_url($link, PHP_URL_SCHEME) === 'https'): ?>
                                    <a class="text-link" href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer"><?= e($label) ?> ↗</a>
                                <?php else: ?>
                                    <span><?= e($label) ?></span><small>Pendiente</small>
                                <?php endif ?>
                            </li>
                        <?php endforeach ?>
                    </ol>
                </div>
            </section>

        <?php elseif ($module && $editing): ?>
            <div class="page-title">
                <div><a class="text-link" href="<?= e(url($page)) ?>">← Volver a <?= e(strtolower($navigation[$page][0])) ?></a>
                <h1><?= e($title) ?></h1></div>
            </div>
            <form method="post" action="<?= e(url($page)) ?>" class="panel entry">
                <?= csrfInput() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= e($recordId) ?>">
                <?php foreach ($module['fields'] as $name => [$label, $type, $limit]): ?>
                    <?php
                    $raw = $values[$name] ?? '';
                    $value = is_scalar($raw) ? (string) $raw : '';
                    $fixedStudent = $page === 'actividades' && $recordId && $name === 'estudiante_id';
                    ?>
                    <div class="form-field <?= $type === 'textarea' ? 'wide' : '' ?>">
                        <label for="<?= e($name) ?>"><?= e($label) ?></label>
                        <?php if ($type === 'select'): ?>
                            <?php $options = is_callable($limit) ? $limit() : $limit ?>
                            <?php if ($fixedStudent): ?>
                                <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>">
                                <input id="<?= e($name) ?>" value="<?= e($options[$value] ?? '') ?>" readonly>
                            <?php else: ?>
                                <select name="<?= e($name) ?>" id="<?= e($name) ?>" required>
                                    <option value="">Seleccionar</option>
                                    <?php foreach ($options as $option => $optionLabel): ?>
                                        <option value="<?= e($option) ?>" <?= (string) $option === $value ? 'selected' : '' ?>><?= e($optionLabel) ?></option>
                                    <?php endforeach ?>
                                </select>
                                <?php if (!$options): ?><small>Primero crea un registro en la sección correspondiente.</small><?php endif ?>
                            <?php endif ?>
                        <?php elseif ($type === 'textarea'): ?>
                            <textarea name="<?= e($name) ?>" id="<?= e($name) ?>" maxlength="<?= e($limit) ?>" rows="4" required><?= e($value) ?></textarea>
                        <?php else: ?>
                            <input name="<?= e($name) ?>" id="<?= e($name) ?>" type="<?= e($type) ?>" value="<?= e($value) ?>"
                                <?= $limit ? 'maxlength="' . e($limit) . '"' : '' ?>
                                <?= $type === 'number' ? 'min="0.01" max="80" step="0.01" inputmode="decimal"' : '' ?>
                                required>
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
                <div class="actions wide">
                    <button class="button primary" type="submit"><?= $recordId ? 'Guardar cambios' : 'Guardar registro' ?></button>
                    <a class="button secondary" href="<?= e(url($page)) ?>">Cancelar</a>
                </div>
            </form>

        <?php elseif ($module): ?>
            <div class="page-title">
                <div><h1><?= e($title) ?></h1><p><?= e($module['description']) ?></p></div>
                <a class="button primary" href="<?= e(url($page, ['edit' => 0])) ?>"><span aria-hidden="true">＋</span> <?= e($module['nuevo']) ?></a>
            </div>
            <section class="panel" aria-label="Registros">
                <div class="panel-title"><h2>Registros</h2><span class="count"><?= count($rows) ?> en total</span></div>
                <?php if ($rows): ?>
                    <div class="table-wrap">
                        <table>
                            <thead><tr>
                                <?php foreach ($module['headings'] as $heading): ?><th><?= e($heading) ?></th><?php endforeach ?>
                                <th>Acciones</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <?php foreach (moduleCells($page, $row) as $cell): ?><td><?= e($cell) ?></td><?php endforeach ?>
                                    <td><div class="row-actions">
                                        <?php if ($page === 'estudiantes'): ?>
                                            <a class="text-link" href="<?= e(url('informe', ['estudiante' => $row['id']])) ?>">Informe</a>
                                        <?php endif ?>
                                        <a class="text-link" href="<?= e(url($page, ['edit' => $row['id']])) ?>">Editar</a>
                                        <form method="post" action="<?= e(url($page)) ?>" data-confirm="¿Eliminar este registro de forma permanente?">
                                            <?= csrfInput() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                            <button type="submit" class="delete-link">Eliminar</button>
                                        </form>
                                    </div></td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?><p class="empty">Aún no hay registros en esta sección.</p><?php endif ?>
            </section>

        <?php elseif ($report): ?>
            <?php
            $student = $report['student'];
            $total = $report['total'];
            $complete = $total >= META_HORAS;
            ?>
            <div class="page-title report-tools">
                <div><a class="text-link" href="<?= e(url('informe', $search === '' ? [] : ['q' => $search])) ?>">← Buscar estudiante</a>
                <h1>Informe de labor social</h1></div>
                <div class="actions">
                    <?php if (!$complete): ?>
                        <a class="button secondary" href="<?= e(url('actividades', ['edit' => 0, 'estudiante' => $student['id']])) ?>">Registrar actividad</a>
                    <?php endif ?>
                    <button class="button primary" type="button" data-print>Imprimir informe</button>
                </div>
            </div>
            <article class="panel report-paper">
                <header class="report-header">
                    <span class="school-mark large" role="img" aria-label="Escudo de la Institución Educativa XYZ">XYZ</span>
                    <div><p>Institución Educativa “XYZ”</p><h2>Registro detallado de actividades</h2><p>Labor social</p></div>
                    <div class="school-flag" role="img" aria-label="Bandera del colegio"></div>
                </header>
                <dl class="report-details">
                    <div><dt>Nombre del estudiante</dt><dd><?= e($student['nombre']) ?></dd></div>
                    <div><dt>Grado</dt><dd><?= e($student['grado']) ?>°</dd></div>
                    <div><dt>Proyecto</dt><dd><?= e($student['proyecto']) ?></dd></div>
                </dl>
                <div class="table-wrap">
                    <table class="report-table">
                        <caption>Informe</caption>
                        <thead><tr>
                            <th>Fecha de actividad</th><th>Cantidad de horas</th><th>Horas acumuladas</th>
                            <th>Trabajo realizado</th><th>Docente responsable</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($report['rows'] as $row): ?>
                            <tr>
                                <td class="nowrap"><?= e(dateLabel($row['fecha'])) ?></td>
                                <td><?= e(hours($row['horas'])) ?></td>
                                <td><strong><?= e(hours($row['acumuladas'])) ?></strong></td>
                                <td><?= e($row['trabajo']) ?></td>
                                <td><?= e($row['docente']) ?></td>
                            </tr>
                        <?php endforeach ?>
                        <?php if (!$report['rows']): ?>
                            <tr><td colspan="5">Este estudiante aún no tiene actividades registradas.</td></tr>
                        <?php endif ?>
                        </tbody>
                    </table>
                </div>
                <footer class="report-total">
                    <div><span>Total de horas acumuladas</span><strong><?= e(hours($total)) ?> / <?= META_HORAS ?> h</strong></div>
                    <span class="status <?= $complete ? 'done' : '' ?>"><?= $complete ? 'Meta cumplida' : e(hours(META_HORAS - $total)) . ' h pendientes' ?></span>
                </footer>
                <progress aria-label="Progreso de horas" max="<?= META_HORAS ?>" value="<?= e($total) ?>"></progress>
            </article>

        <?php elseif ($page === 'informe'): ?>
            <div class="page-title"><div><h1>Informes</h1><p>Busca a un estudiante por nombre o documento para ver su registro detallado.</p></div></div>
            <section class="panel">
                <form class="search-form" method="get">
                    <input type="hidden" name="page" value="informe">
                    <div class="form-field">
                        <label for="q">Buscar estudiante</label>
                        <input type="search" name="q" id="q" value="<?= e($search) ?>" placeholder="Ej. Valentina Rojas" maxlength="120">
                    </div>
                    <button class="button primary" type="submit">Buscar</button>
                    <?php if ($search !== ''): ?><a class="button secondary" href="<?= e(url('informe')) ?>">Limpiar</a><?php endif ?>
                </form>
                <?php if ($rows): ?>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Estudiante</th><th>Documento</th><th>Grado</th><th>Proyecto</th><th>Horas</th><th>Acción</th></tr></thead>
                            <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><strong><?= e($row['nombre']) ?></strong></td><td><?= e($row['documento']) ?></td>
                                    <td><?= e($row['grado']) ?>°</td><td><?= e($row['proyecto']) ?></td>
                                    <td class="nowrap"><?= e(hours($row['total'])) ?> / <?= META_HORAS ?> h</td>
                                    <td><a class="text-link" href="<?= e(url('informe', [
                                        'estudiante' => $row['id'], 'q' => $search,
                                    ])) ?>">Ver informe</a></td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?><p class="empty">No se encontraron estudiantes.</p><?php endif ?>
            </section>
        <?php endif ?>
    </main>
</div>
</body>
</html>
