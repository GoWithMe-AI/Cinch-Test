import axios from 'axios';

const catalogServiceUrl = import.meta.env.VITE_CATALOG_SERVICE_URL || 'http://localhost:8001';
const checkoutServiceUrl = import.meta.env.VITE_CHECKOUT_SERVICE_URL || 'http://localhost:8002';

const catalogApi = axios.create({
  baseURL: `${catalogServiceUrl}/api`,
});

const checkoutApi = axios.create({
  baseURL: `${checkoutServiceUrl}/api`,
});

export const productService = {
  getAll: (page = 1, perPage = 5) => catalogApi.get('/products', {
    params: { page, per_page: perPage }
  }),
  getById: (id) => catalogApi.get(`/products/${id}`),
};

export const orderService = {
  create: (orderData) => checkoutApi.post('/orders', orderData),
};

