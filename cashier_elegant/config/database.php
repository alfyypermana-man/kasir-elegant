<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Nocturne Cashier');
// Kosong agar berjalan di root domain (termasuk hosting/Vercel yang mengarahkan document root ke project).
define('BASE_URL', '');

/**
 * Koneksi PostgreSQL Supabase.
 *
 * Bisa memakai satu variabel DATABASE_URL/SUPABASE_DB_URL, atau variabel terpisah:
 * DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_SSLMODE.
 * Password tidak disimpan di source code.
 */
$databaseUrl = getenv('postgresql://postgres:kasirsugi123@db.rjwqfvhufgkfuywxxvxc.supabase.co:5432/postgres') ?: getenv('SUPABASE_DB_URL');

if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    if ($parts === false || empty($parts['host'])) {
        die('DATABASE_URL tidak valid.');
    }

    $host = $parts['host'];
    $port = $parts['port'] ?? 5432;
    $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : 'postgres';
    $username = isset($parts['user']) ? urldecode($parts['user']) : 'postgres';
    $password = isset($parts['pass']) ? urldecode($parts['pass']) : '';
    $sslmode = 'require';
} else {
    $host = getenv('db.rjwqfvhufgkfuywxxvxc.supabase.co') ?: '';
    $port = getenv('5432') ?: '5432';
    $dbname = getenv('DB_NAME') ?: 'postgres';
    $username = getenv('DB_USER') ?: 'postgres';
    $password = getenv('kasirsugi123') ?: '';
    $sslmode = getenv('DB_SSLMODE') ?: 'require';
}

if ($host === '') {
    die('Konfigurasi database belum diatur. Set DATABASE_URL atau DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASSWORD.');
}

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function rupiah($number): string
{
    return 'Rp ' . number_format((float)$number, 0, ',', '.');
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function current_nav(string $segment): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    return strpos($script, '/' . trim($segment, '/') . '/') !== false ? 'active' : '';
}
