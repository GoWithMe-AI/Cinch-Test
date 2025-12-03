# 🛍️ E-Commerce Microservices System

PHP/Laravel microservices with Vue.js frontend.

## 📋 Architecture

| Service | Port | Description |
|---------|------|-------------|
| Catalog Service | 8001 | Product catalog |
| Checkout Service | 8002 | Order processing |
| Email Service | 8003 | Email notifications |
| Frontend | 5173 | Vue.js application |

## 📦 Prerequisites

- PHP 8.1+, Composer, MySQL, Node.js 16+

## 🚀 Quick Start

### Using Batch Files (Windows)

```bash
SETUP.bat    # One-time setup
START.bat    # Start all services (use Ctrl+C to stop)
```

### Using Commands

**1. Create Database**
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS ecommerce_db;"
```

**2. Install Dependencies**
```bash
npm run install:all
```

**3. Setup Services**
```bash
# Copy .env files
cp catalog-service/.env.example catalog-service/.env
cp checkout-service/.env.example checkout-service/.env
cp email-service/.env.example email-service/.env

# Update database credentials in each .env file
# For checkout-service, add:
# CATALOG_SERVICE_URL=http://127.0.0.1:8001
# EMAIL_SERVICE_URL=http://127.0.0.1:8003

# Generate keys and migrate
cd catalog-service && php artisan key:generate && php artisan migrate && php artisan db:seed
cd ../checkout-service && php artisan key:generate && php artisan migrate
cd ../email-service && php artisan key:generate
```

**4. Start All Services**
```bash
npm start
# or
START.bat
```

## ▶️ Run Services Individually

```bash
# Catalog Service
cd catalog-service && php artisan serve --port=8001

# Checkout Service
cd checkout-service && php artisan serve --port=8002

# Email Service
cd email-service && php artisan serve --port=8003

# Frontend
cd frontend && npm run dev
```

## 🌐 Access

- Frontend: http://localhost:5173
- Catalog API: http://localhost:8001/api/products
- Checkout API: http://localhost:8002/api/orders
- Email API: http://localhost:8003/api/send-order-email

## 🔌 API Endpoints

**Catalog Service**
```
GET  /api/products          - List products
GET  /api/products/{id}    - Get product details
```

**Checkout Service**
```
POST /api/orders            - Create order
Body: { "user_email": "...", "items": [{ "product_id": 1, "quantity": 2 }] }
```

**Email Service**
```
POST /api/send-order-email  - Send order email
```

## 🗄️ Database

**Tables:**
- `migrations` - Laravel tracking
- `products` - Product catalog
- `orders` - Order records
- `order_items` - Order line items

**Relationships:**
- `order_items.order_id` → `orders.id` (Foreign Key with CASCADE delete)
- One order has many order items

## 📁 Project Structure

```
Cinch-Test/
├── catalog-service/     # Product catalog
├── checkout-service/    # Order processing
├── email-service/       # Email notifications
├── frontend/            # Vue.js app
├── SETUP.bat           # Initial setup
└── START.bat            # Start all services
```

## 🔄 Workflow

1. Browse products → Catalog Service
2. Add to cart → localStorage
3. Place order → Checkout Service
4. Order processing → Checkout fetches prices from Catalog, creates order, calls Email Service
5. Email confirmation → Email Service sends summary

## 🛠️ Troubleshooting

**Port in use:**
```bash
# Stop services with Ctrl+C, or manually:
taskkill /F /IM php.exe
taskkill /F /IM node.exe
```

**Service connection issues:**
- Ensure all services are running
- Use `127.0.0.1` instead of `localhost` in `.env`
- Check service URLs in `checkout-service/.env`

**Email Testing (Local):**
Emails are logged to file instead of sent. Check after placing order:
```
email-service/storage/logs/laravel.log
```

Email content appears in the log file. For production, change `MAIL_MAILER=log` to `MAIL_MAILER=smtp` in `email-service/.env`.

**Auto-reload:** Services reload automatically on file changes.
