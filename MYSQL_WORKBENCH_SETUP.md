# MySQL Workbench Connection Guide for MediStock

## Connection Settings

Open MySQL Workbench and create a new connection with these settings:

### Basic Connection Details
- **Connection Name:** `MediStock` (or any name you prefer)
- **Hostname:** `127.0.0.1` (or `localhost`)
- **Port:** `3306`
- **Username:** `medistock`
- **Password:** `!ChangeMe!`
- **Default Schema:** `medistock` (optional, you can select it after connecting)

### Alternative (Root Access)
If you need root access:
- **Username:** `root`
- **Password:** `!ChangeMe!`

## Steps to Connect

1. Open MySQL Workbench
2. Click the **"+"** button next to "MySQL Connections" (or go to Database → Manage Connections)
3. Enter the connection details above
4. Click **"Test Connection"** to verify it works
5. Click **"OK"** to save
6. Double-click the connection to connect

## If Connection Fails

### Check if MySQL is Running

**If using Docker:**
```bash
# Start Docker Desktop first, then:
docker compose up -d database

# Check if container is running:
docker ps
```

**If using local MySQL:**
- Make sure MySQL service is running
- On Windows: Check Services (services.msc) for "MySQL80" or similar
- Or use: `net start MySQL80` (in admin command prompt)

### Create Database and User (if needed)

If the database doesn't exist yet, you can create it:

1. Connect as `root` user first
2. Run these SQL commands:

```sql
CREATE DATABASE IF NOT EXISTS medistock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'medistock'@'%' IDENTIFIED BY '!ChangeMe!';
GRANT ALL PRIVILEGES ON medistock.* TO 'medistock'@'%';
FLUSH PRIVILEGES;
```

## After Connecting

Once connected, you'll see:
- The `medistock` database in the left sidebar
- You can browse tables, run queries, and manage data
- Currently, the database will be empty until you create entities and run migrations

