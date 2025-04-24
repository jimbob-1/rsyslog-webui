CREATE TABLE IF NOT EXISTS SystemEvents (
    ID int unsigned not null auto_increment,
    CustomerID bigint,
    ReceivedAt datetime NULL,
    DeviceReportedTime datetime NULL,
    Facility smallint NULL,
    Priority smallint NULL,
    FromHost varchar(60) NULL,
    Message text,
    NTSeverity int NULL,
    Importance int NULL,
    EventSource varchar(60),
    EventUser varchar(60) NULL,
    EventCategory int NULL,
    EventID int NULL,
    EventBinaryData text NULL,
    MaxAvailable int NULL,
    CurrUsage int NULL,
    MinUsage int NULL,
    MaxUsage int NULL,
    InfoUnitID int NULL,
    SysLogTag varchar(60),
    EventLogType varchar(60),
    GenericFileName VarChar(60),
    SystemID int NULL,
    PRIMARY KEY (ID),
    INDEX idx_received_at (ReceivedAt),
    INDEX idx_device_reported_time (DeviceReportedTime),
    INDEX idx_facility (Facility),
    INDEX idx_priority (Priority),
    INDEX idx_from_host (FromHost),
    INDEX idx_syslogtag (SysLogTag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create a view for common queries
CREATE OR REPLACE VIEW vw_recent_events AS
SELECT 
    ID,
    ReceivedAt,
    DeviceReportedTime,
    Facility,
    Priority,
    FromHost,
    Message,
    SysLogTag
FROM SystemEvents
WHERE ReceivedAt >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY ReceivedAt DESC;

-- Alerts table
CREATE TABLE IF NOT EXISTS Alerts (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    AlertCondition TEXT NOT NULL,
    Status ENUM('active', 'resolved') DEFAULT 'active',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    LastTriggered TIMESTAMP NULL,
    Priority ENUM('emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug') DEFAULT 'warning',
    Description TEXT,
    NotificationMethod ENUM('email', 'webhook', 'both') DEFAULT 'email',
    NotificationTarget VARCHAR(255),
    Enabled BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alert History table
CREATE TABLE IF NOT EXISTS AlertHistory (
    ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    AlertID INT NOT NULL,
    Time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Message TEXT NOT NULL,
    Status VARCHAR(20) NOT NULL,
    PRIMARY KEY (ID),
    KEY idx_alert_id (AlertID),
    KEY idx_time (Time),
    CONSTRAINT fk_alert_id FOREIGN KEY (AlertID) REFERENCES Alerts (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 