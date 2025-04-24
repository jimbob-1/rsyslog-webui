# Web UI for rsyslog

A modern web interface for rsyslog with real-time monitoring, analytics, and alerting capabilities.

## Features

- Real-time log viewing and searching
- Interactive dashboard with charts and statistics
- Alert system with custom rules
- Docker support for easy deployment
- Automatic log rotation and cleanup
- System health monitoring
- Mobile-responsive design

## Requirements

### Docker Setup (Recommended)
- Docker and Docker Compose
- At least 2GB RAM
- 10GB free disk space

### Manual Setup
- Apache with PHP 8.2+
- MySQL 8.0+
- rsyslog with MySQL support
- PHP extensions: mysqli, pdo_mysql, json

## Quick Start with Docker

1. Clone the repository:
```bash
git clone https://github.com/Tiny-Lama/rsyslog-webui.git
cd rsyslog-webui
```

2. Start the services:
```bash
docker-compose up -d
```

3. Access the web interface:
- Main interface: http://localhost:8080
- Dashboard: http://localhost:8080/dashboard.php
- Alerts: http://localhost:8080/alerts.php

## Manual Installation

1. Clone the repository:
```bash
git clone https://github.com/Tiny-Lama/rsyslog-webui.git /var/www/html/syslog-ui
```

2. Create the configuration file:
```bash
cp config-template.php config.php
```

3. Edit the configuration:
```bash
nano config.php
```

4. Configure rsyslog to store logs in MySQL:
```bash
# Add to /etc/rsyslog.conf
$ModLoad ommysql
*.* :ommysql:127.0.0.1,SyslogTableName,SQLUSER,SQLPASSWORD
```

5. Restart rsyslog:
```bash
systemctl restart rsyslog
```

## Configuration

### Database Settings
- `$mysql_server`: MySQL server address
- `$mysql_database`: Database name
- `$mysql_user`: Database user
- `$mysql_password`: Database password
- `$keep_logs_for_days`: Number of days to keep logs

### Site Settings
- `$site_name`: Name of your site
- `date_default_timezone_set`: Set your timezone

## Features in Detail

### Dashboard
- Real-time log volume chart
- Error distribution visualization
- Top error sources
- System health status
- Auto-refreshing every 30 seconds

### Alert System
- Create custom alert rules
- Monitor error rates
- Track log volume
- Pattern matching
- Alert history
- Enable/disable alerts

### Log Management
- Advanced search capabilities
- Log rotation
- Automatic cleanup
- Export functionality

## Maintenance

### Docker
- View logs: `docker-compose logs -f`
- Restart services: `docker-compose restart`
- Stop services: `docker-compose down`
- Update: `git pull && docker-compose up -d --build`

### Manual
- Log cleanup runs daily at 1 AM
- Check logs in `/var/log/rsyslog-webui/`
- Monitor disk usage regularly

## Security

- All database queries use prepared statements
- Input validation and sanitization
- Secure session handling
- CSRF protection
- Rate limiting on API endpoints

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Original code by [hmsdao](https://github.com/hmsdao/bootstrap-rsyslog-ui)
- Bootstrap for the UI framework
- Chart.js for visualizations
