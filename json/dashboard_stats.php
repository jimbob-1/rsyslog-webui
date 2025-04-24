<?php
include '../config.php';

try {
    $db = new PDO(
        "mysql:host=$mysql_server;dbname=$mysql_database;charset=utf8mb4",
        $mysql_user,
        $mysql_password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );

    // Get log volume for last 24 hours
    $logVolumeQuery = "
        SELECT 
            DATE_FORMAT(ReceivedAt, '%H:00') as hour,
            COUNT(*) as count
        FROM SystemEvents
        WHERE ReceivedAt >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY hour
        ORDER BY hour
    ";
    $logVolume = $db->query($logVolumeQuery)->fetchAll();

    // Get error distribution
    $errorDistributionQuery = "
        SELECT 
            Priority,
            COUNT(*) as count
        FROM SystemEvents
        WHERE ReceivedAt >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        GROUP BY Priority
    ";
    $errorDistribution = $db->query($errorDistributionQuery)->fetchAll();

    // Get top error sources
    $topErrorsQuery = "
        SELECT 
            FromHost,
            COUNT(*) as count
        FROM SystemEvents
        WHERE Priority <= 3
        AND ReceivedAt >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        GROUP BY FromHost
        ORDER BY count DESC
        LIMIT 5
    ";
    $topErrors = $db->query($topErrorsQuery)->fetchAll();

    // Get system health
    $systemHealthQuery = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN Priority <= 3 THEN 1 ELSE 0 END) as errors
        FROM SystemEvents
        WHERE ReceivedAt >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ";
    $systemHealth = $db->query($systemHealthQuery)->fetch();

    // Format data for response
    $response = array(
        'logVolume' => array(
            'labels' => array_column($logVolume, 'hour'),
            'data' => array_column($logVolume, 'count')
        ),
        'errorDistribution' => array(
            'data' => array_fill(0, 4, 0) // Initialize with zeros for all priorities
        ),
        'topErrors' => $topErrors,
        'systemHealth' => array(
            'errorRate' => $systemHealth['total'] > 0 ? 
                ($systemHealth['errors'] / $systemHealth['total']) * 100 : 0,
            'status' => $systemHealth['errors'] > 10 ? 'warning' : 'healthy'
        )
    );

    // Fill in error distribution data
    foreach ($errorDistribution as $dist) {
        $response['errorDistribution']['data'][$dist['Priority']] = $dist['count'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
?> 