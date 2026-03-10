# Laravel IoT Dashboard

A real-time IoT dashboard built with Laravel. It connects to an MQTT broker, listens for device data, stores it in a database, and shows it in a live-updating web interface. Built as a fourth-year dissertation project.

## What It Does

- Connects to any MQTT broker and listens for device messages
- Automatically discovers devices as they publish data (no config files needed)
- Groups related sensors with their parent device (e.g. a smart plug and its power/voltage readings)
- Shows all your devices in a web dashboard with real-time updates via WebSockets
- Has specific card layouts for lights (with brightness sliders) and switches, with a generic fallback for everything else

## Supported MQTT Formats

The listener can parse topics from:

- **Home Assistant** — `homeassistant/{type}/{id}/{attribute}`
- **Zigbee2MQTT** — `zigbee2mqtt/{device_name}` (JSON payloads)
- **Tasmota** — `tele/{device}/STATE`, `stat/{device}/POWER`, etc.
- **ESPHome** — `esphome/{device}/{sensor}/state`
- **Shelly** — `shellies/{id}/{component}/{property}`
- **Any generic MQTT** — falls back to parsing the topic structure automatically

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- An MQTT broker (like Mosquitto, the one built into Home Assistant, etc.)
- SQLite (used by default) or any other Laravel-supported database

## Installation

1. **Clone the repo**

   ```bash
   git clone <https://github.com/Abs3601/Laravel-IoT-Dashboard.git>
   cd Dissertation
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Set up your environment file**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Create the database**

   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

5. **Install JS dependencies and build**

   ```bash
   npm install
   npm run build
   ```

6. **Start the app**

   You need three terminals running (or use the composer dev script):

   ```bash
   # Terminal 1 — Web server
   php artisan serve

   # Terminal 2 — Vite dev server (for hot reload)
   npm run dev

   # Terminal 3 — WebSocket server (for real-time updates)
   php artisan reverb:start
   ```

   Or run them all at once:

   ```bash
   composer dev
   ```

7. **Go through onboarding**

   Open `http://localhost:8000` in your browser. You'll be taken to the onboarding page where you enter your MQTT broker details (host, port, username, password).

8. **Start the MQTT listener**

   ```bash
   php artisan app:mqtt:listen-devices
   ```

   Devices will start appearing in the dashboard as they publish messages.

## How It Works

1. The MQTT listener (`app:mqtt:listen-devices`) subscribes to all topics on your broker
2. Each message is parsed to extract a device type, device ID, and attribute
3. Devices are stored/updated in the database with their latest state and attributes
4. Related devices (like a plug and its power sensor) are automatically grouped together using a longest-common-prefix algorithm on their IDs
5. State changes are broadcast over WebSockets so the dashboard updates in real time without refreshing

## Project Structure

| Path | What it does |
|---|---|
| `app/Console/Commands/MqttDeviceListener.php` | The MQTT listener command |
| `app/Http/Controllers/deviceController.php` | Handles device list and detail pages |
| `app/Models/Device.php` | Device model |
| `app/Events/DeviceUpdated.php` | WebSocket broadcast event |
| `resources/views/components/device-card-*.blade.php` | Card components for different device types |
| `resources/views/device-overview.blade.php` | Device type overview page |
| `resources/views/device-group-detail.blade.php` | Individual device detail page with related sensors |

## Known Shortcomings

- **No device control** — The dashboard is read-only. You can see your devices but can't toggle switches, change brightness, etc. Publishing MQTT commands is not implemented yet.
- **No authentication** — There's no login system. Anyone who can reach the web server can see your devices.
- **No historical data view** — Events are logged to the database but there's no UI to view history, charts, or trends.
- **Device grouping isn't perfect** — The prefix-based grouping works well when related entities share a common name prefix, but it can mis-group unrelated devices that happen to start with similar names.
- **No device removal** — Once a device shows up, there's no way to remove it from the dashboard through the UI. You'd need to delete it from the database manually.
- **Limited card types** — Only lights and switches have dedicated card layouts. Everything else uses a generic card.
- **Single broker only** — You can only connect to one MQTT broker at a time.
- **MQTT credentials stored in the database** — The MQTT username and password are stored in the settings table. The password is hashed but this is still not ideal for production use.

## Tech Stack

- **Backend** — Laravel 12, PHP 8.2+
- **Frontend** — Livewire, Tailwind CSS 4, DaisyUI
- **Real-time** — Laravel Reverb (WebSockets), Laravel Echo
- **MQTT** — php-mqtt/laravel-client
- **Build** — Vite

## Changelog

### v0.3.0 — Dynamic Device Discovery (In Progress)

- Replaced the hardcoded device config file with automatic discovery from the database
- Replaced hardcoded suffix list for device grouping with a longest-common-prefix algorithm
- Added support for Zigbee2MQTT, Tasmota, ESPHome, and Shelly topic formats alongside Home Assistant
- Added a generic device group detail page that works with any device type (replaces the plug-only detail page)
- Made all device cards clickable, linking to their detail page
- Removed `config/devices.php`, the old plug-specific routes, and orphaned views

### v0.2.0 — Real-Time Updates & Onboarding

- Added WebSocket support with Laravel Reverb for real-time device updates
- Built an onboarding page for configuring MQTT broker settings
- Added onboarding middleware to redirect new users to setup
- Implemented device grouping to link related sensors with their parent device
- Added a plug detail page showing grouped sensor readings
- Created dedicated card components for lights (with brightness slider) and switches
- Added a generic card fallback for unrecognised device types

### v0.1.0 — Initial Release

- Basic MQTT listener that subscribes to Home Assistant topics
- Stores devices and state changes in the database
- Simple web dashboard showing lights and plugs
- Live-updating cards using Livewire polling
- Brightness slider with dynamic RGB colour support for lights
