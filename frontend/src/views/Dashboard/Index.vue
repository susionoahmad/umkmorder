<template>
  <div class="space-y-8">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-slate-400">Memuat data ringkasan...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 text-center text-red-400">
      {{ error }}
    </div>

    <div v-else class="space-y-8">
      <!-- Welcome Banner -->
      <div class="dashboard-welcome-banner bg-gradient-to-r from-slate-900 via-slate-900 to-slate-900 border border-slate-800 rounded-3xl p-6 md:p-8 relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 via-purple-500/5 to-transparent"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="dashboard-welcome-title text-2xl font-black">Halo, {{ authStore.user?.name }}! 👋</h2>
            <p class="dashboard-welcome-subtitle mt-1 text-sm">Panel kontrol bisnis <span class="dashboard-welcome-tenant font-semibold">{{ authStore.tenant?.name }}</span></p>
          </div>
          <router-link to="/dashboard/analytics"
            class="shrink-0 py-2.5 px-5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/25 text-indigo-400 font-semibold text-sm transition">
            📈 Lihat Analitik Lengkap →
          </router-link>
        </div>
      </div>

      <!-- Subscription Usage -->
      <div v-if="subscriptionUsage" class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 shadow-md backdrop-blur-md">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
          <div class="space-y-1">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Paket Saat Ini</p>
            <h3 class="text-2xl font-black text-slate-100">{{ subscriptionUsage.plan_label }}</h3>
          </div>

          <div class="grid sm:grid-cols-2 gap-4 flex-1 md:max-w-2xl">
            <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-4">
              <div class="flex items-center justify-between text-sm font-bold">
                <span class="text-slate-300">Produk Aktif</span>
                <span class="text-slate-100">{{ formatLimit(subscriptionUsage.active_products.used, subscriptionUsage.active_products.limit) }}</span>
              </div>
              <div class="mt-3 h-2 rounded-full bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full bg-indigo-500 transition-all"
                  :style="{ width: progressWidth(subscriptionUsage.active_products.percentage) }"
                ></div>
              </div>
            </div>

            <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-4">
              <div class="flex items-center justify-between text-sm font-bold">
                <span class="text-slate-300">Order Bulan Ini</span>
                <span class="text-slate-100">{{ formatLimit(subscriptionUsage.monthly_orders.used, subscriptionUsage.monthly_orders.limit) }}</span>
              </div>
              <div class="mt-3 h-2 rounded-full bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full bg-emerald-500 transition-all"
                  :style="{ width: progressWidth(subscriptionUsage.monthly_orders.percentage) }"
                ></div>
              </div>
            </div>
          </div>

          <button
            v-if="subscriptionUsage.plan === 'free'"
            type="button"
            @click="authStore.showUpgradeModal = true"
            class="shrink-0 py-2.5 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm transition text-center"
          >
            Upgrade ke Pro
          </button>
        </div>

        <div v-if="subscriptionUsage.warning_message" class="mt-4 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm font-semibold text-amber-300">
          ⚠ {{ subscriptionUsage.warning_message }}
        </div>
      </div>

      <!-- Metrics Summary Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="card in cards" :key="card.title"
          class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 flex flex-col gap-3 shadow-md backdrop-blur-md">
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ card.title }}</p>
            <div :class="['w-9 h-9 rounded-xl flex items-center justify-center text-lg shadow-sm', card.colorClass]">
              {{ card.icon }}
            </div>
          </div>
          <h3 class="text-3xl font-black text-slate-100">{{ card.value }}</h3>
          <p v-if="card.sub" :class="['text-xs font-semibold', card.subClass || 'text-slate-500']">{{ card.sub }}</p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Visitors vs Orders Chart -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 shadow-md backdrop-blur-md">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-bold text-slate-300">Pengunjung vs Pesanan (7 Hari)</h3>
            <div class="flex items-center gap-4 text-xs text-slate-500">
              <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500 inline-block"></span>Pengunjung</span>
              <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>Pesanan</span>
            </div>
          </div>
          <div class="h-44">
            <svg v-if="metrics?.visitors_vs_orders?.length" viewBox="0 0 560 160" class="w-full h-full" preserveAspectRatio="none">
              <!-- Grid lines -->
              <line v-for="i in 4" :key="i" :x1="0" :y1="(i * 160) / 4" :x2="560" :y2="(i * 160) / 4"
                stroke="#1e293b" stroke-width="1" />
              <!-- Visitors area -->
              <path :d="buildAreaPath(metrics.visitors_vs_orders, 'visitors', chartMax(metrics.visitors_vs_orders, ['visitors', 'orders']), 560, 160)"
                fill="url(#visitorsGrad)" opacity="0.35" />
              <path :d="buildLinePath(metrics.visitors_vs_orders, 'visitors', chartMax(metrics.visitors_vs_orders, ['visitors', 'orders']), 560, 160)"
                fill="none" stroke="#6366f1" stroke-width="2" stroke-linejoin="round" />
              <!-- Orders area -->
              <path :d="buildAreaPath(metrics.visitors_vs_orders, 'orders', chartMax(metrics.visitors_vs_orders, ['visitors', 'orders']), 560, 160)"
                fill="url(#ordersGrad)" opacity="0.35" />
              <path :d="buildLinePath(metrics.visitors_vs_orders, 'orders', chartMax(metrics.visitors_vs_orders, ['visitors', 'orders']), 560, 160)"
                fill="none" stroke="#10b981" stroke-width="2" stroke-linejoin="round" />
              <!-- Dots -->
              <circle v-for="(pt, i) in metrics.visitors_vs_orders" :key="'v'+i"
                :cx="chartX(i, metrics.visitors_vs_orders.length, 560)"
                :cy="chartY(pt.visitors, chartMax(metrics.visitors_vs_orders, ['visitors','orders']), 160)"
                r="3" fill="#6366f1" />
              <circle v-for="(pt, i) in metrics.visitors_vs_orders" :key="'o'+i"
                :cx="chartX(i, metrics.visitors_vs_orders.length, 560)"
                :cy="chartY(pt.orders, chartMax(metrics.visitors_vs_orders, ['visitors','orders']), 160)"
                r="3" fill="#10b981" />
              <defs>
                <linearGradient id="visitorsGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#6366f1" />
                  <stop offset="100%" stop-color="#6366f1" stop-opacity="0" />
                </linearGradient>
                <linearGradient id="ordersGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#10b981" />
                  <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                </linearGradient>
              </defs>
            </svg>
            <div v-else class="h-full flex items-center justify-center text-slate-600 text-sm">Belum ada data</div>
          </div>
          <!-- Date labels -->
          <div v-if="metrics?.visitors_vs_orders?.length" class="flex justify-between mt-2 px-1">
            <span v-for="pt in metrics.visitors_vs_orders" :key="pt.date" class="text-[10px] text-slate-600">
              {{ formatShortDate(pt.date) }}
            </span>
          </div>
        </div>

        <!-- Receivables Trend Chart -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 shadow-md backdrop-blur-md">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-bold text-slate-300">Tren Penagihan Piutang (7 Hari)</h3>
            <div class="flex items-center gap-4 text-xs text-slate-500">
              <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>Piutang Baru</span>
              <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>Tertagih</span>
            </div>
          </div>
          <div class="h-44">
            <svg v-if="metrics?.receivables_trend?.length" viewBox="0 0 560 160" class="w-full h-full" preserveAspectRatio="none">
              <line v-for="i in 4" :key="i" :x1="0" :y1="(i * 160) / 4" :x2="560" :y2="(i * 160) / 4"
                stroke="#1e293b" stroke-width="1" />
              <path :d="buildAreaPath(metrics.receivables_trend, 'new', chartMax(metrics.receivables_trend, ['new', 'collected']), 560, 160)"
                fill="url(#newGrad)" opacity="0.35" />
              <path :d="buildLinePath(metrics.receivables_trend, 'new', chartMax(metrics.receivables_trend, ['new', 'collected']), 560, 160)"
                fill="none" stroke="#f87171" stroke-width="2" stroke-linejoin="round" />
              <path :d="buildAreaPath(metrics.receivables_trend, 'collected', chartMax(metrics.receivables_trend, ['new', 'collected']), 560, 160)"
                fill="url(#collectedGrad)" opacity="0.35" />
              <path :d="buildLinePath(metrics.receivables_trend, 'collected', chartMax(metrics.receivables_trend, ['new', 'collected']), 560, 160)"
                fill="none" stroke="#fbbf24" stroke-width="2" stroke-linejoin="round" />
              <defs>
                <linearGradient id="newGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#f87171" />
                  <stop offset="100%" stop-color="#f87171" stop-opacity="0" />
                </linearGradient>
                <linearGradient id="collectedGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#fbbf24" />
                  <stop offset="100%" stop-color="#fbbf24" stop-opacity="0" />
                </linearGradient>
              </defs>
            </svg>
            <div v-else class="h-full flex items-center justify-center text-slate-600 text-sm">Belum ada data</div>
          </div>
          <div v-if="metrics?.receivables_trend?.length" class="flex justify-between mt-2 px-1">
            <span v-for="pt in metrics.receivables_trend" :key="pt.date" class="text-[10px] text-slate-600">
              {{ formatShortDate(pt.date) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Recent Orders List -->
      <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 shadow-md backdrop-blur-md">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-bold text-slate-200">5 Pesanan Terakhir</h3>
          <router-link to="/dashboard/orders" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">
            Lihat Semua →
          </router-link>
        </div>

        <div v-if="!metrics?.recent_orders?.length" class="text-center py-8 text-slate-500 text-sm">
          Belum ada pesanan masuk.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase font-bold">
                <th class="pb-3 font-semibold">Invoice</th>
                <th class="pb-3 font-semibold">Pelanggan</th>
                <th class="pb-3 font-semibold">Tanggal</th>
                <th class="pb-3 font-semibold">Total</th>
                <th class="pb-3 font-semibold">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in metrics.recent_orders" :key="order.id"
                class="border-b border-slate-800 hover:bg-slate-800/20 text-slate-300 text-sm transition">
                <td class="py-3 font-bold text-slate-200">{{ order.invoice_number }}</td>
                <td class="py-3">{{ order.customer?.name || '-' }}</td>
                <td class="py-3">{{ formatDate(order.order_date) }}</td>
                <td class="py-3 font-bold text-indigo-400">Rp {{ formatRupiah(order.grand_total) }}</td>
                <td class="py-3">
                  <span :class="['px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider', getStatusClass(order.status)]">
                    {{ order.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const authStore  = useAuthStore();
const isLoading  = ref(true);
const error      = ref<string | null>(null);
const metrics    = ref<any>(null);

const subscriptionUsage = computed(() => metrics.value?.subscription_usage ?? null);

const cards = computed(() => {
  if (!metrics.value) return [];
  const m = metrics.value;
  return [
    {
      title: 'Total Pengunjung',
      value: m.total_visitors.toLocaleString(),
      icon: '👁️',
      colorClass: 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
    },
    {
      title: 'Pesanan Draft',
      value: m.draft_orders,
      icon: '📝',
      colorClass: 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
    },
    {
      title: 'Dikonfirmasi',
      value: m.confirmed_orders,
      icon: '✅',
      colorClass: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
      sub: `Konversi ${m.conversion_rate}%`,
      subClass: m.conversion_rate > 5 ? 'text-emerald-500' : 'text-slate-500',
    },
    {
      title: 'Piutang Jatuh Tempo',
      value: m.overdue_receivables,
      icon: '⚠️',
      colorClass: 'bg-red-500/10 text-red-400 border border-red-500/20',
      sub: m.overdue_receivables > 0 ? 'Perlu ditindaklanjuti' : 'Semua lancar',
      subClass: m.overdue_receivables > 0 ? 'text-red-500' : 'text-emerald-500',
    },
  ];
});

async function fetchMetrics() {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await api.get('/dashboard');
    if (response.data.status === 'success') {
      metrics.value = response.data.data;
    } else {
      error.value = response.data.message || 'Gagal memuat data dashboard';
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data. Silakan coba lagi.';
  } finally {
    isLoading.value = false;
  }
}

onMounted(fetchMetrics);

watch(() => authStore.isPro, () => {
  fetchMetrics();
});

// ---- Chart helpers ----
function chartMax(data: any[], keys: string[]): number {
  const vals = data.flatMap(d => keys.map(k => Number(d[k] ?? 0)));
  return Math.max(...vals, 1);
}

function chartX(i: number | string, total: number, width: number): number {
  const idx = typeof i === 'string' ? parseInt(i, 10) : i;
  if (total === 1) return width / 2;
  return (idx / (total - 1)) * width;
}

function chartY(val: number | string, max: number, height: number): number {
  const num = typeof val === 'string' ? parseFloat(val) : val;
  return height - (num / max) * height * 0.9 - height * 0.05;
}

function buildLinePath(data: any[], key: string, max: number, w: number, h: number): string {
  return data.map((pt, i) => {
    const x = chartX(i, data.length, w);
    const y = chartY(Number(pt[key] ?? 0), max, h);
    return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
  }).join(' ');
}

function buildAreaPath(data: any[], key: string, max: number, w: number, h: number): string {
  const line = buildLinePath(data, key, max, w, h);
  const lastX = chartX(data.length - 1, data.length, w);
  const firstX = chartX(0, data.length, w);
  return `${line} L ${lastX} ${h} L ${firstX} ${h} Z`;
}

// ---- Formatters ----
function formatRupiah(val: any): string {
  const num = parseFloat(val);
  if (isNaN(num)) return '0';
  return num.toLocaleString('id-ID');
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatShortDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

function formatLimit(used: number, limit: number | null): string {
  return limit === null ? `${used} / Tak Terbatas` : `${used} / ${limit}`;
}

function progressWidth(percentage: number | null): string {
  return `${percentage ?? 100}%`;
}

function getStatusClass(status: string): string {
  const map: Record<string, string> = {
    completed:  'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    shipped:    'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
    processing: 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
    cancelled:  'bg-red-500/10 text-red-400 border border-red-500/20',
    new:        'bg-amber-500/10 text-amber-400 border border-amber-500/20',
    expired:    'bg-slate-700 text-slate-400 border border-slate-600',
  };
  return map[status] || 'bg-slate-800 text-slate-400 border border-slate-700';
}
</script>

<style scoped>
.dashboard-welcome-banner .dashboard-welcome-title {
  color: #f8fafc !important;
}

.dashboard-welcome-banner .dashboard-welcome-subtitle {
  color: #cbd5e1 !important;
}

.dashboard-welcome-banner .dashboard-welcome-tenant {
  color: #ffffff !important;
}
</style>
