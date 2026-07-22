<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';
$event = event_by_slug((string)($_GET['id'] ?? ''));
if (!$event || $event['status'] !== 'published') not_found();
$count = (int)$event['attendee_count'];
$remaining = $event['capacity'] === null ? null : max(0, (int)$event['capacity'] - $count);
$closed = ($event['registration_deadline'] && $event['registration_deadline'] < date('Y-m-d')) || $event['event_date'] < date('Y-m-d') || $remaining === 0;
$location = $event['format'] === 'online' ? 'オンライン' : trim($event['prefecture'] . ' ' . $event['venue']);
render_header($event['name'], mb_substr($event['description'], 0, 140));
?>
<section class="border-b-2 border-base-content bg-primary px-4 py-12"><div class="mx-auto max-w-5xl"><div class="mb-5 flex flex-wrap gap-2"><span class="badge badge-secondary badge-lg border-2 border-base-content font-bold"><?= h(['onsite'=>'会場開催','online'=>'オンライン','hybrid'=>'会場＋オンライン'][$event['format']] ?? '') ?></span><?php if ($closed): ?><span class="badge badge-error badge-lg border-2 border-base-content font-bold">受付終了</span><?php else: ?><span class="badge badge-success badge-lg border-2 border-base-content font-bold">参加受付中</span><?php endif; ?></div><h1 class="max-w-4xl text-4xl font-black leading-tight sm:text-6xl"><?= h($event['name']) ?></h1></div></section>
<section class="mx-auto grid max-w-5xl gap-8 px-4 py-10 lg:grid-cols-[1fr_340px]">
  <div>
    <div class="grid gap-3 sm:grid-cols-3">
      <div class="stat border-2 border-base-content bg-base-100"><div class="stat-title">開催日</div><div class="stat-value text-2xl"><?= h(date('Y.m.d', strtotime($event['event_date']))) ?></div><div class="stat-desc"><?= h($event['start_time'] ? $event['start_time'] . ' 開始' : '時刻未定') ?></div></div>
      <div class="stat border-2 border-base-content bg-base-100"><div class="stat-title">会場</div><div class="stat-value truncate text-xl"><?= h($location ?: '未定') ?></div></div>
      <div class="stat border-2 border-base-content bg-base-100"><div class="stat-title">申込状況</div><div class="stat-value text-2xl"><?= $count ?><span class="text-sm">名</span></div><div class="stat-desc"><?= $remaining === null ? '定員なし' : '残り ' . $remaining . ' 席' ?></div></div>
    </div>
    <article class="mt-8 border-l-4 border-secondary bg-base-100 p-6 shadow-sm sm:p-8"><h2 class="mb-5 text-2xl font-black">イベントについて</h2><div class="whitespace-pre-wrap break-words leading-8"><?= h($event['description']) ?></div></article>
  </div>
  <aside>
    <div class="card sticky top-24 border-2 border-base-content bg-base-100 shadow-[8px_8px_0_#ff7598]"><div class="card-body">
      <?php if ($closed): ?><h2 class="card-title">受付は終了しました</h2><p>定員到達、締切経過、または開催済みのイベントです。</p>
      <?php else: ?><h2 class="card-title text-2xl">参加を申し込む</h2><p class="text-sm opacity-70">お名前とメールアドレスだけで申し込めます。</p>
      <form action="register.php" method="post" class="mt-3 grid gap-4"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= h($event['slug']) ?>">
        <label class="form-control"><span class="label-text mb-1 font-bold">お名前</span><input class="input input-bordered" name="name" maxlength="80" autocomplete="name" required></label>
        <label class="form-control"><span class="label-text mb-1 font-bold">メールアドレス</span><input class="input input-bordered" type="email" name="email" maxlength="254" autocomplete="email" required></label>
        <button class="btn btn-primary btn-lg border-2 border-base-content shadow-[4px_4px_0_#111]" type="submit">このイベントに参加する</button>
      </form><?php endif; ?>
      <div class="divider"></div><a class="btn btn-ghost btn-sm" href="admin.php?id=<?= h($event['slug']) ?>">主催者メニュー</a>
    </div></div>
  </aside>
</section>
<?php render_footer(); ?>
