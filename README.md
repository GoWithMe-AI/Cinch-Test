# 🛍️ E-Commerce Microservices System

PHP/Laravel microservices with Vue.js frontend.


Uploading Cinch-Test(Docker).mp4…


## 🚀 Quick Start

### Docker

```bash
# Start all services
docker-compose up -d
# Stop services
docker-compose down
```

### npm

```bash
# Install all dependencies
npm run install:all
```

### Batch Files (Windows)

```bash
SETUP.bat    # One-time setup
START.bat    # Start all services
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

## 🔄 Workflow

1. Browse products → Catalog Service
2. Add to cart → localStorage
3. Place order → Checkout Service
4. Order processing → Checkout fetches prices from Catalog, creates order, calls Email Service
5. Email confirmation → Email Service sends summary
