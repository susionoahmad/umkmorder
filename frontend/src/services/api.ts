import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token && config.headers) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (config.data instanceof FormData && config.headers) {
    delete config.headers['Content-Type'];
  }

  // Intercept writing requests in demo dashboard mode
  if (localStorage.getItem('demo_dashboard') === 'true') {
    const method = config.method?.toLowerCase();
    if (method && ['post', 'put', 'patch', 'delete'].includes(method)) {
      const url = config.url || '';
      if (!url.includes('/login') && !url.includes('/logout') && !url.includes('/check-slug')) {
        alert('Mode Demo (Hanya Baca): Perubahan data tidak diizinkan.');
        return Promise.reject(new Error('Mode Demo (Hanya Baca): Aksi tulis tidak diizinkan.'));
      }
    }
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
