# Fix MySQL Connection Error

## The Problem
You're getting "Unable to connect to 127.0.0.1:3306" because MySQL server is not running.

## Solution Options

### Option 1: Use Docker (Easiest - Recommended)

**Step 1: Start Docker Desktop**
- Make sure Docker Desktop is running (check system tray)
- Wait for it to fully start (whale icon should be steady)

**Step 2: Start the Database**
Open PowerShell in your project folder and run:
```bash
docker compose up -d database
```

**Step 3: Wait for Database to be Ready**
```bash
docker ps
```
You should see the MySQL container running.

**Step 4: Connect in MySQL Workbench**
- Use the same connection settings:
  - Hostname: `127.0.0.1`
  - Port: `3306`
  - Username: `medistock`
  - Password: `!ChangeMe!`
  - Default Schema: `medistock`

### Option 2: Use Local MySQL Installation

**Step 1: Check if MySQL is Installed**
- Open Services (Win+R → type `services.msc`)
- Look for "MySQL" or "MySQL80" service

**Step 2: Start MySQL Service**
- Right-click the MySQL service → Start
- Or use PowerShell (as Administrator):
  ```powershell
  Start-Service MySQL80
  ```

**Step 3: Connect as Root First**
In MySQL Workbench, create a connection:
- Hostname: `127.0.0.1`
- Port: `3306`
- Username: `root`
- Password: (your MySQL root password)

**Step 4: Create Database and User**
Once connected, run this SQL:
```sql
CREATE DATABASE IF NOT EXISTS medistock 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'medistock'@'localhost' IDENTIFIED BY '!ChangeMe!';
GRANT ALL PRIVILEGES ON medistock.* TO 'medistock'@'localhost';
FLUSH PRIVILEGES;
```

**Step 5: Create New Connection for MediStock**
Now create a new connection with:
- Username: `medistock`
- Password: `!ChangeMe!`
- Default Schema: `medistock`

## Quick Test

After starting MySQL (either method), test the connection:
```bash
# Test if port is open
Test-NetConnection -ComputerName 127.0.0.1 -Port 3306
```

If `TcpTestSucceeded: True`, MySQL is running!

