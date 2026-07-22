<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';

$prefectures = ['北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県','茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県','山梨県','新潟県','富山県','石川県','福井県','長野県','岐阜県','静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県','徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県'];
render_header('イベントを、もっと気軽に', '登録不要ですぐにイベントページを作成できる、シンプルな告知・参加管理サービスです。');
?>
<section class="hero min-h-[520px] overflow-hidden border-b-2 border-base-content bg-primary px-4">
  <div class="hero-content mx-auto grid max-w-6xl gap-10 py-16 lg:grid-cols-[1.15fr_.85fr]">
    <div>
      <div class="badge badge-secondary badge-lg mb-5 rotate-[-2deg] border-2 border-base-content font-black shadow-[3px_3px_0_#111]">NO SIGN-UP. JUST LAUNCH.</div>
      <h1 class="max-w-3xl text-5xl font-black leading-[1.05] tracking-tight sm:text-7xl">集まるきっかけを、<br><span class="text-secondary text-stroke">最速でつくろう。</span></h1>
      <p class="mt-6 max-w-xl text-lg font-bold leading-8">告知くんは、登録なしでイベントページを作り、参加者を管理できる無料サービスです。</p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a class="btn btn-secondary btn-lg border-2 border-base-content shadow-[5px_5px_0_#111]" href="#new-event">イベントを作る <span aria-hidden="true">→</span></a>
        <a class="btn btn-outline btn-lg border-2 bg-base-100 shadow-[5px_5px_0_#111]" href="events.php">募集中を見る</a>
      </div>
    </div>
    <div class="relative mx-auto w-full max-w-md" aria-hidden="true">
      <div class="absolute -left-5 -top-5 h-full w-full border-2 border-base-content bg-secondary"></div>
      <div class="relative border-2 border-base-content bg-neutral p-5 text-neutral-content shadow-[10px_10px_0_#111]">
        <div class="mb-5 flex items-center gap-2 border-b border-neutral-content/30 pb-3"><span class="h-3 w-3 rounded-full bg-error"></span><span class="h-3 w-3 rounded-full bg-warning"></span><span class="h-3 w-3 rounded-full bg-success"></span><span class="ml-auto font-mono text-xs">KOKUCHI.EXE</span></div>
        <div class="space-y-4 font-mono"><p class="text-success">&gt; create_event</p><p class="text-2xl font-black text-primary">PHP NIGHT #12</p><p>2026.08.08 / TOKYO</p><div class="h-2 bg-primary"></div><p class="text-secondary">20 SEATS AVAILABLE</p><p class="animate-pulse">█</p></div>
      </div>
    </div>
  </div>
</section>

<section id="new-event" class="scroll-mt-20 px-4 py-16">
  <div class="mx-auto max-w-4xl">
    <div class="mb-8 text-center"><p class="font-mono font-bold text-secondary">// CREATE NEW EVENT</p><h2 class="mt-2 text-3xl font-black sm:text-4xl">3ステップで公開できます</h2></div>
    <ul class="steps mb-8 w-full font-bold">
      <li class="step" data-step-dot>基本情報</li><li class="step" data-step-dot>イベント詳細</li><li class="step" data-step-dot>管理設定</li>
    </ul>
    <form action="create.php" method="post" data-event-form class="card border-2 border-base-content bg-base-100 shadow-[10px_10px_0_#ff7598]">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <div class="card-body p-6 sm:p-10">
        <section data-step-panel>
          <h3 class="card-title mb-6 text-2xl"><span class="badge badge-primary badge-lg">01</span> 基本情報</h3>
          <div class="grid gap-5">
            <label class="form-control"><span class="label-text mb-2 font-bold">イベント名 <span class="text-error">必須</span></span><input class="input input-bordered input-lg w-full" type="text" name="name" maxlength="80" required placeholder="例：つくばPHP勉強会 #12" value="<?= old('name') ?>"></label>
            <div class="grid gap-5 sm:grid-cols-2">
              <label class="form-control"><span class="label-text mb-2 font-bold">開催日 <span class="text-error">必須</span></span><input class="input input-bordered w-full" type="date" name="event_date" min="<?= date('Y-m-d') ?>" required value="<?= old('event_date') ?>"></label>
              <label class="form-control"><span class="label-text mb-2 font-bold">開始時刻</span><input class="input input-bordered w-full" type="time" name="start_time" value="<?= old('start_time') ?>"></label>
            </div>
            <label class="form-control"><span class="label-text mb-2 font-bold">開催形式</span><select id="format" class="select select-bordered w-full" name="format"><option value="onsite">会場開催</option><option value="online">オンライン</option><option value="hybrid">会場＋オンライン</option></select></label>
            <div id="location-fields" class="grid gap-5 sm:grid-cols-2">
              <label class="form-control"><span class="label-text mb-2 font-bold">都道府県</span><select class="select select-bordered w-full" name="prefecture"><option value="">選択してください</option><?php foreach ($prefectures as $pref): ?><option value="<?= h($pref) ?>"><?= h($pref) ?></option><?php endforeach; ?></select></label>
              <label class="form-control"><span class="label-text mb-2 font-bold">会場名・URL</span><input class="input input-bordered w-full" type="text" name="venue" maxlength="200" placeholder="例：市民ホール / 配信URL" value="<?= old('venue') ?>"></label>
            </div>
          </div>
          <div class="card-actions mt-8 justify-end"><button class="btn btn-primary" type="button" data-next>詳細へ →</button></div>
        </section>
        <section data-step-panel hidden>
          <h3 class="card-title mb-6 text-2xl"><span class="badge badge-secondary badge-lg">02</span> イベント詳細</h3>
          <div class="grid gap-5">
            <label class="form-control"><span class="label-text mb-2 font-bold">イベントの説明 <span class="text-error">必須</span></span><textarea class="textarea textarea-bordered min-h-56 text-base leading-7" name="description" maxlength="5000" required placeholder="内容、対象者、持ち物などを入力してください。プレーンテキストなので安全に表示されます。"><?= old('description') ?></textarea><span class="label-text-alt mt-2">改行はそのまま公開ページに反映されます（最大5,000文字）</span></label>
            <div class="grid gap-5 sm:grid-cols-2">
              <label class="form-control"><span class="label-text mb-2 font-bold">定員</span><div class="join"><input class="input input-bordered join-item w-full" type="number" name="capacity" min="1" max="100000" placeholder="空欄なら無制限" value="<?= old('capacity') ?>"><span class="btn join-item pointer-events-none">名</span></div></label>
              <label class="form-control"><span class="label-text mb-2 font-bold">申込締切</span><input class="input input-bordered w-full" type="date" name="registration_deadline" value="<?= old('registration_deadline') ?>"></label>
            </div>
          </div>
          <div class="card-actions mt-8 justify-between"><button class="btn btn-ghost" type="button" data-prev>← 戻る</button><button class="btn btn-secondary" type="button" data-next>管理設定へ →</button></div>
        </section>
        <section data-step-panel hidden>
          <h3 class="card-title mb-6 text-2xl"><span class="badge badge-accent badge-lg">03</span> 管理設定</h3>
          <div class="alert mb-6 border-2 border-info"><span>🔐 このパスワードは、参加者一覧・編集・削除に使います。安全に暗号化して保存されます。</span></div>
          <label class="form-control"><span class="label-text mb-2 font-bold">主催者パスワード <span class="text-error">必須</span></span><input class="input input-bordered input-lg w-full" type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required placeholder="8文字以上"><span class="label-text-alt mt-2">英字・数字・記号を組み合わせることをおすすめします。</span></label>
          <label class="label mt-5 cursor-pointer justify-start gap-3"><input class="checkbox checkbox-primary" type="checkbox" required><span class="label-text">入力内容と利用上の注意を確認しました</span></label>
          <div class="card-actions mt-8 justify-between"><button class="btn btn-ghost" type="button" data-prev>← 戻る</button><button class="btn btn-primary btn-lg border-2 border-base-content shadow-[4px_4px_0_#111]" type="submit">イベントを公開する 🚀</button></div>
        </section>
      </div>
    </form>
    <p class="mt-5 text-center text-sm opacity-70">入力内容は送信するまで、この端末のブラウザに一時保存されます。</p>
  </div>
</section>

<section class="bg-neutral px-4 py-16 text-neutral-content">
  <div class="mx-auto grid max-w-6xl gap-6 sm:grid-cols-3">
    <?php foreach ([['⚡','登録不要','アカウント作成なしですぐ公開'],['🧑‍🤝‍🧑','参加管理','定員・申込・取消を一か所に'],['🔐','安全設計','パスワード暗号化と不正操作対策']] as $feature): ?>
      <article class="border border-neutral-content/30 p-6"><div class="text-4xl"><?= $feature[0] ?></div><h3 class="mt-4 text-xl font-black text-primary"><?= $feature[1] ?></h3><p class="mt-2 opacity-80"><?= $feature[2] ?></p></article>
    <?php endforeach; ?>
  </div>
</section>
<?php render_footer(); ?>
