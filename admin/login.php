<?php
require_once '../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        // Regenerate session ID for security
        session_regenerate_id(true);

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();

        header('Location: index.php');
        exit();
    } else {
        $error = 'Kullanıcı adı veya şifre hatalı!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: #151b35;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            border: 2px solid transparent;
            background-image: linear-gradient(#151b35, #151b35),
                            linear-gradient(135deg, #fbbf24, #f59e0b, #f97316);
            background-origin: border-box;
            background-clip: padding-box, border-box;
        }

        .login-title {
            font-size: 2rem;
            color: #ffffff;
            margin-bottom: 2rem;
            text-align: center;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: #a0a0a0;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0a0e27;
            border: 2px solid #1a1f3a;
            border-radius: 8px;
            color: #ffffff;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #f59e0b;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #f97316);
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .back-link {
            display: block;
            text-align: center;
            color: #a0a0a0;
            margin-top: 1rem;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">Admin Paneli</h1>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text"
                       name="username"
                       class="form-input"
                       required
                       autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label">Şifre</label>
                <input type="password"
                       name="password"
                       class="form-input"
                       required
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Giriş Yap</button>
        </form>

        <a href="../index.php" class="back-link">← Ana Sayfaya Dön</a>
    </div>
</body>
</html>
