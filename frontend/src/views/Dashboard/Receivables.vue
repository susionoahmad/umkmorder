<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h2 class="text-2xl font-bold text-slate-100 flex items-center gap-3">
        <span class="p-2 bg-indigo-500/10 text-indigo-400 rounded-xl">💰</span>
        Laporan
      </h2>
      <div class="inline-flex rounded-2xl border border-slate-800 bg-slate-900/70 p-1">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          type="button"
          @click="activeTab = tab.value"
          :class="[
            'px-4 py-2 rounded-xl text-sm font-bold transition',
            activeTab === tab.value
              ? 'bg-indigo-500 text-white shadow-md'
              : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- ===== PRO UPGRADE GATE ===== -->
    <div v-if="!authStore.isPro" class="relative overflow-hidden rounded-3xl border border-indigo-500/30 bg-slate-900/60 backdrop-blur-md shadow-2xl">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-purple-600/20 rounded-full blur-3xl"></div>
      </div>

      <div class="relative z-10 p-10 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 mb-6">
          <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse inline-block"></span>
          <span class="text-xs font-bold text-indigo-300 uppercase tracking-widest">Fitur Eksklusif Paket Pro</span>
        </div>

        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-4xl shadow-xl shadow-indigo-500/30">
          🔒
        </div>

        <h3 class="text-2xl font-black text-slate-100 mb-3">Laporan &amp; Manajemen Piutang</h3>
        <p class="text-slate-400 text-sm max-w-md mx-auto mb-8 leading-relaxed">
          Akses laporan penjualan lengkap, omzet harian, dan manajemen piutang pelanggan. Semua dalam satu dashboard untuk pertumbuhan bisnis yang lebih cepat.
        </p>

        <div class="grid sm:grid-cols-2 gap-3 max-w-lg mx-auto mb-8 text-left">
          <div v-for="f in proFeatures" :key="f" class="flex items-center gap-3 bg-slate-800/50 border border-slate-700/50 rounded-xl px-4 py-3">
            <span class="text-indigo-400 font-bold text-sm shrink-0">✓</span>
            <span class="text-sm text-slate-300 font-medium">{{ f }}</span>
          </div>
        </div>

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
      <!-- TAB: Penjualan -->
      <section v-if="activeTab === 'sales'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 sm:items-end sm:justify-between">
          <div class="flex flex-wrap items-end gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Metode Pembayaran</label>
              <select
                v-model="salesPaymentMethod"
                @change="fetchSalesReport"
                class="bg-slate-950 border border-slate-800 rounded-xl py-2.5 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition text-sm"
              >
                <option value="all">Semua Metode</option>
                <option value="cash">Tunai</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
                <option value="credit_paid">Kredit Lunas</option>
                <option value="receivable_paid">Piutang Lunas</option>
              </select>
            </div>
            <button
              @click="exportReport"
              :disabled="isExporting"
              class="theme-btn-secondary py-2.5 px-4 rounded-xl font-bold text-sm transition flex items-center gap-1.5 h-[42px] disabled:opacity-50"
            >
              <span>📥 {{ isExporting ? 'Ekspor...' : 'Ekspor CSV' }}</span>
            </button>
          </div>
          <SummaryPill label="Total Penjualan" :value="salesSummary.total" />
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
              <tr>
                <th class="p-4 font-medium rounded-tl-xl">Tanggal</th>
                <th class="p-4 font-medium">Invoice</th>
                <th class="p-4 font-medium">Pelanggan</th>
                <th class="p-4 font-medium">Metode</th>
                <th class="p-4 font-medium text-right rounded-tr-xl">Nominal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
              <tr v-if="salesLoading" class="animate-pulse">
                <td colspan="5" class="p-4 text-center text-slate-500">Memuat data...</td>
              </tr>
              <tr v-else-if="salesRows.length === 0">
                <td colspan="5" class="p-8 text-center text-slate-500 bg-slate-950/20 rounded-xl border border-dashed border-slate-800">Belum ada data penjualan untuk filter ini.</td>
              </tr>
              <tr v-else v-for="row in salesRows" :key="row.id" class="hover:bg-slate-800/20 transition">
                <td class="p-4 text-slate-300">{{ formatDate(row.date) }}</td>
                <td class="p-4 font-bold text-slate-200">{{ row.invoice_number || '-' }}</td>
                <td class="p-4 text-slate-300">{{ row.customer_name || '-' }}</td>
                <td class="p-4">
                  <span class="px-2.5 py-1 rounded-lg text-xs font-bold border" :class="getPaymentClass(row.payment_method)">
                    {{ row.payment_method_label }}
                  </span>
                </td>
                <td class="p-4 text-right font-black text-indigo-400">Rp {{ formatCurrency(row.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- TAB: Piutang -->
      <section v-else-if="activeTab === 'receivables'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 sm:items-end sm:justify-between">
          <div class="flex flex-wrap items-end gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Status Piutang</label>
              <select
                v-model="statusFilter"
                @change="fetchReceivables"
                class="bg-slate-950 border border-slate-800 rounded-xl py-2.5 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition text-sm"
              >
                <option value="all">Semua Status</option>
                <option value="unpaid">Belum Dibayar</option>
                <option value="partial">Sebagian</option>
                <option value="overdue">Jatuh Tempo</option>
                <option value="paid">Lunas</option>
              </select>
            </div>
            <button
              @click="exportReport"
              :disabled="isExporting"
              class="theme-btn-secondary py-2.5 px-4 rounded-xl font-bold text-sm transition flex items-center gap-1.5 h-[42px] disabled:opacity-50"
            >
              <span>📥 {{ isExporting ? 'Ekspor...' : 'Ekspor CSV' }}</span>
            </button>
          </div>
          <SummaryPill label="Sisa Piutang" :value="receivablesRemainingTotal" tone="rose" />
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
              <tr>
                <th class="p-4 font-medium rounded-tl-xl">Invoice / Pelanggan</th>
                <th class="p-4 font-medium">Nominal</th>
                <th class="p-4 font-medium">Jatuh Tempo</th>
                <th class="p-4 font-medium">Status</th>
                <th class="p-4 font-medium text-right rounded-tr-xl">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
              <tr v-if="receivablesLoading" class="animate-pulse">
                <td colspan="5" class="p-4 text-center text-slate-500">Memuat data...</td>
              </tr>
              <tr v-else-if="receivables.length === 0">
                <td colspan="5" class="p-8 text-center text-slate-500 bg-slate-950/20 rounded-xl border border-dashed border-slate-800">Tidak ada data piutang ditemukan.</td>
              </tr>
              <tr v-else v-for="item in receivables" :key="item.id" class="hover:bg-slate-800/20 transition">
                <td class="p-4">
                  <div class="font-bold text-slate-200">{{ item.order?.invoice_number || '-' }}</div>
                  <div class="text-xs text-slate-400 mt-1">{{ item.customer?.name }} ({{ item.customer?.whatsapp }})</div>
                </td>
                <td class="p-4">
                  <div class="text-slate-300 text-xs">Total: <span class="font-medium text-slate-200">Rp {{ formatCurrency(item.total_amount) }}</span></div>
                  <div class="text-slate-300 text-xs mt-1">Sisa: <span class="font-bold text-rose-400">Rp {{ formatCurrency(item.remaining_amount) }}</span></div>
                </td>
                <td class="p-4 text-slate-300">{{ formatDate(item.due_date) }}</td>
                <td class="p-4">
                  <span :class="getStatusClass(item.status)" class="px-2.5 py-1 rounded-lg text-xs font-bold whitespace-nowrap">
                    {{ formatStatus(item.status) }}
                  </span>
                </td>
                <td class="p-4 text-right">
                  <button
                    v-if="Number(item.remaining_amount) > 0"
                    @click="sendReminder(item)"
                    class="inline-flex items-center gap-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 hover:border-emerald-500/40 px-3 py-1.5 rounded-lg text-xs font-bold transition"
                  >
                    Tagih via WA
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- TAB: Omzet Harian -->
      <section v-else class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <div class="flex flex-col lg:flex-row gap-4 lg:items-end lg:justify-between">
          <div class="flex flex-wrap items-end gap-3">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-400 mb-2">Dari Tanggal</label>
                <input
                  v-model="turnoverDateFrom"
                  type="date"
                  @change="fetchTurnoverReport"
                  class="bg-slate-950 border border-slate-800 rounded-xl py-2.5 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition text-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-400 mb-2">Sampai Tanggal</label>
                <input
                  v-model="turnoverDateTo"
                  type="date"
                  @change="fetchTurnoverReport"
                  class="bg-slate-950 border border-slate-800 rounded-xl py-2.5 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition text-sm"
                />
              </div>
            </div>
            <button
              @click="exportReport"
              :disabled="isExporting"
              class="theme-btn-secondary py-2.5 px-4 rounded-xl font-bold text-sm transition flex items-center gap-1.5 h-[42px] disabled:opacity-50"
            >
              <span>📥 {{ isExporting ? 'Ekspor...' : 'Ekspor CSV' }}</span>
            </button>
          </div>
          <SummaryPill label="Total Omzet" :value="turnoverSummary.turnover" />
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50">
              <tr>
                <th class="p-4 font-medium rounded-tl-xl">Tanggal</th>
                <th class="p-4 font-medium">Jumlah Pesanan</th>
                <th class="p-4 font-medium text-right rounded-tr-xl">Omzet</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
              <tr v-if="turnoverLoading" class="animate-pulse">
                <td colspan="3" class="p-4 text-center text-slate-500">Memuat data...</td>
              </tr>
              <tr v-else-if="turnoverRows.length === 0">
                <td colspan="3" class="p-8 text-center text-slate-500 bg-slate-950/20 rounded-xl border border-dashed border-slate-800">Belum ada omzet pada rentang tanggal ini.</td>
              </tr>
              <tr v-else v-for="row in turnoverRows" :key="row.date" class="hover:bg-slate-800/20 transition">
                <td class="p-4 text-slate-300">{{ formatDate(row.date) }}</td>
                <td class="p-4 text-slate-300">{{ row.orders_count }}</td>
                <td class="p-4 text-right font-black text-indigo-400">Rp {{ formatCurrency(row.turnover) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, defineComponent, h, onMounted, ref, watch } from 'vue';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

type ReportTab = 'sales' | 'receivables' | 'turnover';

const tabs = [
  { value: 'sales' as ReportTab,       label: 'Penjualan' },
  { value: 'receivables' as ReportTab, label: 'Piutang' },
  { value: 'turnover' as ReportTab,    label: 'Omzet Harian' },
];

const proFeatures = [
  'Laporan Penjualan Lengkap',
  'Manajemen Piutang Pelanggan',
  'Omzet Harian & Tren',
  'Tagih via WhatsApp 1-Klik',
  'Filter & Rekap per Metode',
  'Histori Pembayaran Detail',
];

const activeTab = ref<ReportTab>('sales');

// --- Sales ---
const salesLoading       = ref(false);
const salesPaymentMethod = ref('all');
const salesRows          = ref([] as any[]);
const salesSummary       = ref({ count: 0, total: 0 });

// --- Receivables ---
const receivablesLoading = ref(false);
const receivables        = ref([] as any[]);
const statusFilter       = ref('all');

// --- Turnover ---
const turnoverLoading  = ref(false);
const turnoverRows     = ref([] as any[]);
const turnoverSummary  = ref({ orders_count: 0, turnover: 0 });
const turnoverDateTo   = ref(new Date().toISOString().slice(0, 10));
const turnoverDateFrom = ref(defaultDateFrom());

const receivablesRemainingTotal = computed(() =>
  receivables.value.reduce((sum, item) => sum + Number(item.remaining_amount || 0), 0)
);

watch(activeTab, (tab) => {
  if (!authStore.isPro) return;
  if (tab === 'sales'       && salesRows.value.length === 0)   fetchSalesReport();
  if (tab === 'receivables' && receivables.value.length === 0) fetchReceivables();
  if (tab === 'turnover'    && turnoverRows.value.length === 0) fetchTurnoverReport();
});

watch(() => authStore.isPro, (isPro) => {
  if (isPro) {
    if (activeTab.value === 'sales') fetchSalesReport();
    else if (activeTab.value === 'receivables') fetchReceivables();
    else fetchTurnoverReport();
  }
});

onMounted(() => {
  if (authStore.isPro) fetchSalesReport();
});

async function fetchSalesReport() {
  salesLoading.value = true;
  try {
    const res = await api.get('/reports/sales', { params: { payment_method: salesPaymentMethod.value } });
    if (res.data.status === 'success') {
      salesRows.value    = res.data.data.rows    || [];
      salesSummary.value = res.data.data.summary || { count: 0, total: 0 };
    }
  } finally {
    salesLoading.value = false;
  }
}

async function fetchReceivables() {
  receivablesLoading.value = true;
  try {
    const res = await api.get('/receivables', { params: { status: statusFilter.value } });
    if (res.data.status === 'success') receivables.value = res.data.data;
  } finally {
    receivablesLoading.value = false;
  }
}

async function fetchTurnoverReport() {
  turnoverLoading.value = true;
  try {
    const res = await api.get('/reports/daily-turnover', {
      params: { date_from: turnoverDateFrom.value, date_to: turnoverDateTo.value },
    });
    if (res.data.status === 'success') {
      turnoverRows.value    = res.data.data.rows    || [];
      turnoverSummary.value = res.data.data.summary || { orders_count: 0, turnover: 0 };
    }
  } finally {
    turnoverLoading.value = false;
  }
}

const isExporting = ref(false);

async function exportReport() {
  if (isExporting.value) return;
  isExporting.value = true;
  try {
    let url = `/reports/export?type=${activeTab.value}`;
    if (activeTab.value === 'sales') {
      url += `&payment_method=${salesPaymentMethod.value}`;
    } else if (activeTab.value === 'receivables') {
      url += `&status=${statusFilter.value}`;
    } else if (activeTab.value === 'turnover') {
      url += `&date_from=${turnoverDateFrom.value}&date_to=${turnoverDateTo.value}`;
    }

    const res = await api.get(url, { responseType: 'blob' });
    const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', `laporan_${activeTab.value}_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (err: any) {
    alert('Gagal mengekspor data laporan.');
  } finally {
    isExporting.value = false;
  }
}

function sendReminder(item: any) {
  if (!item.customer?.whatsapp) { alert('Nomor WhatsApp pelanggan tidak tersedia.'); return; }
  let phone = item.customer.whatsapp.replace(/\D/g, '');
  if (phone.startsWith('0')) phone = '62' + phone.slice(1);
  const invoice   = item.order?.invoice_number || 'Pesanan Anda';
  const remaining = formatCurrency(item.remaining_amount);
  const dueDate   = formatDate(item.due_date);
  const message   = `Halo ${item.customer.name},\n\nIni adalah pengingat tagihan untuk *${invoice}*.\n\nSisa tagihan: *Rp ${remaining}*, jatuh tempo *${dueDate}*.\n\nMohon segera melakukan pembayaran. Terima kasih!`;
  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
}

function defaultDateFrom(): string {
  const d = new Date();
  d.setDate(d.getDate() - 6);
  return d.toISOString().slice(0, 10);
}

function formatCurrency(val: any) {
  const num = parseFloat(val);
  return isNaN(num) ? '0' : num.toLocaleString('id-ID');
}

function formatDate(dateStr: string) {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function getPaymentClass(method: string) {
  if (method === 'cash')     return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
  if (method === 'transfer') return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
  if (method === 'qris')     return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
  if (method === 'credit')   return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
  return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
}

function getStatusClass(status: string) {
  if (status === 'paid')    return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
  if (status === 'partial') return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
  if (status === 'overdue') return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
  return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
}

function formatStatus(status: string) {
  const map: Record<string, string> = {
    paid: 'Lunas', partial: 'Sebagian', overdue: 'Jatuh Tempo', unpaid: 'Belum Dibayar',
  };
  return map[status] ?? status;
}

// ---- Inline sub-components ----
const SummaryPill = defineComponent({
  props: {
    label: { type: String,  required: true },
    value: { type: Number,  required: true },
    tone:  { type: String,  default: 'indigo' },
  },
  setup(props) {
    return () => h('div', {
      class: ['rounded-2xl border px-5 py-3 bg-slate-950/70',
        props.tone === 'rose' ? 'border-rose-500/20' : 'border-indigo-500/20'],
    }, [
      h('p', { class: 'text-xs font-bold uppercase tracking-wider text-slate-500' }, props.label),
      h('p', {
        class: props.tone === 'rose'
          ? 'text-xl font-black text-rose-400 mt-1'
          : 'text-xl font-black text-indigo-400 mt-1',
      }, `Rp ${formatCurrency(props.value)}`),
    ]);
  },
});
</script>
