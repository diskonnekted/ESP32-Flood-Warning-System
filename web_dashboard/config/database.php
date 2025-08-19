<?php
/**
 * Database Configuration untuk Flood Warning System
 * Author: Emergent AI
 * Date: 2025
 */

class Database {
    private $host = "localhost";
    private $database = "flood_warning_db";
    private $username = "root";
    private $password = "";
    private $charset = "utf8mb4";
    private $pdo;

    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            return $this->pdo;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function createTables() {
        $queries = [
            // Tabel untuk sensor readings
            "CREATE TABLE IF NOT EXISTS sensor_readings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                water_level DECIMAL(5,2) NOT NULL,
                warning_level INT NOT NULL,
                battery_voltage DECIMAL(4,2) NOT NULL,
                solar_voltage DECIMAL(4,2) NOT NULL,
                system_status VARCHAR(20) NOT NULL,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_timestamp (timestamp),
                INDEX idx_warning_level (warning_level)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // Tabel untuk system events
            "CREATE TABLE IF NOT EXISTS system_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                event_message TEXT NOT NULL,
                severity ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') DEFAULT 'INFO',
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_timestamp (timestamp),
                INDEX idx_severity (severity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // Tabel untuk configuration
            "CREATE TABLE IF NOT EXISTS system_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_key VARCHAR(100) UNIQUE NOT NULL,
                config_value TEXT NOT NULL,
                description TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

            // Tabel untuk alert history
            "CREATE TABLE IF NOT EXISTS alert_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_type VARCHAR(50) NOT NULL,
                water_level DECIMAL(5,2) NOT NULL,
                warning_level INT NOT NULL,
                alert_sent BOOLEAN DEFAULT FALSE,
                resolved_at DATETIME NULL,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_timestamp (timestamp),
                INDEX idx_alert_type (alert_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        ];

        try {
            $pdo = $this->connect();
            foreach ($queries as $query) {
                $pdo->exec($query);
            }
            
            // Insert default configuration
            $this->insertDefaultConfig($pdo);
            echo "Database tables created successfully!\n";
        } catch (PDOException $e) {
            echo "Error creating tables: " . $e->getMessage() . "\n";
        }
    }

    private function insertDefaultConfig($pdo) {
        $defaultConfigs = [
            ['level_1_threshold', '1.0', 'Water level threshold for Level 1 warning (meters)'],
            ['level_2_threshold', '2.0', 'Water level threshold for Level 2 warning (meters)'],
            ['level_3_threshold', '3.0', 'Water level threshold for Level 3 warning (meters)'],
            ['sensor_height', '4.0', 'Sensor mounting height from ground (meters)'],
            ['alert_email', 'admin@floodwarning.com', 'Email for critical alerts'],
            ['system_name', 'Flood Warning System #1', 'Display name for this system'],
            ['update_interval', '2', 'Sensor update interval in seconds'],
            ['battery_low_threshold', '10.8', 'Battery low voltage threshold'],
            ['battery_critical_threshold', '10.0', 'Battery critical voltage threshold']
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO system_config (config_key, config_value, description) VALUES (?, ?, ?)");
        
        foreach ($defaultConfigs as $config) {
            $stmt->execute($config);
        }
    }
}

// Auto-create tables jika dipanggil langsung
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    $db = new Database();
    $db->createTables();
}
?>