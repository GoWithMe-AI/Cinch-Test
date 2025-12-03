<template>
  <div class="product-list">
    <div class="page-header">
      <h1 class="page-title">Our Products</h1>
      <p class="page-subtitle">Discover amazing products at great prices</p>
    </div>
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading products...</p>
    </div>
    <div v-if="error" class="error-message">
      <span class="error-icon">⚠️</span>
      <p>{{ error }}</p>
    </div>
    <div v-if="!loading && !error">
      <div class="products-grid">
        <div
          v-for="product in products"
          :key="product.id"
          class="product-card"
          @click="viewProduct(product.id)"
        >
          <div class="product-image-wrapper">
            <img :src="product.image_url || '/placeholder.png'" :alt="product.name" />
            <div class="product-overlay">
              <span class="view-details">View Details →</span>
            </div>
          </div>
          <div class="product-info">
            <h3 class="product-name">{{ product.name }}</h3>
            <p class="description">{{ product.description }}</p>
            <div class="product-footer">
              <div class="price-section">
                <span class="price">${{ product.price }}</span>
              </div>
              <div class="stock-badge" :class="{ 'low-stock': product.stock < 10 }">
                <span class="stock-icon">📦</span>
                <span>{{ product.stock }} in stock</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="totalPages > 1" class="pagination">
        <button 
          @click="goToPage(currentPage - 1)" 
          :disabled="currentPage === 1"
          class="page-btn"
        >
          ← Previous
        </button>
        <div class="page-numbers">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="['page-number', { active: page === currentPage }]"
          >
            {{ page }}
          </button>
        </div>
        <button 
          @click="goToPage(currentPage + 1)" 
          :disabled="currentPage === totalPages"
          class="page-btn"
        >
          Next →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { productService } from '../services/api';

const router = useRouter();
const route = useRoute();
const products = ref([]);
const loading = ref(true);
const error = ref(null);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 5;

const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
  let end = Math.min(totalPages.value, start + maxVisible - 1);
  
  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1);
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const fetchProducts = async (page = 1) => {
  try {
    loading.value = true;
    const response = await productService.getAll(page, perPage);
    const data = response.data;
    
    // Handle paginated response
    if (data.data) {
      products.value = data.data;
      currentPage.value = data.current_page || page;
      totalPages.value = data.last_page || 1;
    } else {
      // Fallback for non-paginated response
      products.value = Array.isArray(data) ? data : [];
      currentPage.value = page;
      totalPages.value = Math.ceil(products.value.length / perPage);
    }
    
    error.value = null;
    
    // Update URL without reload
    if (page !== 1) {
      router.push({ query: { page } });
    } else {
      router.push({ query: {} });
    }
  } catch (err) {
    error.value = 'Failed to load products. Please try again later.';
    console.error('Error fetching products:', err);
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchProducts(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const viewProduct = (id) => {
  router.push(`/product/${id}`);
};

onMounted(() => {
  const page = parseInt(route.query.page) || 1;
  fetchProducts(page);
});
</script>

<style scoped>
.product-list {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px 40px;
}

.page-header {
  text-align: center;
  margin-bottom: 50px;
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
  padding: 40px 20px;
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

.products-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
}

@media (max-width: 1400px) {
  .products-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 1024px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
}

.product-card {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  position: relative;
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 380px;
}

.product-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
}

.product-image-wrapper {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.product-image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-image-wrapper img {
  transform: scale(1.1);
}

.product-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(102, 126, 234, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
  opacity: 1;
}

.view-details {
  color: white;
  font-size: 18px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.product-info {
  padding: 15px;
  display: flex;
  flex-direction: column;
  flex: 1;
  justify-content: space-between;
}

.product-name {
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 8px 0;
  min-height: 24px;
  line-height: 1.2;
}

.description {
  color: #7f8c8d;
  font-size: 12px;
  margin: 0 0 15px 0;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  min-height: 34px;
  flex-shrink: 0;
}

.product-footer {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: auto;
  padding-top: 10px;
  gap: 10px;
}

.price-section {
  display: flex;
  align-items: baseline;
  justify-content: center;
  width: 100%;
}

.price {
  font-size: 22px;
  font-weight: 700;
  color: #667eea;
  white-space: nowrap;
}

.stock-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 4px 10px;
  background: #e8f5e9;
  border-radius: 15px;
  font-size: 11px;
  color: #2e7d32;
  font-weight: 500;
  white-space: nowrap;
}

.stock-badge.low-stock {
  background: #fff3e0;
  color: #e65100;
}

.stock-icon {
  font-size: 14px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 40px;
  padding: 20px;
}

.page-btn {
  padding: 12px 24px;
  background: white;
  border: 2px solid #667eea;
  border-radius: 8px;
  color: #667eea;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
}

.page-btn:hover:not(:disabled) {
  background: #667eea;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 8px;
}

.page-number {
  width: 40px;
  height: 40px;
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  color: #2c3e50;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-number:hover {
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-2px);
}

.page-number.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #667eea;
  color: white;
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}
</style>

