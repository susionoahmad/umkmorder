<template>
  <header class="navbar" :class="{ 'navbar-scrolled': isScrolled }">
    <div class="navbar-inner">
      <a @click="navigate('#hero')" class="brand cursor-pointer">
        <div class="brand-icon">U</div>
        <span class="brand-name">UMKMOrder</span>
      </a>
      <nav class="nav-links">
        <a @click="navigate('#hero')" :class="{ active: activeSection === 'hero' }" class="cursor-pointer">Beranda</a>
        <a @click="navigate('#fitur')" :class="{ active: activeSection === 'fitur' }" class="cursor-pointer">Fitur</a>
        <a @click="navigate('#cara-kerja')" :class="{ active: activeSection === 'cara-kerja' }" class="cursor-pointer">Cara Kerja</a>
        <a @click="navigate('#harga')" :class="{ active: activeSection === 'harga' }" class="cursor-pointer">Harga</a>
      </nav>
      <div class="nav-actions">
        <router-link v-if="authStore.isAuthenticated" to="/dashboard" class="btn-outline">Dashboard</router-link>
        <template v-else>
          <router-link to="/login" class="btn-outline">Masuk</router-link>
          <router-link to="/register" class="btn-primary">Daftar Gratis →</router-link>
        </template>
      </div>
      <!-- Mobile burger -->
      <button class="burger" :class="{ 'burger-active': mobileOpen }" @click="mobileOpen = !mobileOpen" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
    <!-- Mobile menu -->
    <div class="mobile-menu" :class="{ open: mobileOpen }">
      <a @click="navigate('#hero'); mobileOpen = false" :class="{ active: activeSection === 'hero' }" class="cursor-pointer">Beranda</a>
      <a @click="navigate('#fitur'); mobileOpen = false" :class="{ active: activeSection === 'fitur' }" class="cursor-pointer">Fitur</a>
      <a @click="navigate('#cara-kerja'); mobileOpen = false" :class="{ active: activeSection === 'cara-kerja' }" class="cursor-pointer">Cara Kerja</a>
      <a @click="navigate('#harga'); mobileOpen = false" :class="{ active: activeSection === 'harga' }" class="cursor-pointer">Harga</a>
      <div class="mobile-menu-divider"></div>
      <router-link v-if="authStore.isAuthenticated" to="/dashboard" @click="mobileOpen=false">Dashboard</router-link>
      <template v-else>
        <router-link to="/login" @click="mobileOpen=false">Masuk</router-link>
        <router-link to="/register" class="mobile-cta" @click="mobileOpen=false">Daftar Gratis</router-link>
      </template>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

defineProps<{
  activeSection?: string;
}>();

const authStore = useAuthStore();
const mobileOpen = ref(false);
const isScrolled = ref(false);

const router = useRouter();
const route = useRoute();

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

const navigate = (hash: string) => {
  if (route.name === 'landing') {
    const el = document.querySelector(hash);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  } else {
    router.push('/' + hash);
  }
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  handleScroll();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(3, 7, 17, 0.85);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(255,255,255,0.06);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.navbar-scrolled {
  background: rgba(3, 7, 17, 0.95);
  box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.7);
  border-bottom: 1px solid rgba(99, 102, 241, 0.15);
}
.navbar-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
  gap: 32px;
  transition: height 0.3s ease;
}
.navbar-scrolled .navbar-inner {
  height: 56px;
}
.brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.brand-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex; align-items: center; justify-content: center;
  font-weight: 900; font-size: 18px; color: white;
  box-shadow: 0 4px 15px rgba(99,102,241,0.4);
}
.brand-name { font-weight: 800; font-size: 18px; color: #f1f5f9; letter-spacing: -0.5px; }
.nav-links {
  display: flex;
  gap: 28px;
  margin-left: auto;
  height: 100%;
  align-items: center;
}
.nav-links a {
  font-size: 14px;
  font-weight: 500;
  color: #94a3b8;
  text-decoration: none;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  height: 100%;
  position: relative;
}
.nav-links a:hover { color: #e2e8f0; }
.nav-links a.active {
  color: #a5b4fc;
  font-weight: 600;
}
.nav-links a.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #6366f1, #8b5cf6);
  box-shadow: 0 0 8px rgba(99, 102, 241, 0.6);
}
.nav-actions { display: flex; align-items: center; gap: 10px; }
.btn-outline {
  padding: 8px 18px; border-radius: 10px; font-size: 14px; font-weight: 600;
  border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1;
  text-decoration: none; transition: all 0.2s; background: transparent;
}
.btn-outline:hover { background: rgba(255,255,255,0.06); color: #f1f5f9; }
.btn-primary {
  padding: 8px 18px; border-radius: 10px; font-size: 14px; font-weight: 700;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white; text-decoration: none; transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(99,102,241,0.35);
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.45); }
.burger {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  margin-left: auto;
  position: relative;
  z-index: 101;
}
.burger span {
  display: block;
  width: 22px;
  height: 2px;
  background: #94a3b8;
  border-radius: 2px;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.burger-active span:nth-child(1) {
  transform: translateY(7px) rotate(45deg);
  background: #f1f5f9;
}
.burger-active span:nth-child(2) {
  opacity: 0;
}
.burger-active span:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg);
  background: #f1f5f9;
}
.mobile-menu {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 24px;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  transform: translateY(-10px);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  border-top: 1px solid transparent;
  background: rgba(3, 7, 17, 0.95);
  backdrop-filter: blur(20px);
}
.mobile-menu.open {
  padding: 16px 24px 24px;
  max-height: 400px;
  opacity: 1;
  transform: translateY(0);
  border-top: 1px solid rgba(255,255,255,0.06);
}
.mobile-menu-divider {
  height: 1px;
  background: rgba(255,255,255,0.06);
  margin: 8px 12px;
}
.mobile-menu a {
  padding: 10px 12px; font-size: 15px; font-weight: 500;
  color: #94a3b8; text-decoration: none; border-radius: 8px; transition: all 0.2s;
}
.mobile-menu a:hover { background: rgba(255,255,255,0.05); color: #e2e8f0; }
.mobile-menu a.active {
  color: #818cf8;
  background: rgba(99, 102, 241, 0.08);
  font-weight: 600;
}
.cursor-pointer {
  cursor: pointer;
}
@media (max-width: 768px) {
  .nav-links, .nav-actions { display: none; }
  .burger { display: flex; }
}
</style>
