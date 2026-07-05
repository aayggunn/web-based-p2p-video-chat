<?php
header('Content-Type: application/json');
require_once 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$session_id = isset($data['session_id']) ? mysqli_real_escape_string($baglanti, $data['session_id']) : null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri']);
    exit;
}

// 1. Düşen kişilerin (15 sn sinyal yok) asılı kalan partnerlerini serbest bırak
mysqli_query($baglanti, "
    UPDATE online_users 
    SET status = 'waiting', matched_with = NULL, session_id = NULL 
    WHERE matched_with IN (
        SELECT user_id FROM (
            SELECT user_id FROM online_users WHERE last_ping < NOW() - INTERVAL 15 SECOND
        ) as subquery
    )
");

// 2. Zombi kayıtları sil
mysqli_query($baglanti, "
    DELETE FROM online_users 
    WHERE last_ping < NOW() - INTERVAL 15 SECOND
");

// Kullanıcının last_ping bilgisini güncelle
if ($session_id) {
    $stmt = mysqli_prepare($baglanti, "UPDATE online_users SET last_ping = NOW() WHERE user_id = ? AND session_id = ?");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $session_id);
} else {
    $stmt = mysqli_prepare($baglanti, "UPDATE online_users SET last_ping = NOW() WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
}

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode(['status' => 'ok']);
?>