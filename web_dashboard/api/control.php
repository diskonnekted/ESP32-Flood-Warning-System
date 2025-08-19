<?php
/**
 * Control API untuk Remote Testing dan Configuration
 * Endpoint untuk manual control ESP32
 * Author: Emergent AI
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class ControlAPI {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = new Database();
        $this->pdo = $this->db->connect();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'test_lights':
                return $this->testLights();
            
            case 'update_config':
                return $this->updateConfig();
            
            case 'acknowledge_alert':
                return $this->acknowledgeAlert();
            
            case 'system_status':
                return $this->getSystemStatus();
            
            case 'reset_system':
                return $this->resetSystem();

            default:
                return $this->jsonResponse(['error' => 'Invalid action'], 400);
        }
    }

    public function testLights() {
        try {
            // Log test command
            $this->logEvent('MANUAL_TEST', 'Manual light test initiated via web dashboard', 'INFO');
            
            // Dalam implementasi nyata, ini akan mengirim command ke ESP32
            // Untuk saat ini, kita simulasi dengan database entry
            $test_data = [
                'command' => 'test_lights',
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'sent'
            ];

            // Save command to database untuk ESP32 polling
            $stmt = $this->pdo->prepare("INSERT INTO system_events (event_type, event_message, severity) VALUES (?, ?, ?)");
            $stmt->execute(['MANUAL_TEST_COMMAND', json_encode($test_data), 'INFO']);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Test lights command sent successfully',
                'command_id' => $this->pdo->lastInsertId()
            ]);

        } catch (Exception $e) {
            error_log("Test lights error: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Failed to send test command'], 500);
        }
    }

    public function updateConfig() {
        try {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);

            if (!isset($data['config_key']) || !isset($data['config_value'])) {
                return $this->jsonResponse(['error' => 'Missing config_key or config_value'], 400);
            }

            $config_key = $data['config_key'];
            $config_value = $data['config_value'];

            // Validate config key
            $valid_keys = [
                'level_1_threshold', 'level_2_threshold', 'level_3_threshold',
                'sensor_height', 'alert_email', 'system_name', 'update_interval',
                'battery_low_threshold', 'battery_critical_threshold'
            ];

            if (!in_array($config_key, $valid_keys)) {
                return $this->jsonResponse(['error' => 'Invalid config key'], 400);
            }

            // Update configuration
            $stmt = $this->pdo->prepare("UPDATE system_config SET config_value = ?, updated_at = NOW() WHERE config_key = ?");
            $result = $stmt->execute([$config_value, $config_key]);

            if ($result) {
                $this->logEvent('CONFIG_UPDATE', "Updated {$config_key} to {$config_value}", 'INFO');
                
                return $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Configuration updated successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Failed to update configuration'], 500);
            }

        } catch (Exception $e) {
            error_log("Update config error: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function acknowledgeAlert() {
        try {
            $json_data = file_get_contents('php://input');
            $data = json_decode($json_data, true);

            $alert_id = $data['alert_id'] ?? null;

            if (!$alert_id) {
                return $this->jsonResponse(['error' => 'Missing alert_id'], 400);
            }

            // Mark alert sebagai resolved
            $stmt = $this->pdo->prepare("UPDATE alert_history SET resolved_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$alert_id]);

            if ($result) {
                $this->logEvent('ALERT_ACKNOWLEDGED', "Alert #{$alert_id} acknowledged by user", 'INFO');
                
                return $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Alert acknowledged successfully'
                ]);
            } else {
                return $this->jsonResponse(['error' => 'Alert not found'], 404);
            }

        } catch (Exception $e) {
            error_log("Acknowledge alert error: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function getSystemStatus() {
        try {
            // Get latest reading
            $stmt = $this->pdo->prepare("SELECT * FROM sensor_readings ORDER BY timestamp DESC LIMIT 1");
            $stmt->execute();
            $latest = $stmt->fetch();

            // Get system uptime
            $stmt = $this->pdo->prepare("SELECT MIN(timestamp) as first_reading FROM sensor_readings");
            $stmt->execute();
            $first_reading = $stmt->fetch()['first_reading'];

            // Calculate uptime
            $uptime_seconds = strtotime('now') - strtotime($first_reading);
            $uptime_days = floor($uptime_seconds / (24 * 3600));
            $uptime_hours = floor(($uptime_seconds % (24 * 3600)) / 3600);

            // Get active alerts count
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM alert_history WHERE resolved_at IS NULL");
            $stmt->execute();
            $active_alerts = $stmt->fetch()['count'];

            // System health check
            $last_update = $latest ? strtotime($latest['timestamp']) : 0;
            $minutes_since_update = (time() - $last_update) / 60;
            
            $health_status = 'HEALTHY';
            if ($minutes_since_update > 10) {
                $health_status = 'WARNING';
            }
            if ($minutes_since_update > 30) {
                $health_status = 'CRITICAL';
            }

            return $this->jsonResponse([
                'system_health' => $health_status,
                'last_reading' => $latest,
                'uptime_days' => $uptime_days,
                'uptime_hours' => $uptime_hours,
                'active_alerts' => $active_alerts,
                'minutes_since_update' => round($minutes_since_update, 1)
            ]);

        } catch (Exception $e) {
            error_log("System status error: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function resetSystem() {
        try {
            // Log reset command
            $this->logEvent('SYSTEM_RESET', 'System reset command initiated via web dashboard', 'WARNING');
            
            // Create reset command untuk ESP32
            $reset_data = [
                'command' => 'system_reset',
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ];

            $stmt = $this->pdo->prepare("INSERT INTO system_events (event_type, event_message, severity) VALUES (?, ?, ?)");
            $stmt->execute(['SYSTEM_RESET_COMMAND', json_encode($reset_data), 'WARNING']);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'System reset command sent',
                'warning' => 'System will restart in a few moments'
            ]);

        } catch (Exception $e) {
            error_log("Reset system error: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Failed to send reset command'], 500);
        }
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $control = new ControlAPI();
    $control->handleRequest();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>