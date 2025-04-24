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

-- Alert Rules table
CREATE TABLE IF NOT EXISTS AlertRules (
    id int unsigned not null auto_increment,
    name varchar(100) not null,
    condition varchar(50) not null,
    threshold int not null,
    pattern text,
    status varchar(20) not null default 'active',
    created_at timestamp not null default CURRENT_TIMESTAMP,
    updated_at timestamp not null default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alert History table
CREATE TABLE IF NOT EXISTS AlertHistory (
    id int unsigned not null auto_increment,
    rule_id int unsigned not null,
    time timestamp not null default CURRENT_TIMESTAMP,
    message text not null,
    status varchar(20) not null,
    PRIMARY KEY (id),
    KEY idx_rule_id (rule_id),
    KEY idx_time (time),
    CONSTRAINT fk_rule_id FOREIGN KEY (rule_id) REFERENCES AlertRules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 