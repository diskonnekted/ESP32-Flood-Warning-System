<?php
/**
 * Data Receiver API untuk ESP32 Flood Warning System
 * Endpoint: POST /api/data_receiver.php
 * Author: Emergent AI
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class DataReceiver {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = new Database();
        $this->pdo = $this->db->connect();
    }

    public function receiveData() {
        try {
            // Get JSON data dari ESP32
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);

            // Validate required fields
            if (!$this->validateData($data)) {
                return $this->jsonResponse(['error' => 'Invalid data format'], 400);
            }

            // Save data ke database
            $this->saveReading($data);
            
            // Check untuk alerts
            $this->checkAlerts($data);

            // Log event
            $this->logEvent('DATA_RECEIVED', 
                "Water level: {$data['water_level']}m, Warning: {$data['warning_level']}", 
                'INFO'
            );

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Data received successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            error_log("Data receiver error: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    private function validateData($data) {
        $required_fields = ['water_level', 'warning_level', 'battery_voltage', 'solar_voltage', 'system_status'];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }

        // Validate numeric ranges
        if ($data['water_level'] < 0 || $data['water_level'] > 10) return false;
        if ($data['warning_level'] < 0 || $data['warning_level'] > 3) return false;
        if ($data['battery_voltage'] < 0 || $data['battery_voltage'] > 20) return false;
        if ($data['solar_voltage'] < 0 || $data['solar_voltage'] > 30) return false;

        return true;
    }

    private function saveReading($data) {
        $sql = "INSERT INTO sensor_readings (water_level, warning_level, battery_voltage, solar_voltage, system_status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['water_level'],
            $data['warning_level'],
            $data['battery_voltage'],
            $data['solar_voltage'],
            $data['system_status']
        ]);
    }

    private function checkAlerts($data) {
        // Check untuk warning level changes
        $current_level = $data['warning_level'];
        $water_level = $data['water_level'];

        // Get last alert
        $stmt = $this->pdo->prepare("SELECT warning_level FROM alert_history ORDER BY timestamp DESC LIMIT 1");
        $stmt->execute();
        $last_alert = $stmt->fetch();

        // Jika warning level berubah atau naik, create alert
        if (!$last_alert || $current_level > $last_alert['warning_level']) {
            $this->createAlert($current_level, $water_level);
        }

        // Check battery alerts
        if ($data['battery_voltage'] < 10.8) {
            $this->logEvent('BATTERY_LOW', 
                "Battery voltage: {$data['battery_voltage']}V", 
                $data['battery_voltage'] < 10.0 ? 'CRITICAL' : 'WARNING'
            );
        }

        // Check solar alerts
        if ($data['solar_voltage'] < 5.0 && date('H') >= 8 && date('H') <= 17) {
            $this->logEvent('SOLAR_LOW', 
                "Solar voltage low during daylight: {$data['solar_voltage']}V", 
                'WARNING'
            );
        }
    }

    private function createAlert($warning_level, $water_level) {
        $alert_types = [
            0 => 'NORMAL',
            1 => 'LEVEL_1_WARNING',
            2 => 'LEVEL_2_WARNING', 
            3 => 'LEVEL_3_EMERGENCY'
        ];

        $alert_type = $alert_types[$warning_level] ?? 'UNKNOWN';
        
        // Save alert
        $stmt = $this->pdo->prepare("INSERT INTO alert_history (alert_type, water_level, warning_level) VALUES (?, ?, ?)");
        $stmt->execute([$alert_type, $water_level, $warning_level]);

        // Log event
        $severity = $warning_level >= 3 ? 'CRITICAL' : ($warning_level >= 2 ? 'ERROR' : 'WARNING');
        $this->logEvent($alert_type, "Water level reached {$water_level}m", $severity);
    }

    private function logEvent($type, $message, $severity = 'INFO') {
        $stmt = $this->pdo->prepare("INSERT INTO system_events (event_type, event_message, severity) VALUES (?, ?, ?)");
        $stmt->execute([$type, $message, $severity]);
    }

    private function jsonResponse($data, $status_code = 200) {
        http_response_code($status_code);
        echo json_encode($data);
        return;
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver = new DataReceiver();
    $receiver->receiveData();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>