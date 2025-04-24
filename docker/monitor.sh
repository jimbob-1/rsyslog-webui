#!/bin/bash

# Set variables
LOG_FILE="/var/log/monitor.log"
MYSQL_USER="rsyslog"
MYSQL_PASSWORD="rsyslog_password"
MYSQL_DATABASE="rsyslog"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}

# Check MySQL
check_mysql() {
    if mysql -u$MYSQL_USER -p$MYSQL_PASSWORD -e "SELECT 1" $MYSQL_DATABASE >/dev/null 2>&1; then
        log_message "MySQL is running"
        return 0
    else
        log_message "MySQL is not responding"
        return 1
    fi
}

# Check rsyslog
check_rsyslog() {
    if rsyslogd -N1 >/dev/null 2>&1; then
        log_message "rsyslog is running"
        return 0
    else
        log_message "rsyslog is not responding"
        return 1
    fi
}

# Check Apache
check_apache() {
    if curl -s http://localhost:80 >/dev/null 2>&1; then
        log_message "Apache is running"
        return 0
    else
        log_message "Apache is not responding"
        return 1
    fi
}

# Check disk space
check_disk_space() {
    local threshold=90
    local usage=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
    if [ $usage -gt $threshold ]; then
        log_message "Warning: Disk usage is above $threshold%"
        return 1
    else
        log_message "Disk usage is normal: $usage%"
        return 0
    fi
}

# Run all checks
main() {
    log_message "Starting health checks"
    
    check_mysql
    check_rsyslog
    check_apache
    check_disk_space
    
    log_message "Health checks completed"
}

main 