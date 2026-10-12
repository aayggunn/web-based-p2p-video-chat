<?php
session_start();
// Hataları ekrana basmasını engelliyoruz ki JSON yapısı bozulmasın
ini_set('display_errors', '0'); 
header('Content-Type: application/json; charset=utf-8');

require_once 'connection.php'; // Veritabanı bağlantınız

$data = json_decode(file_get_contents('php://input'), true);
$id_token =$data['token'] ?? '';

if (empty($id_token)) {
    echo json_encode(['success' => false, 'message' => 'Token bulunamadı']);
    exit;
}

// -------------------------------------------------------------------
// KÜTÜPHANESİZ (COMPOSER OLMADAN) TOKEN DOĞRULAMA İŞLEMİ (cURL ile)
// -------------------------------------------------------------------
$verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$verify_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$payload = json_decode($response, true);

// İstemci Kimliği (Client ID) - Sizin kimliğiniz
$CLIENT_ID = '1022652530736-b1he3ojt2h6a49496kideou1dhf5coka.apps.googleusercontent.com';

// Eğer Google'dan gelen cevapta 'aud' (istemci) bizimkiyle eşleşiyorsa token geçerlidir
if ($payload && isset($payload['aud']) && $payload['aud'] ===$CLIENT_ID) {
    
    $google_id =$payload['sub'];
    $email =$payload['email'];
    $name =$payload['name']; // Google'dan gelen Ad Soyad
    
    // E-postanın @ işaretinden önceki kısmını "user-name" olarak belirleyelim
    $user_name = explode('@',$email)[0];
    
    // Kullanıcı daha önce kayıt olmuş mu kontrol et
    $stmt =$baglanti->prepare("SELECT `member-id` FROM `users` WHERE `email` = ?");
    $stmt->bind_param("s", $email);$stmt->execute();
    
    // get_result() yerine her sunucuda çalışan bind_result() kullanıyoruz
    $stmt->bind_result($existing_member_id);$user_exists = false;
    if ($stmt->fetch()) {$user_exists = true;
        $user_id =$existing_member_id;
    }
    $stmt->close();
    
    if (!$user_exists) {
        // Yeni Kayıt
        // Yeni Kayıt (is_verified değerini 1 olarak, verify_token'i boş geçiyoruz)
        $insert =$baglanti->prepare("INSERT INTO `users` (`google_id`, `name-surname`, `user-name`, `email`, `is_verified`) VALUES (?, ?, ?, ?, 1)");
        $insert->bind_param("ssss", $google_id, $name,$user_name, $email);$insert->execute();
        $user_id =$baglanti->insert_id; 
        $insert->close();
    } else {
        // Eski Kayıt
        $update =$baglanti->prepare("UPDATE `users` SET `google_id` = ? WHERE `member-id` = ? AND `google_id` IS NULL");
        $update->bind_param("si", $google_id,$user_id);
        $update->execute();$update->close();
    }
    
    // Oturum (Session) başlat
    $_SESSION['member-id'] =$user_id;
    
    echo json_encode(['success' => true]);
} else {
    // Token geçersiz veya süre aşımı
    echo json_encode(['success' => false, 'message' => 'Geçersiz Token']);
}
?>