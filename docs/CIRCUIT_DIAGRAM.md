# Circuit Diagram - ESP32 Flood Early Warning System

## Skema Rangkaian Lengkap

```
                    SOLAR PANEL (10-20W)
                           |
                           |
                    ┌─────────────┐
                    │   CHARGE    │
                    │ CONTROLLER  │ 
                    │   MPPT      │
                    └─────────────┘
                           |
                    ┌─────────────┐
                    │  LI-ION     │
                    │  BATTERY    │
                    │ 7.4V/11.1V  │
                    └─────────────┘
                           |
                    ┌─────────────┐
                    │  DC-DC      │
                    │ CONVERTER   │
                    │ 12V → 5V    │
                    └─────────────┘
                           |
            ┌──────────────┼──────────────┐
            │              │              │
      ┌─────────┐    ┌─────────┐    ┌─────────┐
      │  ESP32  │    │ RELAY   │    │ HC-SR04 │
      │ DEVKIT  │    │ MODULE  │    │ SENSOR  │
      │   v1    │    │  4CH    │    │         │
      └─────────┘    └─────────┘    └─────────┘
```

## Koneksi Detail ESP32

### Power Connections
```
ESP32 VIN  ←→ 5V dari DC-DC Converter
ESP32 GND  ←→ Ground Common
```

### Digital Pins
```
GPIO 4   ←→ HC-SR04 Trigger Pin
GPIO 5   ←→ HC-SR04 Echo Pin
GPIO 12  ←→ Relay 1 Input (Lampu Hijau)
GPIO 13  ←→ Relay 2 Input (Lampu Kuning)  
GPIO 14  ←→ Relay 3 Input (Lampu Merah)
GPIO 15  ←→ Relay 4 Input (Sirine)
```

### Analog Pins (Monitoring)
```
GPIO 36 (A0) ←→ Battery Voltage Divider
GPIO 39 (A3) ←→ Solar Voltage Divider
```

## HC-SR04 Ultrasonic Sensor

```
HC-SR04 Pin    ESP32 Pin    Description
-----------    ---------    -----------
VCC            3.3V         Power Supply
GND            GND          Ground
Trig           GPIO 4       Trigger Signal
Echo           GPIO 5       Echo Return
```

## 4-Channel Relay Module

```
Relay Pin      ESP32 Pin    Load Connection
---------      ---------    ---------------
VCC            5V           Power Supply
GND            GND          Ground  
IN1            GPIO 12      Lampu Strobo Hijau
IN2            GPIO 13      Lampu Strobo Kuning
IN3            GPIO 14      Lampu Strobo Merah
IN4            GPIO 15      Sirine 12V
```

### Relay Output Connections
```
Relay 1:       COM → 12V+,  NO → Lampu Hijau (+)
               Lampu Hijau (-) → GND

Relay 2:       COM → 12V+,  NO → Lampu Kuning (+)  
               Lampu Kuning (-) → GND

Relay 3:       COM → 12V+,  NO → Lampu Merah (+)
               Lampu Merah (-) → GND

Relay 4:       COM → 12V+,  NO → Sirine (+)
               Sirine (-) → GND
```

## Voltage Monitoring Circuit

### Battery Voltage Divider
```
Battery (+) ──[R1: 10kΩ]──┬── GPIO 36 (A0)
                           │
                        [R2: 10kΩ]
                           │
Battery (-) ──────────────┴── GND

Calculation: V_battery = V_adc × 2
```

### Solar Panel Voltage Divider  
```
Solar (+) ──[R1: 20kΩ]──┬── GPIO 39 (A3)
                         │
                      [R2: 10kΩ]  
                         │
Solar (-) ──────────────┴── GND

Calculation: V_solar = V_adc × 3
```

## Power Distribution Diagram

```
SOLAR PANEL (18V Max)
        │
        ▼
┌───────────────┐
│ MPPT CHARGE   │  
│ CONTROLLER    │ ── LED Status Indicators
│ (12V Output)  │
└───────────────┘
        │
        ▼
┌───────────────┐
│  LI-ION       │
│  BATTERY      │ ── Fuse 10A
│  3S (11.1V)   │  
└───────────────┘
        │
        ▼
┌───────────────┐
│  DC-DC BUCK   │
│  CONVERTER    │ ── Adjustable Output
│  11V → 5V     │    (Set to 5V)
└───────────────┘
        │
   ┌────┼────┐
   │         │
   ▼         ▼
ESP32    RELAY MODULE
(5V)      (5V Logic)
          (12V Coils via separate supply)
```

## PCB Layout Suggestion

### Component Placement
```
┌─────────────────────────────────┐
│  ESP32-DEVKIT-V1               │
│  ┌─────────────────────────┐    │
│  │ [USB] [GPIO PINS]      │    │  
│  │       [POWER PINS]     │    │
│  └─────────────────────────┘    │
│                                 │
│  RELAY MODULE (4CH)             │
│  ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐│
│  │ RLY1│ │RLY2 │ │RLY3 │ │RLY4 ││
│  └─────┘ └─────┘ └─────┘ └─────┘│
│                                 │
│  TERMINAL BLOCKS                │
│  [12V+] [GND] [LAMP1] [LAMP2]   │
│  [LAMP3] [SIREN] [SENSOR]       │
│                                 │
│  VOLTAGE DIVIDERS               │
│  [BAT MON] [SOLAR MON]          │
└─────────────────────────────────┘
```

## Wiring Color Code

```
Power Lines:
- Red:     +12V / +5V
- Black:   Ground (GND)
- Yellow:  Battery Monitor
- Orange:  Solar Monitor

Signal Lines:  
- Blue:    Relay Control (GPIO 12-15)
- Green:   Sensor Trigger (GPIO 4)
- White:   Sensor Echo (GPIO 5)

Load Lines:
- Brown:   Lampu Hijau
- Yellow:  Lampu Kuning  
- Red:     Lampu Merah
- Purple:  Sirine
```

## Safety Features

### Fuses and Protection
```
- Main Fuse: 10A (Battery output)
- ESP32 Fuse: 2A (Logic circuit)  
- Relay Fuse: 5A (Load circuit)
- TVS Diodes: Input protection
- Pull-up Resistors: GPIO protection
```

### Grounding Scheme
```
Single Point Ground:
Battery (-) = Solar (-) = ESP32 GND = Relay GND = Load GND
```

## Assembly Notes

1. **Soldering Order**: Power → ESP32 → Sensors → Relays → Loads
2. **Testing**: Test each section before final assembly
3. **Isolation**: Use heat shrink tubing for all connections
4. **Strain Relief**: Secure all external cables
5. **Labeling**: Label all wires clearly

## Mechanical Mounting

### Sensor Installation
- Mount HC-SR04 in waterproof housing
- Angle downward 90 degrees  
- Minimum 4m height from flood level
- Clear line of sight to water surface

### Box Layout
```
┌─────────────────────────────┐
│ [SOLAR INPUT]   [12V OUT]   │ ← Top panel connectors
│                             │
│  ┌─────┐    ┌─────────┐     │
│  │CHRG │    │  ESP32  │     │ ← Main compartment  
│  │CTRL │    │ MODULE  │     │
│  └─────┘    └─────────┘     │
│                             │
│  ┌─────────────────────┐    │
│  │    RELAY MODULE     │    │ ← Relay compartment
│  └─────────────────────┘    │
│                             │
│ [SENSOR] [LAMPS] [SIREN]    │ ← Bottom panel connectors
└─────────────────────────────┘
```