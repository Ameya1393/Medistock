# Authentication System Setup Guide

## Overview
MediStock now includes a complete authentication system with role-based access control:
- **Admin**: Full access to all features (analytics, reports, predictions, stock management)
- **Ground Level**: Limited access (can only add drugs and log consumption)

## Setup Steps

### 1. Run Database Migration
```bash
php bin/console doctrine:migrations:migrate
```

This will create the `user` table in your database.

### 2. Create First Admin User

You have two options:

#### Option A: Using Symfony Console (Recommended)
```bash
php bin/console app:create-admin
```

If the command doesn't exist, use Option B.

#### Option B: Manual Registration + Database Update
1. Register a new user through the web interface: `http://localhost:8000/register`
2. Note the email address you used
3. Update the user role in the database:

```sql
UPDATE `user` SET roles = '["ROLE_ADMIN"]' WHERE email = 'your-admin-email@example.com';
```

Or use Symfony console:
```bash
php bin/console dbal:run-sql "UPDATE \`user\` SET roles = '[\"ROLE_ADMIN\"]' WHERE email = 'your-admin-email@example.com'"
```

### 3. Access Control

**Admin Users Can:**
- Access dashboard
- Add/Edit/Delete drugs
- Log consumption
- Update stock
- View alerts
- Generate reports
- View predictions (AI/ML)
- Manage user roles

**Ground Level Users Can:**
- Access dashboard
- Add drugs
- Log consumption
- View drugs list

**Ground Level Users Cannot:**
- Access stock management
- View alerts
- Generate reports
- View predictions
- Manage users

### 4. User Management

Admin users can manage roles by:
1. Logging in as admin
2. Going to **Users** in the navigation menu
3. Clicking **Edit Role** for any user
4. Selecting the desired role (Admin or Ground Level)

## Security Features

- Password hashing using Symfony's auto password hasher
- CSRF protection on all forms
- Session-based authentication
- Role-based access control
- Secure logout functionality

## Routes

- `/login` - Login page (public)
- `/register` - Registration page (public)
- `/logout` - Logout (requires authentication)
- `/admin/users` - User management (admin only)
- `/admin/users/{id}/edit-role` - Edit user role (admin only)

## Notes

- All routes except `/login` and `/register` require authentication
- Admin-only routes are protected by `ROLE_ADMIN` check
- Ground level users will see a 403 error if they try to access admin-only features
- The first user you create should be manually promoted to admin using the database update method above


