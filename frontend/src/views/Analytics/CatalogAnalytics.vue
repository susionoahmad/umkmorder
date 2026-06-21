<template>
  <div class="space-y-6">
    <!-- Page Header & Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h3 class="text-xl font-bold text-slate-200">Analisis Katalog</h3>
        <p class="text-sm text-slate-500 mt-0.5">Performa katalog online toko Anda</p>
      </div>

      <!-- Date Range Filter — hanya tampil jika Pro -->
      <div v-if="authStore.isPro" class="flex flex-wrap items-center gap-2">
        <button v-for="r in ranges" :key="r.value" @click="setRange(r.value)"
          :class="[
            'py-2 px-4 rounded-xl text-xs font-bold transition border',
            selectedRange === r.value
              ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-600/20'
              : 'bg-slate-900 border-slate-700 text-slate-400 hover:bg-slate-800 hover:text-slate-200'
          ]">
          {{ r.label }}
        </button>

        <!-- Custom Range -->
        <div v-if="selectedRange === 'custom'" class="flex items-center gap-2">
          <input type="date" v-model="customFrom"
            class="bg-slate-900 border border-slate-700 rounded-xl py-2 px-3 text-xs text-slate-300 focus:outline-none focus:border-indigo-500" />
          <span class="text-slate-600 text-xs">s/d</span>
          <input type="date" v-model="customTo"
            class="bg-slate-900 border border-slate-700 rounded-xl py-2 px-3 text-xs text-slate-300 focus:outline-none focus:border-indigo-500" />
          <button @click="fetchAnalytics"
            class="py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition">
            Terapkan
          </button>
        </div>
      </div>
    </div>

    <!-- ===== PRO UPGRADE GATE ===== -->
    <div v-if="!authStore.isPro" class="relative overflow-hidden rounded-3xl border border-indigo-500/30 bg-slate-900/60 backdrop-blur-md shadow-2xl">
      <!-- Background orbs -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-purple-600/20 rounded-full blur-3xl"></div>
      </div>

      <div class="relative z-10 p-10 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 mb-6">
          <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse inline-block"></span>
          <span class="text-xs font-bold text-indigo-300 uppercase tracking-widest">Fitur Eksklusif Paket Pro</span>
        </div>

        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-4xl shadow-xl shadow-indigo-500/30">
          📊
        </div>

        <h3 class="text-2xl font-black text-slate-100 mb-3">Statistik &amp; Analitik Katalog</h3>
        <p class="text-slate-400 text-sm max-w-md mx-auto mb-8 leading-relaxed">
          Pantau performa katalog dengan grafik pengunjung harian, tren pesanan, tingkat konversi, dan analisis abandoned order dengan filter rentang waktu fleksibel.
        </p>

        <!-- Feature cards preview (blurred) -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 max-w-2xl mx-auto mb-8 opacity-40 blur-[1px] pointer-events-none select-none" aria-hidden="true">
          <div v-for="card in previewCards" :key="card"
            class="bg-slate-800/70 border border-slate-700/50 rounded-2xl p-4 text-center">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ card }}</p>
            <p class="text-2xl font-black text-slate-400">—</p>
          </div>
        </div>

        <!-- CTA -->
        <button type="button" @click="authStore.showUpgradeModal = true"
          class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl font-extrabold text-white text-base transition-all duration-200 shadow-xl shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5"
          style="background: linear-gradient(135deg, #6366f1, #8b5cf6)"
        >
          <span>⚡</span>
          Upgrade ke Paket Pro — Rp 49.000/bulan
          <span>→</span>
        </button>
        <p class="mt-3 text-xs text-slate-600">Langsung aktif setelah upgrade · Tanpa kontrak panjang</p>
      </div>
    </div>

    <!-- ===== KONTEN PRO ===== -->
    <template v-else>
      <!-- Loading -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 space-y-4">
        <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-slate-400">Memuat analitik...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 text-center text-red-400">
        {{ error }}
      </div>

      <div v-else-if="data" class="space-y-6">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
          <div v-for="card in metricCards" :key="card.title"
            class="bg-slate-900/60 border border-slate-800 rounded-2xl p-5 space-y-2 shadow-md">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ card.title }}</p>
            <p class="text-3xl font-black text-slate-100">{{ card.value }}</p>
            <p v-if="card.sub" :class="['text-xs font-semibold', card.subClass]">{{ card.sub }}</p>
          </div>
        </div>

        <!-- Charts -->
        <div class="grid lg:grid-cols-2 gap-6">

          <!-- Visitor Trend -->
          <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 shadow-md">
            <h4 class="text-sm font-bold text-slate-300 mb-4">Tren Pengunjung Harian</h4>
            <div class="h-44">
              <svg v-if="data.trend?.length" viewBox="0 0 560 160" class="w-full h-full" preserveAspectRatio="none">
                <line v-for="i in 4" :key="i" :x1="0" :y1="(i * 160) / 4" :x2="560" :y2="(i * 160) / 4"
                  stroke="#1e293b" stroke-width="1" />
                <path :d="buildAreaPath(data.trend, 'visitors', chartMax(data.trend, ['visitors']), 560, 160)" fill="url(#vg2)" opacity="0.4" />
                <path :d="buildLinePath(data.trend, 'visitors', chartMax(data.trend, ['visitors']), 560, 160)"
                  fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linejoin="round" />
                <circle v-for="(pt, i) in data.trend" :key="i"
                  :cx="chartX(i, data.trend.length, 560)"
                  :cy="chartY(pt.visitors, chartMax(data.trend, ['visitors']), 160)"
                  r="3.5" fill="#6366f1" />
                <defs>
                  <linearGradient id="vg2" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#6366f1" />
                    <stop offset="100%" stop-color="#6366f1" stop-opacity="0" />
                  </linearGradient>
                </defs>
              </svg>
              <div v-else class="h-full flex items-center justify-center text-slate-600 text-sm">Belum ada data</div>
            </div>
            <div v-if="data.trend?.length" class="flex justify-between mt-2 px-1">
              <span v-for="pt in data.trend" :key="pt.date" class="text-[10px] text-slate-600">
                {{ formatShortDate(pt.date) }}
              </span>
            </div>
          </div>

          <!-- Order Trend (Draft / Confirmed / Abandoned) -->
          <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 shadow-md">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-sm font-bold text-slate-300">Tren Pesanan Harian</h4>
              <div class="flex flex-wrap gap-3 text-[10px] text-slate-500">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>Confirmed</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>Draft</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>Abandoned</span>
              </div>
            </div>
            <div class="h-44">
              <svg v-if="data.trend?.length" viewBox="0 0 560 160" class="w-full h-full" preserveAspectRatio="none">
                <line v-for="i in 4" :key="i" :x1="0" :y1="(i * 160) / 4" :x2="560" :y2="(i * 160) / 4"
                  stroke="#1e293b" stroke-width="1" />
                <path :d="buildLinePath(data.trend, 'confirmed', chartMax(data.trend, ['confirmed', 'orders', 'abandoned']), 560, 160)"
                  fill="none" stroke="#34d399" stroke-width="2" stroke-linejoin="round" />
                <path :d="buildLinePath(data.trend, 'orders', chartMax(data.trend, ['confirmed', 'orders', 'abandoned']), 560, 160)"
                  fill="none" stroke="#fbbf24" stroke-width="2" stroke-linejoin="round" stroke-dasharray="4 2" />
                <path :d="buildLinePath(data.trend, 'abandoned', chartMax(data.trend, ['confirmed', 'orders', 'abandoned']), 560, 160)"
                  fill="none" stroke="#f87171" stroke-width="2" stroke-linejoin="round" />
              </svg>
              <div v-else class="h-full flex items-center justify-center text-slate-600 text-sm">Belum ada data</div>
            </div>
            <div v-if="data.trend?.length" class="flex justify-between mt-2 px-1">
              <span v-for="pt in data.trend" :key="pt.date" class="text-[10px] text-slate-600">
                {{ formatShortDate(pt.date) }}
              </span>
            </div>
          </div>

          <!-- Conversion Trend -->
          <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 shadow-md lg:col-span-2">
            <h4 class="text-sm font-bold text-slate-300 mb-4">Tren Tingkat Konversi (%)</h4>
            <div class="h-44">
              <svg v-if="data.trend?.length" viewBox="0 0 560 160" class="w-full h-full" preserveAspectRatio="none">
                <line v-for="i in 4" :key="i" :x1="0" :y1="(i * 160) / 4" :x2="560" :y2="(i * 160) / 4"
                  stroke="#1e293b" stroke-width="1" />
                <path :d="buildAreaPath(data.trend, 'conversion', chartMax(data.trend, ['conversion']), 560, 160)" fill="url(#cg2)" opacity="0.3" />
                <path :d="buildLinePath(data.trend, 'conversion', chartMax(data.trend, ['conversion']), 560, 160)"
                  fill="none" stroke="#a78bfa" stroke-width="2.5" stroke-linejoin="round" />
                <circle v-for="(pt, i) in data.trend" :key="i"
                  :cx="chartX(i, data.trend.length, 560)"
                  :cy="chartY(pt.conversion, chartMax(data.trend, ['conversion']), 160)"
                  r="3.5" fill="#a78bfa" />
                <defs>
                  <linearGradient id="cg2" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#a78bfa" />
                    <stop offset="100%" stop-color="#a78bfa" stop-opacity="0" />
                  </linearGradient>
                </defs>
              </svg>
              <div v-else class="h-full flex items-center justify-center text-slate-600 text-sm">Belum ada data</div>
            </div>
            <div v-if="data.trend?.length" class="flex justify-between mt-2 px-1">
              <span v-for="pt in data.trend" :key="pt.date" class="text-[10px] text-slate-600">
                {{ formatShortDate(pt.date) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const isLoading     = ref(false);
const error         = ref<string | null>(null);
const data          = ref<any>(null);
const selectedRange = ref('7days');
const customFrom    = ref('');
const customTo      = ref('');

const ranges = [
  { value: 'today',  label: 'Hari Ini' },
  { value: '7days',  label: '7 Hari' },
  { value: '30days', label: '30 Hari' },
  { value: 'custom', label: 'Kustom' },
];

const previewCards = ['Pengunjung', 'Draft', 'Dikonfirmasi', 'Terbengkalai', 'Konversi'];

const metricCards = computed(() => {
  if (!data.value) return [];
  const d = data.value;
  return [
    { title: 'Pengunjung',   value: d.total_visitors,   sub: '', subClass: '' },
    { title: 'Draft',        value: d.draft_orders,     sub: '', subClass: '' },
    { title: 'Dikonfirmasi', value: d.confirmed_orders, sub: '', subClass: '' },
    {
      title: 'Terbengkalai', value: d.abandoned_orders,
      sub: d.abandoned_orders > 0 ? 'Perlu perhatian' : 'Bagus!',
      subClass: d.abandoned_orders > 0 ? 'text-red-400' : 'text-emerald-400',
    },
    {
      title: 'Konversi', value: `${d.conversion_rate}%`,
      sub: d.conversion_rate >= 10 ? '🔥 Sangat baik' : d.conversion_rate >= 5 ? '👍 Baik' : 'Perlu ditingkatkan',
      subClass: d.conversion_rate >= 10 ? 'text-emerald-400' : d.conversion_rate >= 5 ? 'text-amber-400' : 'text-red-400',
    },
  ];
});

async function fetchAnalytics() {
  if (!authStore.isPro) return;
  isLoading.value = true;
  error.value = null;
  try {
    const params: Record<string, string> = { range: selectedRange.value };
    if (selectedRange.value === 'custom') {
      params.date_from = customFrom.value;
      params.date_to   = customTo.value;
    }
    const response = await api.get('/dashboard/catalog-analytics', { params });
    if (response.data.status === 'success') {
      data.value = response.data.data;
    } else {
      error.value = response.data.message || 'Gagal memuat analitik';
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal terhubung ke server';
  } finally {
    isLoading.value = false;
  }
}

function setRange(range: string) {
  selectedRange.value = range;
  if (range !== 'custom') fetchAnalytics();
}

onMounted(() => {
  if (authStore.isPro) fetchAnalytics();
});

watch(() => authStore.isPro, (isPro) => {
  if (isPro) fetchAnalytics();
});

// --- Chart helpers ---
function chartMax(d: any[], keys: string[]): number {
  const vals = d.flatMap(p => keys.map(k => Number(p[k] ?? 0)));
  return Math.max(...vals, 1);
}

function chartX(i: number | string, total: number, w: number): number {
  const idx = typeof i === 'string' ? parseInt(i, 10) : i;
  return total === 1 ? w / 2 : (idx / (total - 1)) * w;
}

function chartY(val: number | string, max: number, h: number): number {
  const num = typeof val === 'string' ? parseFloat(val) : val;
  return h - (num / max) * h * 0.9 - h * 0.05;
}

function buildLinePath(d: any[], key: string, max: number, w: number, h: number): string {
  return d.map((pt, i) =>
    `${i === 0 ? 'M' : 'L'} ${chartX(i, d.length, w)} ${chartY(Number(pt[key] ?? 0), max, h)}`
  ).join(' ');
}

function buildAreaPath(d: any[], key: string, max: number, w: number, h: number): string {
  const line  = buildLinePath(d, key, max, w, h);
  const lastX = chartX(d.length - 1, d.length, w);
  return `${line} L ${lastX} ${h} L ${chartX(0, d.length, w)} ${h} Z`;
}

function formatShortDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
</script>
