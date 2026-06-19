<template>
  <div v-if="isOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm">
    <div
      class="modal-card rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6"
      :style="{
        backgroundColor: 'var(--bg-surface)',
        borderColor: 'var(--border-color)',
        border: '1px solid',
      }"
    >
      <h3 class="text-xl font-bold flex items-center gap-2" :style="{ color: 'var(--text-primary)' }">
        <span>Catat Pembayaran Piutang</span>
      </h3>

      <form @submit.prevent="submitPayment" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold mb-2" :style="{ color: 'var(--text-secondary)' }">Jumlah Bayar (Rp)</label>
          <input
            v-model="form.amount"
            type="number"
            required
            min="1"
            :max="maxAmount"
            class="modal-input w-full rounded-xl py-3 px-4 focus:outline-none transition"
            :style="inputStyle"
          />
          <p class="text-xs mt-1" :style="{ color: 'var(--text-muted)' }">Sisa Piutang: Rp {{ formatCurrency(maxAmount) }}</p>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2" :style="{ color: 'var(--text-secondary)' }">Metode Pembayaran</label>
          <select
            v-model="form.payment_method"
            required
            class="modal-input w-full rounded-xl py-3 px-4 focus:outline-none transition"
            :style="inputStyle"
          >
            <option value="cash">Tunai (Cash)</option>
            <option value="transfer">Transfer Bank</option>
            <option value="qris">QRIS</option>
            <option value="credit">Kredit</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2" :style="{ color: 'var(--text-secondary)' }">Tanggal Pembayaran</label>
          <input
            v-model="form.payment_date"
            type="date"
            required
            class="modal-input w-full rounded-xl py-3 px-4 focus:outline-none transition"
            :style="{ ...inputStyle, colorScheme: isLightTheme ? 'light' : 'dark' }"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2" :style="{ color: 'var(--text-secondary)' }">Catatan (Opsional)</label>
          <input
            v-model="form.notes"
            type="text"
            placeholder="Contoh: Titip ke kasir atau referensi TRX"
            class="modal-input w-full rounded-xl py-3 px-4 focus:outline-none transition"
            :style="inputStyle"
          />
        </div>

        <div v-if="errorMessage" class="bg-red-500/10 border border-red-500/30 rounded-xl p-3 text-center text-xs text-red-400">
          {{ errorMessage }}
        </div>

        <div class="flex gap-3 pt-2">
          <button
            type="button"
            @click="close"
            class="flex-1 py-3 px-4 rounded-xl font-bold text-sm transition"
            :style="{
              backgroundColor: 'var(--bg-surface-alt)',
              border: '1px solid var(--border-color)',
              color: 'var(--text-secondary)',
            }"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="flex-1 py-3 px-4 rounded-xl font-bold text-sm transition disabled:opacity-50 text-white"
            style="background: linear-gradient(135deg, #059669, #0d9488)"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Pembayaran' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const props = defineProps<{
  isOpen: boolean;
  orderId: number | string | null;
  maxAmount: number;
}>();

const emit = defineEmits<{
  close: [];
  success: [payload: unknown];
}>();

const authStore = useAuthStore();
const isSubmitting = ref(false);
const errorMessage = ref('');

const form = ref({
  amount: '',
  payment_method: 'cash',
  payment_date: new Date().toISOString().slice(0, 10),
  notes: '',
});

/** Detect if current theme is light (needs dark text on light bg) */
const isLightTheme = computed(() => {
  const tenant = (authStore.tenant as any);
  return tenant?.catalog_setting?.theme === 'light';
});

/** Input style driven by CSS variables from the parent dashboard-root */
const inputStyle = computed(() => ({
  backgroundColor: 'var(--bg-page)',
  border: '1px solid var(--border-color)',
  color: 'var(--text-primary)',
}));

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    form.value.amount = props.maxAmount ? String(props.maxAmount) : '';
    form.value.payment_method = 'cash';
    form.value.payment_date = new Date().toISOString().slice(0, 10);
    form.value.notes = '';
    errorMessage.value = '';
  }
});

function close() {
  emit('close');
}

async function submitPayment() {
  if (!props.orderId) return;

  isSubmitting.value = true;
  errorMessage.value = '';

  try {
    const response = await api.post(`/orders/${props.orderId}/payments`, form.value);

    if (response.data.status === 'success') {
      emit('success', response.data.data);
      close();
    } else {
      errorMessage.value = response.data.message || 'Gagal menyimpan pembayaran';
    }
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan koneksi';
  } finally {
    isSubmitting.value = false;
  }
}

function formatCurrency(amount: number) {
  const num = Number(amount);
  return Number.isNaN(num) ? '0' : num.toLocaleString('id-ID');
}
</script>

<style scoped>
.modal-input:focus {
  border-color: var(--theme-primary) !important;
  box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.15);
  outline: none;
}
.modal-input::placeholder {
  color: var(--text-muted);
  opacity: 1;
}
/* Date picker calendar icon color on light theme */
.modal-input[type="date"]::-webkit-calendar-picker-indicator {
  filter: var(--date-picker-filter, none);
}
</style>
