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