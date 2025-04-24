<?php
include '../config.php';

try {
    if (!$pdo) {
        throw new Exception("Database connection not available");
    }

    // Get log volume for last 24 hours with zero-filled hours
    $logVolumeQuery = "
        WITH RECURSIVE hours AS (
            SELECT DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00') as hour
            UNION ALL
            SELECT DATE_SUB(hour, INTERVAL 1 HOUR)
            FROM hours
            WHERE hour > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        )
        SELECT 
            DATE_FORMAT(h.hour, '%H:00') as hour,
            COALESCE(COUNT(s.ID), 0) as count
        FROM hours h
        LEFT JOIN SystemEvents s ON DATE_FORMAT(s.ReceivedAt, '%Y-%m-%d %H:00:00') = h.hour
        GROUP BY h.hour
        ORDER BY h.hour DESC
        LIMIT 24
    ";
    $logVolume = $pdo->query($logVolumeQuery)->fetchAll();

    // Get error distribution with zero-filled priorities
    $errorDistributionQuery = "
        WITH RECURSIVE priorities AS (
            SELECT 0 as Priority
            UNION ALL
            SELECT Priority + 1
            FROM priorities
            WHERE Priority < 7
        )
        SELECT 
            p.Priority,
            COALESCE(COUNT(s.ID), 0) as count
        FROM priorities p
        LEFT JOIN SystemEvents s ON s.Priority = p.Priority 
            AND s.ReceivedAt >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        GROUP BY p.Priority
        ORDER BY p.Priority
    ";
    $errorDistribution = $pdo->query($errorDistributionQuery)->fetchAll();

    // Get top error sources
    $topErrorsQuery = "
        SELECT 
            FromHost,
            COUNT(*) as count,
            MAX(ReceivedAt) as last_seen,
            MIN(Priority) as min_priority
        FROM SystemEvents
        WHERE Priority <= 3
        AND ReceivedAt >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        GROUP BY FromHost
        HAVING count > 0
        ORDER BY count DESC, min_priority ASC
        LIMIT 5
    ";
    $topErrors = $pdo->query($topErrorsQuery)->fetchAll();

    // Get system health with more detailed metrics
    $systemHealthQuery = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN Priority <= 3 THEN 1 ELSE 0 END) as errors,
            SUM(CASE WHEN Priority = 4 THEN 1 ELSE 0 END) as warnings,
            MAX(ReceivedAt) as latest_event
        FROM SystemEvents
        WHERE ReceivedAt >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ";
    $systemHealth = $pdo->query($systemHealthQuery)->fetch();

    // Get database size
    $dbSizeQuery = "
        SELECT 
            table_schema as 'Database',
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as 'Size (MB)'
        FROM information_schema.tables
        WHERE table_schema = ?
        GROUP BY table_schema
    ";
    $dbSize = $pdo->prepare($dbSizeQuery);
    $dbSize->execute([DB_NAME]);
    $dbSizeResult = $dbSize->fetch();

    // Format data for response
    $response = array(
        'logVolume' => array(
            'labels' => array_column($logVolume, 'hour'),
            'data' => array_column($logVolume, 'count')
        ),
        'errorDistribution' => array(
            'labels' => ['Emergency', 'Alert', 'Critical', 'Error', 'Warning', 'Notice', 'Info', 'Debug'],
            'data' => array_fill(0, 8, 0)
        ),
        'topErrors' => array_map(function($error) {
            return array(
                'host' => $error['FromHost'],
                'count' => (int)$error['count'],
                'lastSeen' => $error['last_seen'],
                'priority' => (int)$error['min_priority']
            );
        }, $topErrors),
        'systemHealth' => array(
            'total' => (int)$systemHealth['total'],
            'errors' => (int)$systemHealth['errors'],
            'warnings' => (int)$systemHealth['warnings'],
            'errorRate' => $systemHealth['total'] > 0 ? round(($systemHealth['errors'] / $systemHealth['total']) * 100, 2) : 0,
            'latestEvent' => $systemHealth['latest_event'],
            'dbSize' => $dbSizeResult ? $dbSizeResult['Size (MB)'] : 0
        )
    );

    // Fill in error distribution data
    foreach ($errorDistribution as $dist) {
        $response['errorDistribution']['data'][(int)$dist['Priority']] = (int)$dist['count'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(array('error' => 'Error: ' . $e->getMessage()));
}
?> 