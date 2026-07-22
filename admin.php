<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';
$event = event_by_slug((string)($_REQUEST['id'] ?? ''));
if (!$event) not_found();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? 'login');
    if ($action === 'login') {
        if (password_verify((string)($_POST['password'] ?? ''), $event['admin_password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_events'][(string)$event['id']] = true;
            flash('success', '主催者としてログインしました。');
        } else {
            flash('error', 'パスワードが違います。');
        }
        redirect('admin.php?id=' . rawurlencode($event['slug']));
    }
    if (!is_admin($event)) { flash('error', 'もう一度ログインしてください。'); redirect('admin.php?id=' . rawurlencode($event['slug'])); }
    if ($action === 'logout') {
        unset($_SESSION['admin_events'][(string)$event['id']]);
        redirect(event_url($event));
    }
    if ($action === 'update') {
        $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 80);
        $description = mb_substr(trim((string)($_POST['description'] ?? '')), 0, 5000);
        $date = (string)($_POST['event_date'] ?? '');
        $capacityRaw = trim((string)($_POST['capacity'] ?? ''));
        $capacity = $capacityRaw === '' ? null : filter_var($capacityRaw, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>100000]]);
        if ($name === '' || $description === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $capacity === false || ($capacity !== null && (int)$capacity < (int)$event['attendee_count'])) {
            flash('error', '入力内容を確認してください。定員は現在の参加者数以上にしてください。');
        } else {
            $stmt = db()->prepare('UPDATE events SET name=:name,description=:description,event_date=:event_date,start_time=:start_time,venue=:venue,capacity=:capacity,registration_deadline=:deadline,updated_at=:updated_at WHERE id=:id');
            $stmt->execute(['name'=>$name,'description'=>$description,'event_date'=>$date,'start_time'=>(string)($_POST['start_time']??''),'venue'=>mb_substr(trim((string)($_POST['venue']??'')),0,200),'capacity'=>$capacity,'deadline'=>trim((string)($_POST['registration_deadline']??'')) ?: null,'updated_at'=>date(DATE_ATOM),'id'=>$event['id']]);
            flash('success', 'イベント情報を更新しました。');
        }
        redirect('admin.php?id=' . rawurlencode($event['slug']));
    }
    if ($action === 'delete') {
        $stmt = db()->prepare('UPDATE events SET status="deleted",updated_at=:updated_at WHERE id=:id');
        $stmt->execute(['updated_at'=>date(DATE_ATOM),'id'=>$event['id']]);
        unset($_SESSION['admin_events'][(string)$event['id']]);
        flash('success', 'イベントを削除しました。'); redirect('events.php');
    }
}

render_header('主催者メニュー');
if (!is_admin($event)):
?>
<section class="mx-auto max-w-lg px-4 py-20"><div class="card border-2 border-base-content bg-base-100 shadow-[8px_8px_0_#ff7598]"><form class="card-body" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= h($event['slug']) ?>"><input type="hidden" name="action" value="login"><p class="font-mono font-bold text-secondary">// ADMIN ACCESS</p><h1 class="card-title text-2xl">主催者ログイン</h1><p class="opacity-70"><?= h($event['name']) ?></p><label class="form-control mt-4"><span class="label-text mb-2 font-bold">作成時のパスワード</span><input class="input input-bordered input-lg" type="password" name="password" autocomplete="current-password" required autofocus></label><button class="btn btn-primary btn-lg mt-4" type="submit">ログイン</button><a class="btn btn-ghost" href="<?= h(event_url($event)) ?>">イベントページへ戻る</a></form></div></section>
<?php else:
$stmt = db()->prepare('SELECT name,email,status,created_at,cancelled_at FROM registrations WHERE event_id=:id ORDER BY created_at'); $stmt->execute(['id'=>$event['id']]); $registrations=$stmt->fetchAll();
?>
<section class="border-b-2 border-base-content bg-secondary px-4 py-10"><div class="mx-auto flex max-w-6xl flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="font-mono font-bold">// EVENT CONTROL</p><h1 class="mt-2 text-3xl font-black"><?= h($event['name']) ?></h1></div><div class="flex gap-2"><a class="btn btn-outline bg-base-100" href="<?= h(event_url($event)) ?>">公開ページ</a><form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= h($event['slug']) ?>"><input type="hidden" name="action" value="logout"><button class="btn btn-ghost" type="submit">ログアウト</button></form></div></div></section>
<section class="mx-auto max-w-6xl px-4 py-10">
  <div class="stats mb-8 w-full border-2 border-base-content bg-base-100 shadow-[5px_5px_0_#111]"><div class="stat"><div class="stat-title">参加者</div><div class="stat-value"><?= (int)$event['attendee_count'] ?></div></div><div class="stat"><div class="stat-title">定員</div><div class="stat-value"><?= $event['capacity'] === null ? '∞' : (int)$event['capacity'] ?></div></div><div class="stat"><div class="stat-title">開催日</div><div class="stat-value text-2xl"><?= h(date('m.d', strtotime($event['event_date']))) ?></div></div></div>
  <div class="grid gap-8 lg:grid-cols-2">
    <div class="card border-2 border-base-content bg-base-100"><form class="card-body" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= h($event['slug']) ?>"><input type="hidden" name="action" value="update"><h2 class="card-title">イベント情報を編集</h2>
      <label class="form-control"><span class="label-text mb-1 font-bold">イベント名</span><input class="input input-bordered" name="name" maxlength="80" required value="<?= h($event['name']) ?>"></label>
      <div class="grid gap-3 sm:grid-cols-2"><label class="form-control"><span class="label-text mb-1 font-bold">開催日</span><input class="input input-bordered" type="date" name="event_date" required value="<?= h($event['event_date']) ?>"></label><label class="form-control"><span class="label-text mb-1 font-bold">開始時刻</span><input class="input input-bordered" type="time" name="start_time" value="<?= h($event['start_time']) ?>"></label></div>
      <label class="form-control"><span class="label-text mb-1 font-bold">会場・URL</span><input class="input input-bordered" name="venue" maxlength="200" value="<?= h($event['venue']) ?>"></label>
      <div class="grid gap-3 sm:grid-cols-2"><label class="form-control"><span class="label-text mb-1 font-bold">定員</span><input class="input input-bordered" type="number" name="capacity" min="<?= max(1,(int)$event['attendee_count']) ?>" value="<?= h($event['capacity'] === null ? '' : (string)$event['capacity']) ?>"></label><label class="form-control"><span class="label-text mb-1 font-bold">申込締切</span><input class="input input-bordered" type="date" name="registration_deadline" value="<?= h($event['registration_deadline']) ?>"></label></div>
      <label class="form-control"><span class="label-text mb-1 font-bold">説明</span><textarea class="textarea textarea-bordered min-h-48" name="description" maxlength="5000" required><?= h($event['description']) ?></textarea></label><button class="btn btn-primary mt-3" type="submit">変更を保存</button>
    </form></div>
    <div><div class="card border-2 border-base-content bg-base-100"><div class="card-body"><div class="flex justify-between"><h2 class="card-title">参加者一覧</h2><span class="badge badge-primary badge-lg"><?= (int)$event['attendee_count'] ?>名</span></div><div class="overflow-x-auto"><table class="table"><thead><tr><th>名前</th><th>メール</th><th>状態</th></tr></thead><tbody><?php if (!$registrations): ?><tr><td colspan="3" class="py-8 text-center">まだ参加者はいません</td></tr><?php endif; ?><?php foreach ($registrations as $r): ?><tr class="<?= $r['status']==='cancelled'?'opacity-50':'' ?>"><td><?= h($r['name']) ?></td><td><a class="link" href="mailto:<?= h($r['email']) ?>"><?= h($r['email']) ?></a></td><td><?= $r['status']==='active'?'<span class="badge badge-success">参加</span>':'<span class="badge">取消</span>' ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
      <div class="collapse collapse-arrow mt-6 border-2 border-error bg-base-100"><input type="checkbox"><div class="collapse-title font-bold text-error">危険な操作</div><div class="collapse-content"><p class="mb-4 text-sm">イベントとすべての参加情報を非公開にします。この操作は元に戻せません。</p><form method="post" onsubmit="return confirm('本当に削除しますか？')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= h($event['slug']) ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-error" type="submit">イベントを削除</button></form></div></div>
    </div>
  </div>
</section>
<?php endif; render_footer(); ?>
