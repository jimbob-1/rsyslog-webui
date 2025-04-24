<?php
include 'config.php';
include 'includes/settings.php';
include 'includes/header.php';

$settings = Settings::getInstance();
$current_settings = $settings->getAllSettings();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_settings = [
        'keep_logs_days' => (int)$_POST['keep_logs_days'],
        'theme' => $_POST['theme'],
        'items_per_page' => (int)$_POST['items_per_page'],
        'refresh_interval' => (int)$_POST['refresh_interval'],
        'timezone' => $_POST['timezone'],
        'debug_mode' => isset($_POST['debug_mode']) && $_POST['debug_mode'] ? 1 : 0,
        'smtp_host' => $_POST['smtp_host'],
        'smtp_port' => (int)$_POST['smtp_port'],
        'smtp_username' => $_POST['smtp_username'],
        'smtp_password' => $_POST['smtp_password'],
        'smtp_from_email' => $_POST['smtp_from_email'],
        'smtp_from_name' => $_POST['smtp_from_name'],
        'smtp_encryption' => $_POST['smtp_encryption']
    ];
    
    if ($settings->updateMultipleSettings($new_settings)) {
        $success_message = "Settings updated successfully!";
        $current_settings = $settings->getAllSettings();
    } else {
        $error_message = "Failed to update settings.";
    }
}

// Get list of available timezones
$timezones = DateTimeZone::listIdentifiers();
?>

<div class="container mt-4">
    <h1>Settings</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">General Settings</h5>
                
                <!-- Theme Selection -->
                <div class="mb-3">
                    <label for="theme" class="form-label">Theme</label>
                    <select class="form-select" id="theme" name="theme" required>
                        <option value="light" <?php echo $current_settings['theme'] === 'light' ? 'selected' : ''; ?>>Light Mode</option>
                        <option value="dark" <?php echo $current_settings['theme'] === 'dark' ? 'selected' : ''; ?>>Dark Mode</option>
                    </select>
                </div>

                <!-- Log Retention -->
                <div class="mb-3">
                    <label for="keep_logs_days" class="form-label">Keep Logs For (Days)</label>
                    <input type="number" class="form-control" id="keep_logs_days" name="keep_logs_days" 
                           value="<?php echo $current_settings['keep_logs_days']; ?>" required min="1" max="365">
                </div>

                <!-- Items Per Page -->
                <div class="mb-3">
                    <label for="items_per_page" class="form-label">Items Per Page</label>
                    <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                           value="<?php echo $current_settings['items_per_page']; ?>" required min="10" max="100">
                </div>

                <!-- Auto-refresh Interval -->
                <div class="mb-3">
                    <label for="refresh_interval" class="form-label">Auto-refresh Interval (seconds)</label>
                    <input type="number" class="form-control" id="refresh_interval" name="refresh_interval" 
                           value="<?php echo $current_settings['refresh_interval']; ?>" required min="0" max="3600">
                </div>

                <!-- Timezone -->
                <div class="mb-3">
                    <label for="timezone" class="form-label">Timezone</label>
                    <select class="form-select" id="timezone" name="timezone" required>
                        <?php foreach ($timezones as $tz): ?>
                            <option value="<?php echo $tz; ?>" <?php echo $current_settings['timezone'] === $tz ? 'selected' : ''; ?>>
                                <?php echo $tz; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Debug Mode -->
                <div class="mb-3">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Debug Mode</h6>
                                    <p class="text-muted small mb-0">Enable advanced features and simulation tools</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="debugMode" name="debug_mode" value="1" 
                                        <?php echo isset($current_settings['debug_mode']) && $current_settings['debug_mode'] ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Email Notification Settings</h5>
                <p class="card-text text-muted small mb-3">Configure SMTP settings for email alert notifications</p>
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- SMTP Host -->
                        <div class="mb-3">
                            <label for="smtp_host" class="form-label">SMTP Server</label>
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                   value="<?php echo isset($current_settings['smtp_host']) ? $current_settings['smtp_host'] : ''; ?>" 
                                   placeholder="smtp.example.com">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- SMTP Port -->
                        <div class="mb-3">
                            <label for="smtp_port" class="form-label">Port</label>
                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                   value="<?php echo isset($current_settings['smtp_port']) ? $current_settings['smtp_port'] : '587'; ?>" 
                                   placeholder="587">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- SMTP Username -->
                        <div class="mb-3">
                            <label for="smtp_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                   value="<?php echo isset($current_settings['smtp_username']) ? $current_settings['smtp_username'] : ''; ?>" 
                                   placeholder="username@example.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- SMTP Password -->
                        <div class="mb-3">
                            <label for="smtp_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                   value="<?php echo isset($current_settings['smtp_password']) ? $current_settings['smtp_password'] : ''; ?>" 
                                   placeholder="•••••••••">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- From Email -->
                        <div class="mb-3">
                            <label for="smtp_from_email" class="form-label">From Email</label>
                            <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" 
                                   value="<?php echo isset($current_settings['smtp_from_email']) ? $current_settings['smtp_from_email'] : ''; ?>" 
                                   placeholder="alerts@example.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- From Name -->
                        <div class="mb-3">
                            <label for="smtp_from_name" class="form-label">From Name</label>
                            <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" 
                                   value="<?php echo isset($current_settings['smtp_from_name']) ? $current_settings['smtp_from_name'] : 'RSyslog Alert System'; ?>" 
                                   placeholder="RSyslog Alert System">
                        </div>
                    </div>
                </div>
                
                <!-- Encryption Type -->
                <div class="mb-3">
                    <label for="smtp_encryption" class="form-label">Encryption</label>
                    <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                        <option value="tls" <?php echo (isset($current_settings['smtp_encryption']) && $current_settings['smtp_encryption'] === 'tls') ? 'selected' : ''; ?>>TLS</option>
                        <option value="ssl" <?php echo (isset($current_settings['smtp_encryption']) && $current_settings['smtp_encryption'] === 'ssl') ? 'selected' : ''; ?>>SSL</option>
                        <option value="none" <?php echo (isset($current_settings['smtp_encryption']) && $current_settings['smtp_encryption'] === 'none') ? 'selected' : ''; ?>>None</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
    
    <?php if (isset($current_settings['debug_mode']) && $current_settings['debug_mode']): ?>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Debug Tools</h5>
            <p class="card-text text-muted small">These tools are available only in debug mode</p>
            
            <div class="d-flex mt-3">
                <button id="startSimulation" class="btn btn-outline-primary me-2">
                    <i class="bi bi-play-circle"></i> Start Simulation
                </button>
                <a href="maintenance/db-maintenance.php" class="btn btn-outline-secondary">
                    <i class="bi bi-database-gear"></i> Database Maintenance
                </a>
            </div>
        </div>
    </div>
    
    <script src="js/simulator.js"></script>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Theme preview
    const themeSelect = document.getElementById('theme');
    themeSelect.addEventListener('change', function() {
        const theme = this.value;
        document.body.classList.remove('theme-light', 'theme-dark');
        document.body.classList.add('theme-' + theme);
        
        // Update theme via AJAX for immediate effect
        fetch('json/update_theme.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'theme=' + encodeURIComponent(theme)
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to save theme preference:', data.error);
            }
        })
        .catch(error => {
            console.error('Error saving theme preference:', error);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 