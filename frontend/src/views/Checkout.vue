<template>
  <div class="checkout">
    <div class="page-header">
      <h1 class="page-title">Checkout</h1>
      <p class="page-subtitle">Review your order and complete your purchase</p>
    </div>
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Processing your order...</p>
    </div>
    <div v-if="error" class="error-message">
      <span class="error-icon">⚠️</span>
      <p>{{ error }}</p>
    </div>
    <div v-if="success" class="success-message">
      <div class="success-icon">✅</div>
      <h2>Order Placed Successfully!</h2>
      <div class="success-details">
        <p><strong>Order ID:</strong> #{{ orderId }}</p>
        <p><strong>Email:</strong> {{ userEmail }}</p>
        <p class="success-note">An email confirmation has been sent to your email address.</p>
      </div>
      <button @click="$router.push('/')" class="continue-shopping">
        <span>🛍️</span>
        Continue Shopping
      </button>
    </div>
    <form v-if="!success && !loading" @submit.prevent="placeOrder" class="checkout-form">
      <div class="form-section">
        <h3 class="section-title">
          <span class="section-icon">📧</span>
          Contact Information
        </h3>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            type="email"
            id="email"
            v-model="userEmail"
            required
            class="form-input"
            placeholder="your@email.com"
          />
        </div>
      </div>
      
      <div class="form-section">
        <div class="section-header">
          <h3 class="section-title">
            <span class="section-icon">🛒</span>
            Order Items
          </h3>
          <div v-if="cartItems.length > 0" class="select-all-controls">
            <button 
              type="button" 
              @click="selectAll" 
              class="select-all-btn"
              :disabled="allSelected"
            >
              ✓ Select All
            </button>
            <button 
              type="button" 
              @click="deselectAll" 
              class="select-all-btn"
              :disabled="!hasSelectedItems"
            >
              ✗ Deselect All
            </button>
          </div>
        </div>
        <div class="order-items">
          <div 
            v-for="item in cartItems" 
            :key="item.product_id" 
            class="order-item"
            :class="{ 'item-unselected': !isSelected(item.product_id) }"
          >
            <div class="item-checkbox">
              <input 
                type="checkbox" 
                :id="`item-${item.product_id}`"
                :checked="isSelected(item.product_id)"
                @change="toggleSelection(item.product_id)"
                class="checkbox-input"
              />
              <label :for="`item-${item.product_id}`" class="checkbox-label"></label>
            </div>
            <div class="item-image">
              <img 
                :src="products[item.product_id]?.image_url || '/placeholder.png'" 
                :alt="products[item.product_id]?.name"
              />
            </div>
            <div class="item-info">
              <h4 class="item-name">{{ products[item.product_id]?.name || 'Loading...' }}</h4>
              <p class="item-description">{{ products[item.product_id]?.description || '' }}</p>
              <p class="item-unit-price">${{ products[item.product_id]?.price || '0.00' }} each</p>
            </div>
            <div class="item-controls">
              <div class="quantity-control">
                <button 
                  type="button" 
                  @click="updateQuantity(item.product_id, item.quantity - 1)" 
                  class="qty-btn"
                  :disabled="!isSelected(item.product_id)"
                >-</button>
                <span class="qty-value">{{ item.quantity }}</span>
                <button 
                  type="button" 
                  @click="updateQuantity(item.product_id, item.quantity + 1)" 
                  class="qty-btn"
                  :disabled="!isSelected(item.product_id)"
                >+</button>
              </div>
              <p class="item-total">${{ (products[item.product_id]?.price * item.quantity || 0).toFixed(2) }}</p>
              <button 
                type="button" 
                @click="removeItem(item.product_id)" 
                class="remove-btn"
              >
                🗑️ Remove
              </button>
            </div>
          </div>
          <div v-if="cartItems.length === 0" class="empty-cart">
            <div class="empty-icon">🛒</div>
            <p class="empty-text">Your cart is empty.</p>
            <button @click="$router.push('/')" class="shop-btn">
              <span>🛍️</span>
              Go Shopping
            </button>
          </div>
        </div>
      </div>
      
      <div v-if="cartItems.length > 0" class="order-summary">
        <div class="summary-header">
          <h3>Order Summary</h3>
          <p class="summary-subtitle">{{ selectedItemsCount }} of {{ cartItems.length }} items selected</p>
        </div>
        <div class="summary-content">
          <div class="summary-row">
            <span>Selected Items:</span>
            <span class="summary-value">{{ selectedItemsCount }}</span>
          </div>
          <div class="summary-row">
            <span>Total Quantity:</span>
            <span class="summary-value">{{ totalSelectedItems }}</span>
          </div>
          <div class="summary-row total-row">
            <span class="total-label">Total Amount:</span>
            <span class="total-amount">${{ totalAmount.toFixed(2) }}</span>
          </div>
        </div>
      </div>
      
      <div v-if="cartItems.length > 0" class="form-actions">
        <button 
          type="submit" 
          :disabled="!userEmail || loading || !hasSelectedItems" 
          class="submit-btn"
        >
          <span class="btn-icon">💳</span>
          Place Order ({{ selectedItemsCount }} {{ selectedItemsCount === 1 ? 'item' : 'items' }})
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { orderService, productService } from '../services/api';
import { cartService } from '../services/cart';

const toast = useToast();

const router = useRouter();
const userEmail = ref('');
const cartItems = ref([]);
const products = ref({});
const loading = ref(false);
const error = ref(null);
const success = ref(false);
const orderId = ref(null);
const selectedItems = ref(new Set()); // Track selected product IDs

const selectedItemsCount = computed(() => {
  return selectedItems.value.size;
});

const hasSelectedItems = computed(() => {
  return selectedItems.value.size > 0;
});

const allSelected = computed(() => {
  return cartItems.value.length > 0 && selectedItems.value.size === cartItems.value.length;
});

const totalSelectedItems = computed(() => {
  return cartItems.value
    .filter(item => selectedItems.value.has(item.product_id))
    .reduce((sum, item) => sum + item.quantity, 0);
});

const totalAmount = computed(() => {
  return cartItems.value
    .filter(item => selectedItems.value.has(item.product_id))
    .reduce((sum, item) => {
      const product = products.value[item.product_id];
      return sum + (product?.price || 0) * item.quantity;
    }, 0);
});

const loadCart = async () => {
  const cart = cartService.getCart();
  cartItems.value = cart;
  
  // Initialize all items as selected by default
  if (selectedItems.value.size === 0) {
    cart.forEach(item => {
      selectedItems.value.add(item.product_id);
    });
  } else {
    // Remove items that are no longer in cart
    const cartProductIds = new Set(cart.map(item => item.product_id));
    selectedItems.value.forEach(productId => {
      if (!cartProductIds.has(productId)) {
        selectedItems.value.delete(productId);
      }
    });
    // Add new items to selection
    cart.forEach(item => {
      if (!selectedItems.value.has(item.product_id)) {
        selectedItems.value.add(item.product_id);
      }
    });
  }
  
  // Fetch product details for all cart items
  for (const item of cart) {
    if (!products.value[item.product_id]) {
      try {
        const response = await productService.getById(item.product_id);
        products.value[item.product_id] = response.data;
      } catch (err) {
        console.error('Error fetching product:', err);
      }
    }
  }
};

const isSelected = (productId) => {
  return selectedItems.value.has(productId);
};

const toggleSelection = (productId) => {
  if (selectedItems.value.has(productId)) {
    selectedItems.value.delete(productId);
  } else {
    selectedItems.value.add(productId);
  }
};

const selectAll = () => {
  cartItems.value.forEach(item => {
    selectedItems.value.add(item.product_id);
  });
};

const deselectAll = () => {
  selectedItems.value.clear();
};

const removeItem = (productId) => {
  cartService.removeFromCart(productId);
  loadCart();
  toast.info('Item removed from cart', {
    timeout: 2000,
  });
};

const updateQuantity = (productId, newQuantity) => {
  if (newQuantity < 1) {
    removeItem(productId);
  } else {
    cartService.updateQuantity(productId, newQuantity);
    loadCart();
  }
};

const placeOrder = async () => {
  const selectedCartItems = cartItems.value.filter(item => 
    selectedItems.value.has(item.product_id)
  );

  if (selectedCartItems.length === 0) {
    toast.warning('Please select at least one product to place an order.', {
      timeout: 3000,
    });
    return;
  }

  if (!userEmail.value) {
    toast.warning('Please enter your email address.', {
      timeout: 3000,
    });
    return;
  }

  try {
    loading.value = true;
    error.value = null;

    const orderData = {
      user_email: userEmail.value,
      items: selectedCartItems.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
      })),
    };

    const response = await orderService.create(orderData);
    orderId.value = response.data.order.id;
    success.value = true;
    
    // Remove only selected items from cart
    selectedCartItems.forEach(item => {
      cartService.removeFromCart(item.product_id);
    });
    
    // Clear selection
    selectedItems.value.clear();
    
    // Reload cart to update UI
    loadCart();
    
    toast.success('Order placed successfully! Check your email for confirmation.', {
      timeout: 4000,
    });
  } catch (err) {
    const errorMsg = err.response?.data?.error || 'Failed to place order. Please try again.';
    error.value = errorMsg;
    toast.error(errorMsg, {
      timeout: 4000,
    });
    console.error('Error placing order:', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadCart();
});
</script>

<style scoped>
.checkout {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 20px 40px;
}

.page-header {
  text-align: center;
  margin-bottom: 40px;
  padding: 30px 0;
}

.page-title {
  font-size: 48px;
  font-weight: 700;
  color: white;
  margin-bottom: 10px;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.page-subtitle {
  font-size: 18px;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 300;
}

.loading-container {
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
  text-align: center;
  padding: 30px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 15px;
  color: #e74c3c;
  margin-bottom: 30px;
}

.error-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 15px;
}

.success-message {
  text-align: center;
  padding: 50px 30px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  margin-bottom: 30px;
}

.success-icon {
  font-size: 64px;
  margin-bottom: 20px;
  animation: scaleIn 0.5s ease;
}

@keyframes scaleIn {
  0% { transform: scale(0); }
  100% { transform: scale(1); }
}

.success-message h2 {
  font-size: 32px;
  color: #2ecc71;
  margin-bottom: 20px;
}

.success-details {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 10px;
  margin: 20px 0;
  text-align: left;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}

.success-details p {
  margin: 10px 0;
  color: #2c3e50;
}

.success-note {
  color: #7f8c8d;
  font-style: italic;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #e0e0e0;
}

.checkout-form {
  background: white;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.form-section {
  margin-bottom: 40px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e0e0e0;
}

.section-title {
  font-size: 24px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-icon {
  font-size: 28px;
}

.select-all-controls {
  display: flex;
  gap: 10px;
}

.select-all-btn {
  padding: 8px 16px;
  border: 2px solid #667eea;
  background: white;
  color: #667eea;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.select-all-btn:hover:not(:disabled) {
  background: #667eea;
  color: white;
  transform: translateY(-2px);
}

.select-all-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f0f0f0;
  border-color: #ccc;
  color: #999;
}

.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  margin-bottom: 10px;
  font-weight: 600;
  color: #2c3e50;
  font-size: 16px;
}

.form-input {
  width: 100%;
  padding: 15px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 16px;
  box-sizing: border-box;
  transition: border-color 0.3s;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
}

.order-items {
  margin-top: 10px;
}

.order-item {
  display: grid;
  grid-template-columns: 40px 100px 1fr auto;
  gap: 20px;
  padding: 20px;
  border: 2px solid #e0e0e0;
  border-radius: 15px;
  margin-bottom: 20px;
  background: #fafafa;
  transition: all 0.3s ease;
  position: relative;
}

.order-item:hover {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.order-item.item-unselected {
  opacity: 0.6;
  background: #f5f5f5;
}

.order-item.item-unselected:hover {
  opacity: 0.8;
}

.item-checkbox {
  display: flex;
  align-items: center;
  justify-content: center;
}

.checkbox-input {
  display: none;
}

.checkbox-label {
  width: 24px;
  height: 24px;
  border: 3px solid #667eea;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
  background: white;
}

.checkbox-label:hover {
  background: #f0f0ff;
  transform: scale(1.1);
}

.checkbox-input:checked + .checkbox-label {
  background: #667eea;
  border-color: #667eea;
}

.checkbox-input:checked + .checkbox-label::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 16px;
  font-weight: bold;
}

.item-image {
  width: 100px;
  height: 100px;
  border-radius: 10px;
  overflow: hidden;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-info {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.item-name {
  margin: 0 0 8px 0;
  color: #2c3e50;
  font-size: 18px;
  font-weight: 600;
}

.item-description {
  color: #7f8c8d;
  font-size: 14px;
  margin: 5px 0;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.item-unit-price {
  color: #667eea;
  font-weight: 600;
  font-size: 14px;
  margin-top: 8px;
}

.item-controls {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: space-between;
  gap: 15px;
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: 10px;
}

.qty-btn {
  width: 35px;
  height: 35px;
  border: 2px solid #667eea;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-size: 18px;
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
  border-color: #ccc;
  color: #999;
}

.qty-value {
  min-width: 40px;
  text-align: center;
  font-weight: 600;
  font-size: 16px;
  color: #2c3e50;
}

.item-total {
  font-weight: 700;
  color: #667eea;
  font-size: 20px;
  margin: 0;
}

.remove-btn {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 5px;
}

.remove-btn:hover {
  background: #c0392b;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
}

.empty-cart {
  text-align: center;
  padding: 60px 20px;
  background: #f8f9fa;
  border-radius: 15px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.empty-text {
  font-size: 18px;
  color: #7f8c8d;
  margin-bottom: 25px;
}

.shop-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 15px 30px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.shop-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.form-actions {
  margin-top: 20px;
}

.submit-btn {
  width: 100%;
  padding: 15px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #45a049;
}

.submit-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.continue-shopping {
  background: #2196F3;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 15px;
}

.continue-shopping:hover {
  background: #0b7dda;
}

.order-summary {
  margin-top: 30px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 15px;
  padding: 0;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.summary-header {
  background: rgba(255, 255, 255, 0.1);
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.summary-header h3 {
  margin: 0 0 5px 0;
  color: white;
  font-size: 22px;
}

.summary-subtitle {
  margin: 0;
  color: rgba(255, 255, 255, 0.9);
  font-size: 14px;
  font-weight: 400;
}

.summary-content {
  padding: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  font-size: 16px;
  color: white;
}

.summary-value {
  font-weight: 600;
}

.total-row {
  border-top: 2px solid rgba(255, 255, 255, 0.3);
  padding-top: 15px;
  margin-top: 15px;
  font-size: 20px;
}

.total-label {
  font-weight: 600;
  font-size: 22px;
}

.total-amount {
  color: white;
  font-weight: 700;
  font-size: 32px;
}

.continue-shopping {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 15px 30px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  margin-top: 25px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.continue-shopping:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.submit-btn {
  width: 100%;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 20px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  margin-top: 30px;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.submit-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-icon {
  font-size: 24px;
}
</style>

