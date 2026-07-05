<?php
session_start();

// Veritabanı bağlantısını dahil et
require_once 'connection.php';

// Eğer oturum açmış bir kullanıcı çıkış yapıyorsa veritabanını temizle
if (isset($_SESSION['member-id'])) {
    $user_id = (int)$_SESSION['member-id'];

    // 1. Eğer çıkış yapan kişi o an biriyle eşleşmişse, karşı tarafı mağdur etmemek için boşa çıkar (waiting)
    mysqli_query($baglanti, "
        UPDATE online_users 
        SET status = 'waiting', matched_with = NULL, session_id = NULL 
        WHERE matched_with = $user_id
    ");

    // 2. Çıkış yapan kullanıcının kendi kaydını tablodan tamamen sil
    $stmt = mysqli_prepare($baglanti, "DELETE FROM online_users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// 3. Klasik Session Temizliği ve Yönlendirme
$_SESSION = array();
session_destroy();

// header yönlendirmesinden sonra kodun okumaya devam etmemesi için exit() eklemek her zaman en güvenlisidir.
header("Location: index.html");
exit();
?>