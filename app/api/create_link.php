<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input) || empty($input)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input. Raw: ' . substr($raw, 0, 200)]);
    exit;
}

$original_url = trim($input['original_url'] ?? '');
$short_code = trim($input['short_code'] ?? '');
$utm_source = trim($input['utm_source'] ?? '') ?: null;
$utm_medium = trim($input['utm_medium'] ?? '') ?: null;
$utm_campaign = trim($input['utm_campaign'] ?? '') ?: null;
$utm_term = trim($input['utm_term'] ?? '') ?: null;
$utm_content = trim($input['utm_content'] ?? '') ?: null;

if (empty($original_url) || !filter_var($original_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'error' => 'Valid Destination URL is required']);
    exit;
}

// Generate random short code if empty
if (empty($short_code)) {
    $short_code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 7);
}

// Allow letters, numbers, hyphens, and underscores in short codes
$short_code = preg_replace('/[^a-zA-Z0-9\-_]/', '', $short_code);
if (empty($short_code)) {
    echo json_encode(['success' => false, 'error' => 'Short code contains invalid characters']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO links (user_id, short_code, original_url, utm_source, utm_medium, utm_campaign, utm_term, utm_content) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $short_code,
        $original_url,
        $utm_source,
        $utm_medium,
        $utm_campaign,
        $utm_term,
        $utm_content
    ]);
    
    echo json_encode(['success' => true, 'short_code' => $short_code]);
} catch (\PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        echo json_encode(['success' => false, 'error' => 'Short code already exists. Try another.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
