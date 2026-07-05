<?php
header('Content-Type: application/json');
require_once 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$peer_id = isset($data['peer_id']) ? mysqli_real_escape_string($baglanti, $data['peer_id']) : '';

if (!$user_id || !$peer_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri']);
    exit;
}

// 1. Zombi Temizliği (Kirli kopmaları önlemek için)
mysqli_query($baglanti, "
    UPDATE online_users 
    SET status = 'waiting', matched_with = NULL, session_id = NULL 
    WHERE matched_with IN (
        SELECT user_id FROM (
            SELECT user_id FROM online_users WHERE last_ping < NOW() - INTERVAL 6 SECOND
        ) as subquery
    )
");

mysqli_query($baglanti, "
    DELETE FROM online_users 
    WHERE last_ping < NOW() - INTERVAL 6 SECOND
");

// 2. Kullanıcı tablodaysa güncelle, yoksa ekle
$result = mysqli_query($baglanti, "SELECT status, session_id, matched_with FROM online_users WHERE user_id = $user_id LIMIT 1");

if (mysqli_num_rows($result) === 0) {
    $stmt = mysqli_prepare($baglanti, "INSERT INTO online_users (user_id, peer_id, status, last_ping) VALUES (?, ?, 'waiting', NOW())");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $peer_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    $stmt = mysqli_prepare($baglanti, "UPDATE online_users SET peer_id = ?, last_ping = NOW() WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "si", $peer_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// 3. EĞER halihazırda biriyle eşleştiysek (Biri bizi kaptıysa) o bilgiyi al ve dön
$check_match = mysqli_query($baglanti, "
    SELECT ou.matched_with, ou.session_id, u.`user-name` AS partner_name, ou2.peer_id AS partner_peer_id
    FROM online_users ou
    LEFT JOIN online_users ou2 ON ou.matched_with = ou2.user_id
    LEFT JOIN users u ON ou.matched_with = u.`member-id`
    WHERE ou.user_id = $user_id AND ou.status = 'in_call'
");

if ($check_match && mysqli_num_rows($check_match) > 0) {
    $row = mysqli_fetch_assoc($check_match);
    echo json_encode([
        'status' => 'matched',
        'session_id' => $row['session_id'],
        'partner_id' => $row['matched_with'],
        'partner_username' => $row['partner_name'],
        'partner_peer_id' => $row['partner_peer_id']
    ]);
    exit;
}

// 4. ATOMİK EŞLEŞME (ÇAKILMAYI ÖNLEYEN KISIM)
$session_id = uniqid('session_', true);

// Veritabanına "Bekleyen birini bana kilitle (in_call yap)" emrini doğrudan veriyoruz
$claim_query = "
    UPDATE online_users 
    SET matched_with = $user_id, session_id = '$session_id', status = 'in_call' 
    WHERE status = 'waiting' AND user_id != $user_id 
    ORDER BY last_ping ASC 
    LIMIT 1
";
mysqli_query($baglanti, $claim_query);

// Eğer birini kapmayı başardıysak, Update işlemi 1 satırı etkilemiş demektir
if (mysqli_affected_rows($baglanti) > 0) {
    
    // Kimi kaptığımızı öğreniyoruz
    $get_partner = mysqli_query($baglanti, "
        SELECT ou.user_id, ou.peer_id, u.`user-name` AS partner_name
        FROM online_users ou
        INNER JOIN users u ON ou.user_id = u.`member-id`
        WHERE ou.session_id = '$session_id' AND ou.user_id != $user_id
        LIMIT 1
    ");
    
    if ($get_partner && mysqli_num_rows($get_partner) > 0) {
        $partner = mysqli_fetch_assoc($get_partner);
        
        // Kendi durumumuzu da güncelliyoruz
        mysqli_query($baglanti, "
            UPDATE online_users 
            SET matched_with = {$partner['user_id']}, session_id = '$session_id', status = 'in_call' 
            WHERE user_id = $user_id
        ");

        echo json_encode([
            'status' => 'matched',
            'session_id' => $session_id,
            'partner_id' => $partner['user_id'],
            'partner_username' => $partner['partner_name'],
            'partner_peer_id' => $partner['peer_id']
        ]);
        exit;
    }
}

// Kimseyi kapamadıysak beklemeye devam
echo json_encode(['status' => 'waiting']);
?>