<?php
header('Content-Type: application/json');
require_once 'connection.php';

// İstek yöntemi POST ise JSON'dan, GET ise parametrelerden al
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $session_id = isset($data['session_id']) ? mysqli_real_escape_string($baglanti, $data['session_id']) : null;
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $session_id = null;
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Yöntem desteklenmiyor']);
    exit;
}

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz kullanıcı']);
    exit;
}

// Kullanıcıyı tamamen sil
$stmt = mysqli_prepare($baglanti, "DELETE FROM online_users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Eğer session_id varsa partneri de temizle
if ($session_id) {
    $result = mysqli_query($baglanti, "SELECT user_id FROM online_users WHERE session_id = '$session_id' AND user_id != $user_id LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $partner = mysqli_fetch_assoc($result);
        $partner_id = (int)$partner['user_id'];

        $stmt2 = mysqli_prepare($baglanti, "DELETE FROM online_users WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt2, "i", $partner_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }
}

echo json_encode(['status' => 'ok']);
