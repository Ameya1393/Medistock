# Starting Database - Step by Step Guide

## Issue
Docker Desktop is not running, which is why the database container can't start.

## Solution

### Step 1: Start Docker Desktop
1. Open Docker Desktop from Start Menu or system tray
2. Wait for Docker Desktop to fully start (whale icon should be steady, not animating)
3. This usually takes 30-60 seconds

### Step 2: Verify Docker is Running
Once Docker Desktop is running, you should see the Docker whale icon in your system tray.

### Step 3: Start the Database
Run this command in PowerShell:
```powershell
docker compose up -d database
```

### Step 4: Wait for Database to be Ready
The database needs a few seconds to initialize. Wait about 15-20 seconds after the container starts.

### Step 5: Check Container Status
Verify the container is running:
```powershell
docker ps
```
You should see `medistock-database-1` with status "Up" and "healthy".

### Step 6: Run Migrations
Once the database is ready:
```powershell
php bin/console doctrine:migrations:migrate
```

## Alternative: Manual Docker Desktop Start

If the command didn't work, manually:
1. Press `Windows Key`
2. Type "Docker Desktop"
3. Click on Docker Desktop
4. Wait for it to start
5. Then run: `docker compose up -d database`

## Troubleshooting

If Docker Desktop won't start:
- Check if virtualization is enabled in BIOS
- Make sure WSL 2 is installed (for Windows)
- Try restarting Docker Desktop
- Check Windows Services for Docker-related services

