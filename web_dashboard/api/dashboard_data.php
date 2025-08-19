<?php
/**
 * Dashboard Data API untuk Web Interface
 * Provides real-time dan historical data
 * Author: Emergent AI
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

class DashboardData {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = new Database();
        $this->pdo = $this->db->connect();
    }

    public function getCurrentData() {
        try {
            // Get latest reading
            $stmt = $this->pdo->prepare("SELECT * FROM sensor_readings ORDER BY timestamp DESC LIMIT 1");
            $stmt->execute();
            $current = $stmt->fetch();

            if (!$current) {
                return $this->jsonResponse(['error' => 'No data available'], 404);
            }

            // Get system statistics
            $stats = $this->getSystemStats();
            
            // Get recent events
            $events = $this->getRecentEvents(5);

            // Get active alerts
            $alerts = $this->getActiveAlerts();

            return $this->jsonResponse([
                'current_reading' => $current,
                'system_stats' => $stats,
                'recent_events' => $events,
                'active_alerts' => $alerts,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            error_log("Dashboard data error: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function getHistoricalData($period = '24h') {
        try {
            $interval_map = [
                '1h' => '1 HOUR',
                '6h' => '6 HOUR', 
                '24h' => '24 HOUR',
                '7d' => '7 DAY',
                '30d' => '30 DAY'
            ];

            $interval = $interval_map[$period] ?? '24 HOUR';
            
            $sql = "SELECT 
                        DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i') as time_label,
                        AVG(water_level) as avg_water_level,
                        MAX(water_level) as max_water_level,
                        MIN(water_level) as min_water_level,
                        AVG(battery_voltage) as avg_battery,
                        AVG(solar_voltage) as avg_solar,
                        MAX(warning_level) as max_warning
                    FROM sensor_readings 
                    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL {$interval})
                    GROUP BY DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i')
                    ORDER BY timestamp ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $historical = $stmt->fetchAll();

            return $this->jsonResponse([
                'period' => $period,
                'data_points' => count($historical),
                'data' => $historical
            ]);

        } catch (Exception $e) {
            error_log("Historical data error: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    public function getSystemStats() {
        // Total readings today
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM sensor_readings WHERE DATE(timestamp) = CURDATE()");
        $stmt->execute();
        $daily_readings = $stmt->fetch()['count'];

        // Average water level today
        $stmt = $this->pdo->prepare("SELECT AVG(water_level) as avg_level FROM sensor_readings WHERE DATE(timestamp) = CURDATE()");
        $stmt->execute();
        $avg_water_today = round($stmt->fetch()['avg_level'] ?? 0, 2);

        // Uptime calculation (time since last reading)
        $stmt = $this->pdo->prepare("SELECT TIMESTAMPDIFF(MINUTE, timestamp, NOW()) as minutes_since FROM sensor_readings ORDER BY timestamp DESC LIMIT 1");
        $stmt->execute();
        $minutes_since = $stmt->fetch()['minutes_since'] ?? 0;
        $system_status = $minutes_since < 5 ? 'ONLINE' : ($minutes_since < 30 ? 'DELAYED' : 'OFFLINE');

        // Total alerts today
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM alert_history WHERE DATE(timestamp) = CURDATE()");
        $stmt->execute();
        $alerts_today = $stmt->fetch()['count'];

        // Peak water level today
        $stmt = $this->pdo->prepare("SELECT MAX(water_level) as max_level FROM sensor_readings WHERE DATE(timestamp) = CURDATE()");
        $stmt->execute();
        $peak_water_today = $stmt->fetch()['max_level'] ?? 0;

        return [
            'daily_readings' => $daily_readings,
            'avg_water_today' => $avg_water_today,
            'peak_water_today' => $peak_water_today,
            'system_status' => $system_status,
            'alerts_today' => $alerts_today,
            'last_update_minutes' => $minutes_since
        ];
    }

    public function getRecentEvents($limit = 10) {
        $stmt = $this->pdo->prepare("SELECT * FROM system_events ORDER BY timestamp DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getActiveAlerts() {
        $stmt = $this->pdo->prepare("SELECT * FROM alert_history WHERE resolved_at IS NULL ORDER BY timestamp DESC LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSystemConfig() {
        try {
            $stmt = $this->pdo->prepare("SELECT config_key, config_value, description FROM system_config ORDER BY config_key");
            $stmt->execute();
            $configs = $stmt->fetchAll();

            $config_array = [];
            foreach ($configs as $config) {
                $config_array[$config['config_key']] = [
                    'value' => $config['config_value'],
                    'description' => $config['description']
                ];
            }

            return $this->jsonResponse(['config' => $config_array]);

        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Config fetch error'], 500);
        }
    }

    private function jsonResponse($data, $status_code = 200) {
        http_response_code($status_code);
        echo json_encode($data);
        return;
    }
}

// Handle requests
$action = $_GET['action'] ?? 'current';
$dashboard = new DashboardData();

switch ($action) {
    case 'current':
        $dashboard->getCurrentData();
        break;
    
    case 'historical':
        $period = $_GET['period'] ?? '24h';
        $dashboard->getHistoricalData($period);
        break;
    
    case 'config':
        $dashboard->getSystemConfig();
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
?>