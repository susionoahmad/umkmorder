<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans flex items-center justify-center p-6 relative overflow-hidden">
    <!-- Neon Background Orbs -->
    <div class="absolute top-[10%] left-[10%] w-[350px] h-[350px] rounded-full bg-indigo-500/10 blur-[80px] pointer-events-none animate-pulse"></div>
    <div class="absolute bottom-[10%] right-[10%] w-[350px] h-[350px] rounded-full bg-purple-500/10 blur-[80px] pointer-events-none animate-pulse"></div>

    <div class="max-w-md w-full z-10">
      <!-- Back Link -->
      <router-link 
        to="/" 
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-slate-200 transition-colors duration-200 mb-6 bg-slate-900/60 border border-slate-800/80 rounded-full px-3 py-1.5 backdrop-blur-md"
      >
        ← Kembali ke Hub
      </router-link>

      <!-- Card Container -->
      <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 md:p-10 shadow-2xl backdrop-blur-md relative">
        <div class="flex items-center gap-3 justify-center mb-8">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-black text-2xl shadow-lg shadow-indigo-500/25">
            U
          </div>
          <span class="font-extrabold text-2xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-slate-100 to-slate-400">
            UMKMOrder
          </span>
        </div>

        <div class="text-center mb-8">
          <h3 class="text-2xl font-bold text-slate-100">Login Tenant</h3>
          <p class="text-sm text-slate-500 mt-2">Masuk ke akun bisnis Anda untuk mengelola produk & pesanan.</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label class="block text-sm font-semibold text-slate-400 mb-2">Email Bisnis</label>
            <input 
              v-model="email" 
              type="email" 
              required
              placeholder="owner1@kurniatelur.com" 
              class="w-full bg-slate-950 border border-slate-850 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition-all duration-200"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-400 mb-2">Password</label>
            <input 
              v-model="password" 
              type="password" 
              required
              placeholder="••••••••" 
              class="w-full bg-slate-950 border border-slate-850 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition-all duration-200"
            />
          </div>

          <!-- Error Alert -->
          <div 
            v-if="authStore.error" 
            class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-center text-sm text-red-400 transition"
          >
            {{ authStore.error }}
          </div>

          <button 
            type="submit" 
            :disabled="authStore.isLoading"
            class="w-full py-4 px-6 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 font-extrabold text-lg transition duration-300 shadow-lg shadow-indigo-600/20 disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <span v-if="authStore.isLoading" class="w-5 h-5 border-2 border-slate-100 border-t-transparent rounded-full animate-spin"></span>
            <span>{{ authStore.isLoading ? 'Mencocokkan...' : 'Masuk Sekarang' }}</span>
          </button>
        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-slate-500">
            Belum memiliki toko? 
            <router-link to="/register" class="text-indigo-400 hover:text-indigo-300 hover:underline font-semibold transition-colors duration-200">
              Daftar gratis sekarang
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');

async function handleLogin() {
  const success = await authStore.login({
    email: email.value,
    password: password.value
  });

  if (success) {
    const redirectPath = route.query.redirect as string;
    if (redirectPath) {
      router.push(redirectPath);
    } else {
      router.push('/dashboard');
    }
  }
}
</script>

<style scoped>
</style>
