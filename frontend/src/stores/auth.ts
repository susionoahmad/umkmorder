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
  phone?: string | null;
  address?: string | null;
  subscription_plan?: string | null;
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
    originalAdminToken: localStorage.getItem('admin_token') || null as string | null,
    originalAdminUser: JSON.parse(localStorage.getItem('admin_user') || 'null') as AuthUser | null,
    isLoading: false,
    error: null as string | null,
    showUpgradeModal: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    role: (state) => state.user?.role || null,
    isSuperAdmin: (state) => state.user?.role === 'super_admin',
    isPro: (state) => {
      if (state.user?.role === 'super_admin') return true;
      const plan = (state.tenant as any)?.subscription_plan ?? 'free';
      return plan === 'pro' || plan === 'business';
    },
    subscriptionPlan: (state) => {
      if (state.user?.role === 'super_admin') return 'business';
      return (state.tenant as any)?.subscription_plan ?? 'free';
    },
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
    setSession(token: string, user: AuthUser, tenant: AuthTenant | null) {
      this.token = token;
      this.user = user;
      this.tenant = tenant;
      localStorage.setItem('auth_token', token);
      localStorage.setItem('auth_user', JSON.stringify(user));
      if (tenant) {
        localStorage.setItem('auth_tenant', JSON.stringify(tenant));
      } else {
        localStorage.removeItem('auth_tenant');
      }
    },
    impersonateTenant(token: string, user: AuthUser, tenant: AuthTenant) {
      // If not already impersonating, save current admin session
      if (!this.originalAdminToken && this.user?.role === 'super_admin') {
        this.originalAdminToken = this.token;
        this.originalAdminUser = this.user;
        localStorage.setItem('admin_token', this.token || '');
        localStorage.setItem('admin_user', JSON.stringify(this.user));
      }
      this.setSession(token, user, tenant);
    },
    switchBackToAdmin() {
      if (this.originalAdminToken && this.originalAdminUser) {
        const adminToken = this.originalAdminToken;
        const adminUser = this.originalAdminUser;

        // Clear original admin storage
        this.originalAdminToken = null;
        this.originalAdminUser = null;
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');

        // Restore admin session
        this.setSession(adminToken, adminUser, null);
        return true;
      }
      return false;
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
        this.originalAdminToken = null;
        this.originalAdminUser = null;
        this.error = null;

        // Clear local storage
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        localStorage.removeItem('auth_tenant');
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        
        this.isLoading = false;
      }
    },
    updateTenant(tenant: AuthTenant) {
      this.tenant = tenant;
      localStorage.setItem('auth_tenant', JSON.stringify(tenant));
    },
  },
});
