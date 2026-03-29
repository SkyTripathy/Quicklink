<?php
require_once 'config.php';
require_once 'branding.php';
if (!isLoggedIn()) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Builder | <?= $brand_name ?></title>
    <?php if ($brand_favicon): ?><link rel="icon" href="<?= $brand_favicon ?>"><?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .builder-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 768px) {
            .builder-grid { grid-template-columns: 1fr; }
        }

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
        .modal-icon { font-size: 48px; color: #10b981; margin-bottom: 20px; filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.4)); }
        .modal-title { font-size: 22px; font-weight: 700; margin-bottom: 12px; }
        .modal-desc { color: var(--text-secondary); font-size: 14px; margin-bottom: 25px; line-height: 1.6; }
        .modal-actions { display: flex; gap: 12px; }
        .modal-btn {
            flex: 1; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; font-size: 14px;
        }
        .modal-btn-cancel { background: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-color); }
        .modal-btn-cancel:hover { background: var(--bg-card-hover); }
        .modal-btn-success { background: #10b981; color: white; }
        .modal-btn-success:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; // We will extract sidebar to a separate file ?>

<main class="main-content">
    <div class="top-header">
        <div class="page-title">
            <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1>Link Builder</h1>
            <p>Create trackable UTM links to attribute your traffic.</p>
        </div>
    </div>

    <div class="glass" style="padding: 30px;">
        <form id="linkForm">
            <div class="form-group">
                <label>Destination URL <span style="color:#ef4444">*</span></label>
                <input type="url" name="original_url" id="original_url" class="form-input" placeholder="https://example.com" required>
            </div>

            <div class="form-group">
                <label>Custom Short Code (Optional)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span style="color:var(--text-secondary)">go.yourdomain.com/</span>
                    <input type="text" name="short_code" id="short_code" class="form-input" placeholder="summer-sale">
                </div>
            </div>

            <h3 style="margin: 30px 0 15px; font-size: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">UTM Parameters</h3>
            
            <div class="builder-grid">
                <div class="form-group">
                    <label>Source</label>
                    <input type="text" name="utm_source" class="form-input" placeholder="e.g., google, facebook, newsletter">
                </div>
                <div class="form-group">
                    <label>Medium</label>
                    <input type="text" name="utm_medium" class="form-input" placeholder="e.g., cpc, banner, email">
                </div>
                <div class="form-group">
                    <label>Campaign</label>
                    <input type="text" name="utm_campaign" class="form-input" placeholder="e.g., spring_sale">
                </div>
                <div class="form-group">
                    <label>Term</label>
                    <input type="text" name="utm_term" class="form-input" placeholder="e.g., running+shoes">
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <input type="text" name="utm_content" class="form-input" placeholder="e.g., logolink or textlink">
                </div>
            </div>

            <button type="submit" class="btn-primary" id="submitBtn" style="margin-top: 20px;">Generate Trackable Link</button>
        </form>
    </div>
</main>

<!-- Success Modal -->
<div class="modal-backdrop" id="successModal">
    <div class="modal-card">
        <i class="fa-solid fa-circle-check modal-icon"></i>
        <div class="modal-title">Link Generated!</div>
        <div class="modal-desc">Your trackable UTM link is ready to use and tracking is active.</div>
        
        <div style="display:flex; gap:10px; margin-bottom: 25px;">
            <input type="text" id="modal_generated_link" class="form-input" readonly>
            <button type="button" class="btn-primary" onclick="copyModalLink(this)" style="width:auto; padding:0 20px; transition: all 0.2s;"><i class="fa-solid fa-copy"></i></button>
        </div>

        <div class="modal-actions">
            <button class="modal-btn modal-btn-cancel" onclick="closeSuccessModal()">Create Another</button>
            <a href="links.php" class="modal-btn modal-btn-success" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">View Analytics Dashboard</a>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" style="position:fixed; top:20px; right:20px; z-index:10000; pointer-events:none;">
    <div id="toastInner" style="background:#1e1e2e; border:1px solid rgba(239,68,68,0.3); border-radius:12px; padding:14px 20px; display:flex; align-items:center; gap:12px; box-shadow:0 8px 32px rgba(0,0,0,0.5); transform:translateX(120%); transition:transform 0.4s cubic-bezier(0.16,1,0.3,1); max-width:400px; pointer-events:auto;">
        <i id="toastIcon" class="fa-solid fa-circle-exclamation" style="color:#ef4444; font-size:18px;"></i>
        <span id="toastMsg" style="font-size:13px; color:#fff; line-height:1.4;"></span>
    </div>
</div>

<script>
function showToast(msg, type = 'error') {
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
    setTimeout(() => { inner.style.transform = 'translateX(120%)'; }, 4000);
}

document.getElementById('linkForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
    btn.disabled = true;

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await fetch('api/create_link.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        
        if (json.success) {
            e.target.reset();
            document.getElementById('modal_generated_link').value = 'https://go.yourdomain.com/' + json.short_code;
            document.getElementById('successModal').classList.add('show');
        } else {
            showToast(json.error || 'Something went wrong', 'error');
        }
    } catch (err) {
        showToast('Failed to connect to API. Please try again.', 'error');
    }
    
    btn.innerHTML = 'Generate Trackable Link';
    btn.disabled = false;
});

function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('show');
}

function copyModalLink(btn) {
    const copyText = document.getElementById("modal_generated_link");
    copyText.select();
    document.execCommand("copy");
    
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
    btn.style.background = '#10b981';
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.style.background = '';
        window.getSelection().removeAllRanges();
    }, 1500);
}
</script>
</body>
</html>
