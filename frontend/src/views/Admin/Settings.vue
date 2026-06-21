<template>
  <div class="space-y-6 animate-fadeIn">
    <!-- Header -->
    <div>
      <h1 class="text-3xl font-black text-slate-100">Pengaturan Platform</h1>
      <p class="text-slate-400 text-sm mt-1">Konfigurasi branding SaaS, kontak utama support, durasi trial default, dan mode pemeliharaan.</p>
    </div>

    <!-- Settings Form -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-8 shadow-md max-w-2xl">
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 space-y-4">
        <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-slate-400 text-xs">Memuat pengaturan platform...</p>
      </div>

      <form v-else @submit.prevent="saveSettings" class="space-y-6 text-sm">
        <!-- App Branding -->
        <div class="space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Identitas &amp; Branding</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Nama Aplikasi</label>
              <input v-model="form.app_name" type="text" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Nama Perusahaan</label>
              <input v-model="form.company_name" type="text" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
          </div>
        </div>

        <!-- Support Configuration -->
        <div class="space-y-4 pt-4 border-t border-slate-800/80">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Customer Support Utama</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Email Bantuan</label>
              <input v-model="form.support_email" type="email" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Nomor WhatsApp Bantuan</label>
              <input v-model="form.support_whatsapp" type="text" required class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
          </div>
        </div>

        <!-- Trial Duration & System -->
        <div class="space-y-4 pt-4 border-t border-slate-800/80">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Konfigurasi Sistem Trial</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Durasi Uji Coba Default (Hari)</label>
              <input v-model.number="form.default_trial_duration" type="number" required min="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
            </div>
          </div>
        </div>

        <!-- Billing & Pembayaran Upgrade Pro -->
        <div class="space-y-4 pt-4 border-t border-slate-800/80">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Billing &amp; Pembayaran Upgrade Pro</h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 mb-2">Informasi Transfer Bank (BCA / Lainnya)</label>
              <textarea 
                v-model="form.admin_bank_transfer_info" 
                rows="3"
                placeholder="Contoh: Bank BCA — 772-0988-123&#10;a/n PT UMKM Order Indonesia"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition resize-none"
              ></textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-400 mb-2">Info QRIS Utama (Teks)</label>
                <input 
                  v-model="form.admin_qris_info" 
                  type="text" 
                  placeholder="Contoh: QRIS GPN UMKM-ORDER"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl py-3 px-4 text-slate-100 focus:outline-none focus:border-indigo-500 transition" 
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-400 mb-2">QRIS QR Code (Gambar)</label>
                <div class="space-y-2">
                  <div v-if="form.admin_qris_image_url" class="relative w-32 h-32 bg-white p-2 rounded-xl border border-slate-800 flex items-center justify-center group overflow-hidden shadow-md">
                    <img :src="form.admin_qris_image_url" alt="QRIS Platform" class="max-w-full max-h-full object-contain" />
                    <button 
                      type="button" 
                      @click="removeQrisImage" 
                      class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-red-400 font-bold text-xs"
                    >
                      Hapus
                    </button>
                  </div>
                  
                  <div v-else class="relative">
                    <button 
                      type="button"
                      @click="qrisInput?.click()"
                      :disabled="isUploadingQris"
                      class="w-full sm:w-auto bg-slate-950 border border-slate-800 border-dashed rounded-xl py-3 px-4 text-slate-400 hover:text-slate-200 hover:border-slate-700 transition flex items-center justify-center gap-2 cursor-pointer font-bold"
                    >
                      <span v-if="isUploadingQris">⏳ Mengunggah...</span>
                      <span v-else>📁 Upload QRIS Image</span>
                    </button>
                    <input 
                      type="file" 
                      ref="qrisInput" 
                      accept="image/*" 
                      class="hidden" 
                      @change="handleQrisUpload" 
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- System Maintenance Mode -->
        <div class="space-y-4 pt-4 border-t border-slate-800/80">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Keamanan &amp; Maintenance</h3>
          <div class="flex items-center gap-3 bg-slate-950/30 border border-slate-850 p-4 rounded-2xl">
            <input 
              type="checkbox" 
              v-model="form.maintenance_mode" 
              id="chk-maintenance" 
              class="w-5 h-5 rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-0 cursor-pointer"
            />
            <div>
              <label for="chk-maintenance" class="block text-xs font-bold text-slate-300 cursor-pointer">Aktifkan Mode Pemeliharaan (Maintenance Mode)</label>
              <span class="text-[10px] text-slate-550 leading-relaxed mt-0.5 block">
                Jika diaktifkan, semua pengguna tenant non-admin akan dibatasi aksesnya sementara waktu demi perawatan sistem.
              </span>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="pt-4 border-t border-slate-800/80 flex justify-end">
          <button 
            type="submit" 
            :disabled="isSaving"
            class="theme-btn py-3 px-8 rounded-xl font-bold transition disabled:opacity-50"
          >
            {{ isSaving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api, { uploadFile } from '@/services/api';

const isLoading = ref(true);
const isSaving = ref(false);
const isUploadingQris = ref(false);
const qrisInput = ref<HTMLInputElement | null>(null);
const form = ref({} as any);

async function fetchSettings() {
  isLoading.value = true;
  try {
    const res = await api.get('/admin/settings');
    if (res.data.status === 'success') {
      form.value = res.data.data;
    }
  } catch (err: any) {
    alert('Gagal memuat pengaturan platform.');
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchSettings();
});

async function handleQrisUpload(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file) return;

  isUploadingQris.value = true;
  try {
    const res = await uploadFile('/admin/settings/upload-qris', file);
    if (res.data.status === 'success') {
      form.value.admin_qris_image_url = res.data.url;
    } else {
      alert(res.data.message || 'Gagal mengunggah QRIS');
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal mengunggah QRIS');
  } finally {
    isUploadingQris.value = false;
    if (qrisInput.value) {
      qrisInput.value.value = '';
    }
  }
}

function removeQrisImage() {
  form.value.admin_qris_image_url = null;
}

async function saveSettings() {
  isSaving.value = true;
  try {
    const res = await api.put('/admin/settings', form.value);
    if (res.data.status === 'success') {
      alert(res.data.message);
      fetchSettings();
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan pengaturan platform.');
  } finally {
    isSaving.value = false;
  }
}
</script>
