# Hardware Setup - ESP32 Flood Early Warning System

## Komponen yang Dibutuhkan

### Komponen Utama
1. **ESP32 DevKit v1** - Mikrokontroller utama
2. **Sensor Ultrasonik HC-SR04** - Deteksi ketinggian air
3. **4x Relay Module 5V** - Kontrol lampu dan sirine
4. **3x Lampu Strobo LED** (Hijau, Kuning, Merah)
5. **1x Sirine 12V**
6. **Breadboard/PCB** untuk koneksi

### Sistem Catu Daya
1. **Battery Li-ion 18650 (2-3 sel)** - 7.4V/11.1V
2. **Solar Panel 10-20W** - Pengisian battery
3. **Solar Charge Controller** - Proteksi battery
4. **DC-DC Step Down Converter** - 5V untuk relay dan sensor
5. **Voltage Divider Resistors** - Monitoring voltage

### Tools dan Aksesoris
- Kabel jumper male-female
- Terminal block/connector
- Housing/box tahan air (IP65)
- Mounting bracket
- Kabel power dan signal

## Wiring Diagram

### ESP32 Pin Configuration
```
ESP32 Pin  |  Component        |  Description
-----------|-------------------|------------------
GPIO 4     |  HC-SR04 Trig     |  Trigger ultrasonik
GPIO 5     |  HC-SR04 Echo     |  Echo ultrasonik  
GPIO 12    |  Relay 1 IN       |  Kontrol lampu hijau
GPIO 13    |  Relay 2 IN       |  Kontrol lampu kuning
GPIO 14    |  Relay 3 IN       |  Kontrol lampu merah
GPIO 15    |  Relay 4 IN       |  Kontrol sirine
A0 (GPIO36)|  Battery Monitor  |  Monitoring tegangan battery
A3 (GPIO39)|  Solar Monitor    |  Monitoring tegangan solar
3.3V       |  HC-SR04 VCC      |  Power sensor
GND        |  Common Ground    |  Ground semua komponen
```

### Relay Connection
```
Relay Module  |  Load Device     |  Power Source
--------------|------------------|---------------
Relay 1 COM   |  Lampu Hijau +   |  12V Supply
Relay 1 NO    |  Lampu Hijau -   |  Ground
Relay 2 COM   |  Lampu Kuning +  |  12V Supply  
Relay 2 NO    |  Lampu Kuning -  |  Ground
Relay 3 COM   |  Lampu Merah +   |  12V Supply
Relay 3 NO    |  Lampu Merah -   |  Ground
Relay 4 COM   |  Sirine +        |  12V Supply
Relay 4 NO    |  Sirine -        |  Ground
```

### Power System Wiring
```
Solar Panel (+) → Charge Controller Solar (+)
Solar Panel (-) → Charge Controller Solar (-)
Battery (+)     → Charge Controller Battery (+)
Battery (-)     → Charge Controller Battery (-)
Load (+)        → Charge Controller Load (+) → DC-DC Converter Input (+)
Load (-)        → Charge Controller Load (-) → DC-DC Converter Input (-)
DC-DC Out (+)   → ESP32 VIN, Relay VCC
DC-DC Out (-)   → ESP32 GND, Relay GND
```

## Level Peringatan System

### Level 0: AMAN (< 1 meter)
- Semua lampu mati
- Sirine mati
- Status: "AMAN"

### Level 1: NORMAL (≥ 1 meter)
- Lampu hijau menyala (berkedip)
- Lampu lain mati
- Status: "NORMAL"

### Level 2: WASPADA (≥ 2 meter)  
- Lampu kuning menyala (berkedip)
- Lampu lain mati
- Status: "WASPADA"

### Level 3: BAHAYA (≥ 3 meter)
- Lampu merah menyala (berkedip cepat)
- Sirine menyala
- Status: "BAHAYA"

## Spesifikasi Teknis

### Power Consumption
- ESP32: ~240mA (WiFi aktif)
- HC-SR04: ~15mA
- Relay Module (4x): ~60mA
- Lampu Strobo: ~500mA each (saat aktif)
- Sirine: ~800mA
- **Total Max**: ~2.5A (semua aktif)

### Dimensi Box
- Minimum: 25cm x 20cm x 15cm
- Material: ABS/Polycarbonate IP65
- Ventilasi: Cooling fan 12V (optional)

### Range Deteksi
- Sensor HC-SR04: 2cm - 400cm
- Resolusi: 0.3cm
- Akurasi: ±1cm
- Update rate: 2 detik

## Tips Instalasi

1. **Posisi Sensor**
   - Pasang sensor 4 meter di atas permukaan normal air
   - Hindari halangan di depan sensor
   - Lindungi dari air hujan langsung

2. **Kalibrasi**
   - Test sensor di berbagai kondisi cuaca
   - Sesuaikan threshold sesuai kondisi lokal
   - Lakukan kalibrasi berkala

3. **Maintenance**
   - Bersihkan sensor setiap bulan
   - Cek koneksi kabel
   - Monitor kondisi battery
   - Update firmware berkala