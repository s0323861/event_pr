<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';
$q = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 80);
$format = (string)($_GET['format'] ?? '');
$params = ['today' => date('Y-m-d')];
$where = ['status = "published"', 'event_date >= :today'];
if ($q !== '') { $where[] = '(name LIKE :q OR description LIKE :q OR venue LIKE :q)'; $params['q'] = '%' . str_replace(['%','_'], ['\%','\_'], $q) . '%'; }
if (in_array($format, ['onsite','online','hybrid'], true)) { $where[] = 'format = :format'; $params['format'] = $format; }
$sql = 'SELECT *, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = events.id AND r.status = "active") AS attendee_count FROM events WHERE ' . implode(' AND ', $where) . ' ORDER BY event_date, start_time LIMIT 100';
$stmt = db()->prepare($sql); $stmt->execute($params); $events = $stmt->fetchAll();
render_header('募集中のイベント');
?>
<section class="border-b-2 border-base-content bg-secondary px-4 py-12"><div class="mx-auto max-w-6xl"><p class="font-mono font-bold">// FIND YOUR NEXT EVENT</p><h1 class="mt-2 text-4xl font-black sm:text-5xl">募集中のイベント</h1></div></section>
<section class="mx-auto max-w-6xl px-4 py-10">
  <form class="mb-8 grid gap-3 border-2 border-base-content bg-base-100 p-4 shadow-[5px_5px_0_#111] sm:grid-cols-[1fr_220px_auto]" method="get">
    <label><span class="sr-only">キーワード</span><input class="input input-bordered w-full" name="q" value="<?= h($q) ?>" placeholder="イベント名・会場から検索"></label>
    <label><span class="sr-only">開催形式</span><select class="select select-bordered w-full" name="format"><option value="">すべての開催形式</option><option value="onsite" <?= $format === 'onsite' ? 'selected' : '' ?>>会場開催</option><option value="online" <?= $format === 'online' ? 'selected' : '' ?>>オンライン</option><option value="hybrid" <?= $format === 'hybrid' ? 'selected' : '' ?>>会場＋オンライン</option></select></label>
    <button class="btn btn-primary" type="submit">検索</button>
  </form>
  <?php if (!$events): ?><div class="card border-2 border-dashed border-base-content bg-base-100"><div class="card-body items-center py-16 text-center"><div class="text-5xl">📭</div><h2 class="card-title">該当するイベントはありません</h2><a class="btn btn-primary mt-3" href="index.php#new-event">最初のイベントを作る</a></div></div><?php else: ?>
  <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($events as $event): $remaining = $event['capacity'] === null ? null : max(0, (int)$event['capacity'] - (int)$event['attendee_count']); ?>
      <article class="card border-2 border-base-content bg-base-100 shadow-[6px_6px_0_#111] transition hover:-translate-y-1 hover:shadow-[8px_8px_0_#ff7598]">
        <div class="card-body"><div class="flex items-start justify-between gap-3"><div class="badge badge-secondary border border-base-content font-bold"><?= h(['onsite'=>'会場','online'=>'オンライン','hybrid'=>'ハイブリッド'][$event['format']] ?? '') ?></div><time class="font-mono font-black" datetime="<?= h($event['event_date']) ?>"><?= h(date('m.d', strtotime($event['event_date']))) ?></time></div>
          <h2 class="card-title mt-2 line-clamp-2 text-xl"><?= h($event['name']) ?></h2><p class="min-h-12 text-sm opacity-70"><?= h(mb_substr($event['description'], 0, 70)) ?><?= mb_strlen($event['description']) > 70 ? '…' : '' ?></p>
          <div class="mt-2 flex justify-between text-sm"><span>📍 <?= h($event['format'] === 'online' ? 'オンライン' : ($event['prefecture'] ?: $event['venue'])) ?></span><span class="font-bold"><?= $remaining === null ? '定員なし' : '残り' . $remaining . '席' ?></span></div>
          <div class="card-actions mt-3"><a class="btn btn-primary w-full" href="<?= h(event_url($event)) ?>">詳しく見る →</a></div>
        </div>
      </article>
    <?php endforeach; ?>
  </div><?php endif; ?>
</section>
<?php render_footer(); ?>
