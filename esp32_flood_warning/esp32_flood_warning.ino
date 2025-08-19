/*
 * ESP32 Flood Early Warning System (EWS)
 * Sistem Peringatan Dini Bahaya Banjir
 * 
 * Author: Emergent AI
 * Date: 2025
 * 
 * Komponen:
 * - ESP32 DevKit v1
 * - Sensor Ultrasonik HC-SR04
 * - 4x Relay Module (untuk lampu strobo dan sirine)
 * - 3x Lampu Strobo (Hijau, Kuning, Merah)
 * - 1x Sirine
 * - Solar Panel + Charge Controller + Battery Li-ion
 * 
 * Level Peringatan:
 * - Level 1 (1m): Lampu Hijau - Normal
 * - Level 2 (2m): Lampu Kuning - Waspada  
 * - Level 3 (3m): Lampu Merah + Sirine - Bahaya
 */

#define BLYNK_TEMPLATE_ID "TMPL6xxxxxxxx"  // Akan diisi setelah setup Blynk
#define BLYNK_TEMPLATE_NAME "Flood Warning System"
#define BLYNK_AUTH_TOKEN "xxxxxxxxxxxxxxxxxxxxxxxxxxx"

#include <WiFi.h>
#include <BlynkSimpleEsp32.h>
#include <NewPing.h>

// Konfigurasi WiFi
char ssid[] = "YOUR_WIFI_SSID";
char pass[] = "YOUR_WIFI_PASSWORD";

// Pin Definitions
#define TRIGGER_PIN 4
#define ECHO_PIN 5
#define MAX_DISTANCE 400  // Maximum distance sensor (cm)

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
unsigned long measurementInterval = 2000; // Baca sensor setiap 2 detik

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
  Blynk.begin(BLYNK_AUTH_TOKEN, ssid, pass);
  
  Serial.println("ESP32 Flood Warning System Started!");
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
  
  delay(100);
}

void readSensors() {
  // Baca jarak dari sensor ultrasonik
  unsigned int distance = sonar.ping_cm();
  
  if (distance == 0) {
    // Jika sensor error, gunakan nilai sebelumnya
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
  solarVoltage = analogRead(SOLAR_PIN) * (3.3 / 4095.0) * 2;
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
  Blynk.virtualWrite(V_WATER_LEVEL, waterLevel);
  Blynk.virtualWrite(V_WARNING_LEVEL, warningLevel);
  Blynk.virtualWrite(V_BATTERY_VOLTAGE, batteryVoltage);
  Blynk.virtualWrite(V_SOLAR_VOLTAGE, solarVoltage);
  
  // Status sistem
  String status = getWarningText();
  Blynk.virtualWrite(V_SYSTEM_STATUS, status);
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
  
  Serial.println("========================");
}

// Fungsi Blynk untuk kontrol manual dari aplikasi
BLYNK_WRITE(V5) { // Manual Test Button
  int buttonValue = param.asInt();
  if (buttonValue == 1) {
    // Test semua lampu secara berurutan
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