<?php
declare(strict_types=1);

date_default_timezone_set('America/Bogota');

function db(): PDO
{
    $localFile = __DIR__ . '/local.php';
    $local = is_file($localFile) ? require $localFile : [];
    $setting = static function (string $key, string $default) use ($local): string {
        $environment = getenv($key);
        return $environment === false ? ($local[$key] ?? $default) : $environment;
    };

    $host = $setting('DB_HOST', '127.0.0.1');
    $port = $setting('DB_PORT', '3306');
    $name = $setting('DB_NAME', 'labor_social');
    return new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
        $setting('DB_USER', 'root'), $setting('DB_PASSWORD', ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
}

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function query(PDO $db, string $sql, array $params = []): PDOStatement
{
    $statement = $db->prepare($sql);
    $statement->execute($params);
    return $statement;
}

function url(string $page, array $params = []): string
{
    return '?' . http_build_query(['page' => $page] + $params);
}

function redirect(string $page, array $params = []): never
{
    header('Location: ' . url($page, $params), true, 303);
    exit;
}

function field(array $source, string $name): string
{
    $value = $source[$name] ?? '';
    return is_string($value) ? trim($value) : '';
}

function id(string $value): int
{
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
}

function hours(string|int|float|null $value): string
{
    return rtrim(rtrim(number_format((float) $value, 2, ',', ''), '0'), ',');
}

function dateLabel(string $date): string
{
    return date('d/m/Y', strtotime($date));
}

function csrfInput(): string
{
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
