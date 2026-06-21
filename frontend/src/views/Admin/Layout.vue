<template>
  <div class="min-h-screen font-sans flex bg-slate-950 text-slate-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between shrink-0 hidden md:flex">
      <div>
        <!-- Logo Header -->
        <div class="h-20 px-6 border-b border-slate-800 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-black text-lg shadow-md shadow-indigo-500/20 text-white">
            S
          </div>
          <span class="font-extrabold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-slate-100 to-slate-400">
            SaaS Admin
          </span>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-2">
          <router-link 
            v-for="item in navItems" 
            :key="item.path" 
            :to="item.path" 
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 text-sm font-semibold hover:bg-slate-800/50 hover:text-slate-200"
            exact-active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-600/30"
          >
            <span class="text-lg">{{ item.icon }}</span>
            <span>{{ item.label }}</span>
          </router-link>
        </nav>
      </div>

      <!-- Footer / Logout -->
      <div class="p-4 border-t border-slate-850 space-y-2">
        <button 
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-500/10 text-red-400 transition duration-200 text-xs font-bold"
        >
          <span>🚪</span>
          <span>Keluar Admin</span>
        </button>
      </div>
    </aside>

    <!-- Mobile Sidebar Drawer -->
    <div v-if="isMobileMenuOpen" class="fixed inset-0 z-50 md:hidden flex">
      <div @click="isMobileMenuOpen = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
      <aside class="relative w-72 bg-slate-900 border-r border-slate-800 flex flex-col justify-between shrink-0 h-full max-w-[80vw] z-10 animate-[slide-in_0.2s_ease-out]">
        <div>
          <div class="h-20 px-6 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-black text-lg text-white">
                S
              </div>
              <span class="font-extrabold text-lg tracking-tight text-slate-100">SaaS Admin</span>
            </div>
            <button @click="isMobileMenuOpen = false" class="p-2 rounded-lg hover:bg-slate-800 text-slate-400">✕</button>
          </div>

          <nav class="p-4 space-y-2">
            <router-link 
              v-for="item in navItems" 
              :key="item.path" 
              :to="item.path" 
              @click="isMobileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 text-sm font-semibold hover:bg-slate-800/50 hover:text-slate-200"
              exact-active-class="bg-indigo-600 text-white shadow-lg shadow-indigo-600/30"
            >
              <span class="text-lg">{{ item.icon }}</span>
              <span>{{ item.label }}</span>
            </router-link>
          </nav>
        </div>

        <div class="p-4 border-t border-slate-850">
          <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-500/10 text-red-400 text-xs font-bold">
            <span>🚪</span>
            <span>Keluar Admin</span>
          </button>
        </div>
      </aside>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Header -->
      <header class="h-20 border-b border-slate-800 bg-slate-900/60 backdrop-blur-md px-6 flex items-center justify-between z-10">
        <div class="flex items-center gap-4">
          <button @click="isMobileMenuOpen = true" class="md:hidden p-2 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-200">
            <span>☰</span>
          </button>
          <h2 class="text-xl font-bold text-slate-100 capitalize">{{ currentTitle }}</h2>
        </div>

        <div class="flex items-center gap-4">
          <!-- Switch back to Admin if impersonating -->
          <button 
            v-if="authStore.originalAdminToken" 
            @click="handleSwitchBack"
            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition duration-150 flex items-center gap-1.5"
          >
            ↩️ Kembali ke Admin
          </button>

          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-sm font-semibold text-slate-400">Platform Owner</span>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6 md:p-8 overflow-y-auto">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isMobileMenuOpen = ref(false);

const navItems = [
  { path: '/admin', label: 'Dashboard', icon: '📊' },
  { path: '/admin/tenants', label: 'Tenants', icon: '🏢' },
  { path: '/admin/subscriptions', label: 'Langganan', icon: '🔑' },
  { path: '/admin/plans', label: 'Paket Fitur', icon: '🏷️' },
  { path: '/admin/billing', label: 'Tagihan', icon: '💳' },
  { path: '/admin/users', label: 'Pengguna', icon: '👤' },
  { path: '/admin/support', label: 'Support', icon: '🛠️' },
  { path: '/admin/settings', label: 'Pengaturan', icon: '⚙️' },
];

const currentTitle = computed(() => {
  const path = route.path;
  if (path === '/admin') return 'SaaS Overview';
  const match = navItems.find(item => item.path === path);
  return match ? match.label : 'SaaS Admin';
});

async function handleLogout() {
  if (confirm('Apakah Anda yakin ingin keluar dari panel admin?')) {
    await authStore.logout();
    router.push('/login');
  }
}

function handleSwitchBack() {
  if (authStore.switchBackToAdmin()) {
    router.push('/admin');
  }
}
</script>

<style scoped>
@keyframes slide-in {
  from { transform: translateX(-100%); }
  to { transform: translateX(0); }
}
</style>
