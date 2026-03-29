<?php
require_once 'config.php';
require_once 'branding.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overview | <?= $brand_name ?></title>
    <?php if ($brand_favicon): ?><link rel="icon" href="<?= $brand_favicon ?>"><?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* === PREMIUM DASHBOARD STYLING === */

        /* Animated accent lines */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 8px rgba(59,130,246,0.0); }
            50% { box-shadow: 0 0 16px rgba(59,130,246,0.15); }
        }

        /* Search bar */
        .search-bar {
            display: flex; align-items: center; gap: 10px;
            background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2);
            border-radius: 8px; padding: 8px 14px; max-width: 280px;
        }
        .search-bar i { color: var(--accent-blue); font-size: 13px; }
        .search-bar input {
            background: none; border: none; outline: none; color: var(--text-secondary);
            font-size: 13px; width: 100%; font-family: 'Inter', sans-serif;
        }

        /* Stat Cards */
        .dash-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 18px;
        }
        .dash-stat-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 20px 22px;
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .dash-stat-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, var(--card-accent, #3b82f6), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
            opacity: 0; transition: opacity 0.3s;
        }
        .dash-stat-card:hover { 
            transform: translateY(-3px); 
            border-color: rgba(255,255,255,0.15);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .dash-stat-card:hover::before { opacity: 1; }

        .dash-stat-card:nth-child(1) { --card-accent: #3b82f6; }
        .dash-stat-card:nth-child(2) { --card-accent: #10b981; }
        .dash-stat-card:nth-child(3) { --card-accent: #8b5cf6; }
        .dash-stat-card:nth-child(4) { --card-accent: #06b6d4; }

        .dash-stat-card .stat-icon-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            margin-bottom: 12px;
        }
        .dash-stat-card .stat-icon-circle.blue { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .dash-stat-card .stat-icon-circle.green { background: rgba(16,185,129,0.15); color: #10b981; }
        .dash-stat-card .stat-icon-circle.purple { background: rgba(139,92,246,0.15); color: #8b5cf6; }
        .dash-stat-card .stat-icon-circle.cyan { background: rgba(6,182,212,0.15); color: #06b6d4; }

        .dash-stat-label {
            font-size: 12px; text-transform: uppercase;
            color: var(--text-secondary); letter-spacing: 0.5px;
            font-weight: 600; margin-bottom: 6px;
        }
        .dash-stat-number {
            font-size: 28px; font-weight: 700; margin-bottom: 4px;
        }
        .dash-stat-sub { font-size: 12px; color: var(--text-secondary); }
        .dash-stat-sub.positive { color: #10b981; }

        /* Charts Row */
        .dash-charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 14px;
            margin-bottom: 18px;
        }
        .dash-chart-box {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 18px 20px;
            height: 240px;
            display: flex; flex-direction: column; overflow: hidden;
            transition: all 0.3s ease;
        }
        .dash-chart-box:hover {
            border-color: rgba(255,255,255,0.12);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }
        .dash-chart-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 10px; flex-shrink: 0;
        }
        .dash-chart-title { font-size: 14px; font-weight: 600; }
        .dash-chart-body { flex: 1; position: relative; min-height: 0; }

        /* Bottom Row */
        .dash-bottom-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .dash-bottom-box {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 18px 20px;
            height: 200px;
            display: flex; flex-direction: column; overflow: hidden;
            transition: all 0.3s ease;
        }
        .dash-bottom-box:hover {
            border-color: rgba(255,255,255,0.12);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }
        .empty-state-sm {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: var(--text-secondary); gap: 8px; font-size: 13px;
        }
        .empty-state-sm i { font-size: 24px; opacity: 0.4; }

        /* Live indicator pulse */
        .live-dot { animation: pulse-glow 2s ease-in-out infinite; }

        @media (max-width: 1024px) {
            .dash-stats { grid-template-columns: repeat(2, 1fr); }
            .dash-charts-row { grid-template-columns: 1fr; }
            .dash-bottom-row { grid-template-columns: 1fr; }
            .search-bar { display: none; }
        }
        @media (max-width: 480px) {
            .dash-stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content" style="padding: 20px 30px;">
    <div class="top-header" style="margin-bottom: 20px;">
        <div style="display:flex; align-items:center; gap: 24px;">
            <div class="page-title">
                <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <p style="font-size:12px; color:var(--text-secondary); margin-bottom:2px;">Dashboard</p>
                <h1>Overview</h1>
                <p>Real-time UTM attribution analytics across all your campaigns.</p>
            </div>
        </div>
        
        <div class="header-actions">
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search clicks, IPs, or campaigns...">
            </div>
            <div class="btn-secondary live-dot" style="border-color: #10b981; color: #10b981;">
                <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Live
            </div>
            <a href="create.php" class="btn-primary" style="padding: 8px 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; border-radius: 8px;" title="Quick Create">
                <i class="fa-solid fa-plus"></i> Quick Create
            </a>
        </div>
    </div>

    <!-- 4 Stat Cards -->
    <div class="dash-stats">
        <div class="dash-stat-card">
            <div class="stat-icon-circle blue"><i class="fa-solid fa-computer-mouse"></i></div>
            <div class="dash-stat-label">Total Clicks</div>
            <div class="dash-stat-number" id="stat-clicks">0</div>
            <div class="dash-stat-sub positive"><i class="fa-solid fa-arrow-up"></i> <span id="stat-today">0</span> today</div>
        </div>

        <div class="dash-stat-card">
            <div class="stat-icon-circle green"><i class="fa-solid fa-link"></i></div>
            <div class="dash-stat-label">Active Links</div>
            <div class="dash-stat-number" id="stat-links">0</div>
            <div class="dash-stat-sub">No links yet</div>
        </div>

        <div class="dash-stat-card">
            <div class="stat-icon-circle purple"><i class="fa-solid fa-filter"></i></div>
            <div class="dash-stat-label">Top Source</div>
            <div class="dash-stat-number" id="stat-source" style="font-size: 22px;">—</div>
            <div class="dash-stat-sub"><span id="stat-source-cnt">0</span> clicks</div>
        </div>

        <div class="dash-stat-card">
            <div class="stat-icon-circle cyan"><i class="fa-solid fa-globe"></i></div>
            <div class="dash-stat-label">Top Region</div>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                <img id="stat-country-flag" src="https://flagcdn.com/32x24/un.png" alt="" style="width:28px; border-radius:3px; box-shadow:0 1px 4px rgba(0,0,0,0.3);">
                <div class="dash-stat-number" id="stat-country-name" style="font-size:20px;">—</div>
            </div>
            <div class="dash-stat-sub" id="stat-country-sub" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;"></div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="dash-charts-row">
        <div class="dash-chart-box">
            <div class="dash-chart-header">
                <span class="dash-chart-title">Clicks Over Time</span>
                <span style="font-size:12px; color:var(--text-secondary);">Last 7 days</span>
            </div>
            <div class="dash-chart-body">
                <canvas id="clicksChart"></canvas>
            </div>
        </div>

        <div class="dash-chart-box">
            <div class="dash-chart-header">
                <span class="dash-chart-title">Traffic Sources</span>
            </div>
            <div class="dash-chart-body">
                <canvas id="sourcesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bottom Row: 3 columns -->
    <div class="dash-bottom-row">
        <div class="dash-bottom-box">
            <div class="dash-chart-header">
                <span class="dash-chart-title">Top Campaigns</span>
            </div>
            <div class="dash-chart-body">
                <canvas id="campaignsChart"></canvas>
            </div>
        </div>

        <div class="dash-bottom-box">
            <div class="dash-chart-header">
                <span class="dash-chart-title">Top Countries</span>
            </div>
            <div id="countriesList" style="flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:8px; padding-right:4px;">
                <div style="flex:1; display:flex; align-items:center; justify-content:center; color:var(--text-secondary); font-size:13px;">
                    <i class="fa-solid fa-globe" style="opacity:0.3; margin-right:8px;"></i> No geo data yet
                </div>
            </div>
        </div>

        <div class="dash-bottom-box">
            <div class="dash-chart-header">
                <span class="dash-chart-title">Mediums</span>
            </div>
            <div class="dash-chart-body">
                <canvas id="mediumsChart"></canvas>
            </div>
        </div>
    </div>

</main>

<script src="assets/js/main.js?v=<?= time() ?>"></script>
</body>
</html>
