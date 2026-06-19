<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-100">Manajemen Langganan</h1>
        <p class="text-slate-400 text-sm mt-1">Perbarui, tingkatkan, atau hentikan langganan tenant secara manual.</p>
      </div>
      <button 
        @click="openAddModal" 
        class="theme-btn py-2.5 px-4 rounded-xl font-bold text-sm transition"
      >
        + Tambah / Perbarui Langganan
      </button>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
          <thead>
            <tr class="border-b border-slate-800 text-slate-500 uppercase text-xs font-bold tracking-wider">
              <th class="pb-3 font-semibold">Tenant / Bisnis</th>
              <th class="pb-3 font-semibold">Paket</th>
              <th class="pb-3 font-semibold">Tanggal Mulai</th>
              <th class="pb-3 font-semibold">Tanggal Berakhir</th>
              <th class="pb-3 font-semibold">Status</th>
              <th class="pb-3 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/50">
            <tr v-if="isLoading" class="animate-pulse">
              <td colspan="6" class="p-8 text-center text-slate-500">Memuat data langganan...</td>
            </tr>
            <tr v-else-if="subscriptions.length === 0">
              <td colspan="6" class="p-8 text-center text-slate-500">Tidak ada riwayat langganan.</td>
            </tr>
            <tr 
              v-else 
              v-for="sub in subscriptions" 
              :key="sub.id"
              class="hover:bg-slate-850/10 transition text-slate-300"
            >
              <td class="py-4">
                <p class="font-bold text-slate-200">{{ sub.tenant?.name || 'Unknown' }}</p>
                <p class="text-xs text-slate-550 mt-0.5">{{ sub.tenant?.phone }}</p>
              </td>
              <td class="py-4">
                <p class="font-bold text-indigo-400 capitalize">{{ sub.plan?.name || '—' }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Rp {{ formatCurrency(sub.plan?.monthly_price) }}/bln</p>
              </td>
              <td class="py-4 text-xs text-slate-400">{{ formatDate(sub.started_at) }}</td>
              <td class="py-4 text-xs text-slate-400 font-semibold">{{ sub.expired_at ? formatDate(sub.expired_at) : 'Seterusnya' }}</td>
              <td class="py-4">
                <span :class="getStatusClass(sub.status)" class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">
                  {{ sub.status }}
                </span>
              </td>
              <td class="py-4 text-right">
                <button 
                  @click="openEditModal(sub)"
                  class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-750 text-indigo-400 font-bold text-xs transition border border-slate-700/30"
                >
                  Edit Langganan
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add / Edit Subscription Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <h3 class="text-2xl font-black text-slate-100">
          {{ isEditMode ? 'Ubah Status Langganan' : 'Daftarkan Langganan Baru' }}
        </h3>

        <form @submit.prevent="saveSubscription" class="space-y-4 text-sm">
          <!-- Tenant Select (Only on Add Mode) -->
          <div v-if="!isEditMode">
            <label class="block text-xs font-bold text-slate-400 mb-2">Pilih Tenant</label>
            <select v-model="form.tenant_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition">
              <option value="" disabled>Pilih bisnis...</option>
              <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div v-else>
            <label class="block text-xs font-bold text-slate-400 mb-1">Tenant</label>
            <p class="text-slate-200 font-bold py-1">{{ form.tenant_name }}</p>
          </div>

          <!-- Plan Select -->
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Pilihan Paket</label>
            <select v-model="form.plan_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition">
              <option value="" disabled>Pilih paket...</option>
              <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }} (Rp {{ formatCurrency(p.monthly_price) }}/bln)</option>
            </select>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Tanggal Mulai</label>
              <input v-model="form.started_at" type="date" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Tanggal Berakhir</label>
              <input v-model="form.expired_at" type="date" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Status</label>
            <select v-model="form.status" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition">
              <option value="trial">Trial</option>
              <option value="active">Active</option>
              <option value="expired">Expired</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4 border-t border-slate-800">
            <button @click="showModal = false" type="button" class="flex-1 py-3.5 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 font-bold transition">Batal</button>
            <button type="submit" class="theme-btn flex-1 py-3.5 px-4 rounded-xl font-bold transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '@/services/api';

const isLoading = ref(true);
const subscriptions = ref([] as any[]);
const tenants = ref([] as any[]);
const plans = ref([] as any[]);

// Modal
const showModal = ref(false);
const isEditMode = ref(false);
const editingSubId = ref<number | null>(null);
const form = ref({} as any);

async function fetchData() {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/subscriptions');
    if (res.data.status === 'success') subscriptions.value = res.data.data;

    const tRes = await api.get('/admin/support/tenants');
    if (tRes.data.status === 'success') tenants.value = tRes.data.data;

    const pRes = await api.get('/admin/plans');
    if (pRes.data.status === 'success') plans.value = pRes.data.data;
  } catch (err: any) {
    alert('Gagal memuat data langganan.');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchData();
});

function openAddModal() {
  isEditMode.value = false;
  editingSubId.value = null;
  form.value = {
    tenant_id: '',
    plan_id: '',
    started_at: new Date().toISOString().slice(0, 10),
    expired_at: defaultExpiryDate(),
    status: 'active',
  };
  showModal.value = true;
}

function openEditModal(sub: any) {
  isEditMode.value = true;
  editingSubId.value = sub.id;
  form.value = {
    tenant_name: sub.tenant?.name,
    plan_id: sub.plan_id,
    started_at: sub.started_at ? sub.started_at.slice(0, 10) : '',
    expired_at: sub.expired_at ? sub.expired_at.slice(0, 10) : '',
    status: sub.status,
  };
  showModal.value = true;
}

async function saveSubscription() {
  try {
    let res;
    if (isEditMode.value && editingSubId.value) {
      res = await api.put(`/admin/subscriptions/${editingSubId.value}`, form.value);
    } else {
      res = await api.post('/admin/subscriptions', form.value);
    }

    if (res.data.status === 'success') {
      showModal.value = false;
      fetchData();
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan data langganan.');
  }
}

// Helpers
function defaultExpiryDate(): string {
  const d = new Date();
  d.setMonth(d.getMonth() + 1); // 1 Month duration
  return d.toISOString().slice(0, 10);
}

function formatCurrency(val: any) {
  const num = parseFloat(val);
  return isNaN(num) ? '0' : num.toLocaleString('id-ID');
}

function formatDate(dateStr: string) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function getStatusClass(status: string) {
  if (status === 'active') return 'bg-emerald-500/10 text-emerald-450 border border-emerald-500/20';
  if (status === 'trial') return 'bg-blue-500/10 text-blue-450 border border-blue-500/20';
  if (status === 'expired') return 'bg-amber-500/10 text-amber-450 border border-amber-500/20';
  return 'bg-red-500/10 text-red-450 border border-red-500/20';
}
</script>
