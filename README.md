# Laravel IoT Dashboard (Dissertation Project)

A real-time IoT dashboard built with Laravel. It connects to an MQTT broker, listens for device data, stores it in a database, and shows it in a live-updating web interface. Developed as a fourth-year dissertation project.

## Getting Started

### 1. Prerequisites
The following dependencies are required to run the project:
-   **PHP 8.2+**
-   **Composer**
-   **Node.js & NPM**
-   **An MQTT Broker** (Mosquitto is recommended)
-   **MySQL** (Required for full support of the statistics dashboard)

### 2. Installation
Clone the repository and navigate into the project directory:
```bash
git clone https://github.com/Abs3601/Laravel-IoT-Dashboard.git
cd Laravel-IoT-Dashboard
```

### 3. Environment Configuration (.env)
The environment file must be created manually to configure your local settings.
```bash
cp .env.example .env
```
Open the `.env` file and configure your database settings (MySQL is recommended for the analytics features).

### 4. Dependencies & Assets
Install the required packages and build the frontend assets:
```bash
composer install
npm install
npm run build
php artisan key:generate
```

### 5. Database Migration
Execute the migrations to set up the database schema:
```bash
php artisan migrate
```

---

## Running the System

To ensure full functionality (real-time updates and data ingestion), the following services should be running simultaneously.

### 1. Web Server
```bash
php artisan serve
```

### 2. WebSocket Server (Laravel Reverb)
This service manages real-time dashboard updates.
```bash
php artisan reverb:start
```

### 3. MQTT Device Listener
This command runs the ingestion pipeline, listening for and processing device events.
```bash
php artisan app:mqtt:listen-devices
```

### 4. Frontend Development (Optional)
For live asset recompilation during development:
```bash
npm run dev
```

---

## Feature Highlights

-   **Device Grouping**: Links sensors with their parent devices.
-   **Real-time Dashboard**: Powered by Livewire & Reverb for instant feedback.
-   **Stats & Latency**: A dedicated stats page that tracks ingestion rates and end-to-end latency (for load tests).
-   **Onboarding**: Plug-and-play setup for your broker credentials.

## Testing

-   **Unit Tests**: Parsing logic, group extraction, and command generation.
-   **Feature Tests**: UI rendering, onboarding, and dashboard visibility.

**To run the tests:**
```bash
./vendor/bin/pest
```

---

*Developed as a 4th-year dissertation project at the University of the West of Scotland.*