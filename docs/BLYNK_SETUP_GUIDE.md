# Panduan Setup Blynk - Flood Early Warning System

## Langkah 1: Install Aplikasi Blynk

### Mobile App
1. **Android**: Download dari Google Play Store - "Blynk IoT"
2. **iOS**: Download dari App Store - "Blynk IoT"

### Desktop/Web
1. Buka browser dan akses: https://blynk.cloud
2. Atau download Blynk Console untuk desktop

## Langkah 2: Buat Akun Blynk

1. Buka aplikasi Blynk IoT
2. Pilih "Sign Up" untuk akun baru
3. Masukkan email dan password
4. Verifikasi email Anda
5. Login ke akun yang telah dibuat

## Langkah 3: Buat Template Baru

1. Di dashboard Blynk, pilih **"Developer Zone"**
2. Klik **"New Template"**
3. Isi informasi template:
   ```
   Name: Flood Warning System
   Hardware: ESP32
   Connection Type: WiFi
   ```
4. Klik **"Done"**

## Langkah 4: Setup Datastreams

Pada template yang telah dibuat, buat datastreams berikut:

### Datastream 1: Water Level
```
Virtual Pin: V0
Name: Water Level
Data Type: Double
Units: meter
Min: 0
Max: 5
Default: 0
```

### Datastream 2: Warning Level  
```
Virtual Pin: V1
Name: Warning Level
Data Type: Integer
Min: 0
Max: 3
Default: 0
```

### Datastream 3: Battery Voltage
```
Virtual Pin: V2  
Name: Battery Voltage
Data Type: Double
Units: V
Min: 0
Max: 15
Default: 12
```

### Datastream 4: Solar Voltage
```
Virtual Pin: V3
Name: Solar Voltage  
Data Type: Double
Units: V
Min: 0
Max: 25
Default: 0
```

### Datastream 5: System Status
```
Virtual Pin: V4
Name: System Status
Data Type: String
Default: "AMAN"
```

### Datastream 6: Manual Test Button
```
Virtual Pin: V5
Name: Test Button
Data Type: Integer
Min: 0
Max: 1
Default: 0
```

## Langkah 5: Desain Mobile Dashboard

### Widget Layout (Mobile App)
1. **Gauge Widget** - Water Level (V0)
   - Size: 2x2
   - Range: 0-5 meter
   - Label: "Ketinggian Air"

2. **LED Widget** - Warning Level (V1)  
   - Size: 1x1
   - Colors: 
     - 0: Off (Gray)
     - 1: Green
     - 2: Yellow  
     - 3: Red
   - Label: "Status Peringatan"

3. **Gauge Widget** - Battery (V2)
   - Size: 1x1  
   - Range: 0-15V
   - Label: "Battery"

4. **Gauge Widget** - Solar (V3)
   - Size: 1x1
   - Range: 0-25V  
   - Label: "Solar Panel"

5. **Label Widget** - System Status (V4)
   - Size: 2x1
   - Label: "Status Sistem"

6. **Button Widget** - Test (V5)
   - Size: 1x1
   - Mode: Push
   - Label: "Test Lampu"

### Chart Widget untuk Historical Data
7. **Chart Widget** - Water Level History
   - Size: 4x2
   - Datastream: V0 (Water Level)
   - Time range: 24 hours
   - Update interval: 1 minute

## Langkang 6: Setup Web Dashboard

1. Login ke https://blynk.cloud
2. Pilih template "Flood Warning System"
3. Klik **"Web Dashboard"**
4. Drag and drop widgets:

### Web Dashboard Layout
- **Chart**: Water Level trend (large, center)
- **Gauge**: Current water level (top left)
- **Status**: Warning level indicator (top right)  
- **Info**: Battery & Solar status (bottom)
- **Control**: Manual test button (bottom right)

## Langkah 7: Mendapatkan Auth Token

1. Buat **New Device** dari template
2. Copy **Auth Token** yang digenerate
3. Masukkan token ke dalam code ESP32:
   ```cpp
   #define BLYNK_AUTH_TOKEN "YourAuthTokenHere"
   ```

## Langkah 8: Setup Notifications

### Email Notifications
1. Di template settings, aktifkan **"Notifications"**
2. Setup rules:
   ```
   If V1 (Warning Level) >= 2
   Then send notification: "PERINGATAN: Level air mencapai batas waspada!"
   
   If V1 (Warning Level) >= 3  
   Then send notification: "BAHAYA: Level air sangat tinggi! Segera evakuasi!"
   ```

### Push Notifications (Mobile)
1. Aktifkan push notifications di mobile app
2. Setup notification rules sama seperti email

## Langkah 9: Testing Koneksi

1. Upload code ESP32 dengan Auth Token yang benar
2. Pastikan WiFi credentials sudah sesuai
3. Monitor Serial output untuk koneksi
4. Check device status di Blynk console
5. Verify data muncul di dashboard

## Langkah 10: Advanced Features

### Automation Rules
```
Rule 1: Auto Test
- Trigger: Every day at 06:00
- Action: Send value 1 to V5 (Test button)

Rule 2: Daily Report  
- Trigger: Every day at 18:00
- Action: Send email with daily statistics

Rule 3: Low Battery Warning
- Trigger: If V2 < 10V
- Action: Send notification "Battery level low!"
```

### Historical Data Export
1. Go to **"Device"** → **"Timeline"**
2. Select date range
3. Export data as CSV untuk analysis

## Troubleshooting

### Koneksi Gagal
- Pastikan WiFi credentials benar
- Check Auth Token
- Verify template setup
- Check internet connection

### Data Tidak Muncul
- Verify datastream pins (V0-V5)
- Check widget configuration  
- Monitor Serial output
- Restart ESP32

### Notification Tidak Bekerja
- Check notification settings
- Verify trigger rules
- Test with manual values
- Check email/push permissions

## Template ID dan Auth Token

Setelah setup lengkap, update code ESP32 dengan:
```cpp
#define BLYNK_TEMPLATE_ID "TMPL6xxxxxxxx"  // Dari template settings
#define BLYNK_AUTH_TOKEN "xxxxxxxxxxx"     // Dari device settings
```

## Support dan Dokumentasi

- Official Docs: https://docs.blynk.io
- Community Forum: https://community.blynk.cc  
- Video Tutorials: YouTube "Blynk IoT"
- Free Plan Limits: 
  - Unlimited devices
  - 1 template
  - 2GB data storage per month