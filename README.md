# E-Commerce Microservices System

PHP/Laravel microservices with Vue.js frontend.

## Prerequisites

- PHP 8.1+
- Composer
- MySQL
- Node.js 16+

## Database Setup

```bash
# Create database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS ecommerce_db;"
```

## Configuration

Copy `.env.example` to `.env` in each service directory and update database credentials:

```bash
# Catalog Service
cp catalog-service/.env.example catalog-service/.env

# Checkout Service
cp checkout-service/.env.example checkout-service/.env

# Email Service
cp email-service/.env.example email-service/.env
```

Update database credentials in each `.env` file:
- `DB_DATABASE=ecommerce_db`
- `DB_USERNAME=root`
- `DB_PASSWORD=` (or your MySQL password)

For checkout-service, also add:
- `CATALOG_SERVICE_URL=http://localhost:8001`
- `EMAIL_SERVICE_URL=http://localhost:8003`

## Install Dependencies

Install all dependencies (Composer for Laravel services + npm for frontend):

```bash
npm run install:all
```

Or install individually:
```bash
# Laravel services
cd catalog-service && composer install
cd checkout-service && composer install
cd email-service && composer install

# Frontend
cd frontend && npm install
```

## Setup Services

After installing dependencies, setup each service:

```bash
# Catalog Service
cd catalog-service
php artisan key:generate
php artisan migrate
php artisan db:seed

# Checkout Service
cd checkout-service
php artisan key:generate
php artisan migrate

# Email Service
cd email-service
php artisan key:generate
```

## Run Services

Start all services in one terminal:

```bash
npm start
```

Or use the batch file:
```bash
START.bat
```

This starts all services in one terminal with colored output:
- Catalog Service (Port 8001)
- Checkout Service (Port 8002)
- Email Service (Port 8003)
- Frontend (Port 5173)

All services auto-reload when files are updated.

## Access

- Frontend: http://localhost:5173
- Catalog API: http://localhost:8001/api/products
- Checkout API: http://localhost:8002/api/orders
- Email API: http://localhost:8003/api/send-order-email

## Database Tables

- `migrations` - Laravel migration tracking
- `products` - Product catalog (Catalog Service)
- `orders` - Order records (Checkout Service)
- `order_items` - Order line items (Checkout Service)
