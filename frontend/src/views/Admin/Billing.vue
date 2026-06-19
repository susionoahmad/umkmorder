<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-100">Tagihan &amp; Invoice</h1>
        <p class="text-slate-400 text-sm mt-1">Terbitkan invoice penagihan manual dan kelola riwayat transaksi platform.</p>
      </div>
      <button 
        @click="openAddModal" 
        class="theme-btn py-2.5 px-4 rounded-xl font-bold text-sm transition"
      >
        + Terbitkan Invoice Baru
      </button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row gap-4 items-center bg-slate-900/40 border border-slate-800 rounded-3xl p-5">
      <div class="flex-1 w-full relative">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari nomor invoice atau nama tenant..." 
          class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 pl-10 text-slate-100 placeholder-slate-650 focus:outline-none focus:border-indigo-500 transition text-sm"
        />
        <span class="absolute left-3.5 top-3.5 text-slate-600 text-sm">🔍</span>
      </div>
      <select 
        v-model="statusFilter" 
        class="w-full md:w-48 bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-350 focus:outline-none focus:border-indigo-500 transition text-sm"
      >
        <option value="all">Semua Status</option>
        <option value="paid">Lunas (Paid)</option>
        <option value="pending">Tertunda (Pending)</option>
        <option value="overdue">Jatuh Tempo (Overdue)</option>
        <option value="cancelled">Dibatalkan (Cancelled)</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
          <thead>
            <tr class="border-b border-slate-800 text-slate-500 uppercase text-xs font-bold tracking-wider">
              <th class="pb-3 font-semibold">Nomor Invoice</th>
              <th class="pb-3 font-semibold">Tenant / Bisnis</th>
              <th class="pb-3 font-semibold">Paket</th>
              <th class="pb-3 font-semibold">Nominal</th>
              <th class="pb-3 font-semibold">Jatuh Tempo</th>
              <th class="pb-3 font-semibold">Status</th>
              <th class="pb-3 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/50">
            <tr v-if="isLoading" class="animate-pulse">
              <td colspan="7" class="p-8 text-center text-slate-500">Memuat data invoice...</td>
            </tr>
            <tr v-else-if="filteredInvoices.length === 0">
              <td colspan="7" class="p-8 text-center text-slate-500">Tidak ada invoice ditemukan.</td>
            </tr>
            <tr 
              v-else 
              v-for="invoice in filteredInvoices" 
              :key="invoice.id"
              class="hover:bg-slate-850/10 transition text-slate-300"
            >
              <td class="py-4 font-bold text-slate-200">#{{ invoice.invoice_number }}</td>
              <td class="py-4">
                <p class="font-bold text-slate-200">{{ invoice.tenant?.name || 'Unknown' }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ invoice.tenant?.phone }}</p>
              </td>
              <td class="py-4 capitalize font-semibold text-slate-300">
                {{ invoice.subscription?.plan?.name || '—' }}
              </td>
              <td class="py-4 font-black text-indigo-400">Rp {{ formatCurrency(invoice.amount) }}</td>
              <td class="py-4 text-xs text-slate-400">
                <p>{{ formatDate(invoice.due_date) }}</p>
                <p v-if="invoice.status === 'paid' && invoice.paid_at" class="text-[10px] text-emerald-500 mt-0.5">
                  Dibayar: {{ formatDate(invoice.paid_at) }}
                </p>
              </td>
              <td class="py-4">
                <span :class="getStatusClass(invoice.status)" class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">
                  {{ formatStatusLabel(invoice.status) }}
                </span>
              </td>
              <td class="py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    v-if="invoice.status !== 'paid'"
                    @click="markAsPaid(invoice)"
                    class="py-1.5 px-3 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-bold text-xs transition border border-emerald-500/20"
                  >
                    Set Lunas ✅
                  </button>
                  <button 
                    v-if="invoice.status === 'pending'"
                    @click="markAsOverdue(invoice)"
                    class="py-1.5 px-3 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 font-bold text-xs transition border border-rose-500/20"
                  >
                    Set Overdue ⚠️
                  </button>
                  <button 
                    @click="openEditModal(invoice)"
                    class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-750 text-slate-350 font-semibold text-xs transition border border-slate-700/30"
                  >
                    Edit
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create / Edit Invoice Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <h3 class="text-2xl font-black text-slate-100">
          {{ isEditMode ? 'Edit Status Invoice' : 'Terbitkan Invoice Baru' }}
        </h3>

        <form @submit.prevent="saveInvoice" class="space-y-4 text-sm">
          <!-- Tenant Select (Add Mode Only) -->
          <div v-if="!isEditMode">
            <label class="block text-xs font-bold text-slate-400 mb-2">Pilih Tenant</label>
            <select v-model="form.tenant_id" required @change="onTenantChange" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition">
              <option value="" disabled>Pilih bisnis...</option>
              <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div v-else>
            <label class="block text-xs font-bold text-slate-400 mb-1">Tenant / Nomor Invoice</label>
            <p class="text-slate-200 font-bold py-1">{{ form.tenant_name }} (#{{ form.invoice_number }})</p>
          </div>

          <!-- Subscription Select (Add Mode Only) -->
          <div v-if="!isEditMode">
            <label class="block text-xs font-bold text-slate-400 mb-2">Pilih Item Langganan (Opsional)</label>
            <select v-model="form.subscription_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition">
              <option value="">Tanpa Langganan Khusus</option>
              <option v-for="s in tenantSubscriptions" :key="s.id" :value="s.id">
                {{ s.plan?.name }} (Berakhir: {{ s.expired_at ? formatDate(s.expired_at) : 'Seterusnya' }})
              </option>
            </select>
          </div>

          <!-- Amount -->
          <div v-if="!isEditMode">
            <label class="block text-xs font-bold text-slate-400 mb-2">Nominal Tagihan (Rp)</label>
            <input v-model.number="form.amount" type="number" required min="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
          </div>
          <div v-else>
            <label class="block text-xs font-bold text-slate-400 mb-1">Nominal Tagihan</label>
            <p class="text-indigo-400 font-black py-1">Rp {{ formatCurrency(form.amount) }}</p>
          </div>

          <!-- Due Date -->
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Tanggal Jatuh Tempo</label>
            <input v-model="form.due_date" type="date" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
          </div>

          <!-- Status -->
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Status</label>
            <select v-model="form.status" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition">
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="overdue">Overdue</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4 border-t border-slate-800">
            <button @click="showModal = false" type="button" class="flex-1 py-3.5 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 font-bold transition">Batal</button>
            <button type="submit" class="theme-btn flex-1 py-3.5 px-4 rounded-xl font-bold transition">Simpan Invoice</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';

const isLoading = ref(true);
const invoices = ref([] as any[]);
const tenants = ref([] as any[]);
const subscriptionsList = ref([] as any[]);

// Filters
const searchQuery = ref('');
const statusFilter = ref('all');

// Modal
const showModal = ref(false);
const isEditMode = ref(false);
const editingInvoiceId = ref<number | null>(null);
const form = ref({} as any);

const tenantSubscriptions = computed(() => {
  if (!form.value.tenant_id) return [];
  return subscriptionsList.value.filter((s: any) => s.tenant_id === form.value.tenant_id);
});

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/billing-invoices');
    if (res.data.status === 'success') invoices.value = res.data.data;

    const tRes = await api.get('/admin/support/tenants');
    if (tRes.data.status === 'success') tenants.value = tRes.data.data;

    const sRes = await api.get('/admin/subscriptions');
    if (sRes.data.status === 'success') subscriptionsList.value = sRes.data.data;
  } catch (err: any) {
    alert('Gagal memuat data tagihan platform.');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchData();
});

const filteredInvoices = computed(() => {
  return invoices.value.filter(inv => {
    const matchesSearch = 
      inv.invoice_number.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (inv.tenant?.name || '').toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesStatus = statusFilter.value === 'all' || inv.status === statusFilter.value;

    return matchesSearch && matchesStatus;
  });
});

function openAddModal() {
  isEditMode.value = false;
  editingInvoiceId.value = null;
  form.value = {
    tenant_id: '',
    subscription_id: '',
    amount: 0,
    status: 'pending',
    due_date: new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 10), // + 7 Days
  };
  showModal.value = true;
}

function openEditModal(invoice: any) {
  isEditMode.value = true;
  editingInvoiceId.value = invoice.id;
  form.value = {
    tenant_name: invoice.tenant?.name,
    invoice_number: invoice.invoice_number,
    amount: invoice.amount,
    status: invoice.status,
    due_date: invoice.due_date ? invoice.due_date.slice(0, 10) : '',
  };
  showModal.value = true;
}

function onTenantChange() {
  form.value.subscription_id = '';
  // Autofill amount if they have subscriptions
  const activeSub = tenantSubscriptions.value.find((s: any) => s.status === 'active');
  if (activeSub && activeSub.plan) {
    form.value.amount = parseFloat(activeSub.plan.monthly_price);
    form.value.subscription_id = activeSub.id;
  }
}

async function saveInvoice() {
  try {
    let res;
    if (isEditMode.value && editingInvoiceId.value) {
      res = await api.put(`/admin/billing-invoices/${editingInvoiceId.value}`, {
        status: form.value.status,
        due_date: form.value.due_date,
      });
    } else {
      res = await api.post('/admin/billing-invoices', form.value);
    }

    if (res.data.status === 'success') {
      showModal.value = false;
      fetchData();
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan invoice.');
  }
}

async function markAsPaid(invoice: any) {
  if (confirm(`Apakah Anda yakin ingin menandai invoice #${invoice.invoice_number} sebagai Lunas?`)) {
    try {
      const res = await api.put(`/admin/billing-invoices/${invoice.id}`, {
        status: 'paid',
        due_date: invoice.due_date.slice(0, 10),
      });
      if (res.data.status === 'success') fetchData();
    } catch (err: any) {
      alert('Gagal memperbarui status invoice.');
    }
  }
}

async function markAsOverdue(invoice: any) {
  try {
    const res = await api.put(`/admin/billing-invoices/${invoice.id}`, {
      status: 'overdue',
      due_date: invoice.due_date.slice(0, 10),
    });
    if (res.data.status === 'success') fetchData();
  } catch (err: any) {
    alert('Gagal memperbarui status invoice.');
  }
}

// Helpers
function formatCurrency(val: any) {
  const num = parseFloat(val);
  return isNaN(num) ? '0' : num.toLocaleString('id-ID');
}

function formatDate(dateStr: string) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function getStatusClass(status: string) {
  if (status === 'paid') return 'bg-emerald-500/10 text-emerald-450 border border-emerald-500/20';
  if (status === 'pending') return 'bg-blue-500/10 text-blue-450 border border-blue-500/20';
  if (status === 'overdue') return 'bg-rose-500/10 text-rose-450 border border-rose-500/20';
  return 'bg-slate-700 text-slate-400 border border-slate-650';
}

function formatStatusLabel(status: string) {
  const map: Record<string, string> = {
    paid: 'Lunas', pending: 'Tertunda', overdue: 'Jatuh Tempo', cancelled: 'Dibatalkan'
  };
  return map[status] ?? status;
}
</script>
