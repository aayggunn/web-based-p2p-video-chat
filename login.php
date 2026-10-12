<?php
session_start();
include("connection.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$uyari = ""; 

if (isset($_POST["giris"])) {
    $username =$_POST["user-name"];
    $password =$_POST["password"];

    if (!empty($username) && !empty($password)) {
        // is_verified sütununu da çekiyoruz
        $secim = "SELECT `member-id`, `user-name`, `email`, `password`, `is_verified` FROM `users` WHERE LOWER(`user-name`) = LOWER(?)";
        $calistir = mysqli_prepare($baglanti,$secim);

        if ($calistir) {
            mysqli_stmt_bind_param($calistir, "s", $username);
            mysqli_stmt_execute($calistir);
            mysqli_stmt_bind_result($calistir,$member_id, $user_name,$email, $hashed_password,$is_verified);

            if (mysqli_stmt_fetch($calistir)) {
                if (password_verify($password,$hashed_password)) {
                    
                    // MAİL ONAY KONTROLÜ
                    if ($is_verified == 0) {$uyari = "Lütfen giriş yapmadan önce e-posta adresinize gönderilen linke tıklayarak hesabınızı doğrulayın.";
                    } else {
                        $_SESSION["member-id"] = $member_id;
                        $_SESSION["user-name"] = $user_name;
                        $_SESSION["email"] = $email;
                        header("location: home-page.php");
                        exit();
                    }
                    
                } else {
                    $uyari = "Şifre yanlış.";
                }
            } else {
                $uyari = "Kullanıcı adı veya şifre yanlış.";
            }
        }
    } else {
        $uyari = "Lütfen kullanıcı adı ve şifre alanlarını doldurun.";
    }
}
mysqli_close($baglanti);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#111111">
    
    <!-- Temel SEO Optimizasyonu -->
    <title>Giriş Yap | 0megle.online - Rastgele Görüntülü Sohbet</title>
    <meta name="description" content="0megle.online hesabınıza giriş yapın ve dünyanın her yerinden insanlarla anında rastgele görüntülü sohbete devam edin.">
    <meta name="keywords" content="omegle giriş, rastgele sohbet, görüntülü sohbet, chat rulet, arkadaş bulma, canlı sohbet, webcam chat">
    <meta name="author" content="0megle.online">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://0megle.online/login.php" />

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn Paylaşım Etiketleri) -->
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://0megle.online/login.php">
    <meta property="og:site_name" content="0megle.online">
    <meta property="og:title" content="Giriş Yap | 0megle.online">
    <meta property="og:description" content="Hesabınıza giriş yapın ve sohbete kaldığınız yerden devam edin.">
    <meta property="og:image" itemprop="image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">
    <meta property="og:image:secure_url" itemprop="image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    
    <!-- Twitter Card Meta Etiketleri -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="0megle.online - Giriş Yap">
    <meta name="twitter:description" content="Hesabınıza giriş yapın ve dünyayla sohbet edin.">
    <meta name="twitter:image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">

    <!-- Sekme (Favicon) Logosu -->
    <link rel="icon" href="0megle-online-rastgele-sohbet-logo.jpg" type="image/jpeg">
    <link rel="apple-touch-icon" href="0megle-online-rastgele-sohbet-logo.jpg">
    
    <!-- Yapısal Veri (Schema Markup) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Giriş Yap | 0megle.online",
      "url": "https://0megle.online/login.php",
      "description": "0megle.online rastgele görüntülü sohbet ağına giriş yapın.",
      "inLanguage": "tr-TR"
    }
    </script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google'ın kimlik doğrulama kütüphanesi -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        /* Modern Glassmorphism Tasarım Ayarları */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #ffffff; 
            overflow-x: hidden;
            background-color: #111; 
        }

        /* Arka Plan Videosu - Tam Ekran Kaplama */
        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -100;
            object-fit: cover;
        }

        /* Videonun üzerindeki siyah/karartma filtresi */
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.3) 100%);
            z-index: -99;
        }

        /* Transparanlık artırılmış Buzlu Cam Kart */
        .glass-card {
            background: rgba(255, 255, 255, 0.02) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 24px !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            color: white;
            padding: 15px;
            margin: 20px 0;
        }

        .glass-card .card-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding-bottom: 15px;
        }

        /* Yarı saydam giriş kutucukları */
        .glass-input {
            background: rgba(0, 0, 0, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .glass-input::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 0.95rem;
        }

        .glass-input:focus {
            background: rgba(0, 0, 0, 0.3) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1) !important;
            outline: none;
        }

        /* Özel Ana Buton Tasarımı */
        .btn-glass-primary {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.8), rgba(0, 86, 179, 0.9));
            border: none;
            color: white;
            border-radius: 12px;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }

        .btn-glass-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.5);
            color: white;
        }

        /* Link Renkleri */
        .glass-card a {
            color: #8bb9fe;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        
        .glass-card a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        /* Hata Uyarı Kutusu */
        .custom-alert {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 12px;
            color: #ffb3b8;
            backdrop-filter: blur(5px);
            font-size: 0.95rem;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255,255,255,0.9);
            margin-bottom: 6px;
        }

        .form-check-label {
            font-weight: 400;
            color: rgba(255,255,255,0.85);
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- Arka Plan Videosu -->
    <video autoplay muted loop id="bg-video">
        <source src="video2.mp4" type="video/mp4">
        Tarayıcınız video arka plan özelliğini desteklemiyor.
    </video>
    
    <!-- Karartma Katmanı -->
    <div class="video-overlay"></div>

    <!-- Google Login Arka Plan Veri Aktarımı -->
    <div id="g_id_onload"
         data-client_id="1022652530736-b1he3ojt2h6a49496kideou1dhf5coka.apps.googleusercontent.com"
         data-callback="handleCredentialResponse">
    </div>

    <div class="container w-100">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-10 col-md-8 col-lg-5">
                <div class="card glass-card">
                    <div class="card-header text-center border-0 pt-3">
                        <h2 class="fw-bold mb-1" style="letter-spacing: 1px;">Giriş Yap</h2>
                        <p class="mb-0" style="color: rgba(255,255,255,0.7); font-size: 0.95rem;">Maceraya kaldığın yerden devam et</p>
                    </div>
                    <div class="card-body pt-2">
                        
                        <!-- Uyarı Mesajı Gösterimi -->
                        <?php if(!empty($uyari)): ?>
                            <div class="alert custom-alert p-3 mb-4 text-center">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $uyari; ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            
                            <!-- Kullanıcı Adı -->
                            <div class="mb-3">
                                <label for="user-name" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control glass-input" id="user-name" name="user-name" placeholder="Kullanıcı adınızı girin" required>
                            </div>

                            <!-- Şifre -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control glass-input" id="password" name="password" placeholder="••••••••" required>
                            </div>

                            <!-- Beni Hatırla ve Şifremi Unuttum -->
                            <div class="mb-4 d-flex justify-content-between align-items-center px-1">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="remember-me" style="background-color: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.4);">
                                    <label class="form-check-label" for="remember-me" style="font-size: 0.9rem;">Beni Hatırla</label>
                                </div>
                                <a href="sifre-sifirla-talep.php" style="font-size: 0.9rem;">Şifremi Unuttum</a>
                            </div>

                            <!-- Giriş Yap Butonu -->
                            <div class="d-grid mb-4 mt-2">
                                <button type="submit" name="giris" class="btn btn-glass-primary">Giriş Yap</button>
                            </div>
                        </form>

                        <!-- Google ile Hızlı Giriş (Ortalanmış) -->
                        <div class="text-center position-relative mt-4 mb-3">
                            <hr style="border-color: rgba(255,255,255,0.2);">
                            <span class="position-absolute top-50 start-50 translate-middle px-3" style="background-color: transparent; color: rgba(255,255,255,0.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">
                                Veya
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-center w-100">
                            <!-- GERÇEK GOOGLE BUTONU -->
                            <div class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="outline" data-text="signin_with" data-size="large" data-logo_alignment="center"></div>
                        </div>

                        <!-- Hesabın Yok Mu? -->
                        <div class="text-center mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Hesabınız Yok Mu? <a href="sign-up.php">Kaydol</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- Google Login İşlemini Yapan Script -->
    <script>
      function handleCredentialResponse(response) {
         const jwtToken = response.credential;
         
         fetch('google_login.php', {
             method: 'POST',
             headers: {
                 'Content-Type': 'application/json'
             },
             body: JSON.stringify({ token: jwtToken })
         })
         .then(res => res.json())
         .then(data => {
             if(data.success) {
                 window.location.href = "home-page.php"; 
             } else {
                 alert("Hata: " + (data.message || "Bilinmeyen Hata"));
             }
         })
         .catch(err => {
             console.error("Fetch hatası:", err);
             alert("Sunucuya bağlanılamadı.");
         });
      }
    </script>
</body>
</html>