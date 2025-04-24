<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../includes/settings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$theme = $_POST['theme'] ?? null;

if (!$theme || !in_array($theme, ['light', 'dark'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid theme']);
    exit;
}

try {
    $settings = Settings::getInstance();
    if ($settings->updateSetting('theme', $theme)) {
        echo json_encode(['success' => true, 'theme' => $theme]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update theme']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
} 