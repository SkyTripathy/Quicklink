<?php
require_once 'config.php';
require_once 'branding.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $error = 'All password fields are required.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (password_verify($current, $user['password_hash'])) {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $update->execute([$newHash, $_SESSION['user_id']]);
                $success = 'Password updated successfully!';
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}

// Get stats for info display
$linkCount = $pdo->prepare("SELECT COUNT(*) as cnt FROM links WHERE user_id = ?");
$linkCount->execute([$_SESSION['user_id']]);
$totalLinks = $linkCount->fetch()['cnt'];

$clickCount = $pdo->prepare("SELECT COUNT(*) as cnt FROM clicks c JOIN links l ON c.link_id = l.id WHERE l.user_id = ?");
$clickCount->execute([$_SESSION['user_id']]);
$totalClicks = $clickCount->fetch()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | <?= $brand_name ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .settings-card {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; padding: 24px;
        }
        .settings-card-title {
            font-size: 16px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        }
        .settings-card-title i { color: var(--accent-blue); }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .info-label { color: var(--text-secondary); font-size: 14px; }
        .info-value { font-weight: 600; font-size: 14px; }

        .success-msg {
            color: #10b981; background: rgba(16,185,129,0.1); padding: 12px; border-radius: 8px; margin-bottom: 20px;
            font-size: 14px; border: 1px solid rgba(16,185,129,0.2);
        }

        @media (max-width: 768px) { .settings-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content" style="padding: 20px 30px;">
    <div class="top-header" style="margin-bottom: 24px;">
        <div class="page-title">
            <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Settings</h1>
            <p>Manage your account and tracker preferences.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success-msg"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="settings-grid">
        <!-- Account Info -->
        <div class="settings-card">
            <div class="settings-card-title"><i class="fa-solid fa-user"></i> Account Info</div>
            <div class="info-row">
                <span class="info-label">Username</span>
                <span class="info-value"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Links</span>
                <span class="info-value" style="color:#3b82f6;"><?= number_format($totalLinks) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Clicks Tracked</span>
                <span class="info-value" style="color:#10b981;"><?= number_format($totalClicks) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Dashboard URL</span>
                <span class="info-value" style="color:var(--accent-blue); font-size:12px;">app.yourdomain.com</span>
            </div>
            <div class="info-row" style="border:none;">
                <span class="info-label">Tracker URL</span>
                <span class="info-value" style="color:var(--accent-blue); font-size:12px;">go.yourdomain.com</span>
            </div>
        </div>

        <!-- Change Password -->
        <div class="settings-card">
            <div class="settings-card-title"><i class="fa-solid fa-lock"></i> Change Password</div>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-input" placeholder="Min 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-input" required>
                </div>
                <button type="submit" class="btn-primary" style="margin-top:10px;">Update Password</button>
            </form>
        </div>

        <!-- Tracker Domain Info -->
        <div class="settings-card">
            <div class="settings-card-title"><i class="fa-solid fa-server"></i> Tracker Info</div>
            <div class="info-row">
                <span class="info-label">Tracking Engine</span>
                <span class="info-value" style="color:#10b981;">Active <i class="fa-solid fa-circle" style="font-size:6px;"></i></span>
            </div>
            <div class="info-row">
                <span class="info-label">Geo Location</span>
                <span class="info-value">ip-api.com</span>
            </div>
            <div class="info-row">
                <span class="info-label">Database</span>
                <span class="info-value">MySQL (Hostinger)</span>
            </div>
            <div class="info-row" style="border:none;">
                <span class="info-label">Platform</span>
                <span class="info-value"><?= $brand_name ?> v1.0</span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="settings-card">
            <div class="settings-card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</div>
            <a href="create.php" class="btn-primary" style="display:block; text-align:center; margin-bottom:12px;">
                <i class="fa-solid fa-plus"></i> Create New Link
            </a>
            <a href="clicks.php" class="btn-secondary" style="display:block; text-align:center; margin-bottom:12px; padding:12px;">
                <i class="fa-solid fa-mouse-pointer"></i> View Click Log
            </a>
            <a href="links.php" class="btn-secondary" style="display:block; text-align:center; padding:12px;">
                <i class="fa-solid fa-link"></i> Manage Links
            </a>
        </div>
    </div>
</main>
</body>
</html>
