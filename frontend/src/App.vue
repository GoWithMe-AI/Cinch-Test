<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { RouterView } from 'vue-router';
import { cartService } from './services/cart';

const cartCount = ref(0);

const updateCartCount = () => {
  cartCount.value = cartService.getCartCount();
};

let interval = null;

onMounted(() => {
  updateCartCount();
  // Update cart count when storage changes (from other tabs/components)
  window.addEventListener('storage', updateCartCount);
  // Listen for custom cart update events
  window.addEventListener('cartUpdated', updateCartCount);
  // Also check periodically for same-tab updates
  interval = setInterval(updateCartCount, 500);
});

onUnmounted(() => {
  if (interval) clearInterval(interval);
  window.removeEventListener('storage', updateCartCount);
  window.removeEventListener('cartUpdated', updateCartCount);
});
</script>

<template>
  <div id="app">
    <header class="header">
      <nav class="navbar">
        <div class="nav-container">
          <div class="nav-brand">
            <router-link to="/" class="brand-link">
              <span class="brand-icon">🛍️</span>
              <h1 class="nav-title">ShopHub</h1>
            </router-link>
          </div>
          <div class="nav-links">
            <router-link to="/" class="nav-link">
              <span class="nav-icon">🏠</span>
              Products
            </router-link>
            <router-link to="/checkout" class="nav-link cart-link">
              <span class="nav-icon">🛒</span>
              Checkout
              <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
            </router-link>
          </div>
        </div>
      </nav>
    </header>
    <main class="main-content">
      <RouterView />
    </main>
    <footer class="footer">
      <div class="footer-container">
        <div class="footer-section">
          <h3>ShopHub</h3>
          <p>Your trusted online shopping destination</p>
        </div>
        <div class="footer-section">
          <h4>Quick Links</h4>
          <router-link to="/">Products</router-link>
          <router-link to="/checkout">Checkout</router-link>
        </div>
        <div class="footer-section">
          <h4>Contact</h4>
          <p>Email: support@shophub.com</p>
          <p>Phone: +1 (555) 123-4567</p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2025 ShopHub. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-attachment: fixed;
  min-height: 100vh;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  width: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.navbar {
  color: white;
  padding: 0;
  width: 100%;
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70px;
  width: 100%;
  box-sizing: border-box;
}

.nav-brand {
  display: flex;
  align-items: center;
}

.brand-link {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: white;
}

.brand-icon {
  font-size: 32px;
}

.nav-title {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
  background: linear-gradient(45deg, #fff, #e0e0e0);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.nav-links {
  display: flex;
  gap: 15px;
  align-items: center;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 25px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  position: relative;
}

.nav-icon {
  font-size: 18px;
}

.nav-link:hover,
.nav-link.router-link-active {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.cart-link {
  position: relative;
}

.cart-badge {
  background: #ff4444;
  color: white;
  border-radius: 50%;
  padding: 4px 8px;
  font-size: 12px;
  font-weight: bold;
  min-width: 20px;
  text-align: center;
  position: absolute;
  top: -5px;
  right: -5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.main-content {
  flex: 1;
  padding: 40px 0;
  min-height: calc(100vh - 70px - 200px);
  width: 100%;
}

.footer {
  width: 100%;
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
  margin-top: auto;
  padding: 40px 0 20px;
  box-sizing: border-box;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 30px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 30px;
  margin-bottom: 30px;
  width: 100%;
  box-sizing: border-box;
}

.footer-section h3 {
  font-size: 24px;
  margin-bottom: 15px;
  color: #fff;
}

.footer-section h4 {
  font-size: 18px;
  margin-bottom: 15px;
  color: #ecf0f1;
}

.footer-section p {
  margin: 8px 0;
  color: #bdc3c7;
  line-height: 1.6;
}

.footer-section a {
  display: block;
  color: #bdc3c7;
  text-decoration: none;
  margin: 8px 0;
  transition: color 0.3s;
}

.footer-section a:hover {
  color: #fff;
}

.footer-bottom {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 30px 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
  color: #95a5a6;
  width: 100%;
  box-sizing: border-box;
}
</style>
