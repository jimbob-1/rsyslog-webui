<?php
class Settings {
    private $pdo;
    private static $instance = null;
    private $settings = [];
    
    private function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        
        // Only try to initialize if we have a database connection
        if ($this->pdo !== null) {
            $this->initializeSettingsTable();
            $this->loadSettings();
        } else {
            // Use default settings when database is not available
            $this->settings = [
                'keep_logs_days' => 30,
                'theme' => 'light',
                'items_per_page' => 50,
                'refresh_interval' => 60,
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d H:i:s',
                'debug_mode' => false
            ];
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Settings();
        }
        return self::$instance;
    }

    private function initializeSettingsTable() {
        if ($this->pdo === null) {
            return;
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->pdo->exec($sql);

        // Initialize default settings if they don't exist
        $defaultSettings = [
            'keep_logs_days' => 30,
            'theme' => 'light',
            'items_per_page' => 50,
            'refresh_interval' => 60,
            'timezone' => 'UTC',
            'date_format' => 'd/m/Y h:i:s A'
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($defaultSettings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
    }

    private function loadSettings() {
        if ($this->pdo === null) {
            return;
        }
        
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    public function getSetting($key) {
        return $this->settings[$key] ?? null;
    }

    public function getAllSettings() {
        return $this->settings;
    }

    public function updateSetting($key, $value) {
        if ($this->pdo === null) {
            $this->settings[$key] = $value;
            return true;
        }
        
        $stmt = $this->pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $result = $stmt->execute([$value, $key]);
        if ($result) {
            $this->settings[$key] = $value;
        }
        return $result;
    }

    public function updateMultipleSettings($settings) {
        if ($this->pdo === null) {
            foreach ($settings as $key => $value) {
                $this->settings[$key] = $value;
            }
            return true;
        }
        
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            foreach ($settings as $key => $value) {
                $stmt->execute([$value, $key]);
                $this->settings[$key] = $value;
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getDefaultSettings() {
        return [
            'keep_logs_days' => 90,
            'theme' => 'light',
            'items_per_page' => 25,
            'refresh_interval' => 30,
            'timezone' => 'UTC',
            'date_format' => 'd/m/Y h:i:s A',
            'debug_mode' => 0
        ];
    }
} 