# MediStock - Hospital Drug Inventory Management System

A comprehensive web-based hospital drug inventory and consumption management system with AI/ML-powered predictions.

## Project Overview

MediStock helps hospitals track available drugs, monitor stock levels, log daily consumption, generate low-stock alerts, and provide intelligent predictions using machine learning algorithms.

## Features

### Core Features
- **Drug Management**: Add, edit, delete, and view drugs
- **Stock Management**: Track and update stock levels
- **Consumption Logging**: Record drug usage with automatic stock deduction
- **Alerts System**: Real-time low stock alerts
- **Reports**: Low stock reports and usage analysis with filters
- **Dashboard**: Central hub with statistics and quick actions

### AI/ML Features
- **Consumption Prediction**: Time series forecasting for future drug consumption
- **Stockout Prediction**: Predict when drugs will run out of stock
- **Interactive Charts**: Visual representation of predictions using Chart.js

## 🛠️ Technology Stack

- **Backend**: Symfony 7.4 (PHP 8.3)
- **Database**: MySQL 8.0
- **ORM**: Doctrine ORM
- **Frontend**: Twig, Bootstrap 5.3, Chart.js
- **ML Library**: PHP-ML (PHP Machine Learning)
- **Containerization**: Docker Compose

## Prerequisites

- PHP 8.2 or higher
- Composer
- Docker Desktop (for MySQL database)
- MySQL Workbench (optional, for database management)

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/Ameya1393/medistock.git
cd medistock
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup

#### Start Docker Database
```bash
docker compose up -d database
```

Wait for the database to be ready (~15-20 seconds).

#### Run Migrations
```bash
php bin/console doctrine:migrations:migrate
```

### 4. Configure Environment

The `.env` file is already configured with default database settings:
- Database: `medistock`
- Username: `medistock`
- Password: `!ChangeMe!`
- Host: `127.0.0.1:3306`

**Important**: Change the password in production!

### 5. Start the Server
```bash
symfony serve
# OR
php -S localhost:8000 -t public
```

### 6. Access the Application
Open your browser and navigate to: `http://localhost:8000/`

## Usage

### Getting Started
1. **Add Drugs**: Go to Drugs → Create new drug
2. **Set Initial Stock**: When creating a drug, set the initial stock quantity
3. **Log Consumption**: Go to Consumption → Log new consumption
4. **Monitor Stock**: Check Stock page for current levels
5. **View Alerts**: Check Alerts page for low stock warnings
6. **View Predictions**: Use Predictions menu for AI-powered forecasts

### Key Workflows

**Adding a New Drug:**
1. Navigate to Drugs → Create new
2. Fill in: Name, Category, Threshold, Initial Stock Quantity
3. Save

**Logging Consumption:**
1. Go to Consumption → Log Consumption
2. Select drug, enter quantity used, date, and who logged it
3. Stock automatically decreases

**Updating Stock:**
1. Go to Stock → Select drug → Update Stock
2. Choose action: Increase, Decrease, or Set
3. Enter quantity and save

**Viewing Predictions:**
1. Go to Predictions → Consumption Prediction
2. Select a drug with consumption history
3. View predicted consumption for next 7/14/30 days

## Project Structure

```
medistock/
├── config/              # Symfony configuration
├── migrations/          # Database migrations
├── public/              # Web root
├── src/
│   ├── Controller/      # HTTP controllers
│   ├── Entity/          # Database entities
│   ├── Form/            # Symfony forms
│   ├── Repository/      # Data access layer
│   └── Service/         # Business logic & ML services
├── templates/           # Twig templates
└── var/                 # Cache and logs
```

## AI/ML Features

### Consumption Prediction
- Uses Moving Average and Trend Analysis
- Predicts future consumption for 7, 14, or 30 days
- Provides confidence levels (High/Medium/Low)
- Shows prediction bounds

### Stockout Prediction
- Projects when drugs will run out
- Categorizes urgency (URGENT/WARNING/SAFE)
- Estimates days until stockout
- Provides confidence scores

## 📊 Database Schema

### Drug Table
- `id`, `name`, `category`, `threshold`, `stock_quantity`, `created_at`

### Consumption Table
- `id`, `drug_id`, `quantity`, `consumed_at`, `logged_by`, `notes`

## Security Notes

- CSRF protection enabled on all forms
- SQL injection prevention via Doctrine ORM
- XSS protection via Twig auto-escaping
- Input validation on all forms

**Note**: Authentication system not implemented (for academic project). Add user authentication for production use.

## 📝 Documentation

- `PROJECT_SUMMARY.md` - Complete project documentation
- `WEBSITE_FEATURES_COMPLETE.md` - Website features documentation
- `AI_ML_FEATURES_COMPLETE.md` - AI/ML features documentation

## 👥 Contributing

This is an academic project. For collaboration:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This project is for academic purposes.

## Authors

- Ameya Dhurde
- Karri Leena

## Acknowledgments

- Symfony Framework
- PHP-ML Library
- Chart.js
- Bootstrap

---


For detailed project information, see `PROJECT_SUMMARY.md`

