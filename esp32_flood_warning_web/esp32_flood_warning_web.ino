/*
 * ESP32 Flood Early Warning System dengan Web Dashboard
 * Dual connectivity: Blynk + Custom Web Server
 * 
 * Author: Emergent AI
 * Date: 2025
 * 
 * Features:
 * - Sensor monitoring & warning system
 * - Blynk IoT integration
 * - Custom web dashboard integration
 * - Solar power management
 * - Dual data transmission
 */

#define BLYNK_TEMPLATE_ID "TMPL6xxxxxxxx"  // Akan diisi setelah setup Blynk
#define BLYNK_TEMPLATE_NAME "Flood Warning System"
#define BLYNK_AUTH_TOKEN "xxxxxxxxxxxxxxxxxxxxxxxxxxx"

#include <WiFi.h>
#include <BlynkSimpleEsp32.h>
#include <NewPing.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// Konfigurasi WiFi
char ssid[] = "YOUR_WIFI_SSID";
char pass[] = "YOUR_WIFI_PASSWORD";

// Konfigurasi Web Server
char webServerURL[] = "http://your-domain.com/web_dashboard/api/data_receiver.php";
// Atau jika local: "http://192.168.1.100/flood_warning/web_dashboard/api/data_receiver.php"

// Pin Definitions
#define TRIGGER_PIN 4
#define ECHO_PIN 5
#define MAX_DISTANCE 400

#define RELAY_GREEN 12    // Relay untuk lampu hijau
#define RELAY_YELLOW 13   // Relay untuk lampu kuning
#define RELAY_RED 14      // Relay untuk lampu merah
#define RELAY_SIREN 15    // Relay untuk sirine

#define BATTERY_PIN A0    // Pin untuk monitoring battery
#define SOLAR_PIN A3      // Pin untuk monitoring solar panel

// Inisialisasi sensor
NewPing sonar(TRIGGER_PIN, ECHO_PIN, MAX_DISTANCE);

// Variables
float waterLevel = 0;
float batteryVoltage = 0;
float solarVoltage = 0;
int warningLevel = 0;
unsigned long lastMeasurement = 0;
unsigned long lastWebSend = 0;
unsigned long measurementInterval = 2000; // Baca sensor setiap 2 detik
unsigned long webSendInterval = 10000;    // Kirim ke web server setiap 10 detik

// Blynk Virtual Pins
#define V_WATER_LEVEL V0
#define V_WARNING_LEVEL V1
#define V_BATTERY_VOLTAGE V2
#define V_SOLAR_VOLTAGE V3
#define V_SYSTEM_STATUS V4

void setup() {
  Serial.begin(115200);
  
  // Initialize pins
  pinMode(RELAY_GREEN, OUTPUT);
  pinMode(RELAY_YELLOW, OUTPUT);
  pinMode(RELAY_RED, OUTPUT);
  pinMode(RELAY_SIREN, OUTPUT);
  
  // Turn off all relays initially (HIGH = OFF untuk relay aktif low)
  digitalWrite(RELAY_GREEN, HIGH);
  digitalWrite(RELAY_YELLOW, HIGH);
  digitalWrite(RELAY_RED, HIGH);
  digitalWrite(RELAY_SIREN, HIGH);
  
  // Connect to WiFi and Blynk
  Serial.println("Connecting to WiFi...");
  WiFi.begin(ssid, pass);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  
  Serial.println("\nWiFi Connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
  
  // Initialize Blynk
  Blynk.config(BLYNK_AUTH_TOKEN);
  
  Serial.println("ESP32 Flood Warning System Started!");
  Serial.println("Dual Mode: Blynk + Web Dashboard");
  Serial.println("================================");
}

void loop() {
  Blynk.run();
  
  // Baca sensor setiap interval tertentu
  if (millis() - lastMeasurement > measurementInterval) {
    readSensors();
    updateWarningLevel();
    controlWarningSystem();
    sendDataToBlynk();
    printStatus();
    
    lastMeasurement = millis();
  }
  
  // Kirim data ke web server setiap interval tertentu
  if (millis() - lastWebSend > webSendInterval) {
    sendDataToWebServer();
    lastWebSend = millis();
  }
  
  delay(100);
}

void readSensors() {
  // Baca jarak dari sensor ultrasonik
  unsigned int distance = sonar.ping_cm();
  
  if (distance == 0) {
    Serial.println("Sensor Error - Using previous value");
  } else {
    // Konversi jarak ke ketinggian air (asumsi sensor dipasang 4m dari dasar)
    float sensorHeight = 400; // cm, tinggi pemasangan sensor dari dasar
    waterLevel = (sensorHeight - distance) / 100.0; // konversi ke meter
    
    // Pastikan nilai tidak negatif
    if (waterLevel < 0) waterLevel = 0;
  }
  
  // Baca voltage battery dan solar panel
  batteryVoltage = analogRead(BATTERY_PIN) * (3.3 / 4095.0) * 2; // Voltage divider
  solarVoltage = analogRead(SOLAR_PIN) * (3.3 / 4095.0) * 3;     // Voltage divider untuk solar
}

void updateWarningLevel() {
  if (waterLevel >= 3.0) {
    warningLevel = 3; // BAHAYA - Merah + Sirine
  } else if (waterLevel >= 2.0) {
    warningLevel = 2; // WASPADA - Kuning
  } else if (waterLevel >= 1.0) {
    warningLevel = 1; // NORMAL - Hijau
  } else {
    warningLevel = 0; // AMAN - Semua mati
  }
}

void controlWarningSystem() {
  // Matikan semua lampu dan sirine terlebih dahulu
  digitalWrite(RELAY_GREEN, HIGH);
  digitalWrite(RELAY_YELLOW, HIGH);
  digitalWrite(RELAY_RED, HIGH);
  digitalWrite(RELAY_SIREN, HIGH);
  
  // Nyalakan sesuai level peringatan
  switch (warningLevel) {
    case 0: // AMAN
      // Semua tetap mati
      break;
      
    case 1: // NORMAL (1m)
      digitalWrite(RELAY_GREEN, LOW); // Nyalakan lampu hijau
      break;
      
    case 2: // WASPADA (2m)
      digitalWrite(RELAY_YELLOW, LOW); // Nyalakan lampu kuning
      break;
      
    case 3: // BAHAYA (3m)
      digitalWrite(RELAY_RED, LOW);   // Nyalakan lampu merah
      digitalWrite(RELAY_SIREN, LOW); // Nyalakan sirine
      break;
  }
}

void sendDataToBlynk() {
  if (Blynk.connected()) {
    Blynk.virtualWrite(V_WATER_LEVEL, waterLevel);
    Blynk.virtualWrite(V_WARNING_LEVEL, warningLevel);
    Blynk.virtualWrite(V_BATTERY_VOLTAGE, batteryVoltage);
    Blynk.virtualWrite(V_SOLAR_VOLTAGE, solarVoltage);
    
    // Status sistem
    String status = getWarningText();
    Blynk.virtualWrite(V_SYSTEM_STATUS, status);
  } else {
    Serial.println("Blynk not connected, attempting reconnection...");
    Blynk.connect();
  }
}

void sendDataToWebServer() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected, cannot send to web server");
    return;
  }
  
  HTTPClient http;
  http.begin(webServerURL);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  StaticJsonDocument<300> doc;
  doc["water_level"] = waterLevel;
  doc["warning_level"] = warningLevel;
  doc["battery_voltage"] = batteryVoltage;
  doc["solar_voltage"] = solarVoltage;
  doc["system_status"] = getWarningText();
  doc["timestamp"] = WiFi.getTime(); // Unix timestamp
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("Sending data to web server:");
  Serial.println(jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("Web server response: ");
    Serial.println(response);
    
    if (httpResponseCode == 200) {
      Serial.println("Data successfully sent to web server");
    } else {
      Serial.printf("Web server returned error code: %d\n", httpResponseCode);
    }
  } else {
    Serial.printf("Error sending to web server: %s\n", http.errorToString(httpResponseCode).c_str());
  }
  
  http.end();
}

String getWarningText() {
  switch (warningLevel) {
    case 0: return "AMAN";
    case 1: return "NORMAL";
    case 2: return "WASPADA";
    case 3: return "BAHAYA";
    default: return "ERROR";
  }
}

void printStatus() {
  Serial.println("===== Status Sistem =====");
  Serial.print("Ketinggian Air: ");
  Serial.print(waterLevel);
  Serial.println(" meter");
  
  Serial.print("Level Peringatan: ");
  Serial.print(warningLevel);
  Serial.print(" (");
  Serial.print(getWarningText());
  Serial.println(")");
  
  Serial.print("Battery: ");
  Serial.print(batteryVoltage);
  Serial.println(" V");
  
  Serial.print("Solar Panel: ");
  Serial.print(solarVoltage);
  Serial.println(" V");
  
  Serial.print("WiFi Status: ");
  Serial.println(WiFi.status() == WL_CONNECTED ? "Connected" : "Disconnected");
  
  Serial.print("Blynk Status: ");
  Serial.println(Blynk.connected() ? "Connected" : "Disconnected");
  
  Serial.println("========================");
}

// Fungsi Blynk untuk kontrol manual dari aplikasi
BLYNK_WRITE(V5) { // Manual Test Button
  int buttonValue = param.asInt();
  if (buttonValue == 1) {
    testAllLights();
  }
}

void testAllLights() {
  Serial.println("Testing all warning lights...");
  
  // Test lampu hijau
  digitalWrite(RELAY_GREEN, LOW);
  delay(1000);
  digitalWrite(RELAY_GREEN, HIGH);
  delay(500);
  
  // Test lampu kuning
  digitalWrite(RELAY_YELLOW, LOW);
  delay(1000);
  digitalWrite(RELAY_YELLOW, HIGH);
  delay(500);
  
  // Test lampu merah
  digitalWrite(RELAY_RED, LOW);
  delay(1000);
  digitalWrite(RELAY_RED, HIGH);
  delay(500);
  
  // Test sirine
  digitalWrite(RELAY_SIREN, LOW);
  delay(2000);
  digitalWrite(RELAY_SIREN, HIGH);
  
  Serial.println("Test completed!");
}

// Fungsi untuk handle command dari web server
void checkWebCommands() {
  // Implementasi polling untuk command dari web server
  // Bisa menggunakan HTTP GET request ke endpoint terpisah
  // Untuk simplicity, ini bisa diimplementasikan later
}

// Power management functions
void enterLowPowerMode() {
  if (warningLevel == 0 && batteryVoltage < 11.0) {
    Serial.println("Entering low power mode...");
    
    // Reduce WiFi power
    WiFi.setSleep(true);
    
    // Increase measurement intervals
    measurementInterval = 10000; // 10 detik
    webSendInterval = 60000;     // 1 menit
    
    // Reduce CPU frequency
    setCpuFrequencyMhz(80); // dari 240MHz ke 80MHz
  } else {
    // Normal power mode
    WiFi.setSleep(false);
    measurementInterval = 2000;  // 2 detik
    webSendInterval = 10000;     // 10 detik
    setCpuFrequencyMhz(240);     // Full speed
  }
}

// WiFi reconnection function
void checkWiFiConnection() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi connection lost. Reconnecting...");
    WiFi.begin(ssid, pass);
    
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
      delay(500);
      Serial.print(".");
      attempts++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("\nWiFi reconnected!");
    } else {
      Serial.println("\nFailed to reconnect WiFi");
    }
  }
}

// Emergency shutdown function
void emergencyShutdown() {
  if (batteryVoltage < 10.0) {
    Serial.println("CRITICAL: Battery voltage too low. Emergency shutdown...");
    
    // Turn off all loads except critical monitoring
    digitalWrite(RELAY_GREEN, HIGH);
    digitalWrite(RELAY_YELLOW, HIGH);
    digitalWrite(RELAY_RED, HIGH);
    digitalWrite(RELAY_SIREN, HIGH);
    
    // Send emergency alert
    if (WiFi.status() == WL_CONNECTED) {
      sendEmergencyAlert();
    }
    
    // Enter deep sleep for 1 hour
    esp_sleep_enable_timer_wakeup(3600000000); // 1 hour in microseconds
    esp_deep_sleep_start();
  }
}

void sendEmergencyAlert() {
  HTTPClient http;
  http.begin(webServerURL);
  http.addHeader("Content-Type", "application/json");
  
  StaticJsonDocument<200> doc;
  doc["emergency"] = true;
  doc["message"] = "CRITICAL: System battery low - Emergency shutdown";
  doc["battery_voltage"] = batteryVoltage;
  doc["timestamp"] = WiFi.getTime();
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  if (httpResponseCode > 0) {
    Serial.println("Emergency alert sent successfully");
  }
  
  http.end();
}