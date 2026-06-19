import { createRouter, createWebHistory } from 'vue-router';
import CatalogHome from '@/views/Catalog/CatalogHome.vue';
import Checkout from '@/views/Catalog/Checkout.vue';
import Landing from '@/views/Landing.vue';
import Login from '@/views/Auth/Login.vue';
import Register from '@/views/Auth/Register.vue';
import Layout from '@/views/Dashboard/Layout.vue';
import DashboardIndex from '@/views/Dashboard/Index.vue';
import DashboardProducts from '@/views/Dashboard/Products.vue';
import DashboardOrders from '@/views/Dashboard/Orders.vue';
import DashboardReceivables from '@/views/Dashboard/Receivables.vue';
import CatalogSettings from '@/views/Dashboard/CatalogSettings.vue';
import OrderSuccess from '@/views/Catalog/OrderSuccess.vue';
import CatalogAnalytics from '@/views/Analytics/CatalogAnalytics.vue';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/',
    name: 'landing',
    component: Landing,
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guest: true },
  },
  {
    path: '/dashboard',
    component: Layout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard-index',
        component: DashboardIndex,
      },
      {
        path: 'products',
        name: 'dashboard-products',
        component: DashboardProducts,
      },
      {
        path: 'orders',
        name: 'dashboard-orders',
        component: DashboardOrders,
      },
      {
        path: 'receivables',
        name: 'dashboard-receivables',
        component: DashboardReceivables,
      },
      {
        path: 'catalog-online',
        name: 'dashboard-catalog-online',
        component: CatalogSettings,
      },
      {
        path: 'analytics',
        name: 'dashboard-analytics',
        component: CatalogAnalytics,
      },
      {
        path: 'settings',
        redirect: '/dashboard/catalog-online',
      },
    ],
  },
  {
    path: '/:slug',
    name: 'catalog-home',
    component: CatalogHome,
  },
  {
    path: '/:slug/checkout',
    name: 'checkout',
    component: Checkout,
  },
  {
    path: '/:slug/order-success',
    name: 'order-success',
    component: OrderSuccess,
  },
  {
    path: '/admin',
    component: () => import('@/views/Admin/Layout.vue'),
    meta: { requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: () => import('@/views/Admin/Dashboard.vue'),
      },
      {
        path: 'tenants',
        name: 'admin-tenants',
        component: () => import('@/views/Admin/Tenants.vue'),
      },
      {
        path: 'subscriptions',
        name: 'admin-subscriptions',
        component: () => import('@/views/Admin/Subscriptions.vue'),
      },
      {
        path: 'plans',
        name: 'admin-plans',
        component: () => import('@/views/Admin/Plans.vue'),
      },
      {
        path: 'billing',
        name: 'admin-billing',
        component: () => import('@/views/Admin/Billing.vue'),
      },
      {
        path: 'support',
        name: 'admin-support',
        component: () => import('@/views/Admin/Support.vue'),
      },
      {
        path: 'settings',
        name: 'admin-settings',
        component: () => import('@/views/Admin/Settings.vue'),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, _, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAdmin) {
    if (!authStore.isAuthenticated) {
      next({ name: 'login', query: { redirect: to.fullPath } });
    } else if (authStore.user?.role !== 'super_admin') {
      next('/dashboard');
    } else {
      next();
    }
  } else if (to.meta.requiresAuth) {
    if (authStore.isAuthenticated && authStore.user?.role === 'super_admin') {
      next('/admin');
    } else if (!authStore.isAuthenticated) {
      next({ name: 'login', query: { redirect: to.fullPath } });
    } else {
      next();
    }
  } else if (to.meta.guest && authStore.isAuthenticated) {
    if (authStore.user?.role === 'super_admin') {
      next('/admin');
    } else {
      next('/dashboard');
    }
  } else {
    next();
  }
});

export default router;
