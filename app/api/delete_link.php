<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
    exit;
}

try {
    // Delete link, cascading delete handles the clicks table records
    $stmt = $pdo->prepare("DELETE FROM links WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
