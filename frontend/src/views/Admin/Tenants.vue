<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-100">Daftar Tenant</h1>
        <p class="text-slate-400 text-sm mt-1">Kelola akun bisnis UMKM yang terdaftar di platform Anda.</p>
      </div>
      <button 
        @click="fetchTenants" 
        class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-750 border border-slate-700 text-sm font-bold transition flex items-center gap-1.5"
      >
        <span>🔄</span> Refresh Data
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col md:flex-row gap-4 items-center bg-slate-900/40 border border-slate-800 rounded-3xl p-5">
      <div class="flex-1 w-full relative">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari nama bisnis, pemilik, atau email..." 
          class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 pl-10 text-slate-100 placeholder-slate-650 focus:outline-none focus:border-indigo-500 transition text-sm"
        />
        <span class="absolute left-3.5 top-3.5 text-slate-600 text-sm">🔍</span>
      </div>
      <div class="flex gap-4 w-full md:w-auto">
        <select 
          v-model="planFilter" 
          class="flex-1 md:w-44 bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-350 focus:outline-none focus:border-indigo-500 transition text-sm"
        >
          <option value="all">Semua Paket</option>
          <option value="free">Free</option>
          <option value="pro">Pro</option>
          <option value="business">Business</option>
        </select>
        <select 
          v-model="statusFilter" 
          class="flex-1 md:w-44 bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-350 focus:outline-none focus:border-indigo-500 transition text-sm"
        >
          <option value="all">Semua Status</option>
          <option value="trial">Trial</option>
          <option value="active">Active</option>
          <option value="expired">Expired</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
          <thead>
            <tr class="border-b border-slate-800 text-slate-500 uppercase text-xs font-bold tracking-wider">
              <th class="pb-3 font-semibold">Bisnis / Pemilik</th>
              <th class="pb-3 font-semibold">Kontak</th>
              <th class="pb-3 font-semibold">Paket</th>
              <th class="pb-3 font-semibold">Status</th>
              <th class="pb-3 font-semibold">Masa Trial</th>
              <th class="pb-3 font-semibold">Terdaftar</th>
              <th class="pb-3 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/50">
            <tr v-if="isLoading" class="animate-pulse">
              <td colspan="7" class="p-8 text-center text-slate-500">Memuat data tenant...</td>
            </tr>
            <tr v-else-if="filteredTenants.length === 0">
              <td colspan="7" class="p-8 text-center text-slate-500">Tidak ada tenant ditemukan.</td>
            </tr>
            <tr 
              v-else 
              v-for="tenant in filteredTenants" 
              :key="tenant.id"
              class="hover:bg-slate-850/10 transition text-slate-300"
            >
              <td class="py-4">
                <p class="font-bold text-slate-200">{{ tenant.name }}</p>
                <p class="text-xs text-slate-500 font-mono mt-0.5">/{{ tenant.slug }}</p>
              </td>
              <td class="py-4">
                <p class="font-semibold text-slate-200">{{ tenant.owner_name }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ tenant.owner_email }} · {{ tenant.phone }}</p>
              </td>
              <td class="py-4 capitalize font-semibold text-indigo-400">{{ tenant.subscription_plan }}</td>
              <td class="py-4">
                <span :class="getStatusClass(tenant.subscription_status)" class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">
                  {{ tenant.subscription_status }}
                </span>
              </td>
              <td class="py-4 text-xs font-medium">{{ tenant.trial_ends_at ? formatDate(tenant.trial_ends_at) : '—' }}</td>
              <td class="py-4 text-xs text-slate-400">{{ formatDate(tenant.created_at) }}</td>
              <td class="py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="viewDetails(tenant)"
                    class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-750 text-indigo-400 font-semibold text-xs transition border border-slate-700/30"
                  >
                    Detail
                  </button>
                  <button 
                    @click="openEditModal(tenant)"
                    class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-750 text-slate-300 font-semibold text-xs transition border border-slate-700/30"
                  >
                    Edit
                  </button>
                  <button 
                    v-if="tenant.is_active"
                    @click="suspendTenant(tenant)"
                    class="py-1.5 px-3 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 font-semibold text-xs transition border border-red-500/20"
                  >
                    Suspend
                  </button>
                  <button 
                    v-else
                    @click="activateTenant(tenant)"
                    class="py-1.5 px-3 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold text-xs transition border border-emerald-500/20"
                  >
                    Aktifkan
                  </button>
                  <button 
                    @click="impersonate(tenant)"
                    class="py-1.5 px-3 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 font-bold text-xs transition border border-amber-500/20"
                  >
                    Masuk 👤
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tenant Detail Modal -->
    <div v-if="activeTenant" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-2xl font-black text-slate-100">{{ activeTenant.name }}</h3>
            <p class="text-xs text-slate-500 mt-1">Slug: /{{ activeTenant.slug }}</p>
          </div>
          <button @click="activeTenant = null" class="text-xl font-bold text-slate-450 hover:text-slate-200 transition">✕</button>
        </div>

        <div class="space-y-4 border-t border-slate-800 pt-4 text-sm text-slate-300">
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-950/50 p-3 rounded-xl border border-slate-850">
              <span class="text-[10px] uppercase font-bold text-slate-500">Total Produk</span>
              <p class="text-lg font-black text-slate-200 mt-1">{{ activeTenant.products_count ?? 0 }}</p>
            </div>
            <div class="bg-slate-950/50 p-3 rounded-xl border border-slate-850">
              <span class="text-[10px] uppercase font-bold text-slate-500">Total Order</span>
              <p class="text-lg font-black text-slate-200 mt-1">{{ activeTenant.orders_count ?? 0 }}</p>
            </div>
            <div class="bg-slate-950/50 p-3 rounded-xl border border-slate-850 col-span-2">
              <span class="text-[10px] uppercase font-bold text-slate-500">Tagihan Piutang (Aktif)</span>
              <p class="text-lg font-black text-rose-450 mt-1">{{ activeTenant.receivables_count ?? 0 }} Piutang</p>
            </div>
          </div>

          <div class="space-y-2.5 mt-2">
            <p><span class="text-slate-500">Plan Terkini:</span> <strong class="capitalize text-indigo-400 ml-1">{{ activeTenant.subscription_plan }}</strong></p>
            <p><span class="text-slate-500">Status Langganan:</span> <span :class="getStatusClass(activeTenant.subscription_status)" class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase">{{ activeTenant.subscription_status }}</span></p>
            <p><span class="text-slate-500">Pemilik Bisnis:</span> <span class="text-slate-200 ml-1">{{ activeTenant.owner_name }} ({{ activeTenant.owner_email }})</span></p>
            <p><span class="text-slate-500">Tanggal Daftar:</span> <span class="text-slate-200 ml-1">{{ formatDate(activeTenant.created_at) }}</span></p>
          </div>
        </div>

        <div class="flex gap-3 pt-2 border-t border-slate-800">
          <button 
            @click="openResetPasswordPrompt(activeTenant)" 
            class="flex-1 py-3 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 font-bold text-xs text-amber-400 transition"
          >
            🔑 Reset Sandi
          </button>
          <button 
            @click="activeTenant = null" 
            class="flex-1 py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-750 font-bold text-xs transition"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Tenant Modal -->
    <div v-if="editTenant" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <h3 class="text-2xl font-black text-slate-100">Edit Detail Tenant</h3>

        <form @submit.prevent="saveEdit" class="space-y-4 text-sm">
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Nama Bisnis</label>
            <input v-model="editForm.name" type="text" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Slug URL</label>
            <input v-model="editForm.slug" type="text" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Pilihan Paket</label>
              <select v-model="editForm.subscription_plan" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition">
                <option value="free">Free</option>
                <option value="pro">Pro</option>
                <option value="business">Business</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Status Langganan</label>
              <select v-model="editForm.subscription_status" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition">
                <option value="trial">Trial</option>
                <option value="active">Active</option>
                <option value="expired">Expired</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Tanggal Berakhir Trial</label>
            <input v-model="editForm.trial_ends_at" type="date" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Nomor Telepon / WA</label>
            <input v-model="editForm.phone" type="text" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
          </div>
          <div class="flex items-center gap-2 py-1">
            <input v-model="editForm.is_active" id="edit_is_active" type="checkbox" class="w-5 h-5 rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-0" />
            <label for="edit_is_active" class="text-xs font-bold text-slate-400 cursor-pointer">Akun Aktif (is_active)</label>
          </div>

          <div class="flex gap-3 pt-4 border-t border-slate-800">
            <button @click="editTenant = null" type="button" class="flex-1 py-3.5 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 font-bold transition">Batal</button>
            <button type="submit" class="theme-btn flex-1 py-3.5 px-4 rounded-xl font-bold transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const isLoading = ref(true);
const tenants = ref([] as any[]);
const searchQuery = ref('');
const planFilter = ref('all');
const statusFilter = ref('all');

// Modals
const activeTenant = ref(null as any);
const editTenant = ref(null as any);
const editForm = ref({} as any);

async function fetchTenants() {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/tenants');
    if (res.data.status === 'success') {
      tenants.value = res.data.data;
    }
  } catch (err: any) {
    alert('Gagal memuat daftar tenant.');
  } finally {
    isLoading.value = false;
  }
}

const filteredTenants = computed(() => {
  return tenants.value.filter(t => {
    const matchesSearch = 
      t.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      t.slug.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      t.owner_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      t.owner_email.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesPlan = planFilter.value === 'all' || t.subscription_plan === planFilter.value;
    const matchesStatus = statusFilter.value === 'all' || t.subscription_status === statusFilter.value;

    return matchesSearch && matchesPlan && matchesStatus;
  });
});

onMounted(() => {
  fetchTenants();
});

function viewDetails(tenant: any) {
  activeTenant.value = tenant;
}

function openEditModal(tenant: any) {
  editTenant.value = tenant;
  editForm.value = {
    name: tenant.name,
    slug: tenant.slug,
    subscription_plan: tenant.subscription_plan,
    subscription_status: tenant.subscription_status,
    trial_ends_at: tenant.trial_ends_at ? tenant.trial_ends_at.slice(0, 10) : '',
    phone: tenant.phone,
    is_active: !!tenant.is_active,
  };
}

async function saveEdit() {
  try {
    const res = await api.put(`/admin/tenants/${editTenant.value.id}`, editForm.value);
    if (res.data.status === 'success') {
      editTenant.value = null;
      fetchTenants();
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan perubahan.');
  }
}

async function suspendTenant(tenant: any) {
  if (confirm(`Apakah Anda yakin ingin menangguhkan tenant '${tenant.name}'?`)) {
    try {
      const res = await api.post(`/admin/tenants/${tenant.id}/suspend`);
      if (res.data.status === 'success') fetchTenants();
    } catch (err: any) {
      alert('Gagal menangguhkan tenant.');
    }
  }
}

async function activateTenant(tenant: any) {
  try {
    const res = await api.post(`/admin/tenants/${tenant.id}/activate`);
    if (res.data.status === 'success') fetchTenants();
  } catch (err: any) {
    alert('Gagal mengaktifkan tenant.');
  }
}

function openResetPasswordPrompt(tenant: any) {
  const newPassword = prompt(`Masukkan kata sandi baru untuk pemilik '${tenant.name}':`);
  if (newPassword === null) return;
  const pw = newPassword.trim();
  if (pw.length < 6) {
    alert('Kata sandi minimal harus 6 karakter.');
    return;
  }
  api.post(`/admin/tenants/${tenant.id}/reset-password`, { password: pw })
    .then(res => {
      if (res.data.status === 'success') alert(res.data.message);
    })
    .catch(() => alert('Gagal mengatur ulang kata sandi.'));
}

async function impersonate(tenant: any) {
  if (confirm(`Apakah Anda ingin bertindak sebagai pemilik toko '${tenant.name}'?`)) {
    try {
      const res = await api.post(`/admin/tenants/${tenant.id}/impersonate`);
      if (res.data.status === 'success') {
        const { token, user, tenant: tenantData } = res.data.data;
        authStore.impersonateTenant(token, user, tenantData);
        router.push('/dashboard'); // Redirect to standard tenant hub dashboard!
      }
    } catch (err: any) {
      alert('Gagal melakukan impersonasi.');
    }
  }
}

// Helpers
function getStatusClass(status: string) {
  if (status === 'active') return 'bg-emerald-500/10 text-emerald-450 border border-emerald-500/20';
  if (status === 'trial') return 'bg-blue-500/10 text-blue-450 border border-blue-500/20';
  if (status === 'expired') return 'bg-amber-500/10 text-amber-450 border border-amber-500/20';
  return 'bg-red-500/10 text-red-450 border border-red-500/20';
}

function formatDate(dateStr: string) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}
</script>
