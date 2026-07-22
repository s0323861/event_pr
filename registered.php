<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';
$event = event_by_slug((string)($_GET['id'] ?? ''));
$success = $_SESSION['registration_success'] ?? null;
if (!$event || !is_array($success) || !hash_equals($event['slug'], (string)$success['event_slug'])) not_found();
unset($_SESSION['registration_success']);
$cancelUrl = 'cancel.php?id=' . rawurlencode($event['slug']) . '&token=' . rawurlencode((string)$success['token']);
render_header('お申し込み完了');
?>
<section class="mx-auto max-w-2xl px-4 py-20 text-center"><div class="text-7xl">🎉</div><h1 class="mt-5 text-4xl font-black">お申し込み完了！</h1><p class="mt-4 text-lg"><strong><?= h($success['name']) ?>さん</strong>、ご参加ありがとうございます。</p>
<div class="card mt-8 border-2 border-base-content bg-base-100 text-left shadow-[8px_8px_0_#ff7598]"><div class="card-body"><h2 class="card-title"><?= h($event['name']) ?></h2><p>📅 <?= h(date('Y年n月j日', strtotime($event['event_date']))) ?> <?= h($event['start_time']) ?></p><div class="alert alert-warning mt-3"><span>都合が悪くなった場合は、下の専用リンクから取り消せます。この画面を保存してください。</span></div><a class="btn btn-outline" href="<?= h($cancelUrl) ?>">申し込みを取り消す</a><a class="btn btn-primary" href="<?= h(event_url($event)) ?>">イベントページへ戻る</a></div></div></section>
<?php render_footer(); ?>
