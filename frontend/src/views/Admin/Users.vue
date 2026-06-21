<!-- Users.vue -->
<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-100">Manajemen Pengguna</h1>
        <p class="text-slate-400 text-sm mt-1">Kelola data pengguna platform UMKMOrder (Super Admin, Owner, Staff).</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="openCreateModal" 
          class="py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-sm font-bold transition flex items-center gap-1.5 shadow-lg shadow-indigo-600/20 text-white"
        >
          <span>➕</span> Tambah Pengguna
        </button>
        <button 
          @click="fetchUsers" 
          class="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-750 border border-slate-700 text-sm font-bold transition flex items-center gap-1.5"
        >
          <span>🔄</span> Refresh Data
        </button>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col md:flex-row gap-4 items-center bg-slate-900/40 border border-slate-800 rounded-3xl p-5">
      <div class="flex-1 w-full relative">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari nama, email, atau nama toko..." 
          class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 pl-10 text-slate-100 placeholder-slate-650 focus:outline-none focus:border-indigo-500 transition text-sm"
        />
        <span class="absolute left-3.5 top-3.5 text-slate-600 text-sm">🔍</span>
      </div>
      <div class="flex gap-4 w-full md:w-auto">
        <select 
          v-model="roleFilter" 
          class="flex-1 md:w-44 bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-350 focus:outline-none focus:border-indigo-500 transition text-sm"
        >
          <option value="all">Semua Role</option>
          <option value="super_admin">Super Admin</option>
          <option value="owner">Owner (Pemilik Toko)</option>
          <option value="staff">Staff Toko</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
          <thead>
            <tr class="border-b border-slate-800 text-slate-500 uppercase text-xs font-bold tracking-wider">
              <th class="pb-3 font-semibold">Nama</th>
              <th class="pb-3 font-semibold">Email</th>
              <th class="pb-3 font-semibold">Role</th>
              <th class="pb-3 font-semibold">Toko / Tenant</th>
              <th class="pb-3 font-semibold">Dibuat Pada</th>
              <th class="pb-3 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/50">
            <tr v-if="isLoading" class="animate-pulse">
              <td colspan="6" class="p-8 text-center text-slate-500">Memuat data pengguna...</td>
            </tr>
            <tr v-else-if="filteredUsers.length === 0">
              <td colspan="6" class="p-8 text-center text-slate-500">Tidak ada pengguna ditemukan.</td>
            </tr>
            <tr 
              v-else 
              v-for="user in filteredUsers" 
              :key="user.id"
              class="hover:bg-slate-850/10 transition text-slate-300"
            >
              <td class="py-4 font-bold text-slate-200">{{ user.name }}</td>
              <td class="py-4">{{ user.email }}</td>
              <td class="py-4">
                <span :class="getRoleClass(user.role)" class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider">
                  {{ user.role === 'super_admin' ? 'Super Admin' : user.role === 'owner' ? 'Owner' : 'Staff' }}
                </span>
              </td>
              <td class="py-4">
                <div v-if="user.tenant">
                  <p class="font-semibold text-slate-200">{{ user.tenant.name }}</p>
                  <p class="text-xs text-slate-500 font-mono mt-0.5">/{{ user.tenant.slug }}</p>
                </div>
                <span v-else class="text-slate-500 font-medium">—</span>
              </td>
              <td class="py-4 text-xs text-slate-400">{{ formatDate(user.created_at) }}</td>
              <td class="py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="openEditModal(user)"
                    class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-750 text-indigo-400 font-semibold text-xs transition border border-slate-700/30"
                  >
                    Edit
                  </button>
                  <button 
                    v-if="user.id !== authStore.user?.id"
                    @click="deleteUser(user)"
                    class="py-1.5 px-3 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 font-semibold text-xs transition border border-red-500/20"
                  >
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6 border border-slate-700">
        <h3 class="text-2xl font-black text-slate-100">
          {{ isEditMode ? 'Edit Pengguna' : 'Tambah Pengguna' }}
        </h3>

        <form @submit.prevent="saveUser" class="space-y-4 text-sm">
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Nama</label>
            <input 
              v-model="form.name" 
              type="text" 
              required 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" 
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Email</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" 
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">
              Password {{ isEditMode ? '(Kosongkan jika tidak ingin diubah)' : '' }}
            </label>
            <input 
              v-model="form.password" 
              type="password" 
              :required="!isEditMode" 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" 
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Role</label>
            <select 
              v-model="form.role" 
              required 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition text-sm"
            >
              <option value="super_admin">Super Admin</option>
              <option value="owner">Owner (Pemilik Toko)</option>
              <option value="staff">Staff Toko</option>
            </select>
          </div>
          
          <div v-if="form.role !== 'super_admin'">
            <label class="block text-xs font-bold text-slate-400 mb-2">Tenant / Toko</label>
            <select 
              v-model="form.tenant_id" 
              required 
              class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-300 focus:outline-none focus:border-indigo-500 transition text-sm"
            >
              <option :value="null" disabled>Pilih Tenant...</option>
              <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                {{ tenant.name }} (/{{ tenant.slug }})
              </option>
            </select>
          </div>

          <div class="flex gap-3 pt-4 border-t border-slate-800">
            <button 
              @click="closeModal" 
              type="button" 
              class="flex-1 py-3.5 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 font-bold transition text-slate-300"
            >
              Batal
            </button>
            <button 
              type="submit" 
              class="flex-1 py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-bold transition text-white"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const isLoading = ref(true);
const users = ref([] as any[]);
const tenants = ref([] as any[]);
const searchQuery = ref('');
const roleFilter = ref('all');

// Modals
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingUserId = ref<number | null>(null);
const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'staff',
  tenant_id: null as number | null,
});

async function fetchUsers() {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/users');
    if (res.data.status === 'success') {
      users.value = res.data.data;
    }
  } catch (err: any) {
    alert('Gagal memuat daftar pengguna.');
  } finally {
    isLoading.value = false;
  }
}

async function fetchTenants() {
  try {
    const res = await api.get('/admin/tenants');
    if (res.data.status === 'success') {
      tenants.value = res.data.data;
    }
  } catch (err) {
    console.error('Gagal memuat tenants dropdown.');
  }
}

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const nameMatch = u.name ? u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) : false;
    const emailMatch = u.email ? u.email.toLowerCase().includes(searchQuery.value.toLowerCase()) : false;
    const tenantNameMatch = u.tenant?.name ? u.tenant.name.toLowerCase().includes(searchQuery.value.toLowerCase()) : false;
    const tenantSlugMatch = u.tenant?.slug ? u.tenant.slug.toLowerCase().includes(searchQuery.value.toLowerCase()) : false;

    const matchesSearch = nameMatch || emailMatch || tenantNameMatch || tenantSlugMatch;
    const matchesRole = roleFilter.value === 'all' || u.role === roleFilter.value;

    return matchesSearch && matchesRole;
  });
});

onMounted(() => {
  fetchUsers();
  fetchTenants();
});

function openCreateModal() {
  isEditMode.value = false;
  editingUserId.value = null;
  form.value = {
    name: '',
    email: '',
    password: '',
    role: 'staff',
    tenant_id: tenants.value.length > 0 ? tenants.value[0].id : null,
  };
  isModalOpen.value = true;
}

function openEditModal(user: any) {
  isEditMode.value = true;
  editingUserId.value = user.id;
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
    tenant_id: user.tenant_id,
  };
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
}

async function saveUser() {
  try {
    const payload = { ...form.value };
    if (payload.role === 'super_admin') {
      payload.tenant_id = null;
    }
    
    if (isEditMode.value && editingUserId.value) {
      if (!payload.password) {
        delete (payload as any).password;
      }
      const res = await api.put(`/admin/users/${editingUserId.value}`, payload);
      if (res.data.status === 'success') {
        alert(res.data.message);
        isModalOpen.value = false;
        fetchUsers();
      }
    } else {
      const res = await api.post('/admin/users', payload);
      if (res.data.status === 'success') {
        alert(res.data.message);
        isModalOpen.value = false;
        fetchUsers();
      }
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan data pengguna.');
  }
}

async function deleteUser(user: any) {
  if (confirm(`Apakah Anda yakin ingin menghapus pengguna '${user.name}'?`)) {
    try {
      const res = await api.delete(`/admin/users/${user.id}`);
      if (res.data.status === 'success') {
        alert(res.data.message);
        fetchUsers();
      }
    } catch (err: any) {
      alert(err.response?.data?.message || 'Gagal menghapus pengguna.');
    }
  }
}

// Helpers
function getRoleClass(role: string) {
  if (role === 'super_admin') return 'bg-purple-500/10 text-purple-400 border border-purple-500/20';
  if (role === 'owner') return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
  return 'bg-slate-800 text-slate-400 border border-slate-700/50';
}

function formatDate(dateStr: string) {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}
</script>
