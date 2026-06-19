<template>
  <div class="space-y-8 animate-fadeIn">
    <!-- Header Summary -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-100">Ringkasan Platform</h1>
        <p class="text-slate-400 text-sm mt-1">Pantau perkembangan bisnis SaaS dan kesehatan sistem Anda.</p>
      </div>
      <button 
        @click="fetchStats" 
        class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-750 border border-slate-700 text-sm font-bold transition flex items-center gap-1.5"
      >
        <span>🔄</span> Refresh
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-32 space-y-4">
      <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-slate-400 text-sm">Memuat data statistik...</p>
    </div>

    <!-- Main Stats Grid -->
    <div v-else class="space-y-8">
      <!-- KPI Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div 
          v-for="kpi in kpiCards" 
          :key="kpi.label"
          class="bg-slate-900/60 border border-slate-800 rounded-3xl p-5 md:p-6 flex flex-col justify-between hover:border-slate-700 hover:shadow-xl transition-all duration-200"
        >
          <div>
            <span class="text-2xl">{{ kpi.icon }}</span>
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mt-3">{{ kpi.label }}</h4>
          </div>
          <p class="text-2xl md:text-3xl font-black text-slate-100 mt-4">{{ kpi.value }}</p>
        </div>
      </div>

      <!-- Charts & Activities Row -->
      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Growth & Distribution Charts -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Tenant Growth -->
          <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-6 shadow-md">
            <h3 class="font-bold text-slate-200 mb-4 flex items-center gap-2">
              <span>📈</span> Pertumbuhan Tenant (30 Hari Terakhir)
            </h3>
            <div class="h-48 flex items-end gap-1.5 pt-6 pb-2 border-b border-slate-800">
              <div 
                v-for="(day, i) in chartsData.tenantGrowth" 
                :key="i"
                class="flex-1 bg-gradient-to-t from-indigo-600 to-purple-500 rounded-t-sm transition-all duration-300 relative group"
                :style="{ height: `${day.percentage}%` }"
              >
                <!-- Tooltip -->
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-slate-950 text-white text-[10px] font-bold py-1 px-2 rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-20 pointer-events-none">
                  {{ formatDate(day.date) }} : {{ day.count }}
                </div>
              </div>
            </div>
            <div class="flex justify-between text-[10px] text-slate-500 font-bold mt-2">
              <span>30 hari lalu</span>
              <span>Hari ini</span>
            </div>
          </div>

          <!-- Free vs Pro vs Business Distribution -->
          <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-6 shadow-md">
            <h3 class="font-bold text-slate-200 mb-4 flex items-center gap-2">
              <span>📊</span> Distribusi Paket Langganan
            </h3>
            <div class="grid sm:grid-cols-3 gap-4">
              <div 
                v-for="plan in chartsData.planDistribution" 
                :key="plan.name"
                class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4 flex flex-col items-center justify-center text-center"
              >
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ plan.name }}</span>
                <span class="text-2xl font-black text-slate-100 mt-2">{{ plan.value }}</span>
                <span class="text-[10px] text-indigo-400 font-bold mt-1">Tenant</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activities Feed -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-3xl p-6 shadow-md flex flex-col h-[400px] lg:h-auto">
          <h3 class="font-bold text-slate-200 mb-4 flex items-center gap-2">
            <span>⚡</span> Aktivitas Terbaru
          </h3>
          <div class="flex-1 overflow-y-auto space-y-4 pr-1 scroll-thin">
            <div v-if="activities.length === 0" class="text-center py-16 text-xs text-slate-600">
              Tidak ada aktivitas terbaru.
            </div>
            <div 
              v-else 
              v-for="(act, idx) in activities" 
              :key="idx" 
              class="flex gap-3 text-sm animate-fadeIn"
            >
              <div class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center text-base shrink-0">
                {{ act.icon }}
              </div>
              <div class="space-y-0.5">
                <p class="font-bold text-slate-200">{{ act.title }}</p>
                <p class="text-xs text-slate-400 leading-normal">{{ act.description }}</p>
                <p class="text-[10px] text-slate-500 font-semibold">{{ formatTimeAgo(act.time) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';

const isLoading = ref(true);
const stats = ref({} as any);
const activities = ref([] as any[]);

const kpiCards = computed(() => {
  const kpis = stats.value.kpis || {};
  return [
    { label: 'Total Tenants', icon: '🏢', value: formatNum(kpis.total_tenants) },
    { label: 'Active / Trial', icon: '⚡', value: `${formatNum(kpis.active_tenants)} / ${formatNum(kpis.trial_tenants)}` },
    { label: 'Expired / Suspended', icon: '⚠️', value: formatNum(kpis.expired_tenants) },
    { label: 'Pro Subscribers', icon: '⭐', value: formatNum(kpis.pro_subscribers) },
    { label: 'Monthly Revenue (MRR)', icon: '💰', value: `Rp ${formatCurrency(kpis.mrr)}` },
    { label: 'Total Orders Month', icon: '📦', value: formatNum(kpis.total_orders) },
    { label: 'Platform Revenue', icon: '🏛️', value: `Rp ${formatCurrency(kpis.total_revenue)}` },
    { label: 'Trial Ratio', icon: '📈', value: calculateTrialRatio(kpis.active_tenants, kpis.total_tenants) },
  ];
});

const chartsData = computed(() => {
  const charts = stats.value.charts || {};
  const tenantGrowth = charts.tenantGrowth || [];
  
  // Calculate relative heights for bar charts
  const maxVal = Math.max(...tenantGrowth.map((day: any) => day.count), 1);
  const formattedGrowth = tenantGrowth.map((day: any) => ({
    ...day,
    percentage: (day.count / maxVal) * 100
  }));

  return {
    tenantGrowth: formattedGrowth,
    planDistribution: charts.planDistribution || [],
  };
});

async function fetchStats() {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/dashboard/stats');
    if (res.data.status === 'success') {
      stats.value = res.data.data;
    }
    const actRes = await api.get('/admin/dashboard/activities');
    if (actRes.data.status === 'success') {
      activities.value = actRes.data.data;
    }
  } catch (err: any) {
    alert('Gagal memuat statistik platform.');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchStats();
});

// Helpers
function formatNum(val: any) {
  const num = parseInt(val);
  return isNaN(num) ? '0' : num.toLocaleString('id-ID');
}

function formatCurrency(val: any) {
  const num = parseFloat(val);
  return isNaN(num) ? '0' : num.toLocaleString('id-ID');
}

function calculateTrialRatio(active: number, total: number): string {
  if (!total) return '0%';
  return Math.round((active / total) * 100) + '%';
}

function formatDate(dateStr: string) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

function formatTimeAgo(timeStr: string) {
  const diff = Date.now() - new Date(timeStr).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'Baru saja';
  if (mins < 60) return `${mins} menit lalu`;
  const hours = Math.floor(mins / 60);
  if (hours < 24) return `${hours} jam lalu`;
  return new Date(timeStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<style scoped>
.scroll-thin {
  scrollbar-width: thin;
  scrollbar-color: #475569 transparent;
}
.scroll-thin::-webkit-scrollbar {
  width: 6px;
}
.scroll-thin::-webkit-scrollbar-thumb {
  background-color: #475569;
  border-radius: 99px;
}
</style>
