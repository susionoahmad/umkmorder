<template>
  <div class="success-page" :style="themeVars">
    <div class="max-w-lg w-full space-y-5">

      <!-- Success Header -->
      <div class="success-card rounded-3xl p-8 shadow-xl backdrop-blur-md text-center space-y-3">
        <div class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-3xl animate-bounce-slow"
          style="background:rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.35)">
          ✅
        </div>
        <p class="text-sm font-bold uppercase tracking-wider" style="color:#10b981">Pesanan Berhasil Dibuat</p>
        <h1 class="text-2xl font-black" :style="{ color: 'var(--text-primary)' }">Draft pesanan sudah dicatat!</h1>
        <p class="text-sm leading-relaxed text-sub">
          Konfirmasi stok, pembayaran, dan pengiriman akan diselesaikan melalui WhatsApp bersama admin toko.
        </p>
      </div>

      <!-- Invoice Summary -->
      <div v-if="result" class="success-card rounded-3xl p-6 shadow-xl backdrop-blur-md space-y-4">
        <!-- Invoice Number -->
        <div class="flex items-center justify-between pb-4 sep-border border-b">
          <div>
            <p class="text-xs uppercase tracking-wider text-muted">No. Invoice</p>
            <p class="text-lg font-extrabold theme-accent-text mt-0.5">{{ result.invoice_number }}</p>
          </div>
          <div class="text-right">
            <p class="text-xs uppercase tracking-wider text-muted">Pelanggan</p>
            <p class="text-sm font-bold mt-0.5" :style="{ color: 'var(--text-primary)' }">{{ result.customer_name }}</p>
          </div>
        </div>

        <!-- Items List -->
        <div v-if="result.items?.length" class="space-y-2">
          <p class="text-xs font-bold uppercase tracking-wider text-muted">Item Pesanan</p>
          <div v-for="item in result.items" :key="item.name"
            class="item-row flex justify-between text-sm py-2 px-3 rounded-xl">
            <span class="text-sub">{{ item.name }} <span class="text-muted">×{{ item.quantity }}</span></span>
            <span class="font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatRupiah(item.total) }}</span>
          </div>
        </div>

        <!-- Payment & Shipping Summary -->
        <div class="pt-3 sep-border border-t space-y-2">
          <div class="flex justify-between items-center text-sm">
            <span class="text-sub">Preferensi Pembayaran</span>
            <span class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ formatPaymentPreference(result.payment_preference) }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-sub">{{ shippingLabel(result) }}</span>
            <span class="font-bold" :style="{ color: 'var(--text-primary)' }">
              {{ Number(result.shipping_cost || 0) === 0 ? 'Gratis' : 'Rp ' + formatRupiah(result.shipping_cost) }}
            </span>
          </div>
          <div class="flex justify-between items-center pt-3 sep-border border-t">
            <span class="text-sm font-medium text-sub">Grand Total</span>
            <span class="text-xl font-black theme-accent-text">Rp {{ formatRupiah(result.grand_total) }}</span>
          </div>
        </div>
      </div>

      <!-- Auto Redirect Countdown -->
      <div v-if="result?.auto_whatsapp_redirect && countdown > 0"
        class="rounded-2xl p-4 text-center space-y-2"
        style="background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.25)">
        <p class="text-sm" style="color:#fbbf24">
          Mengarahkan ke WhatsApp dalam
          <span class="font-extrabold text-lg mx-1" style="color:#fde68a">{{ countdown }}</span>
          detik...
        </p>
        <div class="w-full rounded-full h-1.5 overflow-hidden" :style="{ backgroundColor: 'var(--bg-surface-alt)' }">
          <div class="h-full rounded-full transition-all duration-1000 ease-linear theme-progress-bar"
            :style="{ width: `${(countdown / 10) * 100}%` }"></div>
        </div>
        <button type="button" @click="cancelRedirect"
          class="text-xs underline transition text-muted hover:text-sub">
          Batalkan redirect otomatis
        </button>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col gap-3">
        <a v-if="result?.whatsapp_redirect_url" :href="result.whatsapp_redirect_url" target="_blank"
          class="w-full py-4 px-6 rounded-2xl font-extrabold text-lg text-center text-white transition duration-300 shadow-lg flex items-center justify-center gap-3"
          style="background:#16a34a; box-shadow: 0 8px 25px rgba(22,163,74,0.35)">
          <span class="text-xl">💬</span>
          Hubungi WhatsApp Admin
        </a>
        <router-link :to="`/${slug}`"
          class="back-btn w-full py-3.5 px-6 rounded-2xl font-bold text-center transition duration-300">
          ← Kembali ke Katalog
        </router-link>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCatalogStore } from '@/stores/catalog';

const route = useRoute();
const slug  = computed(() => route.params.slug as string);
const catalogStore = useCatalogStore();

const THEMES: Record<string, {
  primary: string; secondary: string; primaryRgb: string;
  bgPage: string; bgSurface: string; bgSurfaceAlt: string;
  borderColor: string; textPrimary: string; textSecondary: string; textMuted: string;
}> = {
  default: {
    primary: '#6366f1', secondary: '#8b5cf6', primaryRgb: '99, 102, 241',
    bgPage: '#020617', bgSurface: 'rgba(15,23,42,0.75)', bgSurfaceAlt: '#1e293b',
    borderColor: '#334155', textPrimary: '#f1f5f9', textSecondary: '#94a3b8', textMuted: '#475569',
  },
  red: {
    primary: '#ef4444', secondary: '#b91c1c', primaryRgb: '239, 68, 68',
    bgPage: '#0f0202', bgSurface: 'rgba(26,5,5,0.85)', bgSurfaceAlt: '#1f0808',
    borderColor: '#3d1414', textPrimary: '#fef2f2', textSecondary: '#fca5a5', textMuted: '#7f1d1d',
  },
  emerald: {
    primary: '#10b981', secondary: '#059669', primaryRgb: '16, 185, 129',
    bgPage: '#011208', bgSurface: 'rgba(2,30,14,0.8)', bgSurfaceAlt: '#032d14',
    borderColor: '#064e23', textPrimary: '#ecfdf5', textSecondary: '#6ee7b7', textMuted: '#166534',
  },
  light: {
    primary: '#4f46e5', secondary: '#7c3aed', primaryRgb: '79, 70, 229',
    bgPage: '#f8fafc', bgSurface: 'rgba(255,255,255,0.85)', bgSurfaceAlt: '#f1f5f9',
    borderColor: '#cbd5e1', textPrimary: '#0f172a', textSecondary: '#475569', textMuted: '#94a3b8',
  },
  warm: {
    primary: '#f59e0b', secondary: '#d97706', primaryRgb: '245, 158, 11',
    bgPage: '#0d0900', bgSurface: 'rgba(26,18,0,0.85)', bgSurfaceAlt: '#271e00',
    borderColor: '#3d2f00', textPrimary: '#fffbeb', textSecondary: '#fde68a', textMuted: '#92400e',
  },
  ocean: {
    primary: '#0ea5e9', secondary: '#0284c7', primaryRgb: '14, 165, 233',
    bgPage: '#00060e', bgSurface: 'rgba(0,20,40,0.85)', bgSurfaceAlt: '#001a30',
    borderColor: '#003060', textPrimary: '#e0f2fe', textSecondary: '#7dd3fc', textMuted: '#0c4a6e',
  },
};

const themeVars = computed(() => {
  const key = catalogStore.tenant?.settings?.theme || 'default';
  const t = THEMES[key] ?? THEMES['default'];
  return {
    '--theme-primary':     t.primary,
    '--theme-secondary':   t.secondary,
    '--theme-primary-rgb': t.primaryRgb,
    '--bg-page':           t.bgPage,
    '--bg-surface':        t.bgSurface,
    '--bg-surface-alt':    t.bgSurfaceAlt,
    '--border-color':      t.borderColor,
    '--text-primary':      t.textPrimary,
    '--text-secondary':    t.textSecondary,
    '--text-muted':        t.textMuted,
  };
});

const result = computed(() => {
  const raw = sessionStorage.getItem(`order-success:${slug.value}`);
  if (!raw) return null;
  try { return JSON.parse(raw); } catch { return null; }
});

const countdown         = ref(10);
const redirectTimer     = ref<ReturnType<typeof setInterval> | null>(null);
const redirectCancelled = ref(false);

function cancelRedirect() {
  redirectCancelled.value = true;
  if (redirectTimer.value) clearInterval(redirectTimer.value);
}

onMounted(() => {
  if (!catalogStore.tenant && slug.value) {
    catalogStore.fetchCatalog(slug.value);
  }
  if (result.value?.auto_whatsapp_redirect && result.value?.whatsapp_redirect_url) {
    redirectTimer.value = setInterval(() => {
      if (redirectCancelled.value) { clearInterval(redirectTimer.value!); return; }
      countdown.value--;
      if (countdown.value <= 0) {
        clearInterval(redirectTimer.value!);
        window.open(result.value.whatsapp_redirect_url, '_blank');
      }
    }, 1000);
  }
});

onUnmounted(() => { if (redirectTimer.value) clearInterval(redirectTimer.value); });

function formatRupiah(val: number | string): string {
  const num = typeof val === 'string' ? parseFloat(val) : val;
  if (isNaN(num)) return '0';
  return num.toLocaleString('id-ID');
}

function formatPaymentPreference(preference?: string): string {
  const map: Record<string, string> = {
    cash: 'Tunai',
    transfer: 'Transfer Bank',
    qris: 'QRIS',
    tempo: 'Tempo',
    credit: 'Kredit',
  };
  return map[preference || 'cash'] || preference || '-';
}

function shippingLabel(order: any): string {
  if (order?.shipping_distance_km) {
    return `Ongkir (${order.shipping_distance_km} km)`;
  }
  if (order?.shipping_zone) {
    return `Ongkir (${order.shipping_zone})`;
  }
  return 'Ongkir';
}
</script>

<style scoped>
.success-page {
  min-height: 100vh;
  padding: 3rem 1.5rem;
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--bg-page);
  color: var(--text-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.4s ease;
}

.success-card {
  background-color: var(--bg-surface);
  border: 1px solid var(--border-color);
  transition: background-color 0.4s ease;
}

.item-row {
  background-color: var(--bg-surface-alt);
  border: 1px solid var(--border-color);
}

.sep-border { border-color: var(--border-color); }
.text-sub   { color: var(--text-secondary); }
.text-muted { color: var(--text-muted); }

.theme-accent-text { color: var(--theme-primary); }

.theme-progress-bar {
  background: linear-gradient(90deg, var(--theme-primary), var(--theme-secondary));
}

.back-btn {
  background-color: var(--bg-surface);
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  text-decoration: none;
  display: block;
  text-align: center;
}
.back-btn:hover {
  background-color: var(--bg-surface-alt);
  color: var(--text-primary);
}

@keyframes bounce-slow {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(-6px); }
}
.animate-bounce-slow { animation: bounce-slow 2s ease-in-out infinite; }
</style>
