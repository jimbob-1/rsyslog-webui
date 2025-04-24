<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/settings.php';

// Initialize settings with error handling
try {
    $settings = Settings::getInstance();
    $current_theme = $settings->getSetting('theme') ?? 'light';
    $app_name = defined('APP_NAME') ? APP_NAME : 'RSyslog WebUI';
    $debug_mode = $settings->getSetting('debug_mode') ?? 0;
} catch (Exception $e) {
    // Default values if settings cannot be loaded
    $current_theme = 'light';
    $app_name = 'RSyslog WebUI';
    $debug_mode = 0;
}

// Get current page for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $app_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/bootstrap-table@1.22.3/dist/bootstrap-table.min.css" rel="stylesheet">
    <link href="css/themes.css" rel="stylesheet">
    <link href="css/dark-mode.css" rel="stylesheet">
    <link href="js/bootstrap-table.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/theme.js"></script>
    <?php if ($debug_mode): ?>
    <script src="js/simulator.js"></script>
    <?php endif; ?>
</head>
<body class="theme-<?php echo $current_theme; ?>">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-journal-text"></i> <?php echo $app_name; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-list-ul"></i> Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'alerts.php' ? 'active' : ''; ?>" href="alerts.php">
                            <i class="bi bi-exclamation-triangle"></i> Alerts
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                    <?php if ($debug_mode): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="startSimulation">
                            <i class="bi bi-play-circle"></i> Start Simulation
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-4">
    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Settings</h4>
                </div>
                <div class="modal-body">
                    <form id="settingsForm">
                        <div class="form-group">
                            <label for="logRetention">Log Retention (days)</label>
                            <input type="number" class="form-control" id="logRetention" value="<?php echo $keep_logs_for_days; ?>">
                        </div>
                        <div class="form-group">
                            <label for="timezone">Timezone</label>
                            <select class="form-control" id="timezone">
                                <?php
                                $timezones = DateTimeZone::listIdentifiers();
                                foreach ($timezones as $timezone) {
                                    $selected = ($timezone === date_default_timezone_get()) ? 'selected' : '';
                                    echo "<option value=\"$timezone\" $selected>$timezone</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveSettings">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 