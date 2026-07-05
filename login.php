<?php
session_start();
include("connection.php");

/* ---------------- DİL SİSTEMİ ---------------- */
// Kullanıcının tarayıcı dilini PHP üzerinden algılama
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'tr', 0, 2);
    $_SESSION['lang'] = ($browserLang == 'tr') ? 'tr' : 'en';
}
$currentLang = $_SESSION['lang'];

$texts = [
    'tr' => [
        'title' => '0megle.online | Giriş Yap',
        'login' => 'Giriş Yap',
        'username' => 'Kullanıcı Adı',
        'password' => 'Şifre',
        'remember' => 'Beni Hatırla',
        'forgot' => 'Şifremi Unuttum?',
        'or' => 'veya',
        'google' => 'Google ile Giriş Yap',
        'no_account' => 'Hesabın Yok Mu?',
        'signup' => 'Kaydol',
        'wrong_password' => 'Şifre yanlış.',
        'wrong_credentials' => 'Kullanıcı adı veya şifre yanlış.',
        'fill_all' => 'Lütfen tüm alanları doldurun.',
        'home_link' => 'Anasayfaya Dön'
    ],
    'en' => [
        'title' => '0megle.online | Login',
        'login' => 'Login',
        'username' => 'Username',
        'password' => 'Password',
        'remember' => 'Remember Me',
        'forgot' => 'Forgot Password?',
        'or' => 'or',
        'google' => 'Login with Google',
        'no_account' => 'Don’t have an account?',
        'signup' => 'Sign Up',
        'wrong_password' => 'Wrong password.',
        'wrong_credentials' => 'Invalid username or password.',
        'fill_all' => 'Please fill in all fields.',
        'home_link' => 'Back to Home'
    ]
];
$lang = $texts[$currentLang];

/* ---------------- NORMAL GİRİŞ ---------------- */
$uyari = "";
if (isset($_POST["giris"])) {
    $username = trim($_POST["user-name"]);
    $password = $_POST["password"];

    if (!empty($username) && !empty($password)) {
        $secim = "SELECT `member-id`, `user-name`, `email`, `password` FROM `users` WHERE LOWER(`user-name`) = LOWER(?)";
        $calistir = mysqli_prepare($baglanti, $secim);
        mysqli_stmt_bind_param($calistir, "s", $username);
        mysqli_stmt_execute($calistir);
        mysqli_stmt_bind_result($calistir, $member_id, $user_name, $email, $hashed_password);

        if (mysqli_stmt_fetch($calistir)) {
            if (password_verify($password, $hashed_password)) {
                $_SESSION["member-id"] = $member_id;
                $_SESSION["user-name"] = $user_name;
                $_SESSION["email"] = $email;
                header("location: home-page.php");
                exit();
            } else {
                $uyari = $lang['wrong_password'];
            }
        } else {
            $uyari = $lang['wrong_credentials'];
        }
    } else {
        $uyari = $lang['fill_all'];
    }
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
        background-color: #000; /* Video yüklenmeden önceki arka plan */
    }
    
    #background-video {
        position: fixed;
        right: 0;
        bottom: 0;
        min-width: 100%;
        min-height: 100%;
        width: auto;
        height: auto;
        z-index: -2;
        object-fit: cover;
    }

    /* Anasayfaya benzer karanlık filtre */
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: -1;
    }

    /* Modern Glassmorphism Kart Tasarımı */
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

    .btn-google img {
        width: 18px;
        margin-right: 8px;
        vertical-align: middle;
    }

    a {
        color: #1e90ff;
        text-decoration: none;
        transition: 0.3s;
    }

    a:hover {
        color: #66b2ff;
    }

    .form-check-label {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
    }

    hr {
        border-color: rgba(255, 255, 255, 0.2);
    }
</style>
</head>
<body>

<video autoplay muted loop playsinline id="background-video">
    <source src="video2.mp4" type="video/mp4">
</video>
<div class="overlay"></div>

<div class="card">
    <div class="brand-logo">
        <a href="index.html" class="text-white" style="text-decoration: none;">
            0MEGLE<span class="text-dodgerblue">.ONLINE</span>
        </a>
    </div>

    <h3 class="text-center mb-4 font-weight-bold"><?= $lang['login'] ?></h3>

    <?php if ($uyari): ?>
        <div class="alert alert-danger" style="border-radius: 10px; font-size: 0.9rem;">
            <?= htmlspecialchars($uyari) ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="mb-3 position-relative">
            <i class="fas fa-user input-icon"></i>
            <input type="text" class="form-control" name="user-name" placeholder="<?= $lang['username'] ?>" required />
        </div>
        
        <div class="mb-4 position-relative">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" class="form-control" name="password" placeholder="<?= $lang['password'] ?>" required />
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 px-1">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember-me">
                <label class="form-check-label" for="remember-me">
                    <?= $lang['remember'] ?>
                </label>
            </div>
            <a href="sifre-sifirla-talep.php" style="font-size: 0.9rem;"><?= $lang['forgot'] ?></a>
        </div>

        <div class="d-grid">
            <button type="submit" name="giris" class="btn btn-primary"><?= $lang['login'] ?></button>
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
        <span class="text-white-50"><?= $lang['no_account'] ?></span> 
        <a href="sign-up.php" class="fw-bold ms-1"><?= $lang['signup'] ?></a>
    </div>
    
    <div class="text-center mt-3">
        <a href="index.html" class="text-white-50" style="font-size: 0.8rem;">
            <i class="fas fa-arrow-left me-1"></i> <?= $lang['home_link'] ?>
        </a>
    </div>
</div>

</body>
</html>