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

    // Get POST data
    $name = $_POST['name'] ?? '';
    $condition = $_POST['condition'] ?? '';
    $threshold = $_POST['threshold'] ?? 0;
    $pattern = $_POST['pattern'] ?? '';

    // Validate input
    if (empty($name) || empty($condition) || empty($threshold)) {
        throw new Exception('Missing required fields');
    }

    // Insert new alert rule
    $query = "
        INSERT INTO AlertRules (name, condition, threshold, pattern, status)
        VALUES (?, ?, ?, ?, 'active')
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([$name, $condition, $threshold, $pattern]);

    header('Content-Type: application/json');
    echo json_encode(array('success' => true));

} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array('success' => false, 'error' => $e->getMessage()));
}
?> 