<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config.php';
date_default_timezone_set($config['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['session_name']);
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function db(): PDO
{
    static $pdo;
    global $config;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dir = dirname($config['db_path']);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('保存用フォルダを作成できません。');
    }
    $pdo = new PDO('sqlite:' . $config['db_path'], null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec('PRAGMA journal_mode = WAL');
    migrate($pdo);
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS events (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  slug TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  description TEXT NOT NULL,
  event_date TEXT NOT NULL,
  start_time TEXT NOT NULL DEFAULT '',
  format TEXT NOT NULL DEFAULT 'onsite',
  prefecture TEXT NOT NULL DEFAULT '',
  venue TEXT NOT NULL DEFAULT '',
  capacity INTEGER NULL,
  registration_deadline TEXT NULL,
  admin_password_hash TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'published',
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL
)
SQL);
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS registrations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  event_id INTEGER NOT NULL,
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  cancel_token_hash TEXT NOT NULL UNIQUE,
  status TEXT NOT NULL DEFAULT 'active',
  created_at TEXT NOT NULL,
  cancelled_at TEXT NULL,
  FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE
)
SQL);
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS registrations_active_email ON registrations(event_id, email) WHERE status = "active"');
    $pdo->exec('CREATE INDEX IF NOT EXISTS events_date_status ON events(event_date, status)');
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('ページの有効期限が切れました。戻ってもう一度お試しください。');
    }
}

function redirect(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = compact('type', 'message');
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function old(string $key, string $default = ''): string
{
    return h((string)($_SESSION['old'][$key] ?? $default));
}

function remember_old(): void
{
    $_SESSION['old'] = array_map(static fn($v) => is_string($v) ? mb_substr(trim($v), 0, 5000) : '', $_POST);
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function random_slug(): string
{
    return rtrim(strtr(base64_encode(random_bytes(9)), '+/', '-_'), '=');
}

function event_by_slug(string $slug): ?array
{
    if (!preg_match('/^[A-Za-z0-9_-]{12}$/', $slug)) {
        return null;
    }
    $stmt = db()->prepare('SELECT *, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = events.id AND r.status = "active") AS attendee_count FROM events WHERE slug = :slug');
    $stmt->execute(['slug' => $slug]);
    return $stmt->fetch() ?: null;
}

function event_url(array $event): string
{
    return 'event.php?id=' . rawurlencode($event['slug']);
}

function is_admin(array $event): bool
{
    return !empty($_SESSION['admin_events'][(string)$event['id']]);
}

function render_header(string $title, string $description = ''): void
{
    global $config;
    $flash = pull_flash();
    ?><!doctype html>
<html lang="ja" data-theme="cyberpunk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="color-scheme" content="light">
  <meta name="description" content="<?= h($description ?: '登録不要でかんたんにイベントを告知・管理できるWebサービス') ?>">
  <title><?= h($title) ?> | <?= h($config['app_name']) ?></title>
  <link rel="stylesheet" href="assets/app.css">
  <script defer src="assets/app.js"></script>
</head>
<body class="min-h-screen bg-base-200 text-base-content">
<div class="scanlines" aria-hidden="true"></div>
<header class="navbar sticky top-0 z-40 border-b-2 border-primary bg-base-100/95 px-4 shadow-[0_4px_0_#ff7598] backdrop-blur">
  <div class="mx-auto flex w-full max-w-6xl">
    <div class="flex-1"><a class="brand text-xl font-black tracking-wider" href="index.php"><span aria-hidden="true">📣</span> 告知くん</a></div>
    <nav class="flex-none" aria-label="メインメニュー">
      <a class="btn btn-ghost btn-sm" href="events.php">イベント一覧</a>
      <a class="btn btn-primary btn-sm shadow-[3px_3px_0_#111]" href="index.php#new-event">イベントを作る</a>
    </nav>
  </div>
</header>
<?php if ($flash): ?>
  <div class="toast toast-top toast-center z-50"><div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success' ?> shadow-lg"><span><?= h($flash['message']) ?></span></div></div>
<?php endif; ?>
<main>
<?php
}

function render_footer(): void
{
    ?><footer class="mt-16 border-t-2 border-secondary bg-neutral px-4 py-10 text-neutral-content">
  <div class="mx-auto flex max-w-6xl flex-col justify-between gap-3 sm:flex-row">
    <p class="font-bold">📣 告知くん</p>
    <p class="text-sm opacity-80">無料・登録不要・使い捨て型のイベント告知サービス</p>
  </div>
</footer>
</main>
</body>
</html><?php
}

function not_found(): never
{
    http_response_code(404);
    render_header('見つかりません');
    echo '<section class="mx-auto max-w-xl px-4 py-24 text-center"><div class="card border-2 border-base-content bg-base-100 shadow-[8px_8px_0_#ff7598]"><div class="card-body"><p class="text-6xl">404</p><h1 class="card-title justify-center text-2xl">イベントが見つかりません</h1><a class="btn btn-primary mt-4" href="events.php">イベント一覧へ</a></div></div></section>';
    render_footer();
    exit;
}
