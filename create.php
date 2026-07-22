<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php#new-event');
verify_csrf();
remember_old();

$name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 80);
$description = mb_substr(trim((string)($_POST['description'] ?? '')), 0, 5000);
$eventDate = (string)($_POST['event_date'] ?? '');
$startTime = (string)($_POST['start_time'] ?? '');
$format = (string)($_POST['format'] ?? 'onsite');
$prefecture = mb_substr(trim((string)($_POST['prefecture'] ?? '')), 0, 10);
$venue = mb_substr(trim((string)($_POST['venue'] ?? '')), 0, 200);
$deadline = (string)($_POST['registration_deadline'] ?? '');
$capacityRaw = trim((string)($_POST['capacity'] ?? ''));
$password = (string)($_POST['password'] ?? '');

$validDate = static fn(string $date): bool => $date !== '' && DateTimeImmutable::createFromFormat('!Y-m-d', $date)?->format('Y-m-d') === $date;
if ($name === '' || $description === '' || !$validDate($eventDate) || $eventDate < date('Y-m-d') || !in_array($format, ['onsite','online','hybrid'], true) || strlen($password) < 8) {
    flash('error', '入力内容を確認してください。必須項目が不足している可能性があります。');
    redirect('index.php#new-event');
}
if ($startTime !== '' && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $startTime)) $startTime = '';
if ($deadline !== '' && (!$validDate($deadline) || $deadline > $eventDate)) {
    flash('error', '申込締切は開催日以前の日付を指定してください。');
    redirect('index.php#new-event');
}
$capacity = $capacityRaw === '' ? null : filter_var($capacityRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
if ($capacityRaw !== '' && $capacity === false) {
    flash('error', '定員は1〜100,000名で入力してください。');
    redirect('index.php#new-event');
}

$now = date(DATE_ATOM);
for ($attempt = 0; $attempt < 3; $attempt++) {
    try {
        $slug = random_slug();
        $stmt = db()->prepare('INSERT INTO events (slug,name,description,event_date,start_time,format,prefecture,venue,capacity,registration_deadline,admin_password_hash,created_at,updated_at) VALUES (:slug,:name,:description,:event_date,:start_time,:format,:prefecture,:venue,:capacity,:deadline,:password_hash,:created_at,:updated_at)');
        $stmt->execute([
            'slug'=>$slug,'name'=>$name,'description'=>$description,'event_date'=>$eventDate,'start_time'=>$startTime,
            'format'=>$format,'prefecture'=>$format === 'online' ? '' : $prefecture,'venue'=>$venue,
            'capacity'=>$capacity,'deadline'=>$deadline ?: null,'password_hash'=>password_hash($password, PASSWORD_DEFAULT),
            'created_at'=>$now,'updated_at'=>$now,
        ]);
        clear_old();
        $_SESSION['created_event'] = $slug;
        redirect('created.php?id=' . rawurlencode($slug));
    } catch (PDOException $e) {
        if ((string)$e->getCode() !== '23000') throw $e;
    }
}
throw new RuntimeException('イベントIDを生成できませんでした。');
