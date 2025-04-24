<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing alert ID']);
    exit;
}

// Check if $pdo is available
if (!isset($pdo) || $pdo === null) {
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

$id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT 
        ID,
        Name,
        AlertCondition,
        Status,
        CreatedAt,
        UpdatedAt,
        LastTriggered,
        Priority,
        Description,
        NotificationMethod,
        NotificationTarget,
        Enabled
    FROM Alerts WHERE ID = ?");
    
    $stmt->execute([$id]);
    $alert = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$alert) {
        echo json_encode(['success' => false, 'error' => 'Alert not found']);
        exit;
    }

    echo json_encode(['success' => true, 'alert' => $alert]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 