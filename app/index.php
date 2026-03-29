<?php
require_once 'config.php';
require_once 'branding.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Both username and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= $brand_name ?></title>
    <?php if ($brand_favicon): ?><link rel="icon" href="<?= $brand_favicon ?>"><?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="login-container">
    <div class="login-orb-1"></div>
    <div class="login-orb-2"></div>
    
    <div class="glass login-card">
        <div class="login-logo">
            <?php if ($brand_logo): ?>
                <img src="<?= $brand_logo ?>" alt="Logo" style="width:36px; height:36px; border-radius:8px;">
            <?php else: ?>
                <i class="fa-solid fa-chart-line icon-gradient"></i>
            <?php endif; ?>
            <span><?= $brand_name ?></span>
        </div>
        
        <p style="color:var(--text-secondary); margin-bottom: 24px; font-size:14px;">Sign in to access premium analytics.</p>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="admin" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
