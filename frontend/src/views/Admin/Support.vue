<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-black text-slate-100">Customer Support</h1>
      <p class="text-slate-400 text-sm mt-1">Akses cepat kontak pemilik bisnis dan kelola catatan bantuan support internal.</p>
    </div>

    <!-- Filters & Search -->
    <div class="flex gap-4 items-center bg-slate-900/40 border border-slate-800 rounded-3xl p-5">
      <div class="flex-1 relative">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari nama bisnis, kontak, atau catatan..." 
          class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 pl-10 text-slate-100 placeholder-slate-650 focus:outline-none focus:border-indigo-500 transition text-sm"
        />
        <span class="absolute left-3.5 top-3.5 text-slate-600 text-sm">🔍</span>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-6 shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
          <thead>
            <tr class="border-b border-slate-800 text-slate-500 uppercase text-xs font-bold tracking-wider">
              <th class="pb-3 font-semibold">Tenant / Bisnis</th>
              <th class="pb-3 font-semibold">Pemilik</th>
              <th class="pb-3 font-semibold">Kontak WA</th>
              <th class="pb-3 font-semibold">Paket Aktif</th>
              <th class="pb-3 font-semibold">Catatan Bantuan Support</th>
              <th class="pb-3 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/50">
            <tr v-if="isLoading" class="animate-pulse">
              <td colspan="6" class="p-8 text-center text-slate-500">Memuat data support...</td>
            </tr>
            <tr v-else-if="filteredTenants.length === 0">
              <td colspan="6" class="p-8 text-center text-slate-500">Tidak ada tenant ditemukan.</td>
            </tr>
            <tr 
              v-else 
              v-for="t in filteredTenants" 
              :key="t.id"
              class="hover:bg-slate-850/10 transition text-slate-300"
            >
              <td class="py-4 font-bold text-slate-200">{{ t.name }}</td>
              <td class="py-4">{{ t.owner_name }}</td>
              <td class="py-4">
                <a 
                  :href="getWhatsAppLink(t.phone)" 
                  target="_blank" 
                  class="inline-flex items-center gap-1 text-emerald-450 hover:underline font-bold"
                >
                  🟢 {{ t.phone }}
                </a>
              </td>
              <td class="py-4 capitalize font-semibold text-indigo-400">{{ t.subscription_plan }}</td>
              <td class="py-4">
                <p class="max-w-xs truncate text-xs text-slate-400 italic">
                  {{ t.support_notes || 'Belum ada catatan...' }}
                </p>
              </td>
              <td class="py-4 text-right">
                <button 
                  @click="openNotesModal(t)"
                  class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-750 text-indigo-400 font-bold text-xs transition border border-slate-700/30"
                >
                  Edit Catatan ✏️
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Support Notes Modal -->
    <div v-if="showModal && activeTenant" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-5">
        <div>
          <h3 class="text-2xl font-black text-slate-100">{{ activeTenant.name }}</h3>
          <p class="text-xs text-slate-500 mt-1">Pemilik: {{ activeTenant.owner_name }}</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-2">Catatan Bantuan Internal (Masalah, Keluhan, atau Riwayat)</label>
            <textarea 
              v-model="supportNotes" 
              rows="6" 
              placeholder="Tulis catatan di sini..."
              class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition resize-none text-sm"
            ></textarea>
          </div>
        </div>

        <div class="flex gap-3 pt-3 border-t border-slate-800">
          <button @click="showModal = false" class="flex-1 py-3 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 font-bold text-xs transition">Batal</button>
          <button @click="saveNotes" class="theme-btn flex-1 py-3 px-4 rounded-xl font-bold text-xs transition">Simpan Catatan</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';

const isLoading = ref(true);
const tenants = ref([] as any[]);
const searchQuery = ref('');

// Modal
const showModal = ref(false);
const activeTenant = ref(null as any);
const supportNotes = ref('');

async function fetchSupportData() {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/support/tenants');
    if (res.data.status === 'success') {
      tenants.value = res.data.data;
    }
  } catch (err: any) {
    alert('Gagal memuat direktori support.');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchSupportData();
});

const filteredTenants = computed(() => {
  return tenants.value.filter(t => {
    return t.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      t.owner_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (t.support_notes || '').toLowerCase().includes(searchQuery.value.toLowerCase());
  });
});

function openNotesModal(tenant: any) {
  activeTenant.value = tenant;
  supportNotes.value = tenant.support_notes || '';
  showModal.value = true;
}

async function saveNotes() {
  try {
    const res = await api.put(`/admin/support/tenants/${activeTenant.value.id}/notes`, {
      support_notes: supportNotes.value,
    });
    if (res.data.status === 'success') {
      showModal.value = false;
      fetchSupportData();
    }
  } catch (err: any) {
    alert('Gagal menyimpan catatan support.');
  }
}

function getWhatsAppLink(phone: string) {
  let cleaned = phone.replace(/\D/g, '');
  if (cleaned.startsWith('0')) cleaned = '62' + cleaned.slice(1);
  return `https://wa.me/${cleaned}`;
}
</script>
