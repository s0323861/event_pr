<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('events.php');
verify_csrf();
$event = event_by_slug((string)($_POST['id'] ?? ''));
if (!$event || $event['status'] !== 'published') not_found();
$name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 80);
$email = mb_strtolower(mb_substr(trim((string)($_POST['email'] ?? '')), 0, 254));
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('error', 'お名前と正しいメールアドレスを入力してください。'); redirect(event_url($event)); }
if (($event['registration_deadline'] && $event['registration_deadline'] < date('Y-m-d')) || $event['event_date'] < date('Y-m-d')) { flash('error', 'このイベントの受付は終了しています。'); redirect(event_url($event)); }

$pdo = db();
try {
    $pdo->exec('BEGIN IMMEDIATE');
    $stmt = $pdo->prepare('SELECT capacity, (SELECT COUNT(*) FROM registrations WHERE event_id = events.id AND status = "active") AS attendee_count FROM events WHERE id = :id AND status = "published"');
    $stmt->execute(['id' => $event['id']]); $fresh = $stmt->fetch();
    if (!$fresh || ($fresh['capacity'] !== null && (int)$fresh['attendee_count'] >= (int)$fresh['capacity'])) throw new DomainException('定員に達したため、お申し込みできませんでした。');
    $token = bin2hex(random_bytes(24));
    $insert = $pdo->prepare('INSERT INTO registrations (event_id,name,email,cancel_token_hash,created_at) VALUES (:event_id,:name,:email,:token,:created_at)');
    $insert->execute(['event_id'=>$event['id'],'name'=>$name,'email'=>$email,'token'=>hash('sha256',$token),'created_at'=>date(DATE_ATOM)]);
    $pdo->commit();
    $_SESSION['registration_success'] = ['event_slug'=>$event['slug'],'name'=>$name,'token'=>$token];
    redirect('registered.php?id=' . rawurlencode($event['slug']));
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ((string)$e->getCode() === '23000') { flash('error', 'このメールアドレスでは既に申し込み済みです。'); redirect(event_url($event)); }
    throw $e;
} catch (DomainException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash('error', $e->getMessage()); redirect(event_url($event));
}
