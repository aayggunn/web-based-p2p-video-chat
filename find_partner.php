<?php
header('Content-Type: application/json');
require_once 'connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$peer_id = $data['peer_id'];
$user_id = $data['user_id'];
$gender = $data['gender'];
$preference = $data['preference'];

try {
    // Cinsiyet filtresine göre eşleşme sorgusu
    $query = "SELECT s.session_id, s.peer_id, u.gender 
              FROM sessions s
              JOIN `kayeka-member` u ON s.user_id = u.`member-id`
              WHERE s.status = 'waiting' 
              AND s.user_id != ?";
    
    // Cinsiyet filtresi ekle
    if ($preference === 'same') {
        $query .= " AND u.gender = ?";
        $params = [$user_id, $gender];
    } elseif ($preference === 'e' || $preference === 'k') {
        $query .= " AND u.gender = ?";
        $params = [$user_id, $preference];
    } else {
        $params = [$user_id];
    }
    
    $query .= " ORDER BY s.created_at ASC LIMIT 1";
    
    $stmt = $baglanti->prepare($query);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Eşleşme bulundu
        $waiting_session = $result->fetch_assoc();
        $session_id = uniqid('session_', true);
        
        // Mevcut oturumu güncelle
        $update_stmt = $baglanti->prepare("UPDATE sessions SET status = 'matched', matched_with = ? WHERE session_id = ?");
        $update_stmt->bind_param("is", $user_id, $waiting_session['session_id']);
        $update_stmt->execute();
        
        // Yeni oturum oluştur
        $insert_stmt = $baglanti->prepare("INSERT INTO sessions (session_id, user_id, peer_id, status, matched_with) VALUES (?, ?, ?, 'matched', ?)");
        $insert_stmt->bind_param("siss", $session_id, $user_id, $peer_id, $waiting_session['user_id']);
        $insert_stmt->execute();
        
        echo json_encode([
            'status' => 'matched',
            'session_id' => $session_id,
            'partner_peer_id' => $waiting_session['peer_id']
        ]);
    } else {
        // Bekleyen oturum yok, yeni oturum oluştur
        $session_id = uniqid('session_', true);
        
        $insert_stmt = $baglanti->prepare("INSERT INTO sessions (session_id, user_id, peer_id, status) VALUES (?, ?, ?, 'waiting')");
        $insert_stmt->bind_param("sis", $session_id, $user_id, $peer_id);
        $insert_stmt->execute();
        
        echo json_encode([
            'status' => 'waiting',
            'session_id' => $session_id
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>