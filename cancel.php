<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';
$event = event_by_slug((string)($_REQUEST['id'] ?? ''));
$token = (string)($_REQUEST['token'] ?? '');
if (!$event || !preg_match('/^[a-f0-9]{48}$/', $token)) not_found();
$hash = hash('sha256', $token);
$stmt = db()->prepare('SELECT * FROM registrations WHERE event_id = :event_id AND cancel_token_hash = :token AND status = "active"');
$stmt->execute(['event_id'=>$event['id'],'token'=>$hash]); $registration = $stmt->fetch();
if (!$registration) not_found();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $update = db()->prepare('UPDATE registrations SET status = "cancelled", cancelled_at = :cancelled_at WHERE id = :id AND status = "active"');
    $update->execute(['cancelled_at'=>date(DATE_ATOM),'id'=>$registration['id']]);
    flash('success', 'お申し込みを取り消しました。'); redirect(event_url($event));
}
render_header('申し込みの取り消し');
?>
<section class="mx-auto max-w-xl px-4 py-20"><div class="card border-2 border-base-content bg-base-100 shadow-[8px_8px_0_#ff7598]"><div class="card-body"><h1 class="card-title text-2xl">申し込みを取り消しますか？</h1><p><strong><?= h($registration['name']) ?>さん</strong>の「<?= h($event['name']) ?>」への申し込みを取り消します。</p><form method="post" class="card-actions mt-5"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= h($event['slug']) ?>"><input type="hidden" name="token" value="<?= h($token) ?>"><a class="btn btn-ghost" href="<?= h(event_url($event)) ?>">戻る</a><button class="btn btn-error" type="submit">取り消す</button></form></div></div></section>
<?php render_footer(); ?>
