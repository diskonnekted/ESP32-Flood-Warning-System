# Power Management System - ESP32 Flood Warning System

## Overview Sistem Power

Sistem EWS menggunakan pendekatan hybrid power dengan kombinasi solar panel, battery backup, dan power management yang efisien untuk operasi 24/7 tanpa gangguan.

## Arsitektur Power System

```
SOLAR PANEL (18V/20W)
        │
        ▼
MPPT CHARGE CONTROLLER (12V Output)
        │
        ▼  
LI-ION BATTERY PACK (11.1V/6000mAh)
        │
        ├─── 12V Rail (Lampu & Sirine)
        │
        └─── DC-DC CONVERTER (5V/3A)
                  │
                  ├─── ESP32 System (5V)
                  └─── Relay Module (5V Logic)
```

## Komponen Power System

### 1. Solar Panel Specifications
```
Type: Monocrystalline Silicon
Power Rating: 20W (Peak)
Voltage: 18V (Voc), 15V (Vmp)
Current: 1.33A (Isc), 1.11A (Imp)
Efficiency: >18%
Weather Resistance: IP67
Dimensions: 350mm x 300mm x 25mm
```

### 2. MPPT Charge Controller
```
Model: PWM/MPPT 10A Controller
Input Voltage: 12-24V Solar
Battery Type: Li-ion/LiFePO4 Support
Output: 12V Regulated
Protection: Over-current, Over-voltage, Reverse polarity
Display: LED Status Indicators
Efficiency: >95%
```

### 3. Battery Pack Configuration
```
Chemistry: Li-ion 18650 
Configuration: 3S2P (3 Series, 2 Parallel)
Nominal Voltage: 11.1V (3.7V x 3)
Capacity: 6000mAh (3000mAh x 2)
Max Discharge: 6A continuous
Cycle Life: >500 cycles @ 80% DOD
Protection: BMS integrated
```

### 4. DC-DC Buck Converter
```
Input Range: 8-24V DC
Output: 5V ±2% (Adjustable)
Current Rating: 3A Maximum
Efficiency: >92% @ Full Load
Ripple: <50mV
Protection: Short circuit, thermal
Size: 48mm x 23mm x 14mm
```

## Power Consumption Analysis

### System Power Draw by Mode

#### Standby Mode (Normal Operation)
```
ESP32 WiFi Active:     240mA @ 5V = 1.2W
HC-SR04 Sensor:        15mA @ 5V = 0.075W
Relay Module Standby:  20mA @ 5V = 0.1W
MPPT Controller:       10mA @ 12V = 0.12W
DC-DC Converter Loss:  8% efficiency loss = 0.12W
─────────────────────────────────────────────
Total Standby:         ~285mA @ 12V = 3.42W
```

#### Warning Mode - Level 1 (Green Light)
```
Standby Power:         3.42W
LED Strobo Green:      500mA @ 12V = 6W
Relay Activation:      25mA @ 5V = 0.125W
─────────────────────────────────────────────
Total Level 1:         ~830mA @ 12V = 9.96W
```

#### Warning Mode - Level 2 (Yellow Light) 
```
Standby Power:         3.42W
LED Strobo Yellow:     500mA @ 12V = 6W
Relay Activation:      25mA @ 5V = 0.125W
─────────────────────────────────────────────
Total Level 2:         ~830mA @ 12V = 9.96W
```

#### Emergency Mode - Level 3 (Red Light + Siren)
```
Standby Power:         3.42W
LED Strobo Red:        500mA @ 12V = 6W
Sirine 12V:            800mA @ 12V = 9.6W
Relay Activation:      50mA @ 5V = 0.25W
─────────────────────────────────────────────
Total Level 3:         ~1650mA @ 12V = 19.8W
```

## Battery Life Calculations

### Capacity Calculations
```
Battery Capacity: 11.1V x 6Ah = 66.6Wh
Usable Capacity (80% DOD): 66.6 x 0.8 = 53.28Wh
Safety Margin (10%): 53.28 x 0.9 = 47.95Wh
```

### Runtime Estimates
```
Standby Mode:      47.95Wh / 3.42W = 14.02 hours
Warning Level 1-2: 47.95Wh / 9.96W = 4.81 hours  
Emergency Level 3: 47.95Wh / 19.8W = 2.42 hours
Mixed Usage:       47.95Wh / 6W avg = ~8 hours
```

## Solar Charging Performance

### Daily Energy Generation
```
Peak Sun Hours (Indonesia): 4-5 hours/day
Solar Panel Output: 20W x 4.5h = 90Wh/day
Charge Controller Efficiency: 90Wh x 0.95 = 85.5Wh/day
Weather Factor (80%): 85.5Wh x 0.8 = 68.4Wh/day
```

### Energy Balance Analysis
```
Daily Generation: 68.4Wh
Daily Consumption (Standby): 3.42W x 24h = 82.08Wh
Energy Deficit: 82.08 - 68.4 = 13.68Wh/day

With 2 hours warning/day: 6W x 2h = 12Wh additional
Total Daily Need: 94.08Wh
Net Deficit: 94.08 - 68.4 = 25.68Wh/day
```

### Battery Autonomy
```
Battery provides: 47.95Wh usable
Deficit per day: 25.68Wh
Autonomy: 47.95 / 25.68 = 1.87 days without sun
With 50% sun: 47.95 / 12.84 = 3.73 days
```

## Power Management Features

### Battery Monitoring
```cpp
// Voltage divider untuk battery monitoring
float batteryVoltage = analogRead(BATTERY_PIN) * (3.3 / 4095.0) * 2;

// Battery status thresholds
if (batteryVoltage > 12.0) {
    batteryStatus = "FULL";
} else if (batteryVoltage > 11.4) {
    batteryStatus = "GOOD"; 
} else if (batteryVoltage > 10.8) {
    batteryStatus = "LOW";
} else {
    batteryStatus = "CRITICAL";
}
```

### Solar Panel Monitoring
```cpp
// Solar panel voltage monitoring
float solarVoltage = analogRead(SOLAR_PIN) * (3.3 / 4095.0) * 3;

// Charging status
if (solarVoltage > 13.0) {
    chargingStatus = "CHARGING";
} else if (solarVoltage > 10.0) {
    chargingStatus = "AVAILABLE";
} else {
    chargingStatus = "NO SUN";
}
```

### Power Saving Modes

#### Sleep Mode Implementation
```cpp
void enterSleepMode() {
    if (warningLevel == 0 && batteryVoltage < 11.0) {
        // Deep sleep selama 5 menit jika battery low
        esp_sleep_enable_timer_wakeup(300000000); // 5 minutes
        esp_deep_sleep_start();
    }
}
```

#### Dynamic Frequency Scaling
```cpp
void adjustPowerMode() {
    if (batteryVoltage < 11.5) {
        // Reduce CPU frequency untuk power saving
        setCpuFrequencyMhz(80); // dari 240MHz ke 80MHz
        measurementInterval = 5000; // Increase sensor interval
    } else {
        setCpuFrequencyMhz(240); // Full performance
        measurementInterval = 2000; // Normal sensor interval
    }
}
```

## Installation Guidelines

### Solar Panel Positioning
```
Optimal Angle: 15° (untuk Indonesia)
Orientation: Menghadap selatan
Clearance: 50cm dari obstacle
Height: Minimum 3m (anti theft)
Tilt Adjustment: Musiman ±5°
```

### Battery Installation
```
Location: Indoor, temperature controlled
Temperature Range: 0°C to 45°C  
Ventilation: Natural airflow required
Mounting: Secure bracket, vibration resistant
Access: Easy access untuk maintenance
```

### Wiring Best Practices
```
Cable Sizing:
- Solar to Controller: 2.5mm² (max 10m)
- Battery Connection: 4mm² (short runs)
- 12V Distribution: 1.5mm² (per circuit)
- 5V Logic: 1.0mm² (short runs)

Fuse Protection:
- Main Battery: 10A
- Solar Input: 8A  
- 12V Loads: 5A each
- 5V System: 3A
```

## Monitoring & Alerts

### Blynk Integration
```cpp
// Send power data ke Blynk
Blynk.virtualWrite(V_BATTERY_VOLTAGE, batteryVoltage);
Blynk.virtualWrite(V_SOLAR_VOLTAGE, solarVoltage);
Blynk.virtualWrite(V_POWER_STATUS, powerStatus);
Blynk.virtualWrite(V_CHARGING_CURRENT, chargingCurrent);
```

### Alert Thresholds
```
Battery Alerts:
- Low Battery: <11.4V (Send notification)
- Critical Battery: <10.8V (Emergency shutdown)
- Battery Full: >12.6V (Charging complete)

Solar Alerts:  
- No Charging: Solar <10V for 2+ hours (daylight)
- Controller Fault: No charging current despite sun
- Temperature Alert: Controller >60°C
```

## Maintenance Schedule

### Daily Monitoring
- [ ] Check battery voltage via Blynk
- [ ] Verify solar charging status  
- [ ] Monitor system uptime
- [ ] Review power consumption trends

### Weekly Inspection
- [ ] Clean solar panel surface
- [ ] Check cable connections
- [ ] Verify charge controller display
- [ ] Test backup power mode

### Monthly Service
- [ ] Battery voltage calibration
- [ ] Solar panel alignment check
- [ ] Cable insulation inspection
- [ ] Performance data analysis

### Quarterly Maintenance
- [ ] Battery capacity test
- [ ] Solar panel efficiency test
- [ ] Replace worn connectors
- [ ] Update power management firmware

## Troubleshooting Power Issues

### Battery Not Charging
```
Check: Solar panel voltage >13V during daylight
Check: Charge controller connections
Check: Battery voltage <12.6V
Check: Controller LED indicators
Solution: Verify wiring, replace faulty controller
```

### Rapid Battery Drain
```
Check: High current draw devices
Check: System in continuous warning mode
Check: DC-DC converter efficiency
Check: Battery age and capacity
Solution: Optimize power settings, replace old battery
```

### Solar System Underperforming  
```
Check: Panel cleanliness and shading
Check: Panel angle and orientation
Check: Cable resistance and connections
Check: Controller MPPT operation
Solution: Clean panel, adjust position, upgrade controller
```

### System Shutdown
```
Check: Battery voltage >10.5V
Check: Main fuse continuity
Check: DC-DC converter output
Check: ESP32 power LED
Solution: Charge battery, replace fuses, check converter
```

## Advanced Power Features

### Load Prioritization
```cpp
void managePowerLoad() {
    if (batteryVoltage < 11.0) {
        // Priority 1: Core system only
        digitalWrite(RELAY_GREEN, HIGH);  // Turn off
        digitalWrite(RELAY_YELLOW, HIGH); // Turn off  
        // Keep only red light and siren for emergency
    } else if (batteryVoltage < 11.5) {
        // Priority 2: Reduce warning frequency
        warningBlinkRate = 2000; // Slower blinking
        measurementInterval = 10000; // Less frequent measurement
    }
}
```

### Smart Charging
```cpp
void optimizeCharging() {
    // Charge different battery sections sequentially
    if (solarVoltage > 15.0) {
        // High solar: Fast charge mode
        setChargeCurrent(2.0); // 2A charging
    } else if (solarVoltage > 13.0) {
        // Normal solar: Standard charge  
        setChargeCurrent(1.0); // 1A charging
    } else {
        // Low solar: Trickle charge
        setChargeCurrent(0.5); // 0.5A charging
    }
}
```

## Performance Optimization

### Seasonal Adjustments
```
Summer (April-September):
- Solar panel angle: 10°
- Extended daylight: 5+ hours generation
- Higher temperatures: Reduce charge current

Winter (October-March):  
- Solar panel angle: 20°
- Reduced daylight: 3-4 hours generation
- Lower temperatures: Normal charge current
```

### Regional Considerations
```
Coastal Areas:
- Higher humidity: Better sealing required
- Salt air: Corrosion protection
- More clouds: Larger battery capacity

Mountain Areas:
- Temperature extremes: Thermal management
- Higher altitude: UV protection
- Clear skies: Smaller battery acceptable
```