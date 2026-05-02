# 🐷 PORKY - Pork Freshness Grading System

## 📌 Description
PORKY is an AI-based pork freshness grading system that uses an ESP32-CAM and edge machine learning to analyze pork images and classify freshness based on visible characteristics.

This system focuses on **visual grading only** and does not replace laboratory-based freshness testing.

---

## ⚙️ Tech Stack
- Laravel (Web Application)
- Firebase (Realtime Database / Storage)
- ESP32-CAM (Edge Device)
- Machine Learning (Edge-based classification)

---

## 🚀 Features
- 📷 Image capture via ESP32-CAM
- 🧠 Freshness grading (Grade A, B, C)
- 📊 Dashboard monitoring
- 📁 Scan history tracking
- 📄 Report generation
- 🤖 AI Insight (explanation only)

---

## 🧭 System Flow
ESP32-CAM → Local ML Processing → Send Result → Laravel → Dashboard Display

---

## 📂 Project Structure
- `app/` – core application logic
- `resources/views/` – frontend UI (Blade templates)
- `routes/` – web routes
- `storage/` – uploaded images
- `public/` – assets

---

## ▶️ How to Run

1. Install dependencies
```bash
composer install
npm install
```

## Hardware Used
- ESP32-CAM AI Thinker
- MQ-135 Gas Sensor
- DHT22 Temperature & Humidity Sensor
- I2C LCD (16x2)
- 2x Batteries (with holder)
- Voltage Regulator (set to 5V)
- Breadboard
- Jumper Wires
- Resistors:
  - 3 x 10k ohm (used for voltage divider and pull-up)

---

## Libraries Required
Install in Arduino IDE Library Manager:

- DHT sensor library by Adafruit
- Adafruit Unified Sensor
- LiquidCrystal_I2C by Frank de Brabander

Also required:

- Edge Impulse Arduino library (your trained model)
- ESP32 Board Package

---

## Arduino IDE Setup
1. Go to `File > Preferences`
2. Add this URL:
```text
https://dl.espressif.com/dl/package_esp32_index.json
```
3. Go to Board Manager and install `ESP32`
4. Select:
   - Board: `AI Thinker ESP32-CAM`
   - PSRAM: `Enabled`
   - Upload Speed: `115200`

---

## Pin Connections
| Component | Connection |
| --- | --- |
| DHT22 DATA | GPIO 2 |
| MQ-135 AOUT | GPIO 13 |
| LCD SDA | GPIO 15 |
| LCD SCL | GPIO 14 |
| Power | 5V from regulator |
| GND | Common ground |

---

## Power Setup
```text
2 Batteries -> Voltage Regulator -> 5V Output
5V -> ESP32-CAM + MQ-135 + LCD
```

Important:

- Ensure stable 5V supply
- MQ-135 requires enough current for heater

---

## Voltage Divider (MQ-135)
To protect ESP32 (3.3V max), use:

```text
MQ135 AOUT -- 10k --+-- GPIO13
                    |
                   20k (2x10k combined)
                    |
                   GND
```

---

## How to Run Hardware
1. Assemble the circuit
2. Power using battery + regulator
3. Upload code via Arduino IDE
4. Open Serial Monitor at `115200` baud
5. Wait for:
   - MQ-135 warm-up
   - Camera initialization
6. System will automatically:
   - Capture image
   - Analyze meat with AI
   - Read gas and temperature
   - Display result on LCD

---

## Output Meaning
