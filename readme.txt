# Healthcare Management System

A web-based healthcare management system built with PHP and MySQL, designed to manage patient records, medications, and medical staff interactions.

## Features

### For Patients
- Personal dashboard with health overview
- View prescribed medications
- Access medical history
- Update personal profile
- Manage appointments

### For Medical Staff
- Comprehensive patient management
- Prescription management
- Appointment scheduling
- Report generation
- Staff dashboard

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP/WAMP/MAMP
- Web Browser (Chrome, Firefox, Safari, Edge)

## Installation

1. **XAMPP Setup**
   - Install XAMPP
   - Start Apache and MySQL services
   - Verify services are running (green indicators in XAMPP Control Panel)

2. **Project Setup**
   ```bash
   # Clone or copy project files to
   C:/xampp/htdocs/healthcare/
   ```

3. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create new database: `healthcare_db`
   - Import the database schema from `database_schema.sql`

4. **Configuration**
   - Navigate to `config/config.php`
   - Update database credentials if needed
   - Modify BASE_URL if necessary

## File Structure
```
/healthcare
    /assets
        /css
        /js
        /images
    /auth
    /config
    /admin
    /patient
    /includes
```

## Default Login Credentials

### Admin/Medical Staff
- ID: ADMIN001
- Password: yourpassword

### Test Patient
- ID: PAT001
- Password: yourpassword

## Security Features

- Password hashing using bcrypt
- Session management
- SQL injection prevention
- XSS protection
- CSRF protection
- Secure file permissions

## Usage Guidelines

1. **Login**
   - Access system at: http://localhost/healthcare
   - Enter credentials based on user type

2. **Patient Functions**
   - View dashboard
   - Check medications
   - Update profile

3. **Admin Functions**
   - Manage patients
   - Handle prescriptions
   - Generate reports

## Troubleshooting

Common issues and solutions:

1. **Database Connection Failed**
   - Verify XAMPP services are running
   - Check database credentials in config.php
   - Ensure database exists

2. **Access Denied**
   - Check file permissions
   - Verify .htaccess configuration
   - Confirm user privileges

3. **Session Issues**
   - Clear browser cache
   - Check PHP session configuration
   - Verify session timeout settings

## Development

To contribute to this project:

1. Follow PHP PSR-4 coding standards
2. Test thoroughly before committing
3. Document any changes
4. Update README if needed

## Security Notes

- Change default passwords immediately
- Regular system updates recommended
- Keep XAMPP updated
- Monitor error logs
- Regular backup recommended

## Maintenance

Regular maintenance tasks:

1. Database backup
2. Log file cleanup
3. Session cleanup
4. Security updates
5. Performance monitoring

## Support

For technical support:
1. Check documentation
2. Review troubleshooting guide
3. Contact system administrator

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Version History

- 1.0.0: Initial release
- 1.0.1: Security patches
- 1.1.0: Added medication tracking
- 1.1.1: Bug fixes and improvements

---

*Last updated: 2024*

