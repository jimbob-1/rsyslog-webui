<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../includes/settings.php';

// Check debug mode
$settings = Settings::getInstance();
$debug_mode = $settings->getSetting('debug_mode') ?? 0;

if (!$debug_mode) {
    echo json_encode(['success' => false, 'error' => 'Debug mode is not enabled']);
    exit;
}

// Check database connection
if (!isset($pdo) || $pdo === null) {
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'start') {
    // Generate random log events
    try {
        $hostnames = ['web-server', 'db-server', 'app-server', 'auth-server', 'proxy', 'loadbalancer', 'firewall'];
        $facilities = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 16, 17, 18, 19];
        $priorities = [0, 1, 2, 3, 4, 5, 6, 7]; // emergency to debug
        $syslogTags = ['httpd:', 'kernel:', 'mysqld:', 'nginx:', 'sshd:', 'cron:', 'php-fpm:', 'postfix:', 'dovecot:'];
        
        $messages = [
            // Errors (priorities 0-3)
            'System crash detected, automatic recovery initiated',
            'Critical security breach detected from IP {IP}',
            'Database connection failed after {NUM} retries',
            'Authentication service unavailable',
            'Invalid access attempt from unauthorized user',
            'File system error: unable to write to disk',
            'Memory allocation failure',
            'Network connection lost to critical service',
            
            // Warnings (priority 4)
            'High CPU usage detected: {NUM}%',
            'Low disk space warning: {NUM}MB remaining',
            'Slow database query detected: {QUERY}',
            'Potential brute force attack detected from {IP}',
            'Configuration file contains deprecated settings',
            
            // Notices and Info (priorities 5-6)
            'User {USER} logged in successfully',
            'Backup completed successfully in {NUM} seconds',
            'Service restarted automatically',
            'Software update available: version {VERSION}',
            'Scheduled maintenance starting in {NUM} minutes',
            
            // Debug (priority 7)
            'Debug: variable {VAR} set to {VALUE}',
            'Processing request {ID} from client {IP}',
            'Query execution time: {NUM}ms',
            'Cache hit ratio: {NUM}%',
            'Memory usage: {NUM}MB'
        ];
        
        // Generate 10-20 random events
        $eventCount = rand(10, 20);
        $timestamp = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("INSERT INTO SystemEvents 
            (ReceivedAt, DeviceReportedTime, Facility, Priority, FromHost, Message, SysLogTag) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < $eventCount; $i++) {
            $priority = $priorities[array_rand($priorities)];
            $facility = $facilities[array_rand($facilities)];
            $host = $hostnames[array_rand($hostnames)];
            $tag = $syslogTags[array_rand($syslogTags)];
            
            // Select message based on priority level
            if ($priority <= 3) {
                $messageIndex = rand(0, 7); // Error messages
            } elseif ($priority == 4) {
                $messageIndex = rand(8, 12); // Warning messages
            } elseif ($priority <= 6) {
                $messageIndex = rand(13, 17); // Notice/Info messages
            } else {
                $messageIndex = rand(18, 22); // Debug messages
            }
            
            $message = $messages[$messageIndex];
            
            // Replace placeholders with random values
            $message = str_replace('{IP}', rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255), $message);
            $message = str_replace('{NUM}', rand(1, 1000), $message);
            $message = str_replace('{USER}', 'user' . rand(100, 999), $message);
            $message = str_replace('{VERSION}', rand(1, 9) . '.' . rand(0, 9) . '.' . rand(0, 9), $message);
            $message = str_replace('{VAR}', 'variable_' . rand(1, 100), $message);
            $message = str_replace('{VALUE}', 'value_' . rand(1, 100), $message);
            $message = str_replace('{ID}', 'REQ' . rand(10000, 99999), $message);
            $message = str_replace('{QUERY}', 'SELECT * FROM table_' . rand(1, 10) . ' WHERE id = ' . rand(1, 1000), $message);
            
            $stmt->execute([$timestamp, $timestamp, $facility, $priority, $host, $message, $tag]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Generated ' . $eventCount . ' events']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'stop') {
    // Stop the simulation (just return success, actual stopping is handled by the JS)
    echo json_encode(['success' => true, 'message' => 'Simulation stopped']);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?> 