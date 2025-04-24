<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['condition']) || !isset($data['priority'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Check if $pdo is available
if (!isset($pdo) || $pdo === null) {
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

try {
    // If ID is provided, update existing alert
    if (isset($data['id'])) {
        $stmt = $pdo->prepare("UPDATE Alerts SET 
            Name = ?, 
            AlertCondition = ?, 
            Priority = ?, 
            Description = ?, 
            NotificationMethod = ?, 
            NotificationTarget = ?,
            UpdatedAt = NOW()
            WHERE ID = ?");
        
        $result = $stmt->execute([
            $data['name'],
            $data['condition'],
            $data['priority'],
            $data['description'] ?? '',
            $data['notification_method'] ?? 'email',
            $data['notification_target'] ?? '',
            $data['id']
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Alert updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update alert']);
        }
    } 
    // Otherwise create new alert
    else {
        $stmt = $pdo->prepare("INSERT INTO Alerts 
            (Name, AlertCondition, Priority, Description, NotificationMethod, NotificationTarget, CreatedAt, UpdatedAt, Status, Enabled) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), 'active', TRUE)");
        
        $result = $stmt->execute([
            $data['name'],
            $data['condition'],
            $data['priority'],
            $data['description'] ?? '',
            $data['notification_method'] ?? 'email',
            $data['notification_target'] ?? ''
        ]);

        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Alert created successfully',
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create alert']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?> 