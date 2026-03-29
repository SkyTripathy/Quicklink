<?php
require_once 'config.php';
require_once 'helpers.php';
require_once 'branding.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

// Fetch all clicks for user's links
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Total count
$countStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM clicks c JOIN links l ON c.link_id = l.id WHERE l.user_id = ?");
$countStmt->execute([$_SESSION['user_id']]);
$total = $countStmt->fetch()['cnt'];
$total_pages = max(1, ceil($total / $per_page));

// Fetch clicks
$stmt = $pdo->prepare("
    SELECT c.*, l.short_code, l.original_url, l.utm_source, l.utm_campaign 
    FROM clicks c 
    JOIN links l ON c.link_id = l.id 
    WHERE l.user_id = ? 
    ORDER BY c.clicked_at DESC 
    LIMIT $per_page OFFSET $offset
");
$stmt->execute([$_SESSION['user_id']]);
$clicks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Click Log | <?= $brand_name ?></title>
    <?php if ($brand_favicon): ?><link rel="icon" href="<?= $brand_favicon ?>"><?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .log-stats { display: flex; gap: 12px; margin-bottom: 20px; }
        .log-stat {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px; padding: 14px 20px; flex: 1; text-align: center;
        }
        .log-stat-val { font-size: 22px; font-weight: 700; }
        .log-stat-label { font-size: 11px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }

        .click-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .click-table th {
            text-align: left; padding: 12px 14px; color: var(--text-secondary);
            font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .click-table td {
            padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .click-table tbody tr { transition: background 0.15s ease; }
        .click-table tbody tr:hover { background: rgba(255,255,255,0.03); }

        .device-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;
        }
        .device-badge.desktop { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .device-badge.mobile { background: rgba(16,185,129,0.1); color: #10b981; }
        .device-badge.tablet { background: rgba(245,158,11,0.1); color: #f59e0b; }

        .country-flag { display: inline-flex; align-items: center; gap: 6px; }

        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
        .pagination a, .pagination span {
            padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 500;
            border: 1px solid rgba(255,255,255,0.08); color: var(--text-secondary); text-decoration: none;
            transition: all 0.2s;
        }
        .pagination a:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .pagination .active { background: var(--accent-blue); color: #fff; border-color: var(--accent-blue); }

        .source-pill {
            display: inline-block; padding: 3px 8px; border-radius: 4px;
            font-size: 11px; font-weight: 600; background: rgba(139,92,246,0.1); color: #8b5cf6;
        }

        @media (max-width: 768px) {
            .log-stats { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content" style="padding: 20px 30px;">
    <div class="top-header" style="margin-bottom: 20px;">
        <div class="page-title">
            <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Click Log</h1>
            <p>Every single click tracked across all your links in real time.</p>
        </div>
        <div class="header-actions">
            <span style="color:var(--text-secondary); font-size:13px;"><?= number_format($total) ?> total clicks</span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="log-stats">
        <?php
        $todayStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM clicks c JOIN links l ON c.link_id = l.id WHERE l.user_id = ? AND DATE(c.clicked_at) = CURDATE()");
        $todayStmt->execute([$_SESSION['user_id']]);
        $today = $todayStmt->fetch()['cnt'];

        $mobileStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM clicks c JOIN links l ON c.link_id = l.id WHERE l.user_id = ? AND c.device_type = 'Mobile'");
        $mobileStmt->execute([$_SESSION['user_id']]);
        $mobileCount = $mobileStmt->fetch()['cnt'];

        $desktopStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM clicks c JOIN links l ON c.link_id = l.id WHERE l.user_id = ? AND c.device_type = 'Desktop'");
        $desktopStmt->execute([$_SESSION['user_id']]);
        $desktopCount = $desktopStmt->fetch()['cnt'];

        $uniqueCountries = $pdo->prepare("SELECT COUNT(DISTINCT c.country) as cnt FROM clicks c JOIN links l ON c.link_id = l.id WHERE l.user_id = ?");
        $uniqueCountries->execute([$_SESSION['user_id']]);
        $countryCount = $uniqueCountries->fetch()['cnt'];
        ?>
        <div class="log-stat">
            <div class="log-stat-val" style="color:#10b981;"><?= $today ?></div>
            <div class="log-stat-label">Today</div>
        </div>
        <div class="log-stat">
            <div class="log-stat-val" style="color:#3b82f6;"><?= $desktopCount ?></div>
            <div class="log-stat-label">Desktop</div>
        </div>
        <div class="log-stat">
            <div class="log-stat-val" style="color:#10b981;"><?= $mobileCount ?></div>
            <div class="log-stat-label">Mobile</div>
        </div>
        <div class="log-stat">
            <div class="log-stat-val" style="color:#8b5cf6;"><?= $countryCount ?></div>
            <div class="log-stat-label">Countries</div>
        </div>
    </div>

    <!-- Click Table -->
    <div class="glass" style="overflow-x:auto; border-radius:12px;">
        <table class="click-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Link</th>
                    <th>Source</th>
                    <th>Country</th>
                    <th>Device</th>
                    <th>IP</th>
                    <th>Referer</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clicks)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:var(--text-secondary);">
                        <i class="fa-solid fa-mouse-pointer" style="font-size:28px; opacity:0.4; display:block; margin-bottom:10px;"></i>
                        No clicks recorded yet. Share your links to start tracking!
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($clicks as $click): ?>
                <tr>
                    <td style="color:var(--text-secondary); white-space:nowrap;">
                        <i class="fa-regular fa-clock" style="margin-right:4px; opacity:0.5;"></i>
                        <?= date('M j, g:i A', strtotime($click['clicked_at'])) ?>
                    </td>
                    <td>
                        <a href="link_analytics.php?id=<?= $click['link_id'] ?>" style="color:var(--accent-blue); font-weight:500;">
                            /<?= htmlspecialchars($click['short_code']) ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($click['utm_source']): ?>
                            <span class="source-pill"><?= htmlspecialchars($click['utm_source']) ?></span>
                        <?php else: ?>
                            <span style="color:var(--text-secondary); font-size:12px;">Direct</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="country-flag">
                            <?= getCountryFlag($click['country'], 16) ?>
                            <?= htmlspecialchars(getCountryName($click['country'])) ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $dt = strtolower($click['device_type'] ?: 'desktop');
                        $icon = $dt === 'mobile' ? 'fa-mobile-screen' : ($dt === 'tablet' ? 'fa-tablet-screen-button' : 'fa-desktop');
                        ?>
                        <span class="device-badge <?= $dt ?>">
                            <i class="fa-solid <?= $icon ?>"></i> <?= ucfirst($dt) ?>
                        </span>
                    </td>
                    <td style="color:var(--text-secondary); font-family:monospace; font-size:12px;">
                        <?= htmlspecialchars($click['ip_address']) ?>
                    </td>
                    <td style="max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text-secondary); font-size:12px;" 
                        title="<?= htmlspecialchars($click['referer'] ?? '') ?>">
                        <?= htmlspecialchars($click['referer'] ?: '—') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</main>
</body>
</html>
