# MySQL Workbench Connection Setup - Step by Step

## Method 1: Using the Home Screen

1. **Open MySQL Workbench**
2. **Look for "MySQL Connections" section** on the home screen
3. **Click the "+" button** next to "MySQL Connections" (it might say "New Connection" or just be a "+" icon)
4. **A dialog box will open** - enter these details:

### Connection Settings:
- **Connection Name:** `MediStock`
- **Hostname:** `127.0.0.1`
- **Port:** `3306`
- **Username:** `root` (or your MySQL root username)
- **Password:** Click "Store in Keychain" and enter your MySQL root password
- **Default Schema:** Leave blank for now

5. **Click "Test Connection"** - it should say "Successfully made the MySQL connection"
6. **Click "OK"** to save

## Method 2: Using Menu

1. Click **"Database"** in the top menu bar
2. Select **"Manage Connections..."**
3. Click the **"+"** button at the bottom left
4. Enter the connection details above
5. Click **"Test Connection"**
6. Click **"OK"**

## Method 3: Keyboard Shortcut

1. Press **Ctrl + Shift + C** (Windows) or **Cmd + Shift + C** (Mac)
2. This opens the "Manage Connections" dialog
3. Click **"+"** to add new connection
4. Enter details and test

## After Creating Connection

1. **Double-click the "MediStock" connection** to connect
2. You'll be prompted for password (if not stored)
3. Once connected, you'll see the MySQL server in the left sidebar

## Create the Database

After connecting, you need to create the `medistock` database:

1. Click the **"SQL"** tab or press **Ctrl+T** to open a new query tab
2. Copy and paste this SQL:

```sql
CREATE DATABASE IF NOT EXISTS medistock 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'medistock'@'localhost' IDENTIFIED BY '!ChangeMe!';
GRANT ALL PRIVILEGES ON medistock.* TO 'medistock'@'localhost';
FLUSH PRIVILEGES;
```

3. Click the **lightning bolt icon** (⚡) or press **Ctrl+Enter** to execute
4. You should see "Success" messages

## Create New Connection for MediStock User

Now create a second connection using the `medistock` user:

1. Follow steps above to create a new connection
2. Name it: `MediStock User`
3. Username: `medistock`
4. Password: `!ChangeMe!`
5. Default Schema: `medistock`
6. Test and save

Now you can use either connection!

