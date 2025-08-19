# Installation Guide - ESP32 Flood Early Warning System

## Persiapan Instalasi

### Tools yang Dibutuhkan
- Obeng set (Phillips dan minus)
- Tang kombinasi dan wire stripper
- Multimeter untuk testing
- Solder dan timah (untuk koneksi permanen)
- Heat gun dan heat shrink tubing
- Drill dan mata bor untuk mounting
- Level dan meteran
- Cable ties dan mounting bracket

### Lokasi Instalasi
1. **Sensor Ultrasonik**: 4 meter di atas level air normal
2. **Control Box**: Tempat kering, mudah diakses untuk maintenance
3. **Solar Panel**: Area terbuka tanpa bayangan
4. **Lampu Warning**: Posisi visible dari berbagai arah
5. **Sirine**: Posisi optimal untuk jangkauan suara

## Langkah Instalasi

### Step 1: Mounting Control Box

1. **Pilih Lokasi Box**
   - Jarak maksimal 50m dari sensor
   - Terlindung dari hujan langsung
   - Mudah akses untuk maintenance
   - Dekat dengan sumber listrik backup (optional)

2. **Mount ke Tiang/Dinding**
   ```
   Tinggi mounting: 1.5-2 meter dari tanah
   Orientasi: Pintu menghadap ke bawah untuk drainase
   Material: Bracket stainless steel
   ```

3. **Grounding**
   - Pasang grounding rod 1.5m ke tanah
   - Connect ke box dengan cable AWG 12
   - Resistance < 10 Ohm

### Step 2: Solar Panel Installation

1. **Positioning**
   ```
   Angle: 15° menghadap selatan (untuk Indonesia)
   Height: Minimum 3m (anti pencurian)
   Clearance: 50cm dari obstacle terdekat
   ```

2. **Mounting**
   - Gunakan bracket aluminium anti karat
   - Secure dengan baut M8 stainless steel
   - Pastikan struktur kuat untuk angin >60km/h

3. **Wiring ke Control Box**
   ```
   Cable: 2x2.5mm² NYAF (merah/hitam)
   Panjang max: 10 meter
   Protection: Conduit PVC atau spiral wrap
   ```

### Step 3: Sensor Installation

1. **Mount Sensor Housing**
   ```
   Posisi: 4m di atas permukaan air normal
   Angle: Tegak lurus ke bawah (90°)
   Protection: IP65 weatherproof enclosure
   ```

2. **Wiring**
   ```
   Cable: 4-core shielded 0.5mm²
   - Merah: VCC (3.3V)
   - Hitam: GND  
   - Biru: Trigger
   - Putih: Echo
   Max length: 20 meter
   ```

3. **Kalibrasi**
   - Measure jarak sensor ke permukaan air saat normal
   - Input nilai ke variable `sensorHeight` dalam code
   - Test reading pada berbagai level air

### Step 4: Warning Lights Installation

#### Lampu Strobo Setup
```
Lampu Hijau:  Posisi timur (mudah dilihat pagi)
Lampu Kuning: Posisi utara (central visibility)  
Lampu Merah:  Posisi barat (mudah dilihat sore)
```

1. **Mounting Height**
   - Minimum 4m dari tanah
   - Maximum 8m (untuk visibility)
   - 360° visibility (no obstruction)

2. **Power Cable**
   ```
   Specification: 2x1.5mm² outdoor cable
   Length: Sesuaikan dengan jarak ke control box
   Protection: UV resistant conduit
   ```

3. **Waterproofing**
   - Gunakan IP65 connector
   - Apply silicone sealant pada entry points
   - Drip loop pada cable bottom

### Step 5: Siren Installation

1. **Positioning**
   ```
   Height: 6-8 meter (optimal sound propagation)
   Direction: Menghadap area yang perlu diperingatkan
   Distance: Minimum 50m dari residential area (noise consideration)
   ```

2. **Sound Test**
   - Test pada berbagai waktu
   - Pastikan audible radius minimum 500m
   - Cek local noise regulations

### Step 6: System Wiring

#### Main Power Distribution
```
Battery (+) → Fuse 10A → Distribution Block
              │
              ├─ Charge Controller Input (+)
              ├─ DC-DC Converter Input (+)
              └─ Relay Module VCC
```

#### Control Wiring
```
ESP32 GPIO → Optocoupler → Relay Coil → Load
```

#### Monitoring Wiring  
```
Battery Voltage → Voltage Divider → GPIO36
Solar Voltage → Voltage Divider → GPIO39
```

### Step 7: Software Configuration

1. **Upload Arduino Code**
   ```
   Platform: Arduino IDE atau PlatformIO
   Board: ESP32 Dev Module
   Upload Speed: 115200
   ```

2. **WiFi Configuration**
   ```cpp
   char ssid[] = "YOUR_WIFI_SSID";
   char pass[] = "YOUR_WIFI_PASSWORD";
   ```

3. **Blynk Configuration**
   - Setup Blynk account dan template
   - Update BLYNK_AUTH_TOKEN
   - Configure datastreams (V0-V5)

4. **Sensor Calibration**
   ```cpp
   float sensorHeight = 400; // cm, sesuaikan dengan kondisi actual
   ```

### Step 8: System Testing

#### Pre-Operational Test
1. **Power System Test**
   ```
   ✓ Battery voltage: 11.1V ±0.5V
   ✓ Solar charging: >13V saat siang
   ✓ DC-DC output: 5V ±0.1V
   ✓ Relay supply: 5V ±0.1V
   ```

2. **Communication Test**
   ```
   ✓ WiFi connection established
   ✓ Blynk cloud connection
   ✓ Data transmission (V0-V4)
   ✓ Remote control (V5)
   ```

3. **Sensor Test**
   ```
   ✓ Distance reading accuracy ±2cm
   ✓ Update rate: 2 seconds
   ✓ No interference from nearby objects
   ```

4. **Warning System Test**
   ```
   Level 1 (1m): Lampu hijau ON
   Level 2 (2m): Lampu kuning ON  
   Level 3 (3m): Lampu merah + sirine ON
   Manual test: Semua lampu berurutan
   ```

#### Operational Test
1. **Simulate Water Levels**
   - Gunakan objek untuk simulate different distances
   - Verify threshold triggering
   - Test warning escalation

2. **24-Hour Monitoring**
   - Monitor system stability
   - Check battery performance  
   - Verify data logging accuracy

3. **Weather Resistance Test**
   - Test during rain conditions
   - Verify waterproofing integrity
   - Check system performance in humidity

## Commissioning Checklist

### Electrical Safety
- [ ] All connections properly insulated
- [ ] Grounding system installed and tested  
- [ ] Fuses and circuit protection in place
- [ ] No exposed live wires
- [ ] IP rating compliance verified

### Functional Testing
- [ ] Sensor reading accuracy confirmed
- [ ] All warning levels trigger correctly
- [ ] Remote monitoring operational
- [ ] Manual control functions work
- [ ] Battery backup tested

### Documentation
- [ ] Wiring diagram updated with as-built
- [ ] Configuration settings recorded
- [ ] User manual provided
- [ ] Maintenance schedule established
- [ ] Emergency contact list posted

## Troubleshooting Common Issues

### System Won't Start
```
Check: Power supply voltage
Check: Fuse continuity
Check: ESP32 power LED
Solution: Verify 5V supply to ESP32
```

### Sensor Reading Errors
```  
Check: Cable connections
Check: Sensor alignment
Check: Obstruction in sensor path
Solution: Clean sensor face, verify wiring
```

### WiFi Connection Failed
```
Check: SSID and password correct
Check: Signal strength at location
Check: Router firewall settings
Solution: Use WiFi analyzer, adjust antenna
```

### Blynk Connection Issues
```
Check: Auth token correct
Check: Template configuration
Check: Internet connectivity
Solution: Regenerate token, verify template
```

### Relay Not Switching
```
Check: 5V supply to relay module
Check: GPIO signal levels
Check: Relay LED indicators  
Solution: Replace faulty relay, check wiring
```

## Maintenance Schedule

### Daily
- Visual inspection of warning lights
- Check system status in Blynk app
- Verify battery voltage >10.5V

### Weekly  
- Clean sensor surface
- Check all cable connections
- Test manual control function
- Verify solar panel cleanliness

### Monthly
- Full system function test
- Battery performance check
- Calibrate sensor reading
- Update firmware if available

### Quarterly
- Waterproofing inspection
- Tighten all mounting hardware
- Check grounding system
- Professional system audit

### Annually
- Replace batteries if capacity <80%
- Update system documentation
- Review and update threshold settings
- Professional electrical inspection