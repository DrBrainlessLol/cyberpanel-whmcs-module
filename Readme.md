# CyberPanel WHMCS Module (2025 Edition)

A modernized WHMCS server module for automating CyberPanel hosting account management. This updated version includes improved security, error handling, and compatibility with the latest WHMCS and CyberPanel versions.

<div align="center">

[![PayPal](https://img.shields.io/badge/PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white)](https://paypal.me/rawflyanime)
[![Discord](https://img.shields.io/badge/Discord-7289DA?style=for-the-badge&logo=discord&logoColor=white)](https://discord.com/)

**Support this project! Donations help with server maintenance and development costs.**

</div>

## Module Functions

This module provides full automation for:

- ✅ **Account Creation** - Automated website and user account provisioning
- ✅ **Account Termination** - Clean removal of websites and associated data
- ✅ **Account Suspension/Unsuspension** - Temporary account control
- ✅ **Package Management** - Dynamic package changes
- ✅ **Password Management** - Secure password updates
- ✅ **Single Sign-On** - Auto-login for customers and admins

## Enhanced Features (2025 Edition)

- 🔒 **SSL Certificate Management** - Automatic SSL certificate provisioning
- 📧 **DKIM Support** - Email authentication setup
- 🛡️ **Security Enhancements** - Input validation and secure API communication
- 🐘 **PHP Version Control** - Support for PHP 7.4 through 8.3
- 🎨 **Modern UI** - Improved forms with better styling
- 📊 **Better Error Handling** - Comprehensive error reporting and logging
- 🔄 **API Retry Logic** - Automatic retry on connection failures

## System Requirements

- **WHMCS**: Version 8.0 or higher
- **CyberPanel**: Version 2.0 or higher  
- **PHP**: Version 7.4 or higher
- **cURL**: PHP extension enabled
- **JSON**: PHP extension enabled

## Installation

### Automated Installation (Recommended)
1. SSH into your WHMCS server
2. Navigate to your WHMCS modules directory:
   ```bash
   cd /path/to/whmcs/modules/servers/
   ```
3. Clone the repository:
   ```bash
   git clone https://github.com/DrBrainlessLol/cyberpanel-whmcs-module.git cyberpanel
   ```
4. Set proper permissions:
   ```bash
   chmod 644 cyberpanel/*.php
   chmod 755 cyberpanel/
   ```

### Manual Installation
1. Download the latest release as a ZIP file from GitHub
2. Extract the contents to `whmcs_root/modules/servers/cyberpanel/`
3. Ensure proper file permissions:
   ```bash
   chmod 644 cyberpanel.php api.php
   chown www-data:www-data cyberpanel.php api.php
   ```

### Verify Installation
Run the connection test to verify everything is working:
```bash
cd /path/to/whmcs/modules/servers/cyberpanel
php test_connection.php
```

## Configuration

### WHMCS Server Setup
1. Log into WHMCS Admin Area
2. Navigate to **Setup → Products/Services → Servers**
3. Click **Add New Server**
4. Configure the server:
   - **Name**: Your server name (e.g., "CyberPanel Server 1")
   - **Hostname**: Your CyberPanel server IP or domain
   - **Type**: CyberPanel
   - **Username**: CyberPanel admin username
   - **Password**: CyberPanel admin password
   - **Port**: 8090
   - **Secure**: Enable if using HTTPS

### Product Configuration
1. Navigate to **Setup → Products/Services → Products/Services**
2. Create or edit a hosting product
3. In **Module Settings** tab:
   - **Module Name**: CyberPanel
   - **Package Name**: Must match your CyberPanel package name exactly
   - **ACL**: user (for regular customers), reseller, or admin
   - **SSL Certificate**: Enable automatic SSL provisioning
   - **DKIM**: Enable email authentication
   - **Open Base Directory**: Enable PHP security
   - **PHP Version**: Select default PHP version

### CyberPanel Package Setup
Ensure you have created packages in CyberPanel that match your WHMCS product settings:
1. Log into CyberPanel
2. Go to **Packages → Create Package**
3. Set package name to match WHMCS product configuration
4. Configure resource limits (disk space, bandwidth, etc.)

## Testing

### Basic Connection Test
```bash
php test_connection.php
```
Enter your server details when prompted. This will test:
- Basic connectivity to CyberPanel
- SSL certificate validation
- API endpoint availability
- Authentication
- System requirements

### Test Account Creation
1. Create a test client in WHMCS
2. Place a manual order for your hosting product
3. Set the order status to "Active"
4. Verify the account appears in CyberPanel
5. Test customer login functionality

## Troubleshooting

### Common Issues

**Connection Failed**
- Verify server IP and port 8090 accessibility
- Check CyberPanel service status: `systemctl status lscpd`
- Ensure firewall allows port 8090

**Authentication Failed**
- Verify admin username and password
- Check API access is enabled in CyberPanel admin user settings
- Confirm user has admin privileges

**Package Not Found**
- Ensure package name matches exactly between WHMCS and CyberPanel
- Package names are case-sensitive
- Create the package in CyberPanel if it doesn't exist

**Account Creation Fails**
- Check server disk space: `df -h`
- Verify domain doesn't already exist in CyberPanel
- Check CyberPanel error logs: `/home/cyberpanel/error-logs.txt`
   ```

## ⚙️ Configuration

### 1. WHMCS Setup
1. Log into WHMCS Admin Area
2. Navigate to **Setup → Products/Services → Servers**
3. Click **Add New Server**
4. Configure the server details:
   - **Name**: Choose a descriptive name (e.g., "CyberPanel Server 1")
   - **Hostname**: Your CyberPanel server IP or domain
   - **IP Address**: Server IP address
   - **Type**: Select "CyberPanel"
   - **Username**: CyberPanel admin username
   - **Password**: CyberPanel admin password
   - **Port**: 8090 (default CyberPanel port)
   - **Secure**: Enable if using HTTPS

### 2. Product Configuration
1. Navigate to **Setup → Products/Services → Products/Services**
2. Create or edit a hosting product
3. Set **Module Settings**:
   - **Module Name**: CyberPanel
   - **Package Name**: CyberPanel package name (e.g., "Default")
   - **ACL**: Access level (user, reseller, admin)
   - **SSL Certificate**: Enable automatic SSL
   - **DKIM**: Enable email authentication
   - **Open Base Directory**: Enable PHP security
   - **PHP Version**: Select default PHP version

### 3. CyberPanel Setup
1. Log into CyberPanel
2. Navigate to **Users → Admin Users**
3. Edit your admin user and ensure **API Access** is enabled
4. Create hosting packages that match your WHMCS product configurations

## 🔧 Configuration Options

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| Package Name | Text | CyberPanel package name | Default |
| ACL | Dropdown | User access level | user |
| SSL Certificate | Yes/No | Auto-issue SSL certificates | Yes |
| DKIM | Yes/No | Enable email authentication | Yes |
| Open Base Directory | Yes/No | PHP security protection | Yes |
| PHP Version | Dropdown | Default PHP version | PHP 8.1 |

## 🐛 Troubleshooting

### Common Error Messages

#### "API Access Disabled"
**Solution**: Enable API access for the admin user in CyberPanel:
1. Go to **Users → Admin Users**
2. Edit the admin user
3. Enable **API Access**

#### "Data supplied is not accepted, following characters are not allowed..."
**Solution**: Remove special characters from the service password:
- Avoid: `$ & ( ) [ ] { } ; : ' < >`
- Use: Letters, numbers, and basic symbols

#### "Unknown Error Occurred"
**Solution**: Check connectivity and firewall:
1. Verify CyberPanel is accessible on port 8090
2. Check firewall rules allow WHMCS server access
3. Verify hostname/IP address is correct
4. Test connection using the module's test function

#### "Invalid JSON response"
**Solution**: Check CyberPanel API status:
1. Verify CyberPanel is running properly
2. Check server resources (CPU, memory, disk space)
3. Review CyberPanel error logs

### Debug Mode
Enable debug logging in WHMCS:
1. Navigate to **Utilities → Logs → Module Log**
2. Enable logging for the CyberPanel module
3. Check logs for detailed error information

## 🔐 Security Best Practices

1. **Use Strong Passwords**: Ensure CyberPanel admin passwords are complex
2. **Enable HTTPS**: Use SSL/TLS for WHMCS-CyberPanel communication
3. **Restrict API Access**: Limit API access to necessary IP addresses
4. **Regular Updates**: Keep both WHMCS and CyberPanel updated
5. **Monitor Logs**: Regularly review module and system logs

## 🆕 Changelog

### Version 2.0.0 (2025)
- ✨ Added support for SSL certificate management
- ✨ Added DKIM email authentication support
- ✨ Added PHP version selection (7.4-8.3)
- ✨ Added security enhancements (open_basedir)
- 🔧 Improved error handling and validation
- 🔧 Enhanced API reliability with retry logic
- 🔧 Modern PHP coding standards
- 🔧 Better WHMCS 8.x compatibility
- 🎨 Updated client area UI with Bootstrap styling

### Version 1.0.0 (Original)
- Basic account creation, suspension, and termination
- Password and package management
- Single sign-on functionality

## Support & Donations

<div align="center">

[![PayPal](https://img.shields.io/badge/PayPal-Donate-blue?style=for-the-badge&logo=paypal)](https://paypal.me/rawflyanime)

**Help keep this project alive!**  
Your donations go towards server maintenance, development time, and keeping the module updated with the latest WHMCS and CyberPanel versions.

</div>

## Developer & Maintainer

**Romaine**
- **GitHub**: [@DrBrainlessLol](https://github.com/DrBrainlessLol) | [@InstaTechHD](https://github.com/InstaTechHD)
- **Discord**: brainlessintellect
- **PayPal**: [Support Development](https://paypal.me/rawflyanime)

*Both GitHub accounts are owned and maintained by the same developer.*

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- Developed and maintained by Romaine
- Special thanks to the CyberPanel and WHMCS communities

---

**Need Help?** Open an issue on GitHub or reach out on Discord!
