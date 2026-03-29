<?php
require_once 'config.php';
require_once 'branding.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }

// Fetch user links
$stmt = $pdo->prepare("
    SELECT l.*, COUNT(c.id) as total_clicks 
    FROM links l 
    LEFT JOIN clicks c ON l.id = c.link_id 
    WHERE l.user_id = ? 
    GROUP BY l.id 
    ORDER BY l.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$links = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Links | <?= $brand_name ?></title>
    <?php if ($brand_favicon): ?><link rel="icon" href="<?= $brand_favicon ?>"><?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .links-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .links-table th {
            text-align: left;
            padding: 16px;
            color: var(--text-secondary);
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
        }
        .links-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }
        .links-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .link-url {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 4px;
        }
        .link-original {
            color: var(--text-secondary);
            font-size: 12px;
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 4px;
            margin-bottom: 4px;
            background: rgba(255,255,255,0.05);
            color: var(--text-secondary);
        }
        .badge.source { background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); }
        .badge.campaign { background: rgba(139, 92, 246, 0.1); color: var(--accent-purple); }

        /* Modal specific styles */
        .modal-backdrop {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .modal-backdrop.show { opacity: 1; pointer-events: auto; }
        .modal-card {
            background: var(--bg-main); border: 1px solid var(--border-color);
            box-shadow: 0 20px 40px rgba(0,0,0,0.8); border-radius: 16px;
            width: 90%; max-width: 400px; padding: 30px; text-align: center;
            transform: translateY(20px) scale(0.95); transition: transform 0.3s ease;
        }
        .modal-backdrop.show .modal-card { transform: translateY(0) scale(1); }
        .modal-icon { font-size: 48px; color: #ef4444; margin-bottom: 20px; }
        .modal-title { font-size: 22px; font-weight: 700; margin-bottom: 12px; }
        .modal-desc { color: var(--text-secondary); font-size: 14px; margin-bottom: 30px; line-height: 1.6; }
        .modal-actions { display: flex; gap: 12px; }
        .modal-btn {
            flex: 1; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; font-size: 14px;
        }
        .modal-btn-cancel { background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color); }
        .modal-btn-cancel:hover { background: var(--bg-card-hover); }
        .modal-btn-danger { background: #ef4444; color: white; }
        .modal-btn-danger:hover { background: #dc2626; transform: translateY(-2px); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="top-header">
        <div class="page-title">
            <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>My Links</h1>
            <p>Manage and track all your generated UTM links.</p>
        </div>
        <div class="header-actions">
            <a href="create.php" class="btn-primary" style="padding: 10px 20px; text-decoration: none;">+ Create New</a>
        </div>
    </div>

    <div class="glass" style="overflow-x: auto;">
        <table class="links-table">
            <thead>
                <tr>
                    <th>Short Link</th>
                    <th>UTM Parameters</th>
                    <th>Clicks</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($links)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                        <i class="fa-solid fa-link" style="font-size: 32px; margin-bottom: 12px; opacity:0.5;"></i><br>
                        You haven't created any links yet.
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php foreach ($links as $link): ?>
                <tr>
                    <td>
                        <div class="link-url">go.yourdomain.com/<?= htmlspecialchars($link['short_code']) ?></div>
                        <div class="link-original" title="<?= htmlspecialchars($link['original_url']) ?>"><?= htmlspecialchars($link['original_url']) ?></div>
                    </td>
                    <td>
                        <?php if($link['utm_source']): ?><span class="badge source">src: <?= htmlspecialchars($link['utm_source']) ?></span><?php endif; ?>
                        <?php if($link['utm_campaign']): ?><span class="badge campaign">cmp: <?= htmlspecialchars($link['utm_campaign']) ?></span><?php endif; ?>
                        <?php if($link['utm_medium']): ?><span class="badge">med: <?= htmlspecialchars($link['utm_medium']) ?></span><?php endif; ?>
                    </td>
                    <td style="font-weight: 600; color: #10b981;"><?= number_format($link['total_clicks']) ?></td>
                    <td style="color: var(--text-secondary);"><?= date('M j, Y', strtotime($link['created_at'])) ?></td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="link_analytics.php?id=<?= $link['id'] ?>" class="btn-secondary" style="color:var(--accent-blue); padding:8px 12px;" title="View Analytics">
                                <i class="fa-solid fa-chart-pie"></i>
                            </a>
                            <button class="btn-secondary" onclick="copy('https://go.yourdomain.com/<?= htmlspecialchars($link['short_code']) ?>')" style="padding:8px 12px;" title="Copy Link">
                                <i class="fa-solid fa-copy"></i>
                            </button>
                            <button class="btn-secondary" onclick="deleteLink(<?= $link['id'] ?>)" style="color:#ef4444; padding:8px 12px;" title="Delete Link">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Delete Confirmation Modal -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-card">
        <i class="fa-solid fa-triangle-exclamation modal-icon"></i>
        <div class="modal-title">Delete Link?</div>
        <div class="modal-desc">Are you sure you want to permanently delete this link and ALL its click data? This action cannot be undone.</div>
        <div class="modal-actions">
            <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="modal-btn modal-btn-danger" id="confirmDeleteBtn"><i class="fa-solid fa-trash"></i> Yes, Delete</button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" style="position:fixed; top:20px; right:20px; z-index:10000; pointer-events:none;">
    <div id="toastInner" style="background:#1e1e2e; border:1px solid rgba(16,185,129,0.3); border-radius:12px; padding:14px 20px; display:flex; align-items:center; gap:12px; box-shadow:0 8px 32px rgba(0,0,0,0.5); transform:translateX(120%); transition:transform 0.4s cubic-bezier(0.16,1,0.3,1); max-width:400px; pointer-events:auto;">
        <i id="toastIcon" class="fa-solid fa-circle-check" style="color:#10b981; font-size:18px;"></i>
        <span id="toastMsg" style="font-size:13px; color:#fff; line-height:1.4;"></span>
    </div>
</div>

<script>
function showToast(msg, type = 'success') {
    const inner = document.getElementById('toastInner');
    const icon = document.getElementById('toastIcon');
    const text = document.getElementById('toastMsg');
    
    text.textContent = msg;
    if (type === 'error') {
        inner.style.borderColor = 'rgba(239,68,68,0.3)';
        icon.className = 'fa-solid fa-circle-exclamation';
        icon.style.color = '#ef4444';
    } else {
        inner.style.borderColor = 'rgba(16,185,129,0.3)';
        icon.className = 'fa-solid fa-circle-check';
        icon.style.color = '#10b981';
    }
    
    inner.style.transform = 'translateX(0)';
    setTimeout(() => { inner.style.transform = 'translateX(120%)'; }, 3000);
}

function copy(text) {
    navigator.clipboard.writeText(text);
    showToast('Link copied to clipboard!', 'success');
}

let targetDeleteId = null;

function deleteLink(id) {
    targetDeleteId = id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('show');
    targetDeleteId = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
    if(!targetDeleteId) return;
    
    const btn = document.getElementById('confirmDeleteBtn');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
    btn.disabled = true;
    
    try {
        const res = await fetch('api/delete_link.php?id=' + targetDeleteId, { method: 'DELETE' });
        const json = await res.json();
        if(json.success) {
            window.location.reload();
        } else {
            showToast('Failed to delete: ' + json.error, 'error');
            closeModal();
            btn.innerHTML = '<i class="fa-solid fa-trash"></i> Yes, Delete';
            btn.disabled = false;
        }
    } catch(err) {
        showToast('Server connection error. Please try again.', 'error');
        closeModal();
        btn.innerHTML = '<i class="fa-solid fa-trash"></i> Yes, Delete';
        btn.disabled = false;
    }
});
</script>
</body>
</html>
