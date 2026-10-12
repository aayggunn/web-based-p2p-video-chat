<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'connection.php';

if (!isset($baglanti) || !$baglanti) {
    die("Veritabani baglantisi kurulamadi.");
}

if (!isset($_SESSION['member-id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['member-id'];$username = "Kullanici";

$query = "SELECT `user-name` FROM `users` WHERE `member-id` = ?";
$stmt = mysqli_prepare($baglanti,$query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt,$db_username);
    if (mysqli_stmt_fetch($stmt)) {
        $username =$db_username;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#0f172a">
    
    <!-- SEO Optimizasyonu -->
    <title>Sohbet Odası | 0megle.online - Rastgele Görüntülü Sohbet</title>
    <meta name="description" content="0megle.online ile anında rastgele görüntülü sohbete başla. Yeni insanlarla tanış, sınırları kaldır.">
    <!-- DİKKAT: Bu sayfa kullanıcı girişi gerektirdiği için arama motorlarına kapalı kalmalıdır (noindex) -->
    <meta name="robots" content="noindex, nofollow">

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn Paylaşım Etiketleri) -->
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://0megle.online/home-page.php">
    <meta property="og:site_name" content="0megle.online">
    <meta property="og:title" content="Canlı Sohbet Odası | 0megle.online">
    <meta property="og:description" content="0megle.online ile anında rastgele görüntülü sohbete başla.">
    <meta property="og:image" itemprop="image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">
    <meta property="og:image:secure_url" itemprop="image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter Card Meta Etiketleri -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="0megle.online - Canlı Sohbet Odası">
    <meta name="twitter:description" content="Sınırları kaldır, yeni insanlarla anında tanış. Ücretsiz rastgele video sohbet ağı.">
    <meta name="twitter:image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">
    
    <!-- Sekme (Favicon) Logosu -->
    <link rel="icon" href="0megle-online-rastgele-sohbet-logo.jpg?v=2" type="image/jpeg">
    <link rel="apple-touch-icon" href="0megle-online-rastgele-sohbet-logo.jpg?v=2">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Genel Tasarım & Koyu Tema */
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Glassmorphism Header */
        .app-header {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-logo {
            font-weight: 700;
            font-size: 1.4rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: 0.5px;
        }
        
        .brand-logo span {
            color: #3b82f6;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px 15px 5px 5px;
            border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }

        .user-info-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .logout-link {
            font-size: 0.75rem;
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }

        .logout-link:hover {
            color: #ef4444;
        }

        /* Video Alanı Tasarımı (16:9 Geniş Ekran Olarak Güncellendi) */
        .video-wrapper {
            position: relative;
            background: #1e293b;
            border-radius: 20px;
            overflow: hidden;
            aspect-ratio: 16/9;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .video-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #000;
        }

        #localVideo {
            transform: scaleX(-1);
        }

        .video-badge {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 10;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .dot-green { background-color: #22c55e; box-shadow: 0 0 8px #22c55e; }
        .dot-blue { background-color: #3b82f6; box-shadow: 0 0 8px #3b82f6; }

        /* Modern Status (Bildirim) Kutusu */
        #statusInfo {
            display: none;
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            text-align: center;
            margin: 20px auto 0 auto;
            max-width: 600px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        #statusInfo i {
            margin-right: 8px;
        }

        /* Durum Kutusu Animasyonu */
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }

        .status-searching {
            animation: pulse-glow 2s infinite;
        }

        /* Kontrol Butonları Tasarımı */
        .controls-area {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 20px;
            padding: 20px;
            margin-top: 25px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .action-btn {
            border: none;
            border-radius: 50px;
            padding: 12px 28px;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: white;
            width: 100%;
        }

        .action-btn i {
            font-size: 1.1rem;
        }

        #startBtn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        
        #startBtn:not(:disabled):hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
        }

        #nextBtn {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        #nextBtn:not(:disabled):hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
        }

        #stopBtn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        #stopBtn:not(:disabled):hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
        }

        .action-btn:disabled {
            background: #334155 !important;
            box-shadow: none !important;
            color: #64748b;
            cursor: not-allowed;
            transform: none !important;
        }

        @media (max-width: 768px) {
            .app-header {
                padding: 10px 0;
            }
            .video-wrapper {
                margin-bottom: 15px;
            }
            .controls-area {
                padding: 15px;
                background: transparent;
                border: none;
                margin-top: 10px;
            }
            .action-btn {
                padding: 14px 20px;
                font-size: 1.05rem;
            }
            .btn-col {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    
    <header class="app-header py-2">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="#" class="brand-logo">
                0MEGLE<span>.ONLINE</span>
            </a>
            
            <div class="user-profile">
                <div class="avatar">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <div class="user-info-text d-none d-sm-flex">
                    <span class="user-name"><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt me-1"></i>Güvenli Çıkış</a>
                </div>
                <!-- Mobil için sadece ikonlu çıkış -->
                <a href="logout.php" class="logout-link d-flex d-sm-none ms-2 me-2" style="font-size: 1.2rem;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="container py-4 flex-grow-1">
        <div class="row g-4 justify-content-center">
            <!-- Videolar PC'de col-lg-6 ile daha büyük hale getirildi -->
            <div class="col-12 col-md-6 col-lg-6">
                <div class="video-wrapper">
                    <div class="video-badge">
                        <div class="badge-dot dot-blue"></div>
                        Siz
                    </div>
                    <video id="localVideo" autoplay muted playsinline></video>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-6">
                <div class="video-wrapper">
                    <div class="video-badge">
                        <div class="badge-dot dot-green"></div>
                        <!-- İsim alanı ID ile dinamikleştirildi -->
                        <span id="remoteUserName">Yabancı</span>
                    </div>
                    <video id="remoteVideo" autoplay playsinline></video>
                </div>
            </div>
        </div>

        <div id="statusInfo">
            <i class="fas fa-circle-notch fa-spin"></i>
            <span id="statusText">Eşleşme aranıyor...</span>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="controls-area">
                    <div class="row">
                        <div class="col-12 col-md-4 btn-col">
                            <button id="startBtn" class="action-btn">
                                <i class="fas fa-video"></i> Aramayı Başlat
                            </button>
                        </div>
                        <div class="col-12 col-md-4 btn-col">
                            <button id="nextBtn" class="action-btn" disabled>
                                <i class="fas fa-forward-step"></i> Geç / Sonraki
                            </button>
                        </div>
                        <div class="col-12 col-md-4 btn-col">
                            <button id="stopBtn" class="action-btn" disabled>
                                <i class="fas fa-phone-slash"></i> Görüşmeyi Bitir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- PeerJS Kütüphanesi -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/peerjs/1.4.7/peerjs.min.js"></script>

   <!-- ÖZEL JS VE YENİ ÖZELLİKLER -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const startBtn = document.getElementById('startBtn');
        const nextBtn = document.getElementById('nextBtn');
        const stopBtn = document.getElementById('stopBtn');
        const localVideo = document.getElementById('localVideo');
        const remoteVideo = document.getElementById('remoteVideo');
        const statusInfo = document.getElementById('statusInfo');
        const statusText = document.getElementById('statusText');
        const remoteUserName = document.getElementById('remoteUserName'); 
        
        // YENİ: Kendi ismimizi JavaScript'e alıyoruz ki arama yaparken karşıya anında gönderebilelim
        const myUserName = "<?php echo addslashes(htmlspecialchars($username, ENT_QUOTES, 'UTF-8')); ?>";

        let state = 'idle';
        let peer = null;
        let currentCall = null;
        let localStream = null;
        let heartbeatInterval = null;
        let matchmakingTimer = null;
        let connectTimer = null;
        let searchSeq = 0; 
        const CONNECT_TIMEOUT_MS = 15000;

        function showStatus(text, type = 'spin') {
            let iconHtml = '';
            if (type === 'spin') {
                iconHtml = '<i class="fas fa-circle-notch fa-spin"></i>';
                statusInfo.classList.add('status-searching');
            } else if (type === 'success') {
                iconHtml = '<i class="fas fa-check-circle" style="color: #22c55e;"></i>';
                statusInfo.classList.remove('status-searching');
            } else if (type === 'error') {
                iconHtml = '<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>';
                statusInfo.classList.remove('status-searching');
            }
            
            statusInfo.innerHTML = iconHtml + ' <span id="statusText">' + text + '</span>';
            statusInfo.style.display = 'block';
        }

        function postJSON(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body || {})
            }).then(function(response) {
                if (response.status === 401) {
                    window.location.href = 'login.php';
                    throw new Error('Oturum süresi doldu');
                }
                return response.json();
            });
        }

        function randomPeerId() {
            const bytes = new Uint8Array(12);
            crypto.getRandomValues(bytes);
            return 'p_' + Array.from(bytes, function(b) {
                return b.toString(16).padStart(2, '0');
            }).join('');
        }

        function notifyLeave() {
            navigator.sendBeacon('leave.php');
        }

        function search(action) {
            const seq = ++searchSeq;
            postJSON('matchmaker.php', {
                action: action,
                peer_id: peer.id
            })
            .then(function(data) {
                if (seq !== searchSeq || state !== 'searching') return;
                handleMatchmakerResponse(data);
            })
            .catch(function(error) {
                console.error('Eşleşme hatası:', error);
                if (seq === searchSeq && state === 'searching') scheduleNextPoll();
            });
        }

        function scheduleNextPoll() {
            clearTimeout(matchmakingTimer);
            const randomDelay = Math.floor(Math.random() * 4000) + 1000;
            matchmakingTimer = setTimeout(function() {
                if (state === 'searching') search('poll');
            }, randomDelay);
        }

        function startMatchmaking() {
            clearTimeout(matchmakingTimer);
            clearTimeout(connectTimer);
            state = 'searching';
            startBtn.disabled = true;
            nextBtn.disabled = true;
            stopBtn.disabled = false;
            remoteUserName.textContent = 'Aranıyor...'; 
            showStatus('Eşleşme aranıyor...', 'spin');
            search('join');
        }

        function handleMatchmakerResponse(data) {
            if (data.status === 'matched') {
                clearTimeout(matchmakingTimer);
                state = 'connecting';
                remoteUserName.textContent = data.partner_name || 'Kullanıcı'; 
                showStatus('Eşleşme bulundu! Bağlantı kuruluyor...', 'spin');
                armConnectTimeout();
                // YENİ: Aramayı başlatırken, kendi ismimizi "metadata" ile doğrudan sinyalin içine gömüyoruz
                setupCall(peer.call(data.partner_peer_id, localStream, { metadata: { callerName: myUserName } }));
            } else if (data.status === 'in_call') {
                clearTimeout(matchmakingTimer);
                state = 'connecting';
                remoteUserName.textContent = data.partner_name || 'Kullanıcı';
                showStatus('Eşleşme bulundu! Karşı tarafın bağlanması bekleniyor...', 'spin');
                armConnectTimeout();
            } else if (data.status === 'waiting') {
                scheduleNextPoll();
            } else {
                showStatus('Sistem hatası, yeniden deneniyor...', 'error');
                scheduleNextPoll();
            }
        }

        function armConnectTimeout() {
            clearTimeout(connectTimer);
            connectTimer = setTimeout(function() {
                if (state === 'connecting') {
                    showStatus('Bağlantı kurulamadı, yeni eşleşme aranıyor...', 'error');
                    endCall(false);
                    startMatchmaking();
                }
            }, CONNECT_TIMEOUT_MS);
        }

        function setupCall(call) {
            currentCall = call;

            call.on('stream', function(remoteStream) {
                clearTimeout(connectTimer);
                state = 'connected';
                remoteVideo.srcObject = remoteStream;
                nextBtn.disabled = false;
                stopBtn.disabled = false;
                startBtn.disabled = true;
                showStatus('Bağlantı kuruldu!', 'success'); 
            });

            if (call.peerConnection) {
                call.peerConnection.onconnectionstatechange = function() {
                    const pcState = call.peerConnection.connectionState;
                    if (pcState === 'disconnected' || pcState === 'failed' || pcState === 'closed') {
                        handleRemoteDisconnect();
                    }
                };
                call.peerConnection.oniceconnectionstatechange = function() {
                    const iceState = call.peerConnection.iceConnectionState;
                    if (iceState === 'disconnected' || iceState === 'failed' || iceState === 'closed') {
                        handleRemoteDisconnect();
                    }
                };
            }

            call.on('close', handleRemoteDisconnect);
            call.on('error', function(err) {
                console.error('Çağrı hatası:', err);
                handleRemoteDisconnect();
            });

            function handleRemoteDisconnect() {
                if (currentCall === call && state === 'connected') {
                    endCall(false);
                    startMatchmaking(); 
                }
            }
        }

        function endCall(notifyServer) {
            if (notifyServer === undefined) notifyServer = true;

            searchSeq++; 
            clearTimeout(matchmakingTimer);
            clearTimeout(connectTimer);

            const call = currentCall;
            currentCall = null; 
            if (call) call.close();

            if (notifyServer) notifyLeave();

            remoteVideo.srcObject = null;
            remoteVideo.load(); 
            remoteUserName.textContent = 'Yabancı'; 

            state = 'idle';
            nextBtn.disabled = true;
            stopBtn.disabled = true;
            startBtn.disabled = false;
        }

        function sendHeartbeat() {
            fetch('heartbeat.php', { method: 'POST' }).catch(function(error) {
                console.error('Heartbeat error:', error);
            });
        }

        function startHeartbeat() {
            clearInterval(heartbeatInterval);
            sendHeartbeat();
            heartbeatInterval = setInterval(sendHeartbeat, 10000);
        }

        startBtn.addEventListener('click', function() {
            if (localStream && peer && !peer.destroyed && peer.open) {
                startMatchmaking();
                return;
            }

            startBtn.disabled = true;
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(function(stream) {
                    localStream = stream;
                    localVideo.srcObject = stream;

                    peer = new Peer(randomPeerId(), {
                        host: '0.peerjs.com',
                        port: 443,
                        secure: true,
                        debug: 3
                    });

                    peer.on('open', function() {
                        startMatchmaking(); 
                        startHeartbeat();
                    });

                    peer.on('call', function(call) {
                        if (currentCall || (state !== 'searching' && state !== 'connecting')) {
                            call.close();
                            return;
                        }
                        clearTimeout(matchmakingTimer);
                        if (state === 'searching') {
                            state = 'connecting';
                            armConnectTimeout();
                        }
                        
                        // YENİ: Bize çağrı düştüğü anda, karşı tarafın sinyalin içine gömdüğü ismi okuyup ekrana basıyoruz
                        if (call.metadata && call.metadata.callerName) {
                            remoteUserName.textContent = call.metadata.callerName;
                        } else if (remoteUserName.textContent === 'Aranıyor...') {
                            remoteUserName.textContent = 'Kullanıcı';
                        }

                        showStatus('Bağlantı kuruluyor...', 'spin');
                        call.answer(localStream);
                        setupCall(call);
                    });

                    peer.on('error', function(err) {
                        if (err.type === 'peer-unavailable' && state === 'connecting') {
                            showStatus('Karşı taraf bağlanamadı, yeni eşleşme aranıyor...', 'error');
                            endCall(false);
                            startMatchmaking();
                        } else {
                            showStatus('Bağlantı hatası: ' + err.message, 'error');
                        }
                    });
                })
                .catch(function(err) {
                    startBtn.disabled = false;
                    showStatus('Kamera/mikrofon erişimine izin vermelisiniz', 'error');
                });
        });

        nextBtn.addEventListener('click', function() {
            if (!peer) return;
            endCall(false);      
            startMatchmaking();
        });

        stopBtn.addEventListener('click', function() {
            endCall();           
            if (localStream) {
                localStream.getTracks().forEach(function(track) { track.stop(); });
                localStream = null;
                localVideo.srcObject = null;
                localVideo.load();
            }
            if (peer) {
                peer.destroy();
                peer = null;
            }
            clearInterval(heartbeatInterval);
            statusInfo.style.display = 'none';
        });

        window.addEventListener('beforeunload', function() {
            if (peer) peer.destroy();
            notifyLeave();
        });
    });
    </script>
</body>
</html>