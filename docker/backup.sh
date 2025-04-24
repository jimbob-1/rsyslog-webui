#!/bin/bash

# Set variables
BACKUP_DIR="/backup"
DATE=$(date +%Y%m%d_%H%M%S)
MYSQL_USER="rsyslog"
MYSQL_PASSWORD="rsyslog_password"
MYSQL_DATABASE="rsyslog"

# Create backup
mysqldump -u$MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE > $BACKUP_DIR/backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 7 days of backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete 