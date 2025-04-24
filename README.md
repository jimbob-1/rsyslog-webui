# RSyslog WebUI

A modern web interface for viewing and managing RSyslog events. This project is currently in active development.

## ⚠️ Development Status

This is a development version and while mostly functional, there are known issues and incomplete features. Use in production environments is not recommended at this time.

### Known Issues
- Column visibility and export functionality in the events table may not work consistently
- Some UI elements need proper styling and alignment
- Performance optimization needed for large log volumes
- Documentation is incomplete
- Security hardening needed before production use

## Features

- 📊 Modern Bootstrap-based interface for viewing system logs
- 🔍 Real-time log viewing with search and filter capabilities
- 📱 Responsive design that works on desktop and mobile
- 🎨 Severity-based color coding for easy log level identification
- ⚡ Fast data loading with server-side pagination
- 📥 Export capabilities (CSV, Excel, TXT) - *currently being stabilized*
- 📋 Column visibility toggles - *currently being stabilized*

## Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- RSyslog with MySQL output module configured
- Web server (Apache/Nginx)

## Quick Start

1. Clone the repository:
```bash
git clone https://github.com/yourusername/rsyslog-webui.git
```

2. Copy the configuration template:
```bash
cp config-template.php config.php
```

3. Edit `config.php` with your database credentials and settings.

4. Ensure your web server has proper permissions:
```bash
chown -R www-data:www-data /path/to/rsyslog-webui
```

## Docker Setup

A Docker setup is provided for development purposes:

```bash
docker-compose up -d
```

Access the application at `http://localhost:8080`

## Development

### Setting Up Development Environment

1. Clone the repository
2. Install dependencies
3. Copy `config-template.php` to `config.php` and configure
4. Use Docker or set up local development environment

### Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Security Notice

⚠️ This version is not yet hardened for production use. Known areas needing attention:
- Input validation
- SQL injection prevention
- XSS protection
- CSRF protection
- Proper authentication/authorization
- Secure session handling

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Roadmap

- [ ] Stabilize column visibility and export functionality
- [ ] Add user authentication and authorization
- [ ] Implement proper security measures
- [ ] Add unit tests
- [ ] Improve documentation
- [ ] Add configuration UI
- [ ] Performance optimizations
- [ ] Add alert management system
- [ ] Implement log rotation management

## Acknowledgments

- Bootstrap Table library
- Bootstrap framework
- RSyslog team
- All contributors

## Support

This is a development version. For issues and feature requests, please use the GitHub issue tracker.
