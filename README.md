# 🌊 ESP32 Flood Early Warning System (EWS)
## Sistem Peringatan Dini Bahaya Banjir Berbasis IoT

![ESP32 Flood Warning System](https://img.shields.io/badge/ESP32-Flood%20Warning-blue.svg)
![Version](https://img.shields.io/badge/Version-1.0-green.svg)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)

### 📋 Deskripsi Proyek

Sistem Peringatan Dini (Early Warning System) untuk deteksi bahaya banjir menggunakan ESP32 yang terintegrasi dengan aplikasi Blynk untuk monitoring real-time. Sistem ini menggunakan sensor ultrasonik untuk mendeteksi ketinggian air dan memberikan peringatan visual serta audio berdasarkan 3 level bahaya yang telah ditentukan.

### 🎯 Fitur Utama

- **Multi-Level Warning System**: 3 tingkat peringatan (1m, 2m, 3m)
- **Visual Indicators**: Lampu strobo LED warna-warni (Hijau, Kuning, Merah)
- **Audio Alert**: Sirine otomatis untuk peringatan level tertinggi
- **IoT Connectivity**: Monitoring real-time via WiFi dan aplikasi Blynk
- **Solar Powered**: Sistem catu daya mandiri dengan solar panel
- **Battery Backup**: Battery lithium dengan monitoring voltage
- **Remote Control**: Kontrol dan monitoring dari jarak jauh

### 🔧 Komponen Hardware

#### Komponen Utama
- **ESP32 DevKit v1** - Mikrokontroller utama
- **HC-SR04 Ultrasonic Sensor** - Deteksi ketinggian air
- **4-Channel Relay Module** - Kontrol lampu dan sirine
- **3x LED Strobo** (Hijau, Kuning, Merah)
- **1x Sirine 12V** - Alert audio untuk bahaya tinggi

#### Sistem Power
- **Solar Panel 10-20W** - Sumber energi utama
- **MPPT Charge Controller** - Pengaturan pengisian battery
- **Li-ion Battery 18650 (3S)** - Penyimpanan energi 11.1V
- **DC-DC Buck Converter** - Konverter 12V ke 5V untuk sistem

### 🚨 Sistem Peringatan

| Level | Ketinggian Air | Indikator Visual | Audio | Status |
|-------|---------------|------------------|--------|---------|
| 0 | < 1 meter | Semua mati | Tidak ada | **AMAN** |
| 1 | ≥ 1 meter | Lampu Hijau | Tidak ada | **NORMAL** |
| 2 | ≥ 2 meter | Lampu Kuning | Tidak ada | **WASPADA** |
| 3 | ≥ 3 meter | Lampu Merah | Sirine ON | **BAHAYA** |

### 📱 Integrasi Blynk IoT

#### Dashboard Features
- **Real-time Water Level Monitoring**
- **Warning Level Indicators** 
- **Battery & Solar Panel Status**
- **Historical Data Charts**
- **Remote Manual Testing**
- **Automatic Notifications**

#### Supported Platforms
- **Mobile Apps**: Android & iOS
- **Web Dashboard**: Desktop browser access
- **Email Notifications**: Alert via email
- **Push Notifications**: Instant mobile alerts

### 🔌 Wiring & Installation

#### Pin Configuration ESP32
```cpp
// Sensor Pins
#define TRIGGER_PIN 4    // HC-SR04 Trigger
#define ECHO_PIN 5       // HC-SR04 Echo

// Relay Control Pins  
#define RELAY_GREEN 12   // Lampu Hijau
#define RELAY_YELLOW 13  // Lampu Kuning
#define RELAY_RED 14     // Lampu Merah
#define RELAY_SIREN 15   // Sirine

// Monitoring Pins
#define BATTERY_PIN A0   // Battery Voltage Monitor
#define SOLAR_PIN A3     // Solar Panel Monitor
```

### 💻 Software Requirements

#### Arduino Libraries
- **Blynk Library v1.3.2+** - IoT connectivity
- **NewPing Library v1.9.7+** - Ultrasonic sensor control
- **WiFi Library** (Built-in ESP32)
- **ESP32 Arduino Core v2.0.14+**

#### Development Environment
- **Arduino IDE 1.8.19+** atau **PlatformIO**
- **Blynk IoT Account** (Free tier available)
- **WiFi Network** dengan internet access

### 🚀 Quick Start Guide

1. **Hardware Assembly**
   ```
   1. Connect components sesuai wiring diagram
   2. Install sensor pada ketinggian 4m dari permukaan air normal
   3. Mount lampu strobo dan sirine pada posisi optimal
   4. Setup solar panel dan battery system
   ```

2. **Software Setup**
   ```cpp
   1. Install required libraries
   2. Update WiFi credentials dalam code
   3. Setup Blynk account dan dapatkan Auth Token
   4. Upload code ke ESP32
   ```

3. **Blynk Configuration**
   ```
   1. Create new template "Flood Warning System"
   2. Setup datastreams (V0-V5)
   3. Design mobile & web dashboard
   4. Configure notifications
   ```

### 📊 Technical Specifications

#### Power Consumption
- **Standby Mode**: ~300mA (WiFi connected)
- **Warning Active**: ~1.5A (single lamp)  
- **Emergency Mode**: ~2.5A (all devices active)
- **Battery Life**: 24-48 jam (tergantung usage)

#### Sensor Performance
- **Detection Range**: 4cm - 400cm
- **Accuracy**: ±1cm
- **Update Rate**: 2 detik
- **Weather Resistance**: IP65 enclosure

#### Communication
- **WiFi**: 802.11 b/g/n (2.4GHz)
- **Range**: Up to 100m from router
- **Protocol**: HTTPS/WSS untuk Blynk
- **Latency**: <2 detik untuk alert

### 📖 Documentation

| Document | Description |
|----------|-------------|
| [Hardware Setup](docs/README_HARDWARE.md) | Detailed component dan wiring guide |
| [Blynk Setup](docs/BLYNK_SETUP_GUIDE.md) | Step-by-step Blynk configuration |
| [Circuit Diagram](docs/CIRCUIT_DIAGRAM.md) | Complete schematic dan PCB layout |
| [Installation](docs/INSTALLATION_GUIDE.md) | Field installation procedures |
| [Libraries](libraries.txt) | Required Arduino libraries |

### 🔧 Maintenance

#### Daily Checks
- ✅ Visual inspection warning lights
- ✅ Check battery voltage >10.5V
- ✅ Verify system status di Blynk

#### Weekly Maintenance  
- 🧹 Clean sensor surface
- 🔌 Check cable connections
- 🧪 Test manual functions
- ☀️ Clean solar panel

#### Monthly Service
- 🔧 Full system test
- 📊 Battery performance analysis
- 📏 Sensor calibration
- 💾 Firmware updates

### ⚠️ Safety & Compliance

#### Electrical Safety
- All installations harus mengikuti local electrical codes
- Use proper grounding dan surge protection
- IP65 rating untuk outdoor components
- Fusing dan circuit protection installed

#### Environmental Considerations
- Sensor positioning: minimum 4m height
- Proper drainage untuk control box
- UV protection untuk outdoor cables
- Regular cleaning maintenance

### 🆘 Troubleshooting

#### Common Issues

**System Won't Connect**
```
- Check WiFi credentials
- Verify Blynk Auth Token
- Confirm internet connectivity
- Reset ESP32 dan retry
```

**Inaccurate Readings**
```  
- Clean sensor surface
- Check sensor alignment
- Verify mounting height
- Calibrate sensor values
```

**Battery Draining Fast**
```
- Check solar panel positioning
- Verify charge controller settings  
- Test battery capacity
- Optimize power management
```

### 📈 Future Enhancements

- **Weather Integration**: API cuaca untuk prediksi hujan
- **SMS Alerts**: Backup notification via SMS gateway  
- **Data Logging**: Local SD card storage
- **AI Prediction**: Machine learning untuk early prediction
- **Multi-Sensor**: Multiple sensor locations untuk area coverage

### 🤝 Contributing

Contributions welcome! Please read contributing guidelines dan submit pull requests untuk improvements.

### 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

### 👨‍💻 Author

**Emergent AI Engineering Team**
- Email: support@emergent.ai
- Documentation: [docs.emergent.ai](https://docs.emergent.ai)
- Community: [forum.emergent.ai](https://forum.emergent.ai)

### 🆘 Support

Untuk technical support dan pertanyaan:
- 📧 Email: technical@emergent.ai
- 💬 Forum: [Community Discussion](https://forum.emergent.ai/flood-warning)
- 📱 WhatsApp: +62-xxx-xxxx-xxxx

---

**⚡ Made with ESP32 & ❤️ for Flood Safety**

> **Disclaimer**: Sistem ini dirancang sebagai alat bantu peringatan dini. Selalu ikuti protokol keamanan resmi dan evacuasi procedures dari pihak berwenang dalam situasi banjir sebenarnya.