import axios from 'axios';
import { token } from './auth.js';

// Create axios instance with default config
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Request interceptor to add auth token
api.interceptors.request.use((config) => {
  // If token exists, add to headers
  if (token.value) {
    config.headers.Authorization = `Bearer ${token.value}`;
  }
  return config;
});

export default api;
