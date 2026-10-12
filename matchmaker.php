<?php
// matchmaker.php (0megle.online)
//
// ÖNCE veritabanında bir kez çalıştır:
//   ALTER TABLE online_users ENGINE = InnoDB;
// online_users.user_id sütunu PRIMARY KEY ya da UNIQUE olmalı.
// Cinsiyet artık kullanılmıyor: online_users.gender sütunu varsa ve NOT NULL ise
// ekleme hata verebilir, şunu çalıştır:  ALTER TABLE online_users DROP COLUMN gender;
//
// İstemci iki tür istek gönderir:
//   action = 'join' -> kuyruğa (yeniden) gir, önceki durumu sıfırla
//   action = 'poll' -> beklemeye devam et; başkası eşleştirdiyse bunu bildir
//
// Yanıt durumları:
//   matched -> eşleşme bulundu, ARAMAYI SEN başlat (partner_peer_id verilir)
//   in_call -> birisi seni eşleştirdi, ARAMAYI O başlatacak, sen bekle
//   waiting -> uygun kimse yok, tekrar sor

ini_set('display_errors', '0'); // JSON çıktısı bozulmasın
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $payload, int$code = 200): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['status' => 'error', 'message' => 'Yalnızca POST'], 405);
}

// Kimlik her zaman oturumdan gelir, istemciye güvenilmez
session_start();
if (!isset($_SESSION['member-id'])) {
    respond(['status' => 'error', 'message' => 'Giriş gerekli'], 401);
}
$user_id = (int)$_SESSION['member-id'];
session_write_close(); // oturum kilidini hemen bırak (yoksa istekler sıraya girer)

/* ---------- Yardımcı fonksiyonlar ---------- */

// YENİ: Karşı tarafın kullanıcı adını users tablosundan çeken fonksiyon
function getUserName(mysqli $db, int$uid): string
{
    $s =$db->prepare('SELECT `user-name` FROM `users` WHERE `member-id` = ?');
    if (!$s) return 'Kullanıcı';
    
    $s->bind_param('i',$uid);
    $s->execute();$s->bind_result($name);$result = $s->fetch() ?$name : 'Kullanıcı';
    $s->close();
    
    return $result;
}

function getRow(mysqli $db, int$uid): ?array
{
    $s = $db->prepare('SELECT status, matched_with, session_id FROM online_users WHERE user_id = ?');$s->bind_param('i', $uid);$s->execute();
    $s->bind_result($status, $matched_with,$session_id);
    $row =$s->fetch()
        ? ['status' => $status, 'matched_with' => $matched_with, 'session_id' =>$session_id]
        : null;
    $s->close();
    return $row;
}

// Kuyruğa gir: satır varsa tamamen sıfırlar (bilerek)
function joinQueue(mysqli $db, int $uid, string$peer): void
{
    $s =$db->prepare(
        "INSERT INTO online_users (user_id, peer_id, status, matched_with, last_ping)
         VALUES (?, ?, 'waiting', NULL, NOW())
         ON DUPLICATE KEY UPDATE
            peer_id      = VALUES(peer_id),
            status       = 'waiting',
            matched_with = NULL,
            last_ping    = NOW()"
    );
    $s->bind_param('is', $uid,$peer);
    $s->execute();$s->close();
}

function pingRow(mysqli $db, int$uid): void
{
    $s =$db->prepare('UPDATE online_users SET last_ping = NOW() WHERE user_id = ?');
    $s->bind_param('i',$uid);
    $s->execute();$s->close();
}

// Bekleyenler arasından en fazla 5 rastgele aday döndürür
function findCandidates(mysqli $db, int$me): array
{
    $s =$db->prepare(
        "SELECT user_id FROM online_users
         WHERE status = 'waiting'
           AND user_id != ?
           AND last_ping > NOW() - INTERVAL 30 SECOND
         ORDER BY RAND() LIMIT 5"
    );
    $s->bind_param('i',$me);
    $s->execute();$s->bind_result($cid);$ids = [];
    while ($s->fetch()) {
        $ids[] = (int)$cid;
    }
    $s->close();
    return $ids;
}

// İki satırı kilitleyip ikisi de hâlâ 'waiting' ise eşleştirir.
// Kilitler hep küçük user_id -> büyük user_id sırasıyla alınır (deadlock'u önler).
function claimPair(mysqli $db, int $me, int$other): ?array
{
    $ids = [$me,$other];
    sort($ids);
    $first  =$ids[0];
    $second =$ids[1];

    try {
        $db->begin_transaction();

        $s = $db->prepare(             'SELECT user_id, status, peer_id FROM online_users              WHERE user_id IN (?, ?) ORDER BY user_id FOR UPDATE'         );$s->bind_param('ii', $first,$second);
        $s->execute();$s->bind_result($uid,$status, $peer);$rows = [];
        while ($s->fetch()) {
            $rows[(int)$uid] = ['status' => $status, 'peer_id' =>$peer];
        }
        $s->close();

        // Kilidi aldıktan sonra tekrar doğrula: biri bizden önce davranmış olabilir
        if (!isset($rows[$me], $rows[$other])
            || $rows[$me]['status'] !== 'waiting'
            || $rows[$other]['status'] !== 'waiting') {$db->rollback();
            return null;
        }

        $session_id = uniqid('session_', true);
        $u =$db->prepare(
            "UPDATE online_users SET status = 'in_call', matched_with = ?, session_id = ? WHERE user_id = ?"
        );
        $partner =$other;
        $target  =$me;
        $u->bind_param('isi', $partner,$session_id, $target);$u->execute();

        $partner =$me;
        $target  =$other;
        $u->execute();$u->close();

        $db->commit();

        return ['session_id' => $session_id, 'partner_peer_id' => $rows[$other]['peer_id']];
    } catch (mysqli_sql_exception $e) {
        // Kilitlenme vb.: bu turu pas geç, istemci birazdan tekrar soracak
        try {
            $db->rollback();
        } catch (Throwable $ignored) {
        }
        error_log('matchmaker claimPair: ' . $e->getMessage());
        return null;
    }
}

/* ---------- Ana akış ---------- */

try {
    require_once 'connection.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {$input = [];
    }

    $action  = (($input['action'] ?? 'join') === 'poll') ? 'poll' : 'join';
    $peer_id = (string)($input['peer_id'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/',$peer_id)) {
        respond(['status' => 'error', 'message' => 'Geçersiz peer_id'], 400);
    }

    // Ping atmayan (zombi) kayıtları temizle
    $baglanti->query('DELETE FROM online_users WHERE last_ping < NOW() - INTERVAL 30 SECOND');

    $row = ($action === 'poll') ? getRow($baglanti,$user_id) : null;

    // Başkası bizi zaten eşleştirdiyse DOKUNMA: sıfırlamak eşleşmeyi bozuyordu
    if ($row &&$row['status'] === 'in_call') {
        // YENİ: Eşleşilen kişinin adını çekip gönderiyoruz
        $partner_name = getUserName($baglanti, (int)$row['matched_with']);
        respond([
            'status'       => 'in_call', 
            'session_id'   => $row['session_id'],
            'partner_name' => $partner_name
        ]);
    }

    if ($row) {
        pingRow($baglanti,$user_id);                 // poll + waiting
    } else {
        joinQueue($baglanti, $user_id,$peer_id);     // join veya satır silinmiş
    }

    $match = null;
    $partner_id = null; // YENİ: Eşleşilen kişinin ID'sini tutmak için
    foreach (findCandidates($baglanti, $user_id) as$candidate) {
        $match = claimPair($baglanti, $user_id,$candidate);
        if ($match) {
            $partner_id =$candidate; // ID'yi kaydet
            break;
        }
    }

    if ($match) {
        // YENİ: Tarafımızdan başlatılan eşleşmede kişinin adını çekip gönderiyoruz
        $partner_name = getUserName($baglanti,$partner_id);
        respond([
            'status'          => 'matched',
            'session_id'      => $match['session_id'],
            'partner_peer_id' => $match['partner_peer_id'],
            'partner_name'    => $partner_name
        ]);
    }

    // Eşleşme bulamadık. Bu sırada biri bizi eşleştirmiş olabilir, tekrar bak
    $row = getRow($baglanti,$user_id);
    if ($row &&$row['status'] === 'in_call') {
        // YENİ: Son saniye eşleşmesinde kişinin adını çekip gönderiyoruz
        $partner_name = getUserName($baglanti, (int)$row['matched_with']);
        respond([
            'status'       => 'in_call', 
            'session_id'   => $row['session_id'],
            'partner_name' => $partner_name
        ]);
    }

    respond(['status' => 'waiting']);
} catch (Throwable $e) {
    error_log('matchmaker: ' . $e->getMessage());
    respond(['status' => 'error', 'message' => 'Sunucu hatası'], 500);
}