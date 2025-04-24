<?php
/**
 * RSyslog WebUI - Log Simulator
 * Handles requests from the simulator.js script to generate test logs and alerts
 */

// Include configuration
include '../config.php';

// Ensure this is only accessible in debug mode
if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'error' => 'Debug mode is not enabled']);
    exit;
}

// Set response header
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Only POST requests are allowed']);
    exit;
}

// Get the raw POST data
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

// Validate input data
if (!$data || !isset($data['timestamp']) || !isset($data['source']) || 
    !isset($data['facility']) || !isset($data['severity']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

try {
    // Sanitize the input
    $timestamp = filter_var($data['timestamp'], FILTER_SANITIZE_STRING);
    $source = filter_var($data['source'], FILTER_SANITIZE_STRING);
    $facility = filter_var($data['facility'], FILTER_SANITIZE_STRING);
    $severity = filter_var($data['severity'], FILTER_SANITIZE_STRING);
    $message = filter_var($data['message'], FILTER_SANITIZE_STRING);
    $createAlert = isset($data['create_alert']) && $data['create_alert'] ? 1 : 0;
    
    // Convert the timestamp to MySQL format if needed
    $dbTimestamp = date('Y-m-d H:i:s', strtotime($timestamp));
    
    // Insert log entry into the database
    $stmt = $pdo->prepare("
        INSERT INTO SystemEvents (
            ReceivedAt, 
            DeviceReportedTime, 
            FromHost, 
            Facility, 
            Priority, 
            Message,
            SysLogTag
        ) VALUES (
            NOW(), 
            :timestamp, 
            :source, 
            :facility, 
            :severity, 
            :message,
            'simulated'
        )
    ");
    
    $stmt->execute([
        ':timestamp' => $dbTimestamp,
        ':source' => $source,
        ':facility' => $facility,
        ':severity' => getSeverityCode($severity),
        ':message' => $message
    ]);
    
    $logId = $pdo->lastInsertId();
    $alertId = null;
    
    // Create alert if required
    if ($createAlert) {
        $stmt = $pdo->prepare("
            INSERT INTO Alerts (
                event_id,
                hostname,
                facility,
                priority,
                message,
                first_occurrence,
                last_occurrence,
                status,
                count
            ) VALUES (
                :event_id,
                :hostname,
                :facility,
                :priority,
                :message,
                :timestamp,
                :timestamp,
                'new',
                1
            )
        ");
        
        $stmt->execute([
            ':event_id' => $logId,
            ':hostname' => $source,
            ':facility' => $facility,
            ':priority' => getSeverityCode($severity),
            ':message' => $message,
            ':timestamp' => $dbTimestamp
        ]);
        
        $alertId = $pdo->lastInsertId();
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'log_id' => $logId,
        'alert_id' => $alertId,
        'alert_created' => $createAlert
    ]);
    
} catch (PDOException $e) {
    // Handle database errors
    error_log('Simulation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}

/**
 * Convert human-readable severity to syslog numeric code
 * @param string $severity The severity name
 * @return int The numeric code for the severity
 */
function getSeverityCode($severity) {
    $severityCodes = [
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7
    ];
    
    return isset($severityCodes[strtolower($severity)]) 
        ? $severityCodes[strtolower($severity)] 
        : 6; // default to info if not recognized
} 