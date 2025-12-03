<template>
  <div class="product-detail">
    <button @click="$router.push('/')" class="back-btn">
      <span class="back-icon">←</span>
      Back to Products
    </button>
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading product...</p>
    </div>
    <div v-if="error" class="error-message">
      <span class="error-icon">⚠️</span>
      <p>{{ error }}</p>
    </div>
    <div v-if="!loading && !error && product" class="product-content">
      <div class="product-image-section">
        <div class="image-wrapper">
          <img :src="product.image_url || '/placeholder.png'" :alt="product.name" />
        </div>
      </div>
      <div class="product-info-section">
        <div class="product-header">
          <h1 class="product-title">{{ product.name }}</h1>
          <div class="price-badge">
            <span class="currency">$</span>
            <span class="amount">{{ product.price }}</span>
          </div>
        </div>
        <p class="description">{{ product.description }}</p>
        <div class="stock-info">
          <span class="stock-icon">📦</span>
          <span class="stock-text">{{ product.stock }} items available</span>
        </div>
        <div class="quantity-section">
          <label class="quantity-label">Quantity:</label>
          <div class="quantity-controls">
            <button 
              @click="quantity > 1 ? quantity-- : null" 
              class="qty-btn minus"
              :disabled="quantity <= 1"
            >-</button>
            <input
              type="number"
              v-model.number="quantity"
              min="1"
              :max="product.stock"
              class="quantity-input"
            />
            <button 
              @click="quantity < product.stock ? quantity++ : null" 
              class="qty-btn plus"
              :disabled="quantity >= product.stock"
            >+</button>
          </div>
        </div>
        <button
          @click="addToCart"
          :disabled="quantity > product.stock || quantity < 1"
          class="add-to-cart-btn"
        >
          <span class="cart-icon">🛒</span>
          Add to Cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { productService } from '../services/api';
import { cartService } from '../services/cart';

const toast = useToast();

const route = useRoute();
const router = useRouter();
const product = ref(null);
const loading = ref(true);
const error = ref(null);
const quantity = ref(1);

const fetchProduct = async () => {
  try {
    loading.value = true;
    const response = await productService.getById(route.params.id);
    product.value = response.data;
    error.value = null;
  } catch (err) {
    error.value = 'Failed to load product. Please try again later.';
    console.error('Error fetching product:', err);
  } finally {
    loading.value = false;
  }
};

const addToCart = () => {
  if (quantity.value < 1 || quantity.value > product.value.stock) {
    toast.error('Invalid quantity. Please select a valid amount.', {
      timeout: 3000,
    });
    return;
  }
  
  cartService.addToCart(product.value.id, quantity.value);
  toast.success('Product added to cart!', {
    timeout: 2000,
  });
  setTimeout(() => {
    router.push('/checkout');
  }, 500);
};

onMounted(() => {
  fetchProduct();
});
</script>

<style scoped>
.product-detail {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.back-btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
  padding: 12px 24px;
  border-radius: 25px;
  cursor: pointer;
  margin-bottom: 30px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 16px;
  font-weight: 500;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateX(-5px);
}

.back-icon {
  font-size: 20px;
}

.loading-container,
.error-message {
  text-align: center;
  padding: 60px 20px;
  color: white;
}

.spinner {
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top: 4px solid white;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 10px;
  max-width: 500px;
  margin: 0 auto;
  color: #e74c3c;
}

.error-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 15px;
}

.product-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 50px;
  margin-top: 20px;
  background: white;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

@media (max-width: 968px) {
  .product-content {
    grid-template-columns: 1fr;
    gap: 30px;
  }
}

.product-image-section {
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-wrapper {
  width: 100%;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.image-wrapper img {
  width: 100%;
  height: auto;
  display: block;
}

.product-info-section {
  display: flex;
  flex-direction: column;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.product-title {
  font-size: 36px;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  flex: 1;
}

.price-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 15px 25px;
  border-radius: 15px;
  display: flex;
  align-items: baseline;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.currency {
  font-size: 20px;
  font-weight: 600;
}

.amount {
  font-size: 36px;
  font-weight: 700;
  margin-left: 2px;
}

.description {
  color: #7f8c8d;
  font-size: 16px;
  line-height: 1.8;
  margin: 20px 0;
}

.stock-info {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  background: #e8f5e9;
  border-radius: 10px;
  margin: 20px 0;
  font-size: 16px;
  color: #2e7d32;
  font-weight: 500;
}

.stock-icon {
  font-size: 20px;
}

.quantity-section {
  margin: 30px 0;
}

.quantity-label {
  display: block;
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 12px;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.qty-btn {
  width: 45px;
  height: 45px;
  border: 2px solid #667eea;
  background: white;
  border-radius: 10px;
  cursor: pointer;
  font-size: 20px;
  font-weight: 600;
  color: #667eea;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qty-btn:hover:not(:disabled) {
  background: #667eea;
  color: white;
  transform: scale(1.1);
}

.qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity-input {
  width: 100px;
  height: 45px;
  padding: 0 15px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 18px;
  font-weight: 600;
  text-align: center;
  transition: border-color 0.3s;
}

.quantity-input:focus {
  outline: none;
  border-color: #667eea;
}

.add-to-cart-btn {
  width: 100%;
  padding: 18px;
  margin-top: 20px;
  border: none;
  border-radius: 12px;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.add-to-cart-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.add-to-cart-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

.cart-icon {
  font-size: 20px;
}
</style>

