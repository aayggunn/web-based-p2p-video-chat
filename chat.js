document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startBtn');
    const nextBtn = document.getElementById('nextBtn');
    const stopBtn = document.getElementById('stopBtn');
    const localVideo = document.getElementById('localVideo');
    const remoteVideo = document.getElementById('remoteVideo');
    
    let peer;
    let currentCall;
    let localStream;
    let currentSessionId;
    
    // PeerJS bağlantısını başlat
    function initializePeer() {
        peer = new Peer({
            host: '0.peerjs.com',
            port: 443,
            secure: true,
            debug: 3
        });
        
        peer.on('open', function(id) {
            console.log('PeerJS ID: ' + id);
            findPartner(id);
        });
        
        peer.on('call', function(call) {
            call.answer(localStream);
            setupCall(call);
        });
        
        peer.on('error', function(err) {
            console.error('PeerJS error:', err);
            alert('Bağlantı hatası: ' + err.message);
        });
    }
    
    // Eşleşme bul
    function findPartner(peerId) {
        fetch('find_partner.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                peer_id: peerId,
                user_id: <?php echo $logged_in ? $user_id : 'null'; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'waiting') {
                currentSessionId = data.session_id;
                console.log('Eşleşme bekleniyor...');
            } else if (data.status === 'matched') {
                currentSessionId = data.session_id;
                connectToPartner(data.partner_peer_id);
            }
        })
        .catch(error => {
            console.error('Hata:', error);
        });
    }
    
    // Partnera bağlan
    function connectToPartner(partnerPeerId) {
        const call = peer.call(partnerPeerId, localStream);
        setupCall(call);
    }
    
    // Çağrıyı ayarla
    function setupCall(call) {
        currentCall = call;
        
        call.on('stream', function(remoteStream) {
            remoteVideo.srcObject = remoteStream;
            nextBtn.disabled = false;
            stopBtn.disabled = false;
            startBtn.disabled = true;
        });
        
        call.on('close', function() {
            endCall();
        });
        
        call.on('error', function(err) {
            console.error('Çağrı hatası:', err);
            endCall();
        });
    }
    
    // Çağrıyı sonlandır
    function endCall() {
        if (currentCall) {
            currentCall.close();
            currentCall = null;
        }
        
        if (currentSessionId) {
            fetch('end_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    session_id: currentSessionId
                })
            });
            currentSessionId = null;
        }
        
        remoteVideo.srcObject = null;
        nextBtn.disabled = true;
        stopBtn.disabled = true;
        startBtn.disabled = false;
    }
    
    // Kamerayı başlat
    startBtn.addEventListener('click', function() {
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(function(stream) {
                localStream = stream;
                localVideo.srcObject = stream;
                startBtn.disabled = true;
                initializePeer();
            })
            .catch(function(err) {
                console.error('Kamera hatası:', err);
                alert('Kamera ve mikrofon erişimine izin vermelisiniz.');
            });
    });
    
    // Sonraki partner
    nextBtn.addEventListener('click', function() {
        endCall();
        if (peer) {
            findPartner(peer.id);
        }
    });
    
    // Sohbeti bitir
    stopBtn.addEventListener('click', function() {
        endCall();
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
            localVideo.srcObject = null;
        }
        if (peer) {
            peer.destroy();
            peer = null;
        }
        startBtn.disabled = false;
    });
});