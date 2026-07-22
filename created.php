<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';
$slug = (string)($_GET['id'] ?? '');
$event = event_by_slug($slug);
if (!$event || !hash_equals((string)($_SESSION['created_event'] ?? ''), $slug)) not_found();
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
$publicUrl = $scheme . '://' . $host . $base . '/event.php?id=' . rawurlencode($slug);
$adminUrl = $scheme . '://' . $host . $base . '/admin.php?id=' . rawurlencode($slug);
render_header('公開しました');
?>
<section class="mx-auto max-w-3xl px-4 py-16 text-center">
  <div class="mb-8 text-7xl">🚀</div><p class="font-mono font-bold text-secondary">EVENT LAUNCHED</p><h1 class="mt-2 text-4xl font-black">イベントを公開しました！</h1>
  <div class="card mt-10 border-2 border-base-content bg-base-100 text-left shadow-[10px_10px_0_#ff7598]"><div class="card-body gap-6">
    <div><h2 class="font-black">参加者向けURL</h2><p class="mt-1 text-sm opacity-70">SNSやメールで共有してください。</p><div class="mt-3 flex flex-col gap-2 sm:flex-row"><input class="input input-bordered w-full font-mono text-sm" readonly value="<?= h($publicUrl) ?>"><button class="btn btn-secondary" type="button" data-copy="<?= h($publicUrl) ?>">URLをコピー</button></div></div>
    <div class="divider"></div>
    <div><h2 class="font-black">主催者用URL</h2><p class="mt-1 text-sm opacity-70">参加者一覧・編集・削除に使います。作成時のパスワードも必要です。</p><div class="mt-3 flex flex-col gap-2 sm:flex-row"><input class="input input-bordered w-full font-mono text-sm" readonly value="<?= h($adminUrl) ?>"><button class="btn btn-primary" type="button" data-copy="<?= h($adminUrl) ?>">URLをコピー</button></div></div>
    <div class="alert alert-warning"><span>⚠️ 告知くんはユーザー登録を行いません。主催者用URLとパスワードを必ず保存してください。</span></div>
    <div class="card-actions justify-center"><a class="btn btn-lg btn-primary" href="<?= h(event_url($event)) ?>">公開ページを見る →</a></div>
  </div></div>
</section>
<?php render_footer(); ?>
