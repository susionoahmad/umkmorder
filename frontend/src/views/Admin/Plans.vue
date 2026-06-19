<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-100">Paket Langganan</h1>
        <p class="text-slate-400 text-sm mt-1">Konfigurasi batasan produk, batasan pesanan, harga bulanan, dan fitur-fitur aktif.</p>
      </div>
      <button 
        @click="openAddModal" 
        class="theme-btn py-2.5 px-4 rounded-xl font-bold text-sm transition shadow-lg"
      >
        + Tambah Paket Baru
      </button>
    </div>

    <!-- Plans List Cards -->
    <div class="grid md:grid-cols-3 gap-6">
      <div 
        v-for="plan in plans" 
        :key="plan.id"
        class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col justify-between hover:border-indigo-500/50 transition-all duration-300"
      >
        <div>
          <div class="flex justify-between items-start">
            <h3 class="text-xl font-black text-slate-100 capitalize">{{ plan.name }}</h3>
            <span 
              :class="plan.is_active ? 'bg-emerald-500/10 text-emerald-450 border border-emerald-500/20' : 'bg-red-500/10 text-red-450 border border-red-500/20'"
              class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
            >
              {{ plan.is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>

          <div class="mt-4 flex items-baseline gap-1">
            <span class="text-2xl font-black text-slate-100">Rp {{ formatCurrency(plan.monthly_price) }}</span>
            <span class="text-xs text-slate-500">/bulan</span>
          </div>
          <p class="text-xs text-slate-450 mt-1">Tahunan: Rp {{ formatCurrency(plan.yearly_price) }}/thn</p>

          <!-- Plan Limits -->
          <div class="mt-6 space-y-2 border-t border-b border-slate-800/80 py-4 text-xs text-slate-350">
            <p class="flex justify-between">
              <span>Batas Produk Aktif:</span>
              <strong class="text-slate-200">{{ plan.max_products === -1 ? 'Tanpa Batas' : `${plan.max_products} Produk` }}</strong>
            </p>
            <p class="flex justify-between">
              <span>Batas Order Bulanan:</span>
              <strong class="text-slate-200">{{ plan.max_orders === -1 ? 'Tanpa Batas' : `${plan.max_orders} Order` }}</strong>
            </p>
            <p class="flex justify-between">
              <span>Durasi Trial Gratis:</span>
              <strong class="text-slate-200">{{ plan.trial_days }} Hari</strong>
            </p>
          </div>

          <!-- Feature List Checklist -->
          <div class="mt-6 space-y-3">
            <h4 class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Fitur yang Didapatkan</h4>
            <div class="grid grid-cols-1 gap-2 text-xs">
              <div 
                v-for="(label, key) in featureFlags" 
                :key="key"
                class="flex items-center gap-2"
              >
                <span class="text-sm" :class="plan.features_json?.[key] ? 'text-indigo-450' : 'text-slate-650'">
                  {{ plan.features_json?.[key] ? '✓' : '✕' }}
                </span>
                <span :class="plan.features_json?.[key] ? 'text-slate-300' : 'text-slate-550'">{{ label }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-3 mt-8 border-t border-slate-800/80 pt-4">
          <button 
            @click="openEditModal(plan)"
            class="flex-1 py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-750 border border-slate-700/50 font-bold text-xs text-slate-300 transition"
          >
            ✏️ Edit Paket
          </button>
          <button 
            @click="deletePlan(plan)"
            class="py-2.5 px-3 rounded-xl bg-red-500/10 hover:bg-red-500/25 text-red-400 font-bold text-xs transition"
            title="Hapus paket"
          >
            ✕
          </button>
        </div>
      </div>
    </div>

    <!-- Create / Edit Plan Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm overflow-y-auto">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 max-w-xl w-full shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto min-h-0">
        <h3 class="text-2xl font-black text-slate-100">
          {{ isEditMode ? 'Ubah Pengaturan Paket' : 'Buat Paket Langganan Baru' }}
        </h3>

        <form @submit.prevent="savePlan" class="space-y-4 text-sm">
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Nama Paket</label>
              <input v-model="form.name" type="text" required placeholder="Contoh: Premium Pro" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Durasi Trial (Hari)</label>
              <input v-model.number="form.trial_days" type="number" required min="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Harga Bulanan (Rp)</label>
              <input v-model.number="form.monthly_price" type="number" required min="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Harga Tahunan (Rp)</label>
              <input v-model.number="form.yearly_price" type="number" required min="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Batas Produk Aktif (-1 untuk Unlimited)</label>
              <input v-model.number="form.max_products" type="number" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Batas Order Bulanan (-1 untuk Unlimited)</label>
              <input v-model.number="form.max_orders" type="number" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
          </div>

          <!-- Feature Flags Switch List -->
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-3">Fitur yang Diaktifkan</label>
            <div class="grid sm:grid-cols-2 gap-3 bg-slate-950/50 border border-slate-850 p-4 rounded-2xl">
              <div 
                v-for="(label, key) in featureFlags" 
                :key="key"
                class="flex items-center gap-2"
              >
                <input 
                  type="checkbox"
                  v-model="form.features_json[key]"
                  :id="'chk-' + key"
                  class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-0 cursor-pointer"
                />
                <label :for="'chk-' + key" class="text-xs text-slate-355 font-semibold cursor-pointer">{{ label }}</label>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 py-1">
            <input v-model="form.is_active" id="plan_is_active" type="checkbox" class="w-5 h-5 rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-0" />
            <label for="plan_is_active" class="text-xs font-bold text-slate-400 cursor-pointer">Paket Aktif &amp; Ditampilkan (is_active)</label>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4 border-t border-slate-800">
            <button @click="showModal = false" type="button" class="flex-1 py-3.5 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 font-bold transition">Batal</button>
            <button type="submit" class="theme-btn flex-1 py-3.5 px-4 rounded-xl font-bold transition">Simpan Paket</button>
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
const plans = ref([] as any[]);

// Modal
const showModal = ref(false);
const isEditMode = ref(false);
const editingPlanId = ref<number | null>(null);
const form = ref({} as any);

const featureFlags = {
  catalog_online: 'Katalog Online',
  whatsapp_order: 'WhatsApp Order / Checkout',
  tier_pricing: 'Harga Grosir (Bertingkat)',
  receivables: 'Manajemen Piutang',
  payment_tracking: 'Pencatatan Pembayaran',
  distance_shipping: 'Pengiriman berbasis Jarak',
  qr_code_catalog: 'QR Code Katalog',
  export_data: 'Ekspor Data Laporan (P2)',
  reminder_whatsapp: 'Pengingat Tagihan WhatsApp',
  automatic_reminder: 'Reminder WhatsApp Otomatis',
  multi_user: 'Multi User / Staff',
  api_access: 'Akses API Platform',
};

async function fetchPlans() {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/plans');
    if (res.data.status === 'success') {
      plans.value = res.data.data;
    }
  } catch (err: any) {
    alert('Gagal memuat daftar paket.');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchPlans();
});

function openAddModal() {
  isEditMode.value = false;
  editingPlanId.value = null;
  form.value = {
    name: '',
    monthly_price: 0,
    yearly_price: 0,
    max_products: -1,
    max_orders: -1,
    trial_days: 30,
    is_active: true,
    features_json: initializeFeatures(),
  };
  showModal.value = true;
}

function openEditModal(plan: any) {
  isEditMode.value = true;
  editingPlanId.value = plan.id;
  form.value = {
    name: plan.name,
    monthly_price: plan.monthly_price,
    yearly_price: plan.yearly_price,
    max_products: plan.max_products,
    max_orders: plan.max_orders,
    trial_days: plan.trial_days,
    is_active: !!plan.is_active,
    features_json: Object.assign(initializeFeatures(), plan.features_json || {}),
  };
  showModal.value = true;
}

function initializeFeatures() {
  const feats: Record<string, boolean> = {};
  Object.keys(featureFlags).forEach(k => {
    feats[k] = false;
  });
  return feats;
}

async function savePlan() {
  try {
    let res;
    if (isEditMode.value && editingPlanId.value) {
      res = await api.put(`/admin/plans/${editingPlanId.value}`, form.value);
    } else {
      res = await api.post('/admin/plans', form.value);
    }

    if (res.data.status === 'success') {
      showModal.value = false;
      fetchPlans();
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan paket.');
  }
}

async function deletePlan(plan: any) {
  if (confirm(`Apakah Anda yakin ingin menghapus paket '${plan.name}'?`)) {
    try {
      const res = await api.delete(`/admin/plans/${plan.id}`);
      if (res.data.status === 'success') fetchPlans();
    } catch (err: any) {
      alert('Gagal menghapus paket.');
    }
  }
}

// Helpers
function formatCurrency(val: any) {
  const num = parseFloat(val);
  return isNaN(num) ? '0' : num.toLocaleString('id-ID');
}
</script>
