/**
 * Dashboard JavaScript untuk ESP32 Flood Warning System
 * Real-time monitoring dan control interface
 * Author: Emergent AI
 */

class FloodWarningDashboard {
    constructor() {
        this.apiBaseUrl = 'api/';
        this.updateInterval = 5000; // 5 detik
        this.chart = null;
        this.isConnected = false;
        this.lastUpdateTime = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeChart();
        this.startDataUpdates();
        this.loadInitialData();
    }

    setupEventListeners() {
        // Test Lights Button
        document.getElementById('testLightsBtn').addEventListener('click', () => {
            this.testLights();
        });

        // Refresh Data Button
        document.getElementById('refreshDataBtn').addEventListener('click', () => {
            this.loadCurrentData();
        });

        // Export Data Button
        document.getElementById('exportDataBtn').addEventListener('click', () => {
            this.exportData();
        });

        // Chart Period Change
        document.getElementById('chartPeriod').addEventListener('change', (e) => {
            this.updateChart(e.target.value);
        });
    }

    async loadInitialData() {
        try {
            await this.loadCurrentData();
            await this.updateChart('24h');
            this.updateConnectionStatus(true);
        } catch (error) {
            console.error('Failed to load initial data:', error);
            this.updateConnectionStatus(false);
        }
    }

    async loadCurrentData() {
        try {
            const response = await fetch(`${this.apiBaseUrl}dashboard_data.php?action=current`);
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            this.updateCurrentReadings(data.current_reading);
            this.updateSystemStats(data.system_stats);
            this.updateRecentEvents(data.recent_events);
            this.updateActiveAlerts(data.active_alerts);
            
            this.lastUpdateTime = new Date();
            this.updateConnectionStatus(true);

        } catch (error) {
            console.error('Failed to load current data:', error);
            this.updateConnectionStatus(false);
        }
    }

    updateCurrentReadings(reading) {
        if (!reading) return;

        // Water Level
        const waterLevel = parseFloat(reading.water_level);
        document.getElementById('waterLevel').textContent = `${waterLevel.toFixed(1)} m`;
        
        // Warning Level
        const warningLevel = parseInt(reading.warning_level);
        document.getElementById('warningLevel').textContent = warningLevel;
        
        // Update warning level styling
        this.updateWarningLevelDisplay(warningLevel);
        
        // Battery
        const batteryVoltage = parseFloat(reading.battery_voltage);
        document.getElementById('batteryVoltage').textContent = `${batteryVoltage.toFixed(1)} V`;
        this.updateBatteryDisplay(batteryVoltage);
        
        // Solar
        const solarVoltage = parseFloat(reading.solar_voltage);
        document.getElementById('solarVoltage').textContent = `${solarVoltage.toFixed(1)} V`;
        this.updateSolarDisplay(solarVoltage);
        
        // System Status
        document.getElementById('waterLevelStatus').textContent = reading.system_status;

        // Update last update time
        document.getElementById('lastUpdateTime').textContent = 
            new Date(reading.timestamp).toLocaleTimeString('id-ID');
    }

    updateWarningLevelDisplay(level) {
        const card = document.getElementById('warningLevelCard');
        const icon = document.getElementById('warningLevelIcon');
        const text = document.getElementById('warningLevelText');
        
        // Reset classes
        card.className = 'bg-white rounded-xl shadow-lg p-6 border-l-4';
        icon.className = 'p-3 rounded-full';
        
        const configs = {
            0: {
                borderColor: 'border-gray-500',
                iconBg: 'bg-gray-100',
                iconColor: 'text-gray-600',
                icon: 'fas fa-shield-alt',
                text: 'Aman',
                textColor: 'text-gray-600'
            },
            1: {
                borderColor: 'border-green-500',
                iconBg: 'bg-green-100',
                iconColor: 'text-green-600',
                icon: 'fas fa-shield-alt',
                text: 'Normal',
                textColor: 'text-green-600'
            },
            2: {
                borderColor: 'border-yellow-500',
                iconBg: 'bg-yellow-100',
                iconColor: 'text-yellow-600',
                icon: 'fas fa-exclamation-triangle',
                text: 'Waspada',
                textColor: 'text-yellow-600'
            },
            3: {
                borderColor: 'border-red-500',
                iconBg: 'bg-red-100',
                iconColor: 'text-red-600',
                icon: 'fas fa-exclamation-triangle blink',
                text: 'BAHAYA',
                textColor: 'text-red-600'
            }
        };
        
        const config = configs[level] || configs[0];
        
        card.classList.add(config.borderColor);
        icon.classList.add(config.iconBg);
        icon.innerHTML = `<i class="${config.icon} text-2xl ${config.iconColor}"></i>`;
        text.textContent = config.text;
        text.className = `text-sm mt-1 font-medium ${config.textColor}`;
        
        // Update warning level number color
        const warningLevelNum = document.getElementById('warningLevel');
        warningLevelNum.className = `text-3xl font-bold ${config.textColor}`;
    }

    updateBatteryDisplay(voltage) {
        const batteryBar = document.getElementById('batteryBar');
        const batteryIcon = document.getElementById('batteryIcon');
        
        // Calculate battery percentage (assume 10V = 0%, 12.6V = 100%)
        const minVoltage = 10.0;
        const maxVoltage = 12.6;
        const percentage = Math.max(0, Math.min(100, ((voltage - minVoltage) / (maxVoltage - minVoltage)) * 100));
        
        batteryBar.style.width = `${percentage}%`;
        
        // Update battery icon and color based on percentage
        if (percentage > 75) {
            batteryBar.className = 'bg-green-500 h-2 rounded-full transition-all duration-500';
            batteryIcon.className = 'fas fa-battery-full text-2xl text-green-600';
        } else if (percentage > 50) {
            batteryBar.className = 'bg-yellow-500 h-2 rounded-full transition-all duration-500';
            batteryIcon.className = 'fas fa-battery-three-quarters text-2xl text-yellow-600';
        } else if (percentage > 25) {
            batteryBar.className = 'bg-orange-500 h-2 rounded-full transition-all duration-500';
            batteryIcon.className = 'fas fa-battery-half text-2xl text-orange-600';
        } else {
            batteryBar.className = 'bg-red-500 h-2 rounded-full transition-all duration-500';
            batteryIcon.className = 'fas fa-battery-quarter text-2xl text-red-600';
        }
    }

    updateSolarDisplay(voltage) {
        const solarStatus = document.getElementById('solarStatus');
        
        if (voltage > 15) {
            solarStatus.textContent = 'Charging';
            solarStatus.className = 'text-sm text-green-600 font-medium mt-1';
        } else if (voltage > 10) {
            solarStatus.textContent = 'Available';
            solarStatus.className = 'text-sm text-yellow-600 font-medium mt-1';
        } else {
            solarStatus.textContent = 'No Sun';
            solarStatus.className = 'text-sm text-gray-500 mt-1';
        }
    }

    updateSystemStats(stats) {
        if (!stats) return;

        document.getElementById('dailyReadings').textContent = stats.daily_readings || 0;
        document.getElementById('avgWaterToday').textContent = `${stats.avg_water_today || 0} m`;
        document.getElementById('alertsToday').textContent = stats.alerts_today || 0;
        
        // Format uptime
        const minutes = stats.last_update_minutes || 0;
        if (minutes < 60) {
            document.getElementById('systemUptime').textContent = `${Math.floor(minutes)} menit lalu`;
        } else {
            const hours = Math.floor(minutes / 60);
            document.getElementById('systemUptime').textContent = `${hours} jam lalu`;
        }

        // Update system status indicators
        if (stats.system_status === 'ONLINE') {
            document.getElementById('wifiStatus').className = 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs';
            document.getElementById('wifiStatus').textContent = 'Connected';
        } else {
            document.getElementById('wifiStatus').className = 'px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs';
            document.getElementById('wifiStatus').textContent = 'Disconnected';
        }
    }

    updateRecentEvents(events) {
        const container = document.getElementById('recentEvents');
        container.innerHTML = '';

        if (!events || events.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada event terbaru</p>';
            return;
        }

        events.forEach(event => {
            const eventDiv = document.createElement('div');
            eventDiv.className = 'flex items-start space-x-3 p-3 bg-gray-50 rounded-lg';

            const severityColors = {
                'INFO': 'text-blue-500',
                'WARNING': 'text-yellow-500',
                'ERROR': 'text-red-500',
                'CRITICAL': 'text-red-600 blink'
            };

            const severityIcons = {
                'INFO': 'fas fa-info-circle',
                'WARNING': 'fas fa-exclamation-triangle',
                'ERROR': 'fas fa-times-circle',
                'CRITICAL': 'fas fa-exclamation-circle'
            };

            eventDiv.innerHTML = `
                <div class="flex-shrink-0">
                    <i class="${severityIcons[event.severity] || 'fas fa-info-circle'} ${severityColors[event.severity] || 'text-gray-500'}"></i>
                </div>
                <div class="flex-grow">
                    <p class="text-sm font-medium text-gray-800">${event.event_type}</p>
                    <p class="text-xs text-gray-600">${event.event_message}</p>
                    <p class="text-xs text-gray-400 mt-1">${new Date(event.timestamp).toLocaleString('id-ID')}</p>
                </div>
            `;

            container.appendChild(eventDiv);
        });
    }

    updateActiveAlerts(alerts) {
        const alertSection = document.getElementById('alertSection');
        const alertsContainer = document.getElementById('activeAlerts');

        if (!alerts || alerts.length === 0) {
            alertSection.classList.add('hidden');
            return;
        }

        alertSection.classList.remove('hidden');
        alertsContainer.innerHTML = '';

        alerts.forEach(alert => {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'flex justify-between items-center py-2';
            alertDiv.innerHTML = `
                <div>
                    <span class="font-medium">${alert.alert_type}</span>
                    <span class="text-xs ml-2">(${alert.water_level}m)</span>
                </div>
                <button class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700" 
                        onclick="dashboard.acknowledgeAlert(${alert.id})">
                    Acknowledge
                </button>
            `;
            alertsContainer.appendChild(alertDiv);
        });
    }

    async updateChart(period) {
        try {
            const response = await fetch(`${this.apiBaseUrl}dashboard_data.php?action=historical&period=${period}`);
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            this.renderChart(data.data, period);
        } catch (error) {
            console.error('Failed to update chart:', error);
        }
    }

    initializeChart() {
        const ctx = document.getElementById('waterLevelChart').getContext('2d');
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Ketinggian Air (m)',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' m';
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Ketinggian: ${context.parsed.y} m`;
                            }
                        }
                    }
                }
            }
        });
    }

    renderChart(data, period) {
        if (!this.chart || !data) return;

        const labels = data.map(item => {
            const date = new Date(item.time_label);
            if (period === '1h' || period === '6h') {
                return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            } else if (period === '24h') {
                return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
            }
        });

        const waterLevels = data.map(item => parseFloat(item.avg_water_level || 0));

        this.chart.data.labels = labels;
        this.chart.data.datasets[0].data = waterLevels;
        this.chart.update();
    }

    async testLights() {
        const button = document.getElementById('testLightsBtn');
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim Perintah...';
        button.disabled = true;

        try {
            const response = await fetch(`${this.apiBaseUrl}control.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_lights'
            });

            const result = await response.json();

            if (result.status === 'success') {
                button.innerHTML = '<i class="fas fa-check mr-2"></i>Perintah Terkirim!';
                button.className = 'w-full mb-4 bg-green-600 text-white font-medium py-3 px-4 rounded-lg';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.className = 'w-full mb-4 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors';
                    button.disabled = false;
                }, 3000);
            } else {
                throw new Error(result.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Test lights failed:', error);
            button.innerHTML = '<i class="fas fa-times mr-2"></i>Gagal!';
            button.className = 'w-full mb-4 bg-red-600 text-white font-medium py-3 px-4 rounded-lg';
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = 'w-full mb-4 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors';
                button.disabled = false;
            }, 3000);
        }
    }

    async acknowledgeAlert(alertId) {
        try {
            const response = await fetch(`${this.apiBaseUrl}control.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'acknowledge_alert',
                    alert_id: alertId
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                // Reload current data to update alerts
                await this.loadCurrentData();
            }
        } catch (error) {
            console.error('Failed to acknowledge alert:', error);
        }
    }

    exportData() {
        // Simple CSV export functionality
        const csvContent = "data:text/csv;charset=utf-8,Timestamp,Water Level,Warning Level,Battery,Solar\n";
        
        // In a real implementation, you'd fetch all data and format as CSV
        const link = document.createElement("a");
        link.setAttribute("href", csvContent);
        link.setAttribute("download", `flood_warning_data_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    updateConnectionStatus(connected) {
        const statusDiv = document.getElementById('connectionStatus');
        
        if (connected) {
            statusDiv.innerHTML = `
                <div class="w-3 h-3 bg-green-500 rounded-full pulse"></div>
                <span class="text-sm text-green-600 font-medium">Online</span>
            `;
            this.isConnected = true;
        } else {
            statusDiv.innerHTML = `
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <span class="text-sm text-red-600 font-medium">Offline</span>
            `;
            this.isConnected = false;
        }
    }

    startDataUpdates() {
        setInterval(() => {
            this.loadCurrentData();
        }, this.updateInterval);
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.dashboard = new FloodWarningDashboard();
});