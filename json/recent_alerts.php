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

    // Get recent alerts
    $query = "
        SELECT 
            a.id,
            a.time,
            r.name as rule_name,
            a.message,
            a.status
        FROM AlertHistory a
        JOIN AlertRules r ON a.rule_id = r.id
        ORDER BY a.time DESC
        LIMIT 50
    ";
    $alerts = $db->query($query)->fetchAll();

    header('Content-Type: application/json');
    echo json_encode(array('alerts' => $alerts));

} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
?> 