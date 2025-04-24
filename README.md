# RSyslog WebUI

A web interface for viewing and managing RSyslog events. This project is currently under active development and not ready for production use.

## ⚠️ Development Status

This is an early development version with multiple known issues that are being worked on. Not recommended for production use.

### Known Issues
- Column visibility and export functionality not working properly
- UI elements need proper styling and alignment
- Performance issues with large log volumes
- Documentation is incomplete
- Security features not implemented yet
- Table filtering and search needs improvement

## Planned Features

- 📊 Bootstrap-based interface for viewing system logs
- 🔍 Real-time log viewing with search capabilities
- 📱 Responsive design for desktop and mobile
- 🎨 Severity-based color coding for log levels
- ⚡ Server-side pagination
- 📥 Export capabilities (CSV, Excel, TXT)
- 📋 Column visibility controls

## Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB
- RSyslog with MySQL output module configured
- Web server (Apache/Nginx)

## Quick Start

1. Clone the repository:
```bash
git clone https://github.com/tinylama/rsyslog-webui.git
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

A basic Docker setup is provided for development:

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

⚠️ This version has no security measures implemented yet. Known areas to be addressed:
- Input validation
- SQL injection prevention
- XSS protection
- CSRF protection
- Authentication/authorization
- Secure session handling

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Roadmap

- [ ] Fix column visibility and export functionality
- [ ] Implement basic security measures
- [ ] Add user authentication
- [ ] Add unit tests
- [ ] Complete documentation
- [ ] Add configuration UI
- [ ] Optimize performance
- [ ] Add alert management
- [ ] Implement log rotation

## Support

This is an early development version. For issues and feature requests, please use the GitHub issue tracker.
