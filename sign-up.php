<?php
session_start();
include("connection.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
        'title' => '0megle.online | Üye Ol',
        'heading' => 'Hesap Oluştur',
        'name_surname' => 'İsim Soyisim',
        'username' => 'Kullanıcı Adı',
        'email' => 'E-posta',
        'password' => 'Şifre',
        'gender' => 'Cinsiyet',
        'male' => 'Erkek',
        'female' => 'Kadın',
        'unspecified' => 'Belirtmek İstemiyorum',
        'btn_signup' => 'Üye Ol',
        'or' => 'veya',
        'google' => 'Google ile Kayıt Ol',
        'already_member' => 'Zaten üye misiniz?',
        'login' => 'Giriş Yap',
        'err_username' => 'Bu kullanıcı adı kullanılıyor!',
        'err_email' => 'Bu e-posta adresi zaten kayıtlı! Giriş Yap ekranındaki şifre sıfırlama seçeneğini kullanabilirsiniz.',
        'err_fail' => 'Kayıt başarısız: ',
        'home_link' => 'Anasayfaya Dön'
    ],
    'en' => [
        'title' => '0megle.online | Sign Up',
        'heading' => 'Create Account',
        'name_surname' => 'Full Name',
        'username' => 'Username',
        'email' => 'Email Address',
        'password' => 'Password',
        'gender' => 'Gender',
        'male' => 'Male',
        'female' => 'Female',
        'unspecified' => 'Prefer not to say',
        'btn_signup' => 'Sign Up',
        'or' => 'or',
        'google' => 'Sign Up with Google',
        'already_member' => 'Already have an account?',
        'login' => 'Log In',
        'err_username' => 'This username is already taken!',
        'err_email' => 'This email is already registered! You can use the forgot password option on the login page.',
        'err_fail' => 'Registration failed: ',
        'home_link' => 'Back to Home'
    ]
];
$lang = $texts[$currentLang];

/* ---------------- KAYIT İŞLEMİ ---------------- */
$uyari = "";

if (isset($_POST["kaydet"])) {
    $name = trim($_POST["name-surname"]);
    $username = trim($_POST["user-name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $gender = $_POST["gender"];

    // Kullanıcı adı veya e-posta kontrolü
    $kontrol_sorgu = "SELECT `user-name`, email FROM `users` WHERE `user-name` = ? OR email = ?";
    $stmt = $baglanti->prepare($kontrol_sorgu);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_username, $db_email);
        $stmt->fetch();
        if (strtolower($db_username) === strtolower($username)) {
            $uyari = $lang['err_username'];
        } elseif (strtolower($db_email) === strtolower($email)) {
            $uyari = $lang['err_email'];
        }
    } else {
        // Yeni kullanıcıyı ekle
        $ekle = "INSERT INTO `users` (`name-surname`, `user-name`, `email`, `password`, `gender`) VALUES (?, ?, ?, ?, ?)";
        $stmt = $baglanti->prepare($ekle);
        $stmt->bind_param("sssss", $name, $username, $email, $password, $gender);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $uyari = $lang['err_fail'] . $stmt->error;
        }
    }
    $stmt->close();
    mysqli_close($baglanti);
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= $lang['title'] ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
    body {
        margin: 0;
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #000;
        padding: 20px 0;
    }
    
   #background-video {
    position: fixed;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
    /* object-fit: cover yerine contain veya doğrudan cover bırakıp konumlandıracağız */
    object-fit: cover; 
    /* Videoyu üst merkeze hizalar, böylece tepeden aşağı doğru odaklanır */
    object-position: center top; 
}

    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: -1;
    }

    .card {
        background: rgba(20, 20, 20, 0.65);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        width: 100%;
        max-width: 450px;
        color: #fff;
        padding: 2.5rem !important;
    }

    .brand-logo {
        text-align: center;
        margin-bottom: 1.5rem;
        font-weight: 600;
        font-size: 1.5rem;
        letter-spacing: 1px;
    }

    .text-dodgerblue { color: #1e90ff; }

    .form-control {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 30px;
        padding: 12px 15px 12px 45px;
        color: #fff;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: #1e90ff;
        color: #fff;
        box-shadow: 0 0 0 0.25rem rgba(30, 144, 255, 0.25);
    }

    .input-icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 18px;
        color: rgba(255, 255, 255, 0.6);
    }

    /* Radio Button Özelleştirmesi */
    .form-check-label {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
        cursor: pointer;
    }
    
    .form-check-input {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.4);
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: #1e90ff;
        border-color: #1e90ff;
    }

    .gender-label-main {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 8px;
        display: block;
    }

    .btn-primary {
        background: #1e90ff;
        border: none;
        border-radius: 30px;
        padding: 10px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-primary:hover {
        background: #0073e6;
        transform: translateY(-2px);
    }

    .btn-google {
        background-color: #fff;
        color: #333;
        border-radius: 30px;
        padding: 10px;
        font-weight: 500;
        transition: 0.3s;
    }

    .btn-google:hover {
        background-color: #f1f1f1;
        color: #000;
    }

    a {
        color: #1e90ff;
        text-decoration: none;
        transition: 0.3s;
    }

    a:hover {
        color: #66b2ff;
    }
</style>
</head>
<body>

<video autoplay muted loop playsinline id="background-video">
    <source src="vidyo.mp4" type="video/mp4">
</video>
<div class="overlay"></div>

<div class="card">
    <div class="brand-logo">
        <a href="index.html" class="text-white" style="text-decoration: none;">
            0MEGLE<span class="text-dodgerblue">.ONLINE</span>
        </a>
    </div>

    <h3 class="text-center mb-4 font-weight-bold"><?= $lang['heading'] ?></h3>

    <?php if ($uyari): ?>
        <div class="alert alert-danger" style="border-radius: 10px; font-size: 0.9rem;">
            <?= htmlspecialchars($uyari) ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="mb-3 position-relative">
            <i class="fas fa-id-card input-icon"></i>
            <input type="text" class="form-control" name="name-surname" placeholder="<?= $lang['name_surname'] ?>" required />
        </div>

        <div class="mb-3 position-relative">
            <i class="fas fa-user input-icon"></i>
            <input type="text" class="form-control" name="user-name" placeholder="<?= $lang['username'] ?>" required />
        </div>
        
        <div class="mb-3 position-relative">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" class="form-control" name="email" placeholder="<?= $lang['email'] ?>" required />
        </div>

        <div class="mb-3 position-relative">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" class="form-control" name="password" placeholder="<?= $lang['password'] ?>" required />
        </div>

        <div class="mb-4">
            <label class="gender-label-main"><i class="fas fa-venus-mars me-2" style="color: rgba(255,255,255,0.6);"></i><?= $lang['gender'] ?></label>
            <div class="d-flex justify-content-between px-1">
                <div class="form-check form-check-inline m-0">
                    <input class="form-check-input" type="radio" name="gender" id="gender-male" value="e" required>
                    <label class="form-check-label" for="gender-male"><?= $lang['male'] ?></label>
                </div>
                <div class="form-check form-check-inline m-0">
                    <input class="form-check-input" type="radio" name="gender" id="gender-female" value="k" required>
                    <label class="form-check-label" for="gender-female"><?= $lang['female'] ?></label>
                </div>
                <div class="form-check form-check-inline m-0">
                    <input class="form-check-input" type="radio" name="gender" id="gender-none" value="b" required>
                    <label class="form-check-label" for="gender-none"><?= $lang['unspecified'] ?></label>
                </div>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" name="kaydet" class="btn btn-primary"><?= $lang['btn_signup'] ?></button>
        </div>
    </form>

    <div class="text-center my-3 text-white-50" style="font-size: 0.85rem;">
        <?= $lang['or'] ?>
    </div>

    <div class="d-grid mb-4">
        <a href="#" class="btn btn-google">
            <i class="fab fa-google text-danger me-2"></i> <?= $lang['google'] ?>
        </a>
    </div>

    <div class="text-center mt-2" style="font-size: 0.95rem;">
        <span class="text-white-50"><?= $lang['already_member'] ?></span> 
        <a href="login.php" class="fw-bold ms-1"><?= $lang['login'] ?></a>
    </div>
    
    <div class="text-center mt-3">
        <a href="index.html" class="text-white-50" style="font-size: 0.8rem;">
            <i class="fas fa-arrow-left me-1"></i> <?= $lang['home_link'] ?>
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>