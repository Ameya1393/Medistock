# Database Connection Information

## Connection Details

**Host:** `127.0.0.1` or `localhost`  
**Port:** `3306`  
**Database:** `medistock`  
**Username:** `medistock`  
**Password:** `!ChangeMe!`  

**Root Access:**
- **Username:** `root`
- **Password:** `!ChangeMe!`

## Connection String (for .env)
```
DATABASE_URL="mysql://medistock:!ChangeMe!@127.0.0.1:3306/medistock?serverVersion=8.0&charset=utf8mb4"
```

## Ways to View the Database

### 1. MySQL Workbench (Recommended)
- Download: https://dev.mysql.com/downloads/workbench/
- Create a new connection with the details above

### 2. DBeaver (Free, Cross-platform)
- Download: https://dbeaver.io/download/
- Create a new MySQL connection

### 3. Command Line (if MySQL client is installed)
```bash
mysql -h 127.0.0.1 -u medistock -p medistock
# Enter password: !ChangeMe!
```

### 4. Using Docker (if using Docker Compose)
```bash
# Access MySQL container
docker exec -it medistock-database-1 mysql -u medistock -p medistock
# Enter password: !ChangeMe!

# Or as root
docker exec -it medistock-database-1 mysql -u root -p
# Enter password: !ChangeMe!
```

### 5. Symfony Doctrine Commands
```bash
# List all tables
php bin/console doctrine:schema:validate

# Show database info
php bin/console dbal:run-sql "SHOW TABLES;"
```

## Starting the Database

If using Docker Compose:
```bash
docker compose up -d database
```

If using a local MySQL installation, make sure MySQL service is running.

