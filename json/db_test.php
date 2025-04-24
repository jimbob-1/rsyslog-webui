<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config.php';

// Check database connection
$connection_status = [
    'success' => isset($pdo) && $pdo !== null,
    'message' => isset($pdo) && $pdo !== null ? 'Database connection successful' : 'Database connection failed'
];

// Check Alerts table
$table_info = [];
if ($connection_status['success']) {
    try {
        // Get the table structure
        $stmt = $pdo->prepare("DESCRIBE Alerts");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count alerts
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Alerts");
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get sample alert (if any)
        $sample = null;
        if ($count > 0) {
            $stmt = $pdo->prepare("SELECT * FROM Alerts LIMIT 1");
            $stmt->execute();
            $sample = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $table_info = [
            'success' => true,
            'columns' => $columns,
            'total_alerts' => $count,
            'sample_alert' => $sample
        ];
    } catch (PDOException $e) {
        $table_info = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Check config file info
$config_info = [
    'host' => defined('DB_HOST') ? DB_HOST : (getenv('MYSQL_HOST') ?: 'Not defined'),
    'database' => defined('DB_NAME') ? DB_NAME : (getenv('MYSQL_DATABASE') ?: 'Not defined'),
    'username' => defined('DB_USER') ? DB_USER : (getenv('MYSQL_USER') ?: 'Not defined'),
    'file_path' => __FILE__,
    'config_include_path' => __DIR__ . '/../config.php'
];

// Output diagnostic information
echo json_encode([
    'connection' => $connection_status,
    'table_info' => $table_info,
    'config' => $config_info,
    'environment' => [
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'php_version' => phpversion(),
        'pdo_drivers' => PDO::getAvailableDrivers()
    ]
], JSON_PRETTY_PRINT);
?> 