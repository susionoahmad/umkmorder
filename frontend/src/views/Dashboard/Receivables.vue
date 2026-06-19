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

    <section v-if="activeTab === 'sales'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
      <div class="flex flex-col sm:flex-row gap-4 sm:items-end sm:justify-between">
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
        <SummaryPill label="Total Penjualan" :value="salesSummary.total" />
      </div>

      <ReportTable
        :is-loading="salesLoading"
        :empty="salesRows.length === 0"
        empty-text="Belum ada data penjualan untuk filter ini."
        :colspan="5"
      >
        <template #head>
          <tr>
            <th class="p-4 font-medium rounded-tl-xl">Tanggal</th>
            <th class="p-4 font-medium">Invoice</th>
            <th class="p-4 font-medium">Pelanggan</th>
            <th class="p-4 font-medium">Metode</th>
            <th class="p-4 font-medium text-right rounded-tr-xl">Nominal</th>
          </tr>
        </template>
        <template #body>
          <tr v-for="row in salesRows" :key="row.id" class="hover:bg-slate-850/50 transition">
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
        </template>
      </ReportTable>
    </section>

    <section v-else-if="activeTab === 'receivables'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
      <div class="flex flex-col sm:flex-row gap-4 sm:items-end sm:justify-between">
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
        <SummaryPill label="Sisa Piutang" :value="receivablesRemainingTotal" tone="rose" />
      </div>

      <ReportTable
        :is-loading="receivablesLoading"
        :empty="receivables.length === 0"
        empty-text="Tidak ada data piutang ditemukan."
        :colspan="5"
      >
        <template #head>
          <tr>
            <th class="p-4 font-medium rounded-tl-xl">Invoice / Pelanggan</th>
            <th class="p-4 font-medium">Nominal</th>
            <th class="p-4 font-medium">Jatuh Tempo</th>
            <th class="p-4 font-medium">Status</th>
            <th class="p-4 font-medium text-right rounded-tr-xl">Aksi</th>
          </tr>
        </template>
        <template #body>
          <tr v-for="item in receivables" :key="item.id" class="hover:bg-slate-850/50 transition">
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
        </template>
      </ReportTable>
    </section>

    <section v-else class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
      <div class="flex flex-col lg:flex-row gap-4 lg:items-end lg:justify-between">
        <div class="grid sm:grid-cols-2 gap-4">
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
        <SummaryPill label="Total Omzet" :value="turnoverSummary.turnover" />
      </div>

      <ReportTable
        :is-loading="turnoverLoading"
        :empty="turnoverRows.length === 0"
        empty-text="Belum ada omzet pada rentang tanggal ini."
        :colspan="3"
      >
        <template #head>
          <tr>
            <th class="p-4 font-medium rounded-tl-xl">Tanggal</th>
            <th class="p-4 font-medium">Jumlah Pesanan</th>
            <th class="p-4 font-medium text-right rounded-tr-xl">Omzet</th>
          </tr>
        </template>
        <template #body>
          <tr v-for="row in turnoverRows" :key="row.date" class="hover:bg-slate-850/50 transition">
            <td class="p-4 text-slate-300">{{ formatDate(row.date) }}</td>
            <td class="p-4 text-slate-300">{{ row.orders_count }}</td>
            <td class="p-4 text-right font-black text-indigo-400">Rp {{ formatCurrency(row.turnover) }}</td>
          </tr>
        </template>
      </ReportTable>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, defineComponent, h, onMounted, ref, watch } from 'vue';
import api from '@/services/api';

type ReportTab = 'sales' | 'receivables' | 'turnover';

const tabs = [
  { value: 'sales' as ReportTab, label: 'Penjualan' },
  { value: 'receivables' as ReportTab, label: 'Piutang' },
  { value: 'turnover' as ReportTab, label: 'Omzet Harian' },
];

const activeTab = ref<ReportTab>('sales');

const salesLoading = ref(false);
const salesPaymentMethod = ref('all');
const salesRows = ref([] as any[]);
const salesSummary = ref({ count: 0, total: 0 });

const receivablesLoading = ref(false);
const receivables = ref([] as any[]);
const statusFilter = ref('all');

const turnoverLoading = ref(false);
const turnoverRows = ref([] as any[]);
const turnoverSummary = ref({ orders_count: 0, turnover: 0 });
const turnoverDateTo = ref(new Date().toISOString().slice(0, 10));
const turnoverDateFrom = ref(defaultDateFrom());

const receivablesRemainingTotal = computed(() =>
  receivables.value.reduce((sum, item) => sum + Number(item.remaining_amount || 0), 0)
);

watch(activeTab, (tab) => {
  if (tab === 'sales' && salesRows.value.length === 0) fetchSalesReport();
  if (tab === 'receivables' && receivables.value.length === 0) fetchReceivables();
  if (tab === 'turnover' && turnoverRows.value.length === 0) fetchTurnoverReport();
});

onMounted(() => {
  fetchSalesReport();
});

async function fetchSalesReport() {
  salesLoading.value = true;
  try {
    const response = await api.get('/reports/sales', {
      params: { payment_method: salesPaymentMethod.value },
    });
    if (response.data.status === 'success') {
      salesRows.value = response.data.data.rows || [];
      salesSummary.value = response.data.data.summary || { count: 0, total: 0 };
    }
  } finally {
    salesLoading.value = false;
  }
}

async function fetchReceivables() {
  receivablesLoading.value = true;
  try {
    const response = await api.get('/receivables', {
      params: { status: statusFilter.value },
    });
    if (response.data.status === 'success') {
      receivables.value = response.data.data;
    }
  } finally {
    receivablesLoading.value = false;
  }
}

async function fetchTurnoverReport() {
  turnoverLoading.value = true;
  try {
    const response = await api.get('/reports/daily-turnover', {
      params: {
        date_from: turnoverDateFrom.value,
        date_to: turnoverDateTo.value,
      },
    });
    if (response.data.status === 'success') {
      turnoverRows.value = response.data.data.rows || [];
      turnoverSummary.value = response.data.data.summary || { orders_count: 0, turnover: 0 };
    }
  } finally {
    turnoverLoading.value = false;
  }
}

function sendReminder(item: any) {
  if (!item.customer || !item.customer.whatsapp) {
    alert('Nomor WhatsApp pelanggan tidak tersedia.');
    return;
  }

  let phone = item.customer.whatsapp.replace(/\D/g, '');
  if (phone.startsWith('0')) phone = '62' + phone.slice(1);

  const invoice = item.order?.invoice_number || 'Pesanan Anda';
  const remaining = formatCurrency(item.remaining_amount);
  const dueDate = formatDate(item.due_date);
  const message = `Halo ${item.customer.name},\n\nIni adalah pengingat tagihan untuk *${invoice}*.\n\nSisa tagihan yang belum dibayar adalah sebesar *Rp ${remaining}*, jatuh tempo pada tanggal *${dueDate}*.\n\nMohon segera melakukan pembayaran. Terima kasih!`;

  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
}

function defaultDateFrom(): string {
  const date = new Date();
  date.setDate(date.getDate() - 6);
  return date.toISOString().slice(0, 10);
}

function formatCurrency(val: any) {
  const num = parseFloat(val);
  if (isNaN(num)) return '0';
  return num.toLocaleString('id-ID');
}

function formatDate(dateStr: string) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function getPaymentClass(method: string) {
  if (method === 'cash') return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
  if (method === 'transfer') return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
  if (method === 'qris') return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
  if (method === 'credit') return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
  return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
}

function getStatusClass(status: string) {
  if (status === 'paid') return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
  if (status === 'partial') return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
  if (status === 'overdue') return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
  return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
}

function formatStatus(status: string) {
  switch (status) {
    case 'paid': return 'Lunas';
    case 'partial': return 'Sebagian';
    case 'overdue': return 'Jatuh Tempo';
    case 'unpaid': return 'Belum Dibayar';
    default: return status;
  }
}

const SummaryPill = defineComponent({
  props: {
    label: { type: String, required: true },
    value: { type: Number, required: true },
    tone: { type: String, default: 'indigo' },
  },
  setup(props) {
    return () => h('div', {
      class: [
        'rounded-2xl border px-5 py-3 bg-slate-950/70',
        props.tone === 'rose' ? 'border-rose-500/20' : 'border-indigo-500/20',
      ],
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

const ReportTable = defineComponent({
  props: {
    isLoading: { type: Boolean, required: true },
    empty: { type: Boolean, required: true },
    emptyText: { type: String, required: true },
    colspan: { type: Number, required: true },
  },
  setup(props, { slots }) {
    return () => h('div', { class: 'overflow-x-auto' }, [
      h('table', { class: 'w-full text-left text-sm whitespace-nowrap' }, [
        h('thead', { class: 'uppercase tracking-wider text-slate-400 border-b border-slate-800 bg-slate-950/50' }, slots.head?.()),
        h('tbody', { class: 'divide-y divide-slate-800/50' }, props.isLoading
          ? [h('tr', { class: 'animate-pulse' }, [
              h('td', { colspan: props.colspan, class: 'p-4 text-center text-slate-500' }, 'Memuat data...'),
            ])]
          : props.empty
            ? [h('tr', [
                h('td', { colspan: props.colspan, class: 'p-8 text-center text-slate-500 bg-slate-950/20 rounded-xl border border-dashed border-slate-800' }, props.emptyText),
              ])]
            : slots.body?.()
        ),
      ]),
    ]);
  },
});
</script>
