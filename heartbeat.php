<?php
// heartbeat.php: istemci 10 saniyede bir çağırır, kullanıcıyı "çevrimiçi" tutar
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error']);
    exit;
}

session_start();
if (!isset($_SESSION['member-id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error']);
    exit;
}
$user_id = (int)$_SESSION['member-id'];
session_write_close();

try {
    require_once 'connection.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $s = $baglanti->prepare('UPDATE online_users SET last_ping = NOW() WHERE user_id = ?');
    $s->bind_param('i', $user_id);
    $s->execute();
    $s->close();

    // matchmaker.php ile aynı süre (30 sn); aksi halde aktif kullanıcılar bile silinebilir
    $baglanti->query('DELETE FROM online_users WHERE last_ping < NOW() - INTERVAL 30 SECOND');

    echo json_encode(['status' => 'updated']);
} catch (Throwable $e) {
    error_log('heartbeat: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error']);
}