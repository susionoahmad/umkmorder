import { defineStore } from 'pinia';
import api from '@/services/api';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: string;
}

export interface AuthTenant {
  id: number;
  name: string;
  slug: string;
  catalog_setting?: {
    id: number;
    tenant_id: number;
    catalog_title: string;
    catalog_description: string | null;
    catalog_banner: string | null;
    catalog_enabled: boolean;
    auto_whatsapp_redirect: boolean;
    show_price: boolean;
    theme: string;
    bank_transfer_info?: string | null;
    qris_info?: string | null;
    qris_image_url?: string | null;
  };
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null as string | null,
    user: JSON.parse(localStorage.getItem('auth_user') || 'null') as AuthUser | null,
    tenant: JSON.parse(localStorage.getItem('auth_tenant') || 'null') as AuthTenant | null,
    isLoading: false,
    error: null as string | null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    role: (state) => state.user?.role || null,
  },
  actions: {
    async login(credentials: { email: string; password: string; }) {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await api.post('/login', credentials);
        if (response.data.status === 'success') {
          const { token, user, tenant } = response.data.data;
          this.setSession(token, user, tenant);
          return true;
        } else {
          this.error = response.data.message || 'Login gagal';
        }
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Email atau password salah';
      } finally {
        this.isLoading = false;
      }
      return false;
    },
    setSession(token: string, user: AuthUser, tenant: AuthTenant) {
      this.token = token;
      this.user = user;
      this.tenant = tenant;
      localStorage.setItem('auth_token', token);
      localStorage.setItem('auth_user', JSON.stringify(user));
      localStorage.setItem('auth_tenant', JSON.stringify(tenant));
    },
    async logout() {
      this.isLoading = true;
      try {
        await api.post('/logout');
      } catch (err) {
        // Silently catch if token is already expired/invalid on backend
      } finally {
        this.token = null;
        this.user = null;
        this.tenant = null;

        // Clear local storage
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        localStorage.removeItem('auth_tenant');
        
        this.isLoading = false;
      }
    },
  },
});
