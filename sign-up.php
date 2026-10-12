<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include("connection.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uyari = "";

if (isset($_POST["kaydet"])) {
    $name =$_POST["name-surname"];
    $username =$_POST["user-name"];
    $email =$_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $gender =$_POST["gender"];

    // Kullanıcı adı veya e-posta kontrolü
    $kontrol_sorgu = "SELECT `user-name`, email FROM `users` WHERE `user-name` = ? OR email = ?";
    $stmt =$baglanti->prepare($kontrol_sorgu);$stmt->bind_param("ss", $username,$email);
    $stmt->execute();$stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_username, $db_email);$stmt->fetch();
        if ($db_username === $username) {$uyari = "Bu kullanıcı adı şu anda kullanımda, lütfen farklı bir tane deneyin.";
        } elseif ($db_email === $email) {$uyari = "Bu e-posta adresi sistemimizde zaten kayıtlı.";
        }
    } else {
        $verify_token = bin2hex(random_bytes(32));

        $ekle = "INSERT INTO `users` (`name-surname`, `user-name`, `email`, `password`, `gender`, `is_verified`, `verify_token`) VALUES (?, ?, ?, ?, ?, 0, ?)";
        $stmt_ekle =$baglanti->prepare($ekle);$stmt_ekle->bind_param("ssssss", $name,$username, $email,$password, $gender,$verify_token);

        if ($stmt_ekle->execute()) {
            
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'mail.0megle.online';$mail->SMTPAuth   = true;
                $mail->Username   = 'noreply@0megle.online';
                $mail->Password   = 'Q9.mtLD3Rsva3cb';$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('noreply@0megle.online', '0megle.online');
                $mail->addAddress($email, $name);$mail->CharSet = 'UTF-8';

                $mail->isHTML(true);$mail->Subject = '0megle.online\'a Hos Geldiniz - Hesap Onayi';
                $verify_link = "https://0megle.online/verify.php?token=" . $verify_token;
                
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #0d6efd; text-align: center;'>0megle.online'a Hoş Geldiniz!</h2>
                    <p>Merhaba <strong>$name</strong>,</p>
                    <p>0megle.online platformuna katıldığınız için çok mutluyuz. Rastgele görüntülü sohbet etmenin en kolay yolu olan platformumuzu kullanmaya başlamanıza çok az kaldı.</p>
                    <p>Güvenliğiniz ve hesabınızın onaylanması bizim için önemlidir. Hesabınızı aktifleştirmek ve giriş yapabilmek için lütfen aşağıdaki butona tıklayın:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$verify_link' style='background-color: #0d6efd; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Hesabımı Doğrula ve Başla</a>
                    </div>
                    
                    <p style='font-size: 14px;'>Eğer yukarıdaki butona tıklayamıyorsanız, şu bağlantıyı kopyalayıp tarayıcınızın adres çubuğuna yapıştırabilirsiniz:</p>
                    <p style='font-size: 14px; word-break: break-all; background: #f9f9f9; padding: 10px; border-radius: 5px;'><a href='$verify_link'>$verify_link</a></p>
                    
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #777; text-align: center;'>
                        Bu e-posta sistem tarafından otomatik olarak gönderilmiştir. Lütfen yanıtlamayınız.<br>
                        <strong>0megle.online İletişim Ekibi</strong><br>
                        Kayseri / Türkiye
                    </p>
                </div>";

                $mail->AltBody = "Merhaba $name, \n\n0megle.online'a hoş geldiniz! Hesabınızı onaylamak için şu linke tıklayın veya tarayıcınıza yapıştırın: \n$verify_link \n\n0megle.online İletişim Ekibi - Kayseri / Türkiye";

                $mail->send();
                
                echo "<script>alert('Aramıza hoş geldiniz! Lütfen e-posta adresinize gönderilen doğrulama bağlantısına tıklayarak hesabınızı aktifleştirin.'); window.location.href='login.php';</script>";
                exit();
            } catch (Exception $e) {$uyari = "Kayıt işlemi tamamlandı ancak sunucu yoğunluğundan doğrulama e-postası gönderilemedi. Lütfen daha sonra tekrar deneyin.";
            }
        } else {
            $uyari = "Kayıt işlemi sırasında sistemsel bir hata oluştu: " . $stmt_ekle->error;
        }
        $stmt_ekle->close();
    }
    $stmt->close();
    mysqli_close($baglanti);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#111111">
    
    <!-- Temel SEO Optimizasyonu -->
    <title>Kayıt Ol | 0megle.online - Rastgele Görüntülü Sohbet</title>
    <meta name="description" content="0megle.online'a ücretsiz kayıt olun ve dünyanın her yerinden insanlarla anında rastgele görüntülü sohbete başlayın. Yeni arkadaşlar edinmenin en güvenli ve kolay yolu.">
    <meta name="keywords" content="omegle, omegle alternatif, rastgele sohbet, görüntülü sohbet, chat rulet, arkadaş bulma, canlı sohbet, webcam chat">
    <meta name="author" content="0megle.online">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://0megle.online/sign-up.php" />
    
    <!-- Open Graph (Facebook, WhatsApp, LinkedIn Paylaşım Etiketleri) -->
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://0megle.online/sign-up.php">
    <meta property="og:site_name" content="0megle.online">
    <meta property="og:title" content="Kayıt Ol | 0megle.online">
    <meta property="og:description" content="Hemen ücretsiz kayıt olun ve anında rastgele görüntülü sohbete başlayın.">
    <meta property="og:image" itemprop="image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">
    <meta property="og:image:secure_url" itemprop="image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter Card Meta Etiketleri -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="0megle.online - Ücretsiz Kayıt Ol">
    <meta name="twitter:description" content="Dünyanın her yerinden yeni insanlarla tanışın. Güvenli ve ücretsiz rastgele görüntülü sohbet ağı.">
    <meta name="twitter:image" content="https://0megle.online/0megle-online-rastgele-sohbet-logo.jpg">

    <!-- Sekme (Favicon) Logosu -->
    <link rel="icon" href="0megle-online-rastgele-sohbet-logo.jpg?v=2" type="image/jpeg">
    <link rel="apple-touch-icon" href="0megle-online-rastgele-sohbet-logo.jpg?v=2">

    <!-- Yapısal Veri (Schema Markup) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Kayıt Ol | 0megle.online",
      "url": "https://0megle.online/sign-up.php",
      "description": "0megle.online ücretsiz kayıt sayfası. Rastgele görüntülü sohbet ağına katılın.",
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
        /* Modern Glassmorphism Tasarım Ayarları (Daha Şeffaf ve Elegant) */
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
            background-color: #111; /* Video yüklenene kadar gösterilecek yedek renk */
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

        /* Videonun üzerindeki siyah/karartma filtresi (Okunabilirliği ve derinliği artırır) */
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.3) 100%);
            z-index: -99;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.02) !important; /* Maksimum şeffaflık */
            backdrop-filter: blur(8px); /* Bulanıklık düşürüldü ki arkası çok daha net görünsün */
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 24px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            color: white;
            padding: 15px;
            margin: 40px 0;
        }

        .glass-card .card-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding-bottom: 15px;
        }

        /* Yarı saydam giriş kutucukları (Modern görünüm) */
        .glass-input {
            background: rgba(0, 0, 0, 0.2) !important; /* İç kısımlar koyu ve şeffaf yapıldı */
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
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
            background: rgba(0, 0, 0, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
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

        /* Hata Uyarı Kutusu Tasarımı */
        .custom-alert {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 12px;
            color: #ffb3b8;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            font-size: 0.95rem;
        }

        /* Form elemanları ince ayar */
        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.9);
            margin-bottom: 6px;
        }

        .form-check-label {
            font-weight: 400;
            color: rgba(255,255,255,0.85);
            cursor: pointer;
        }
        
        .form-check-input {
            background-color: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.4);
            cursor: pointer;
        }
        
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>

    <!-- Arka Plan Videosu -->
    <video autoplay muted loop id="bg-video">
        <source src="vidyo.mp4" type="video/mp4">
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
                        <h2 class="fw-bold mb-1" style="letter-spacing: 1px;">Hesap Oluştur</h2>
                        <p class="mb-0" style="color: rgba(255,255,255,0.7); font-size: 0.95rem;">Maceraya katılmak için kayıt ol</p>
                    </div>
                    <div class="card-body pt-2">
                        
                        <!-- Uyarı Mesajı -->
                        <?php if(!empty($uyari)): ?>
                            <div class="alert custom-alert p-3 mb-4 text-center">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $uyari; ?>
                            </div>
                        <?php endif; ?>

                        <form action="sign-up.php" method="POST">
                            
                            <div class="mb-3">
                                <label for="name-surname" class="form-label">İsim Soyisim</label>
                                <input type="text" class="form-control glass-input" id="name-surname" name="name-surname" placeholder="Adınız Soyadınız" required>
                            </div>

                            <div class="mb-3">
                                <label for="user-name" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control glass-input" id="user-name" name="user-name" placeholder="Sistemde görünecek adınız" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <input type="email" class="form-control glass-input" id="email" name="email" placeholder="ornek@mail.com" required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control glass-input" id="password" name="password" placeholder="••••••••" required>
                            </div>                            

                            <!-- Cinsiyet Seçimi (Mobil uyumlu grid yapısı) -->
                            <div class="mb-4">
                                <label class="form-label d-block mb-2">Cinsiyet</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="gender-male" value="e" required>
                                        <label class="form-check-label" for="gender-male">Erkek</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="gender-female" value="k" required>
                                        <label class="form-check-label" for="gender-female">Kadın</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="gender-none" value="b" required>
                                        <label class="form-check-label" for="gender-none">Belirtmek İstemiyorum</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mb-4 mt-2">
                                <button type="submit" name="kaydet" class="btn btn-glass-primary">Üye Ol</button>
                            </div>
                        </form>

                        <!-- Google ile Hızlı Kayıt (Ortalanmış) -->
                        <div class="text-center position-relative mt-4 mb-3">
                            <hr style="border-color: rgba(255,255,255,0.2);">
                            <span class="position-absolute top-50 start-50 translate-middle px-3" style="background-color: transparent; color: rgba(255,255,255,0.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">
                                Veya
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-center w-100">
                            <!-- GERÇEK GOOGLE BUTONU -->
                            <div class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="outline" data-text="signup_with" data-size="large" data-logo_alignment="center"></div>
                        </div>

                        <!-- Giriş Yap Yönlendirmesi -->
                        <div class="text-center mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                            <p class="mb-0" style="color: rgba(255,255,255,0.8);">Zaten bir hesabınız var mı? <a href="login.php">Giriş Yapın</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- Google Doğrulama ve Yönlendirme İşlemleri -->
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
                 alert("Google ile kayıt olunamadı! Hata: " + (data.message || "Bilinmeyen Hata"));
             }
         })
         .catch(err => {
             console.error("Fetch hatası:", err);
             alert("Sunucuya bağlanılamadı. Lütfen internet bağlantınızı ve geliştirici konsolunu (F12) kontrol edin.");
         });
      }
    </script>
</body>
</html>