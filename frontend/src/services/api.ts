import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Auto inject Bearer token if stored in local storage
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token && config.headers) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (config.data instanceof FormData && config.headers) {
    delete config.headers['Content-Type'];
  }
  return config;
});

// Auto logout on 401 — token expired or invalid
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Don't redirect if already on login page or for the logout endpoint itself
      const url: string = error.config?.url ?? '';
      if (!url.includes('/login') && !url.includes('/logout')) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        localStorage.removeItem('auth_tenant');
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export function uploadFile(endpoint: string, file: File, fieldName = 'file') {
  const formData = new FormData();
  formData.append(fieldName, file);
  return api.post(endpoint, formData);
}

export default api;
