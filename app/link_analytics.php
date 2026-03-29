<?php
require_once 'config.php';
require_once 'helpers.php';
require_once 'branding.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

$id = $_GET['id'] ?? null;
if (!$id) die("No link ID provided.");

$stmt = $pdo->prepare("SELECT * FROM links WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$link = $stmt->fetch();
if (!$link) die("Link not found.");

$clicksStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM clicks WHERE link_id = ?");
$clicksStmt->execute([$id]);
$totalClicks = $clicksStmt->fetch()['cnt'];

$countriesStmt = $pdo->prepare("SELECT country, COUNT(*) as cnt FROM clicks WHERE link_id = ? GROUP BY country ORDER BY cnt DESC");
$countriesStmt->execute([$id]);
$countries = $countriesStmt->fetchAll();

$devicesStmt = $pdo->prepare("SELECT device_type, COUNT(*) as cnt FROM clicks WHERE link_id = ? GROUP BY device_type ORDER BY cnt DESC");
$devicesStmt->execute([$id]);
$devices = $devicesStmt->fetchAll();
$topDevice = !empty($devices) ? $devices[0]['device_type'] : "None";

$logStmt = $pdo->prepare("SELECT ip_address, country, device_type, referer, clicked_at FROM clicks WHERE link_id = ? ORDER BY clicked_at DESC LIMIT 50");
$logStmt->execute([$id]);
$logs = $logStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Analytics | <?= $brand_name ?></title>
    <?php if ($brand_favicon): ?><link rel="icon" href="<?= $brand_favicon ?>"><?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .data-table th { text-align: left; padding: 12px; color: var(--text-secondary); border-bottom: 1px solid rgba(255,255,255,0.08); }
        .data-table td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .data-table tbody tr { transition: background 0.15s; }
        .data-table tbody tr:hover { background: rgba(255,255,255,0.03); }
        .log-table th, .log-table td { font-size: 13px; }
        .cat-box { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.08); transition: all 0.3s; }
        .cat-box:hover { border-color: rgba(255,255,255,0.12); box-shadow: 0 4px 16px rgba(0,0,0,0.2); }
        .cat-title { font-size: 15px; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .country-row { display: flex; align-items: center; gap: 10px; }
        .device-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;
        }
        .device-badge.desktop { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .device-badge.mobile { background: rgba(16,185,129,0.1); color: #10b981; }
        .device-badge.tablet { background: rgba(245,158,11,0.1); color: #f59e0b; }
        @media (max-width: 768px) { .analytics-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content" style="padding: 20px 30px;">
    <div class="top-header" style="margin-bottom:20px;">
        <div class="page-title">
            <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div style="display:flex; align-items:center; gap: 15px;">
                <a href="links.php" class="btn-secondary" style="padding: 8px 12px;"><i class="fa-solid fa-arrow-left"></i></a>
                <div>
                    <h1>go.yourdomain.com/<?= htmlspecialchars($link['short_code']) ?></h1>
                    <p style="max-width:400px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">→ <?= htmlspecialchars($link['original_url']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom:20px;">
        <div class="glass stat-card blue">
            <div class="stat-header">Total Clicks <i class="fa-solid fa-mouse"></i></div>
            <div class="stat-value"><?= number_format($totalClicks) ?></div>
        </div>
        <div class="glass stat-card purple">
            <div class="stat-header">Unique Countries <i class="fa-solid fa-globe"></i></div>
            <div class="stat-value"><?= count($countries) ?></div>
        </div>
        <div class="glass stat-card green">
            <div class="stat-header">Top Device <i class="fa-solid fa-mobile-screen"></i></div>
            <div class="stat-value" style="font-size:24px;"><?= htmlspecialchars($topDevice) ?></div>
        </div>
    </div>

    <div class="analytics-grid">
        <!-- Countries with Flags -->
        <div class="cat-box">
            <div class="cat-title"><i class="fa-solid fa-earth-americas" style="color:var(--accent-blue)"></i> Clicks by Country</div>
            <table class="data-table">
                <tr><th>Country</th><th style="text-align:right">Clicks</th></tr>
                <?php if(empty($countries)): ?><tr><td colspan="2" style="text-align:center; color:var(--text-secondary)">No data</td></tr><?php endif; ?>
                <?php foreach($countries as $c): ?>
                <tr>
                    <td>
                        <span class="country-row">
                            <?= getCountryFlag($c['country'], 20) ?>
                            <?= htmlspecialchars(getCountryName($c['country'])) ?>
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:600;"><?= $c['cnt'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Devices with Badges -->
        <div class="cat-box">
            <div class="cat-title"><i class="fa-solid fa-laptop" style="color:var(--accent-purple)"></i> Clicks by Device</div>
            <table class="data-table">
                <tr><th>Device Type</th><th style="text-align:right">Clicks</th></tr>
                <?php if(empty($devices)): ?><tr><td colspan="2" style="text-align:center; color:var(--text-secondary)">No data</td></tr><?php endif; ?>
                <?php foreach($devices as $d): ?>
                <?php $dt = strtolower($d['device_type'] ?: 'desktop'); $icon = $dt === 'mobile' ? 'fa-mobile-screen' : ($dt === 'tablet' ? 'fa-tablet-screen-button' : 'fa-desktop'); ?>
                <tr>
                    <td>
                        <span class="device-badge <?= $dt ?>">
                            <i class="fa-solid <?= $icon ?>"></i> <?= ucfirst($dt) ?>
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:600;"><?= $d['cnt'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- Raw Logs with Flags -->
    <div class="cat-box" style="overflow-x:auto;">
        <div class="cat-title"><i class="fa-solid fa-list-ul"></i> Recent Click Log (Last 50)</div>
        <table class="data-table log-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>IP Address</th>
                    <th>Country</th>
                    <th>Device</th>
                    <th>Referer</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($logs)): ?><tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-secondary)">No clicks recorded yet.</td></tr><?php endif; ?>
                <?php foreach($logs as $log): ?>
                <?php $dt = strtolower($log['device_type'] ?: 'desktop'); $icon = $dt === 'mobile' ? 'fa-mobile-screen' : ($dt === 'tablet' ? 'fa-tablet-screen-button' : 'fa-desktop'); ?>
                <tr>
                    <td style="color:var(--text-secondary); white-space:nowrap;"><?= date('M j, g:i A', strtotime($log['clicked_at'])) ?></td>
                    <td style="font-family:monospace; font-size:12px; color:var(--text-secondary);"><?= htmlspecialchars($log['ip_address']) ?></td>
                    <td>
                        <span class="country-row">
                            <?= getCountryFlag($log['country'], 16) ?>
                            <?= htmlspecialchars(getCountryName($log['country'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="device-badge <?= $dt ?>">
                            <i class="fa-solid <?= $icon ?>"></i> <?= ucfirst($dt) ?>
                        </span>
                    </td>
                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text-secondary);" title="<?= htmlspecialchars($log['referer'] ?? '') ?>">
                        <?= htmlspecialchars($log['referer'] ?: 'Direct / None') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html>
