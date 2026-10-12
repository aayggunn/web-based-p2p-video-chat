<?php
require_once 'connection.php';

if (isset($_GET['token'])) {
    $token =$_GET['token'];
    
    // Tokeni veritabanında ara
    $stmt =$baglanti->prepare("SELECT `member-id` FROM `users` WHERE `verify_token` = ? AND `is_verified` = 0");
    $stmt->bind_param("s", $token);$stmt->execute();
    $stmt->bind_result($member_id);
    
    if ($stmt->fetch()) {$stmt->close();
        
        // Kullanıcıyı onayla ve tokeni sil
        $update =$baglanti->prepare("UPDATE `users` SET `is_verified` = 1, `verify_token` = NULL WHERE `member-id` = ?");
        $update->bind_param("i", $member_id);
        $update->execute();$update->close();
        
        echo "<script>alert('Tebrikler! Hesabınız başarıyla doğrulandı. Şimdi giriş yapabilirsiniz.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Geçersiz veya süresi dolmuş / zaten onaylanmış bir bağlantı!'); window.location.href='login.php';</script>";
    }
} else {
    header("Location: login.php");
}
?>