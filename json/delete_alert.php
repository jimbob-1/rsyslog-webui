<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../includes/settings.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing alert ID']);
    exit;
}

// Check if $pdo is available
if (!isset($pdo) || $pdo === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

$id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("DELETE FROM Alerts WHERE ID = ?");
    $result = $stmt->execute([$id]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Alert deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Alert not found or already deleted']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 