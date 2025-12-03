// Cart management service
const CART_KEY = 'ecommerce_cart';

export const cartService = {
  getCart() {
    const cart = localStorage.getItem(CART_KEY);
    return cart ? JSON.parse(cart) : [];
  },

  addToCart(productId, quantity = 1) {
    const cart = this.getCart();
    const existingItem = cart.find(item => item.product_id === productId);
    
    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      cart.push({ product_id: productId, quantity });
    }
    
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    return cart;
  },

  removeFromCart(productId) {
    const cart = this.getCart();
    const filtered = cart.filter(item => item.product_id !== productId);
    localStorage.setItem(CART_KEY, JSON.stringify(filtered));
    window.dispatchEvent(new Event('cartUpdated'));
    return filtered;
  },

  updateQuantity(productId, quantity) {
    const cart = this.getCart();
    const item = cart.find(item => item.product_id === productId);
    if (item) {
      if (quantity <= 0) {
        return this.removeFromCart(productId);
      }
      item.quantity = quantity;
    }
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    window.dispatchEvent(new Event('cartUpdated'));
    return cart;
  },

  clearCart() {
    localStorage.removeItem(CART_KEY);
    window.dispatchEvent(new Event('cartUpdated'));
  },

  getCartCount() {
    return this.getCart().reduce((sum, item) => sum + item.quantity, 0);
  },
};

