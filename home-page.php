<?php
// homepage.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Bağlantı dosyasını include et
require_once 'connection.php';

// Bağlantı kontrolü
if (!isset($baglanti) || !$baglanti) {
    die("Veritabanı bağlantısı kurulamadı.");
}

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['member-id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['member-id'];
$username = "Kullanıcı";
$gender = null;

// Sorguyu hazırla
$query = "SELECT `user-name`, `gender` FROM `users` WHERE `member-id` = ?";
$stmt = mysqli_prepare($baglanti, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $db_username, $db_gender);
    if (mysqli_stmt_fetch($stmt)) {
        $username = $db_username;
        $gender = $db_gender;
    }
    mysqli_stmt_close($stmt);
}

/* ---------------- DİL SİSTEMİ ---------------- */
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'tr', 0, 2);
    $_SESSION['lang'] = ($browserLang == 'tr') ? 'tr' : 'en';
}
$currentLang = $_SESSION['lang'];

$texts = [
    'tr' => [
        'title' => '0megle.online | Görüntülü Sohbet',
        'logout' => 'Çıkış Yap',
        'you' => 'Siz',
        'remote' => 'Karşıdaki Kullanıcı',
        'status_searching' => 'Eşleşme aranıyor...',
        'status_connecting' => 'Bağlantı kuruluyor...',
        'status_connected' => 'Bağlantı kuruldu!',
        'status_ended' => 'Görüşme sonlandırıldı.',
        'status_partner_left' => 'Karşı taraf ayrıldı, yeni kişi aranıyor...',
        'status_error' => 'Sistem hatası, lütfen yeniden deneyin',
        'status_cam_error' => 'Kamera/mikrofon erişimine izin vermelisiniz',
        'status_peer_error' => 'Bağlantı hatası: ',
        'btn_start' => 'Kamerayı Aç',
        'btn_starting' => 'Kamera Açılıyor...',
        'btn_active' => 'Kamera Aktif',
        'btn_retry_cam' => 'İzin Ver ve Tekrar Dene',
        'btn_retry' => 'Yeniden Dene',
        'btn_next' => 'Sonraki Kişi',
        'btn_stop' => 'Görüşmeyi Bitir',
    ],
    'en' => [
        'title' => '0megle.online | Video Chat',
        'logout' => 'Log Out',
        'you' => 'You',
        'remote' => 'Stranger',
        'status_searching' => 'Looking for a match...',
        'status_connecting' => 'Connecting...',
        'status_connected' => 'Connected!',
        'status_ended' => 'Call ended.',
        'status_partner_left' => 'Partner left, searching for a new one...',
        'status_error' => 'System error, please try again',
        'status_cam_error' => 'You must allow camera/microphone access',
        'status_peer_error' => 'Connection error: ',
        'btn_start' => 'Turn on Camera',
        'btn_starting' => 'Starting Camera...',
        'btn_active' => 'Camera Active',
        'btn_retry_cam' => 'Allow and Try Again',
        'btn_retry' => 'Try Again',
        'btn_next' => 'Next Person',
        'btn_stop' => 'End Call',
    ]
];
$lang = $texts[$currentLang];
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title><?= $lang['title'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #1e90ff;
            --primary-hover: #0073e6;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --glass-bg: rgba(25, 25, 35, 0.65);
            --glass-border: rgba(255, 255, 255, 0.08);
        }
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at top, #14142b 0%, #05050f 100%);
            color: #fff;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand-logo {
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: 1px;
            margin: 0;
        }
        .brand-logo .text-dodgerblue { color: var(--primary-color); }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #8a2be2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .user-details {
            display: flex;
            flex-direction: column;
            text-align: right;
        }
        .user-details span {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .user-details a {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
            text-decoration: none;
            transition: 0.3s;
        }
        .user-details a:hover { color: var(--danger-color); }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 30px 0;
        }
        .video-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            width: 100%;
        }
        .video-box {
            flex: 1;
            max-width: 650px;
            aspect-ratio: 4 / 3;
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .video-box::before {
            content: attr(data-label);
            position: absolute;
            bottom: 15px;
            left: 15px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 400;
            z-index: 10;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .video-box::after {
            content: '\f03d';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            font-size: 4rem;
            color: rgba(255,255,255,0.05);
            z-index: 1;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
            z-index: 5;
            position: relative;
            background-color: #000;
            opacity: 0; 
            transition: opacity 0.3s ease-in-out; 
        }
        #localVideo, #remoteVideo { transform: scaleX(-1); }
        .status-info {
            text-align: center;
            margin: 0 auto 30px;
            padding: 12px 25px;
            background: rgba(30, 144, 255, 0.15);
            border: 1px solid rgba(30, 144, 255, 0.3);
            border-radius: 30px;
            display: none;
            width: fit-content;
            backdrop-filter: blur(5px);
            animation: pulse 2s infinite;
        }
        .status-info i { margin-right: 8px; color: var(--primary-color); }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(30, 144, 255, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(30, 144, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(30, 144, 255, 0); }
        }
        .controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            background: var(--glass-bg);
            padding: 20px 40px;
            border-radius: 40px;
            width: fit-content;
            margin: 0 auto;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
        }
        button {
            padding: 14px 35px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.05rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }
        #startBtn { background-color: var(--primary-color); }
        #startBtn:hover:not(:disabled) { background-color: var(--primary-hover); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(30, 144, 255, 0.4); }
        #nextBtn { background-color: var(--warning-color); }
        #nextBtn:hover:not(:disabled) { background-color: #e67e22; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4); }
        #stopBtn { background-color: var(--danger-color); }
        #stopBtn:hover:not(:disabled) { background-color: #c0392b; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4); }
        button:disabled {
            background-color: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.3);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        @media (max-width: 768px) {
            .video-container { flex-direction: column; gap: 20px; }
            .video-box { width: 100%; max-width: 100%; border-radius: 15px; }
            .video-box::before { bottom: 10px; left: 10px; font-size: 0.8rem; }
            .controls {
                width: 100%;
                border-radius: 20px;
                padding: 15px;
                flex-direction: column;
                box-sizing: border-box;
            }
            button { width: 100%; justify-content: center; padding: 15px; }
        }
    </style>
</head>
<body>

    <header>
        <div class="container header-content">
            <h1 class="brand-logo">
                <a href="index.html" class="text-white" style="text-decoration: none;">0MEGLE<span class="text-dodgerblue">.ONLINE</span></a>
            </h1>
            <div class="user-info">
                <div class="user-details">
                    <span><?= htmlspecialchars($username) ?></span>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <?= $lang['logout'] ?></a>
                </div>
                <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
            </div>
        </div>
    </header>

    <div class="main-content container">
        
        <div class="video-container">
            <div class="video-box" data-label="<?= $lang['you'] ?>">
                <video id="localVideo" autoplay muted playsinline></video>
            </div>
            <div class="video-box" id="remoteVideoBox" data-label="<?= $lang['remote'] ?>">
                <video id="remoteVideo" autoplay playsinline></video>
            </div>
        </div>

        <div class="status-info" id="statusInfo">
            <i class="fas fa-spinner fa-spin"></i>
            <span id="statusText"><?= $lang['status_searching'] ?></span>
        </div>

        <div class="controls">
            <button id="startBtn">
                <i class="fas fa-video"></i> <?= $lang['btn_start'] ?>
            </button>
            <button id="nextBtn" disabled>
                <i class="fas fa-random"></i> <?= $lang['btn_next'] ?>
            </button>
            <button id="stopBtn" disabled>
                <i class="fas fa-phone-slash"></i> <?= $lang['btn_stop'] ?>
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/peerjs/1.4.7/peerjs.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const trans = {
            searching: <?= json_encode($lang['status_searching']) ?>,
            connecting: <?= json_encode($lang['status_connecting']) ?>,
            connected: <?= json_encode($lang['status_connected']) ?>,
            ended: <?= json_encode($lang['status_ended']) ?>,
            partner_left: <?= json_encode($lang['status_partner_left']) ?>,
            sys_error: <?= json_encode($lang['status_error']) ?>,
            cam_error: <?= json_encode($lang['status_cam_error']) ?>,
            peer_error: <?= json_encode($lang['status_peer_error']) ?>,
            remote: <?= json_encode($lang['remote']) ?>,
            btn_start: <?= json_encode($lang['btn_start']) ?>,
            btn_starting: <?= json_encode($lang['btn_starting']) ?>,
            btn_active: <?= json_encode($lang['btn_active']) ?>,
            btn_retry_cam: <?= json_encode($lang['btn_retry_cam']) ?>,
            btn_retry: <?= json_encode($lang['btn_retry']) ?>
        };

        const startBtn = document.getElementById('startBtn');
        const nextBtn = document.getElementById('nextBtn');
        const stopBtn = document.getElementById('stopBtn');
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const remoteVideoBox = document.getElementById('remoteVideoBox');
        const statusInfo = document.getElementById('statusInfo');
        const statusText = document.getElementById('statusText');
        
        let peer;
        let currentCall;
        let localStream;
        let currentSessionId;
        let heartbeatInterval;
        let matchmakingInterval;
        const userId = <?= json_encode($user_id) ?>;
        
        let isSearching = false;
        let intentionallyEnded = false;

        // --- ORTAK KOPMA ALGILAYICI ---
        const handleDisconnect = () => {
            if (!intentionallyEnded && !isSearching) {
                intentionallyEnded = true; // Tekrarlı tetiklenmeyi önle
                endCall();
                
                // Karşı tarafa anında bildirim göster
                statusInfo.style.display = 'block';
                statusText.textContent = trans.partner_left;
                statusInfo.querySelector('i').className = 'fas fa-exclamation-circle';
                
                // 1.5 Saniye sonra otomatik yeni aramaya fırlat
                if (peer) {
                    setTimeout(() => {
                        intentionallyEnded = false; 
                        startMatchmaking(peer.id);
                    }, 1500);
                }
            }
        };

        function initializeUser(peerId) {
            fetch('matchmaker.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, peer_id: peerId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'matched') {
                    handleMatch(data);
                } else {
                    startMatchmaking(peerId);
                }
            })
            .catch(error => {
                console.error('API Error:', error);
                statusText.textContent = trans.sys_error;
                statusInfo.querySelector('i').className = 'fas fa-exclamation-triangle';
            });
        }

        function startMatchmaking(peerId) {
            if (isSearching) return;
            isSearching = true;
            intentionallyEnded = false; 
            
            statusInfo.style.display = 'block';
            statusText.textContent = trans.searching;
            statusInfo.querySelector('i').className = 'fas fa-spinner fa-spin';
            
            startBtn.disabled = true;
            nextBtn.disabled = true;
            stopBtn.disabled = false; 

            const checkForMatch = () => {
                if (!isSearching) return;

                fetch('matchmaker.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, peer_id: peerId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'matched') {
                        handleMatch(data);
                    } else {
                        const randomDelay = Math.floor(Math.random() * 4000) + 1000;
                        matchmakingInterval = setTimeout(checkForMatch, randomDelay);
                    }
                })
                .catch(error => {
                    const randomDelay = Math.floor(Math.random() * 4000) + 1000;
                    matchmakingInterval = setTimeout(checkForMatch, randomDelay);
                });
            };

            checkForMatch();
        }

        function handleMatch(data) {
            clearTimeout(matchmakingInterval);
            isSearching = false;
            currentSessionId = data.session_id;
            statusText.textContent = trans.connecting;
            statusInfo.querySelector('i').className = 'fas fa-link';
            
            let partnerName = (data.partner_username && data.partner_username !== "null") ? data.partner_username : trans.remote;
            remoteVideoBox.setAttribute('data-label', partnerName);
            
            connectToPartner(data.partner_peer_id);
        }

        function startHeartbeat() {
            if (heartbeatInterval) clearInterval(heartbeatInterval);
            sendHeartbeat();
            heartbeatInterval = setInterval(sendHeartbeat, 10000);
        }

        function sendHeartbeat() {
            fetch('heartbeat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, session_id: currentSessionId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'session_ended' && currentSessionId) {
                    handleDisconnect();
                }
            })
            .catch(e => console.log(e));
        }

        function connectToPartner(partnerPeerId) {
            const call = peer.call(partnerPeerId, localStream);
            setupCall(call);
        }

        function setupCall(call) {
            currentCall = call;
            intentionallyEnded = false; 

            if (call.peerConnection) {
                call.peerConnection.oniceconnectionstatechange = function() {
                    let state = call.peerConnection.iceConnectionState;
                    if (state === 'disconnected' || state === 'failed' || state === 'closed') {
                        handleDisconnect();
                    }
                };
                call.peerConnection.onconnectionstatechange = function() {
                    let state = call.peerConnection.connectionState;
                    if (state === 'disconnected' || state === 'failed' || state === 'closed') {
                        handleDisconnect();
                    }
                };
            }

            call.on('stream', function(remoteStream) {
                remoteVideo.srcObject = remoteStream;
                remoteVideo.style.opacity = '1'; 
                
                nextBtn.disabled = false;
                stopBtn.disabled = false;
                startBtn.disabled = true;
                statusText.textContent = trans.connected;
                statusInfo.querySelector('i').className = 'fas fa-check-circle';
                
                setTimeout(() => {
                    if(!isSearching) statusInfo.style.display = 'none';
                }, 3000);
            });

            call.on('close', handleDisconnect);
            call.on('error', handleDisconnect);
        }

        function endCall() {
            if (currentCall) {
                if (currentCall.peerConnection) {
                    try { currentCall.peerConnection.close(); } catch(e) {}
                }
                try { currentCall.close(); } catch(e) {}
                currentCall = null;
            }

            if (currentSessionId) {
                fetch('leave.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, session_id: currentSessionId })
                });
                currentSessionId = null;
            }

            remoteVideo.srcObject = null;
            remoteVideo.removeAttribute('src');
            try { remoteVideo.load(); } catch(e) {}
            remoteVideo.style.opacity = '0';
            
            nextBtn.disabled = true;
            stopBtn.disabled = true;
            startBtn.disabled = true; 
            isSearching = false;
            clearTimeout(matchmakingInterval);
            
            remoteVideoBox.setAttribute('data-label', trans.remote);
        }

        startBtn.addEventListener('click', function() {
            startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + trans.btn_starting;
            
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(function(stream) {
                    localStream = stream;
                    localVideo.srcObject = stream;
                    localVideo.style.opacity = '1'; 
                    
                    startBtn.disabled = true;
                    startBtn.innerHTML = '<i class="fas fa-video"></i> ' + trans.btn_active;

                    const peerId = 'user_' + userId + '_' + Math.random().toString(36).substr(2, 9);
                    peer = new Peer(peerId, {
                        host: '0.peerjs.com',
                        port: 443,
                        secure: true,
                        debug: 3
                    });

                    peer.on('open', function(id) {
                        initializeUser(id);
                        startHeartbeat();
                        stopBtn.disabled = false; 
                    });

                    peer.on('call', function(call) {
                        call.answer(localStream);
                        setupCall(call);
                    });

                    peer.on('error', function(err) {
                        statusInfo.style.display = 'block';
                        statusText.textContent = trans.peer_error + err.message;
                        statusInfo.querySelector('i').className = 'fas fa-exclamation-triangle';
                        startBtn.disabled = false;
                        startBtn.innerHTML = '<i class="fas fa-video"></i> ' + trans.btn_retry;
                    });
                })
                .catch(function(err) {
                    statusInfo.style.display = 'block';
                    statusText.textContent = trans.cam_error;
                    statusInfo.querySelector('i').className = 'fas fa-camera-slash';
                    startBtn.innerHTML = '<i class="fas fa-video"></i> ' + trans.btn_retry_cam;
                });
        });

        nextBtn.addEventListener('click', function() {
            intentionallyEnded = true; 
            endCall();
            
            if (peer) {
                startMatchmaking(peer.id);
                stopBtn.disabled = false;
            }
        });

        stopBtn.addEventListener('click', function() {
            intentionallyEnded = true; 
            endCall(); // İlk önce leave.php çağrılır ve WebRTC kanalları kapatılır.
            
            // DÜZELTME: Soketin tamamen yok edilmesini (destroy) 400ms geciktiriyoruz.
            // Bu sayede sinyal sunucu üzerinden karşı tarafa pürüzsüzce ulaşabiliyor.
            setTimeout(() => {
                if (localStream) {
                    localStream.getTracks().forEach(track => track.stop());
                    localStream = null;
                    localVideo.srcObject = null;
                    localVideo.removeAttribute('src');
                    localVideo.load();
                    localVideo.style.opacity = '0'; 
                }
                if (peer) {
                    peer.destroy();
                    peer = null;
                }
            }, 400);
            
            statusInfo.style.display = 'block';
            statusText.textContent = trans.ended;
            statusInfo.querySelector('i').className = 'fas fa-info-circle';
            
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="fas fa-video"></i> ' + trans.btn_start;
            stopBtn.disabled = true;
        });

        window.addEventListener('beforeunload', function() {
            if (peer) peer.destroy();
            const url = 'leave.php';
            const data = JSON.stringify({ user_id: userId });
            navigator.sendBeacon(url, data);
        });
    });
    </script>
</body>
</html>