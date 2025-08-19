# Web Dashboard Setup Guide - ESP32 Flood Warning System

## Overview

Web Dashboard ini merupakan alternatif dari Blynk yang dapat di-host sendiri. Dashboard menyediakan real-time monitoring, historical data, dan remote control untuk sistem peringatan dini banjir ESP32.

## Features

### 🎯 **Real-time Monitoring**
- Live water level monitoring dengan gauge visual
- Warning level indicators dengan color-coded system
- Battery dan solar panel status monitoring
- Connection status dan system health indicators

### 📊 **Data Visualization**
- Interactive charts untuk historical data
- Multiple time periods (1h, 6h, 24h, 7d, 30d)
- System statistics dan performance metrics
- Export functionality untuk data analysis

### 🎛️ **Remote Control**
- Manual testing untuk lampu dan sirine
- System configuration management
- Alert acknowledgment system
- Emergency controls

### 🔔 **Alert System**
- Real-time alerts untuk different warning levels
- Event logging dengan severity levels
- Alert history dan acknowledgment tracking
- Email notifications (dapat dikembangkan)

## Installation Requirements

### Server Requirements
```
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.2+
- Apache/Nginx web server
- PHP Extensions: mysqli, json, curl
- Minimum 100MB disk space
```

### Development Requirements
```
- Text editor (VS Code, Sublime, etc.)
- Web browser modern (Chrome, Firefox, Safari)
- Database management tool (phpMyAdmin, MySQL Workbench)
```

## Installation Steps

### Step 1: Server Setup

1. **Install XAMPP/WAMP/LAMP**
   ```bash
   # For Ubuntu/Debian
   sudo apt update
   sudo apt install apache2 mysql-server php php-mysql
   
   # For Windows: Download XAMPP
   # For macOS: Download MAMP
   ```

2. **Start Services**
   ```bash
   sudo systemctl start apache2
   sudo systemctl start mysql
   ```

### Step 2: Database Setup

1. **Create Database**
   ```sql
   CREATE DATABASE flood_warning_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'flood_user'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON flood_warning_db.* TO 'flood_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Update Database Configuration**
   Edit `config/database.php`:
   ```php
   private $host = "localhost";
   private $database = "flood_warning_db";
   private $username = "flood_user";  // Update if needed
   private $password = "your_secure_password";  // Update if needed
   ```

### Step 3: File Upload

1. **Copy Files**
   ```bash
   # Copy semua files ke web directory
   sudo cp -r web_dashboard/ /var/www/html/flood_warning/
   
   # Set proper permissions
   sudo chown -R www-data:www-data /var/www/html/flood_warning/
   sudo chmod -R 755 /var/www/html/flood_warning/
   ```

2. **For XAMPP (Windows/Mac)**
   ```
   Copy folder web_dashboard ke:
   Windows: C:\xampp\htdocs\flood_warning\
   Mac: /Applications/XAMPP/htdocs/flood_warning/
   ```

### Step 4: Database Initialization

1. **Auto Setup via Web**
   - Buka browser: `http://localhost/flood_warning/setup.php`
   - Klik "Mulai Setup Database"
   - Tunggu hingga setup selesai

2. **Manual Setup via CLI**
   ```bash
   cd /path/to/web_dashboard
   php config/database.php
   ```

### Step 5: Test Installation

1. **Access Dashboard**
   - URL: `http://localhost/flood_warning/`
   - Atau: `http://your-domain.com/flood_warning/`

2. **Verify APIs**
   ```bash
   # Test data receiver
   curl -X POST http://localhost/flood_warning/api/data_receiver.php \
     -H "Content-Type: application/json" \
     -d '{"water_level":1.5,"warning_level":1,"battery_voltage":12.1,"solar_voltage":15.2,"system_status":"NORMAL"}'
   
   # Test dashboard data
   curl http://localhost/flood_warning/api/dashboard_data.php?action=current
   ```

## ESP32 Configuration

### Update Arduino Code

1. **Web Server URL**
   ```cpp
   // Update in esp32_flood_warning_web.ino
   char webServerURL[] = "http://your-domain.com/flood_warning/api/data_receiver.php";
   // Atau untuk local testing:
   char webServerURL[] = "http://192.168.1.100/flood_warning/api/data_receiver.php";
   ```

2. **Required Libraries**
   ```cpp
   #include <HTTPClient.h>
   #include <ArduinoJson.h>  // Version 6.21.3+
   ```

3. **WiFi Configuration**
   ```cpp
   char ssid[] = "YOUR_WIFI_SSID";
   char pass[] = "YOUR_WIFI_PASSWORD";
   ```

## API Endpoints

### Data Receiver API
```
POST /api/data_receiver.php
Content-Type: application/json

{
  "water_level": 1.5,
  "warning_level": 1,
  "battery_voltage": 12.1,
  "solar_voltage": 15.2,
  "system_status": "NORMAL"
}
```

### Dashboard Data API
```
GET /api/dashboard_data.php?action=current
GET /api/dashboard_data.php?action=historical&period=24h
GET /api/dashboard_data.php?action=config
```

### Control API
```
POST /api/control.php
Content-Type: application/x-www-form-urlencoded

action=test_lights
action=system_status
action=acknowledge_alert&alert_id=123
```

## Configuration Options

### System Thresholds
```sql
-- Update via database atau web interface
UPDATE system_config SET config_value = '1.2' WHERE config_key = 'level_1_threshold';
UPDATE system_config SET config_value = '2.5' WHERE config_key = 'level_2_threshold';
UPDATE system_config SET config_value = '3.5' WHERE config_key = 'level_3_threshold';
```

### Alert Settings
```sql
UPDATE system_config SET config_value = 'admin@yourcompany.com' WHERE config_key = 'alert_email';
UPDATE system_config SET config_value = 'Flood Warning Site #1' WHERE config_key = 'system_name';
```

## Customization

### Theme Colors
Edit `index.html` Tailwind config:
```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#1e40af',    // Blue
                secondary: '#64748b',  // Gray
                success: '#059669',    // Green
                warning: '#d97706',    // Orange
                danger: '#dc2626',     // Red
                info: '#0ea5e9'        // Light Blue
            }
        }
    }
}
```

### Chart Configuration
Edit `js/dashboard.js`:
```javascript
// Update chart colors, intervals, etc.
const chartConfig = {
    borderColor: '#3b82f6',
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    tension: 0.4,
    // Add more customizations
};
```

## Security Considerations

### 1. Database Security
```sql
-- Create dedicated user dengan limited privileges
CREATE USER 'flood_readonly'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT ON flood_warning_db.* TO 'flood_readonly'@'localhost';
```

### 2. API Security
```php
// Add API key authentication
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($api_key !== 'your_secure_api_key') {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}
```

### 3. HTTPS Setup
```apache
# Apache SSL configuration
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/html/flood_warning
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
</VirtualHost>
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Failed
```
Error: SQLSTATE[HY000] [1045] Access denied

Solutions:
- Check database credentials in config/database.php
- Verify MySQL service is running
- Check user permissions: GRANT ALL ON flood_warning_db.* TO 'user'@'localhost';
```

#### 2. ESP32 Can't Send Data
```
Error: HTTP POST failed

Solutions:
- Check web server URL in Arduino code
- Verify WiFi connection on ESP32
- Check firewall settings on server
- Test API endpoint dengan curl
```

#### 3. Charts Not Loading
```
Error: Chart.js not found

Solutions:
- Check internet connection (CDN dependency)
- Download Chart.js locally if needed
- Check browser console for JavaScript errors
```

#### 4. Real-time Updates Not Working
```
Error: API calls returning 404

Solutions:
- Check API file paths dan permissions
- Verify web server configuration
- Check PHP error logs: tail -f /var/log/apache2/error.log
```

## Performance Optimization

### 1. Database Indexing
```sql
-- Add indexes untuk better performance
CREATE INDEX idx_timestamp ON sensor_readings(timestamp);
CREATE INDEX idx_warning_level ON sensor_readings(warning_level);
CREATE INDEX idx_event_timestamp ON system_events(timestamp);
```

### 2. Data Cleanup
```sql
-- Auto cleanup old data (run via cron)
DELETE FROM sensor_readings WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY);
DELETE FROM system_events WHERE timestamp < DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### 3. Caching
```php
// Add simple file caching for dashboard data
$cache_file = "cache/dashboard_" . date('Y-m-d-H-i') . ".json";
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 60) {
    echo file_get_contents($cache_file);
    exit;
}
```

## Backup and Maintenance

### Automated Backup
```bash
#!/bin/bash
# backup_flood_db.sh
DATE=$(date +"%Y%m%d_%H%M%S")
mysqldump -u flood_user -p flood_warning_db > backup_flood_${DATE}.sql
tar -czf backup_flood_${DATE}.tar.gz backup_flood_${DATE}.sql web_dashboard/
```

### Monitoring Script
```bash
#!/bin/bash
# monitor_system.sh
curl -f http://localhost/flood_warning/api/dashboard_data.php?action=current > /dev/null
if [ $? -ne 0 ]; then
    echo "WARNING: Flood Warning System API not responding" | mail -s "System Alert" admin@company.com
fi
```

## Advanced Features

### Email Notifications
```php
// Add to data_receiver.php
function sendEmailAlert($level, $waterLevel) {
    $to = "admin@yourcompany.com";
    $subject = "Flood Warning Alert - Level {$level}";
    $message = "Water level reached {$waterLevel}m";
    $headers = "From: floodwarning@yourcompany.com";
    
    mail($to, $subject, $message, $headers);
}
```

### SMS Integration
```php
// Using Twilio atau SMS gateway
function sendSMSAlert($message) {
    // Implement SMS gateway integration
    $url = "https://api.smsgateway.com/send";
    $data = [
        'phone' => '+62812345678',
        'message' => $message,
        'api_key' => 'your_sms_api_key'
    ];
    // Send via cURL
}
```

### Weather Integration
```php
// Add weather data correlation
function getWeatherData() {
    $api_key = "your_weather_api_key";
    $url = "https://api.openweathermap.org/data/2.5/weather?lat=-6.2088&lon=106.8456&appid={$api_key}";
    
    $response = file_get_contents($url);
    return json_decode($response, true);
}
```

## Support dan Documentation

### Online Resources
- Project Repository: [GitHub Link]
- API Documentation: [API Docs Link]
- Video Tutorials: [YouTube Playlist]
- Community Forum: [Forum Link]

### Contact Information
- Technical Support: tech@emergent.ai
- Documentation: docs@emergent.ai
- Community: community@emergent.ai

---

**Happy Monitoring! 🌊⚡**