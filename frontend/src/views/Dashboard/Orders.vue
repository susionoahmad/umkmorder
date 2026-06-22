<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-bold text-slate-200">Daftar Pesanan</h3>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-slate-400">Memuat pesanan...</p>
    </div>

    <!-- Error Alert -->
    <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 text-center text-red-400">
      {{ error }}
    </div>

    <div v-else class="space-y-6">
      <div v-if="orders.length === 0" class="text-center py-16 bg-slate-900/40 border border-slate-800 rounded-2xl text-slate-500 text-sm">
        Belum ada pesanan masuk ke toko Anda.
      </div>

      <!-- Orders Table -->
      <div v-else class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 shadow-md backdrop-blur-md">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase font-bold">
                <th class="pb-3 font-semibold">Invoice</th>
                <th class="pb-3 font-semibold">Pelanggan</th>
                <th class="pb-3 font-semibold">Tanggal</th>
                <th class="pb-3 font-semibold">Metode</th>
                <th class="pb-3 font-semibold">Pembayaran</th>
                <th class="pb-3 font-semibold">Total</th>
                <th class="pb-3 font-semibold">Status</th>
                <th class="pb-3 font-semibold text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="order in orders" 
                :key="order.id" 
                class="border-b border-slate-850 hover:bg-slate-850/20 text-slate-300 text-sm transition animate-fadeIn"
              >
                <td class="py-4 font-bold text-slate-200">{{ order.invoice_number }}</td>
                <td class="py-4">
                  <div>
                    <p class="font-semibold text-slate-200">{{ order.customer?.name || '-' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ order.customer?.whatsapp || '-' }}</p>
                  </div>
                </td>
                <td class="py-4">{{ formatDate(order.order_date) }}</td>
                <td class="py-4 capitalize">{{ order.order_type }}</td>
                <td class="py-4">{{ formatPaymentPreference(order.payment_preference) }}</td>
                <td class="py-4 font-bold text-indigo-400">Rp {{ formatRupiah(order.grand_total) }}</td>
                <td class="py-4">
                  <div class="flex flex-col gap-1 items-start">
                    <span :class="['px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider', getStatusClass(order.status)]">
                      {{ order.status }}
                    </span>
                    <span 
                      v-if="order.status === 'completed'" 
                      :class="[
                        'px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wide border',
                        (!order.receivable || order.receivable.status === 'paid') 
                          ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
                          : 'bg-red-500/10 text-red-400 border-red-500/20'
                      ]"
                    >
                      {{ (!order.receivable || order.receivable.status === 'paid') ? 'Lunas' : 'Piutang' }}
                    </span>
                  </div>
                </td>
                <td class="py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button 
                      @click="openDetails(order.id)"
                      class="py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-750 text-indigo-400 font-semibold text-xs transition border border-slate-700/30"
                    >
                      Detail
                    </button>
                    <button
                      v-if="order.status === 'draft' || order.status === 'new'"
                      @click="confirmOrderViaWhatsApp(order)"
                      class="py-1.5 px-3 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold text-xs transition border border-emerald-500/20"
                    >
                      Konfirmasi WA
                    </button>
                    
                    <select 
                      :value="order.status"
                      @change="updateStatus(order.id, ($event.target as HTMLSelectElement).value)"
                      class="bg-slate-950 border border-slate-850 rounded-lg py-1.5 px-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500 transition"
                    >
                      <option value="draft">Draft</option>
                      <option value="new">New</option>
                      <option value="processing">Processing</option>
                      <option value="shipped">Shipped</option>
                      <option value="completed">Completed</option>
                      <option value="cancelled">Cancelled</option>
                    </select>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <div v-if="showModal && activeOrder" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm" :style="themeVars">
      <div class="rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6 max-h-[85vh] overflow-y-auto flex flex-col" :style="{ backgroundColor: 'var(--bg-surface)', border: '1px solid var(--border-color)' }">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-start shrink-0 no-print">
          <div>
            <h3 class="text-2xl font-black" :style="{ color: 'var(--text-primary)' }">{{ activeOrder.invoice_number }}</h3>
            <p class="text-xs mt-1" :style="{ color: 'var(--text-muted)' }">Status: <span class="capitalize font-bold">{{ activeOrder.status }}</span></p>
          </div>
          <div class="flex items-center gap-3">
            <button 
              v-if="isReceiptAvailable" 
              @click="toggleReceiptMode"
              class="py-1.5 px-3 rounded-xl border text-xs font-bold transition-all duration-200"
              :class="showReceiptMode ? 'bg-indigo-500/20 text-indigo-400 border-indigo-500/40 hover:bg-indigo-500/30' : 'bg-slate-800 hover:bg-slate-750 text-indigo-400 border-slate-700'"
            >
              {{ showReceiptMode ? '⬅️ Detail' : '🧾 Struk/Invoice' }}
            </button>
            <button @click="closeModal" class="text-xl font-bold transition" :style="{ color: 'var(--text-secondary)' }">×</button>
          </div>
        </div>

        <!-- Normal Details Content -->
        <div v-if="!showReceiptMode" class="space-y-4 border-t pt-4 overflow-y-auto pr-1 flex-1" :style="{ borderColor: 'var(--border-color)' }">
          <!-- Customer Details -->
          <div>
            <h4 class="text-xs font-bold uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Detail Pelanggan</h4>
            <div class="mt-2 text-sm space-y-1" :style="{ color: 'var(--text-secondary)' }">
              <p><span :style="{ color: 'var(--text-muted)' }">Nama:</span> {{ activeOrder.customer?.name }}</p>
              <p><span :style="{ color: 'var(--text-muted)' }">WhatsApp:</span> {{ activeOrder.customer?.whatsapp }}</p>
              <p><span :style="{ color: 'var(--text-muted)' }">Preferensi Pembayaran:</span> {{ formatPaymentPreference(activeOrder.payment_preference) }}</p>
              <p v-if="activeOrder.payment_due_date"><span :style="{ color: 'var(--text-muted)' }">Jatuh Tempo:</span> {{ formatDate(activeOrder.payment_due_date) }}</p>
              <p><span :style="{ color: 'var(--text-muted)' }">Alamat:</span> {{ activeOrder.customer?.address || '-' }}</p>
              <p v-if="activeOrder.location_link">
                <span :style="{ color: 'var(--text-muted)' }">Pin Lokasi:</span>
                <a :href="getGoogleMapsLink(activeOrder.location_link)" target="_blank" class="text-indigo-400 hover:text-indigo-300 font-semibold underline inline-flex items-center gap-1 ml-1">
                  Buka di Google Maps 🗺️
                </a>
              </p>
              <p v-if="activeOrder.notes"><span :style="{ color: 'var(--text-muted)' }">Catatan:</span> {{ activeOrder.notes }}</p>
            </div>
          </div>

          <!-- Payment Status Details -->
          <div class="border-t pt-4" :style="{ borderColor: 'var(--border-color)' }">
            <h4 class="text-xs font-bold uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Status Pembayaran</h4>
            <div class="mt-2 text-sm space-y-1" :style="{ color: 'var(--text-secondary)' }">
              <p>
                <span :style="{ color: 'var(--text-muted)' }">Status:</span>
                <span
                  :class="[
                    'ml-2 px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider border',
                    (!activeOrder.receivable || activeOrder.receivable.status === 'paid')
                      ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                      : 'bg-red-500/10 text-red-400 border-red-500/20'
                  ]"
                >
                  {{ (!activeOrder.receivable || activeOrder.receivable.status === 'paid') ? 'Lunas' : 'Piutang' }}
                </span>
              </p>
              <p v-if="activeOrder.receivable"><span :style="{ color: 'var(--text-muted)' }">Jumlah Dibayar:</span> Rp {{ formatRupiah(activeOrder.receivable.paid_amount) }}</p>
              <div v-if="activeOrder.receivable" class="flex flex-wrap items-center gap-3">
                <p><span :style="{ color: 'var(--text-muted)' }">Sisa Piutang:</span> Rp {{ formatRupiah(activeOrder.receivable.remaining_amount) }}</p>
                <button
                  v-if="activeOrder.receivable.status !== 'paid'"
                  type="button"
                  @click="openPaymentModal(activeOrder)"
                  class="py-1.5 px-3 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold text-xs transition border border-emerald-500/20"
                >
                  Catat Pembayaran
                </button>
              </div>
              <p v-else><span :style="{ color: 'var(--text-muted)' }">Jumlah Tagihan:</span> Rp {{ formatRupiah(activeOrder.grand_total) }}</p>
            </div>
          </div>

          <!-- Items Ordered -->
          <div>
            <h4 class="text-xs font-bold uppercase tracking-wider mb-2" :style="{ color: 'var(--text-muted)' }">Item Pesanan</h4>
            <div class="space-y-2">
              <div
                v-for="item in activeOrder.items"
                :key="item.id"
                class="flex justify-between text-sm py-2 px-3 rounded-xl"
                :style="{ backgroundColor: 'var(--bg-surface-alt)', border: '1px solid var(--border-color)' }"
              >
                <span :style="{ color: 'var(--text-secondary)' }">{{ item.product?.name }} <span :style="{ color: 'var(--text-muted)' }">x{{ item.quantity }} {{ item.product?.unit || 'pcs' }}</span></span>
                <span class="font-bold" :style="{ color: 'var(--text-secondary)' }">Rp {{ formatRupiah(item.total) }}</span>
              </div>
            </div>
          </div>

          <!-- Totals -->
          <div class="border-t pt-4 space-y-1.5 text-sm" :style="{ borderColor: 'var(--border-color)' }">
            <div class="flex justify-between" :style="{ color: 'var(--text-muted)' }">
              <span>Subtotal</span>
              <span>Rp {{ formatRupiah(activeOrder.subtotal) }}</span>
            </div>
            <div class="flex justify-between" :style="{ color: 'var(--text-muted)' }">
              <span>Diskon</span>
              <span>- Rp {{ formatRupiah(activeOrder.discount) }}</span>
            </div>
            <div class="flex justify-between" :style="{ color: 'var(--text-muted)' }">
              <span>Biaya Pengiriman</span>
              <span>Rp {{ formatRupiah(activeOrder.shipping_cost) }}</span>
            </div>
            <div class="flex justify-between font-extrabold text-base pt-2 border-t" :style="{ color: 'var(--text-primary)', borderColor: 'var(--border-color)' }">
              <span>Grand Total</span>
              <span :style="{ color: 'var(--theme-primary)' }">Rp {{ formatRupiah(activeOrder.grand_total) }}</span>
            </div>
          </div>
        </div>

        <!-- Receipt / Struk Content -->
        <div v-else class="printable-receipt-area bg-slate-950 border border-slate-850 rounded-2xl p-6 font-mono text-[11px] text-slate-300 relative overflow-y-auto flex-1 select-text" :style="{ borderColor: 'var(--border-color)' }">
          <!-- Watermark Stamp -->
          <div 
            class="watermark absolute right-6 bottom-24 border-4 font-extrabold text-xl uppercase px-3 py-1.5 rounded-xl rotate-[-12deg] opacity-60 select-none pointer-events-none"
            :class="receiptStamp.class"
          >
            {{ receiptStamp.text }}
          </div>

          <!-- Header -->
          <div class="text-center space-y-1">
            <h4 class="text-sm font-black text-slate-100 uppercase">{{ authStore.tenant?.name || 'Toko Kami' }}</h4>
            <p class="text-[10px] text-slate-500">{{ authStore.tenant?.address || '' }}</p>
            <p class="text-[10px] text-slate-500">WA: {{ authStore.tenant?.phone || '' }}</p>
          </div>

          <!-- Divider -->
          <div class="divider border-b border-dashed border-slate-800 my-3"></div>

          <!-- Info -->
          <div class="space-y-1 text-slate-400">
            <div class="flex justify-between">
              <span>No Invoice:</span>
              <span class="font-bold text-slate-200">{{ activeOrder.invoice_number }}</span>
            </div>
            <div class="flex justify-between">
              <span>Tanggal:</span>
              <span>{{ formatDate(activeOrder.order_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Pelanggan:</span>
              <span>{{ activeOrder.customer?.name || '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span>WhatsApp:</span>
              <span>{{ activeOrder.customer?.whatsapp || '-' }}</span>
            </div>
          </div>

          <!-- Divider -->
          <div class="divider border-b border-dashed border-slate-800 my-3"></div>

          <!-- Items -->
          <div class="space-y-2">
            <div v-for="item in activeOrder.items" :key="item.id" class="space-y-0.5">
              <div class="text-slate-200 font-bold text-[11px]">{{ item.product?.name }}</div>
              <div class="flex justify-between text-slate-500 text-[10px]">
                <span>{{ item.quantity }} {{ item.product?.unit || 'pcs' }} x Rp {{ formatRupiah(item.price || (item.total / item.quantity)) }}</span>
                <span class="text-slate-300 font-bold">Rp {{ formatRupiah(item.total) }}</span>
              </div>
            </div>
          </div>

          <!-- Divider -->
          <div class="divider border-b border-dashed border-slate-800 my-3"></div>

          <!-- Totals -->
          <div class="space-y-1 text-slate-400">
            <div class="flex justify-between">
              <span>Subtotal:</span>
              <span>Rp {{ formatRupiah(activeOrder.subtotal) }}</span>
            </div>
            <div v-if="parseFloat(activeOrder.discount) > 0" class="flex justify-between">
              <span>Diskon:</span>
              <span>- Rp {{ formatRupiah(activeOrder.discount) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Ongkos Kirim:</span>
              <span>Rp {{ formatRupiah(activeOrder.shipping_cost) }}</span>
            </div>
            <div class="border-b border-dotted border-slate-850 my-1.5"></div>
            <div class="flex justify-between text-xs font-black text-slate-100">
              <span>TOTAL BAYAR:</span>
              <span :style="{ color: 'var(--theme-primary)' }">Rp {{ formatRupiah(activeOrder.grand_total) }}</span>
            </div>
          </div>

          <!-- Payment History (Installments/Debt) -->
          <div v-if="activeOrder.receivable" class="space-y-1.5 text-slate-400 text-[10px]">
            <div class="divider border-b border-dashed border-slate-800 my-3"></div>
            
            <div v-if="activeOrder.payments && activeOrder.payments.length > 0" class="space-y-1.5">
              <div class="font-bold text-slate-200 text-[11px]">Riwayat Pembayaran:</div>
              <div v-for="payment in activeOrder.payments" :key="payment.id" class="flex justify-between">
                <span>- {{ formatDate(payment.payment_date) }} ({{ formatPaymentMethodName(payment.payment_method) }}):</span>
                <span>Rp {{ formatRupiah(payment.amount) }}</span>
              </div>
              <div class="border-b border-dotted border-slate-850 my-1"></div>
            </div>
            
            <div class="flex justify-between">
              <span>Total Terbayar:</span>
              <span class="font-bold text-slate-350">Rp {{ formatRupiah(activeOrder.receivable.paid_amount) }}</span>
            </div>
            <div class="flex justify-between text-[11px]">
              <span>Sisa Piutang:</span>
              <span class="font-black text-slate-200">Rp {{ formatRupiah(activeOrder.receivable.remaining_amount) }}</span>
            </div>
          </div>

          <!-- Divider -->
          <div class="divider border-b border-dashed border-slate-800 my-3"></div>

          <!-- Footer/Payment Method Info -->
          <div class="text-center space-y-2 text-[9px] text-slate-500">
            <p v-if="!activeOrder.receivable">Metode Pembayaran: <span class="uppercase font-bold text-slate-400">{{ getPaymentMethodName(activeOrder) }}</span></p>
            <p class="mt-4 font-bold text-slate-450 tracking-wider">--- Terima Kasih ---</p>
            <p>Simpan Struk Ini Sebagai Bukti Pembayaran</p>
          </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="flex flex-col gap-3 sticky bottom-0 pt-4 bg-slate-900 border-t border-slate-850 no-print shrink-0" :style="{ backgroundColor: 'var(--bg-surface)', borderColor: 'var(--border-color)' }">
          <div v-if="showReceiptMode" class="flex gap-4">
            <button 
              @click="printReceipt"
              class="flex-1 py-3 px-4 rounded-xl border hover:bg-slate-850 transition font-bold text-sm text-slate-300 flex items-center justify-center gap-2"
              :style="{ backgroundColor: 'var(--bg-surface-alt)', borderColor: 'var(--border-color)', color: 'var(--text-secondary)' }"
            >
              🖨️ Cetak Struk
            </button>
            <button 
              @click="sendReceiptViaWhatsApp(activeOrder)"
              class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500 transition font-bold text-sm text-white flex items-center justify-center gap-2"
            >
              💬 Kirim Struk
            </button>
          </div>
          <button
            @click="closeModal"
            class="w-full py-3.5 px-4 rounded-xl font-bold text-sm transition"
            :style="{ backgroundColor: 'var(--bg-surface-alt)', border: '1px solid var(--border-color)', color: 'var(--text-secondary)' }"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Status Selector Modal for Completed Orders -->
    <div v-if="showPaymentStatusModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm" :style="themeVars">
      <div class="rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6" :style="{ backgroundColor: 'var(--bg-surface)', border: '1px solid var(--border-color)' }">
        <h3 class="text-xl font-bold flex items-center gap-2" :style="{ color: 'var(--text-primary)' }">
          <span>💰</span> Status Pembayaran Pesanan
        </h3>
        <p class="text-sm" :style="{ color: 'var(--text-secondary)' }">
          Pesanan ini akan ditandai sebagai <strong>Selesai (Completed)</strong>. Silakan tentukan status pembayarannya:
        </p>

        <div class="grid grid-cols-2 gap-4">
          <button
            @click="completedPaymentStatus = 'paid'"
            :class="[
              'flex flex-col items-center justify-center p-4 rounded-2xl border transition',
              completedPaymentStatus === 'paid'
                ? 'bg-emerald-500/20 border-emerald-500/60 text-emerald-400 ring-2 ring-emerald-500/30'
                : 'bg-emerald-500/10 border-emerald-500/20 hover:bg-emerald-500/20 text-emerald-400'
            ]"
          >
            <span class="text-2xl mb-2">✅</span>
            <span class="font-bold text-sm">Lunas (Paid)</span>
            <span class="text-[10px] text-emerald-500 mt-1">Pembayaran Penuh</span>
          </button>

          <button
            @click="completedPaymentStatus = 'piutang'"
            :class="[
              'flex flex-col items-center justify-center p-4 rounded-2xl border transition',
              completedPaymentStatus === 'piutang'
                ? 'bg-red-500/20 border-red-500/60 text-red-400 ring-2 ring-red-500/30'
                : 'bg-red-500/10 border-red-500/20 hover:bg-red-500/20 text-red-400'
            ]"
          >
            <span class="text-2xl mb-2">📌</span>
            <span class="font-bold text-sm">Piutang (Debt)</span>
            <span class="text-[10px] text-red-500 mt-1">Bayar Nanti</span>
          </button>
        </div>

        <div v-if="completedPaymentStatus === 'paid'" class="space-y-2">
          <label class="block text-sm font-semibold" :style="{ color: 'var(--text-secondary)' }">Metode Pembayaran</label>
          <select
            v-model="completedPaymentMethod"
            class="w-full rounded-xl py-3 px-4 focus:outline-none transition"
            :style="{ backgroundColor: 'var(--bg-page)', border: '1px solid var(--border-color)', color: 'var(--text-primary)' }"
          >
            <option value="cash">Tunai (Cash)</option>
            <option value="transfer">Transfer Bank</option>
            <option value="qris">QRIS</option>
            <option value="credit">Kredit</option>
          </select>
        </div>

        <div v-if="completedPaymentStatus === 'piutang'" class="space-y-2">
          <label class="block text-sm font-semibold" :style="{ color: 'var(--text-secondary)' }">Jatuh Tempo</label>
          <input
            v-model="completedPaymentDueDate"
            type="date"
            class="w-full rounded-xl py-3 px-4 focus:outline-none transition"
            :style="{ backgroundColor: 'var(--bg-page)', border: '1px solid var(--border-color)', color: 'var(--text-primary)' }"
          />
        </div>

        <div class="flex gap-3 pt-2">
          <button
            @click="cancelCompletedStatus"
            class="flex-1 py-3 px-4 rounded-xl font-bold text-sm transition"
            :style="{ backgroundColor: 'var(--bg-surface-alt)', border: '1px solid var(--border-color)', color: 'var(--text-secondary)' }"
          >
            Batal
          </button>
          <button
            @click="submitCompletedStatus"
            :disabled="!completedPaymentStatus"
            class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 font-bold text-sm transition text-white disabled:opacity-50"
          >
            Konfirmasi
          </button>
        </div>
      </div>
    </div>

    <RecordPaymentModal
      :is-open="isPaymentModalOpen"
      :order-id="paymentOrderId"
      :max-amount="paymentMaxAmount"
      :theme-vars="themeVars"
      @close="closePaymentModal"
      @success="handlePaymentSuccess"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import RecordPaymentModal from '@/components/RecordPaymentModal.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const THEMES: Record<string, Record<string, string>> = {
  default: { '--theme-primary': '#6366f1', '--theme-secondary': '#8b5cf6', '--theme-primary-rgb': '99, 102, 241', '--bg-page': '#020617', '--bg-surface': 'rgba(15,23,42,0.95)', '--bg-surface-alt': '#1e293b', '--border-color': '#334155', '--text-primary': '#f8fafc', '--text-secondary': '#94a3b8', '--text-muted': '#475569' },
  red:     { '--theme-primary': '#ef4444', '--theme-secondary': '#b91c1c', '--theme-primary-rgb': '239, 68, 68',   '--bg-page': '#0f0202', '--bg-surface': 'rgba(26,5,5,0.95)',   '--bg-surface-alt': '#1f0808', '--border-color': '#3d1414', '--text-primary': '#fef2f2', '--text-secondary': '#fca5a5', '--text-muted': '#7f1d1d' },
  emerald: { '--theme-primary': '#10b981', '--theme-secondary': '#059669', '--theme-primary-rgb': '16, 185, 129',  '--bg-page': '#011208', '--bg-surface': 'rgba(2,30,14,0.95)',  '--bg-surface-alt': '#032d14', '--border-color': '#064e23', '--text-primary': '#ecfdf5', '--text-secondary': '#6ee7b7', '--text-muted': '#166534' },
  light:   { '--theme-primary': '#4f46e5', '--theme-secondary': '#7c3aed', '--theme-primary-rgb': '79, 70, 229',   '--bg-page': '#f8fafc', '--bg-surface': 'rgba(255,255,255,0.98)', '--bg-surface-alt': '#f1f5f9', '--border-color': '#cbd5e1', '--text-primary': '#0f172a', '--text-secondary': '#475569', '--text-muted': '#94a3b8' },
  warm:    { '--theme-primary': '#f59e0b', '--theme-secondary': '#d97706', '--theme-primary-rgb': '245, 158, 11',  '--bg-page': '#0d0900', '--bg-surface': 'rgba(26,18,0,0.95)',  '--bg-surface-alt': '#271e00', '--border-color': '#3d2f00', '--text-primary': '#fffbeb', '--text-secondary': '#fde68a', '--text-muted': '#92400e' },
  ocean:   { '--theme-primary': '#0ea5e9', '--theme-secondary': '#0284c7', '--theme-primary-rgb': '14, 165, 233',  '--bg-page': '#00060e', '--bg-surface': 'rgba(0,20,40,0.95)',  '--bg-surface-alt': '#001a30', '--border-color': '#003060', '--text-primary': '#e0f2fe', '--text-secondary': '#7dd3fc', '--text-muted': '#0c4a6e' },
};

const themeVars = computed(() => {
  const key = (authStore.tenant as any)?.catalog_setting?.theme || 'default';
  return THEMES[key] ?? THEMES['default'];
});

const isLoading = ref(true);
const error = ref(null as string | null);
const orders = ref([] as any[]);

// Detail Modal
const showModal = ref(false);
const activeOrder = ref(null as any);
const showReceiptMode = ref(false);

const isReceiptAvailable = computed(() => {
  if (!activeOrder.value) return false;
  return activeOrder.value.status === 'completed';
});

const receiptStamp = computed(() => {
  if (!activeOrder.value) return { text: '', class: '' };
  
  const rec = activeOrder.value.receivable;
  if (!rec) {
    return { text: 'LUNAS', class: 'border-emerald-500 text-emerald-500' };
  }
  
  if (rec.status === 'paid' || parseFloat(rec.remaining_amount) <= 0) {
    return { text: 'LUNAS', class: 'border-emerald-500 text-emerald-500' };
  }
  
  const paid = parseFloat(rec.paid_amount) || 0;
  if (paid > 0) {
    return { text: 'CICILAN', class: 'border-amber-500 text-amber-500' };
  }
  
  return { text: 'BELUM LUNAS', class: 'border-red-500 text-red-500' };
});

function toggleReceiptMode() {
  showReceiptMode.value = !showReceiptMode.value;
}

function getPaymentMethodName(order: any): string {
  if (order.payments && order.payments.length > 0) {
    const methods = order.payments.map((p: any) => {
      const m = p.payment_method || '';
      if (m === 'cash') return 'Tunai';
      if (m === 'transfer') return 'Transfer Bank';
      if (m === 'qris') return 'QRIS';
      if (m === 'credit') return 'Kredit';
      return m;
    });
    return [...new Set(methods)].join(', ');
  }
  return formatPaymentPreference(order.payment_preference);
}

function formatPaymentMethodName(method?: string): string {
  const map: Record<string, string> = {
    cash: 'Tunai',
    transfer: 'Transfer Bank',
    qris: 'QRIS',
    credit: 'Kredit',
  };
  return map[method || ''] || method || '-';
}

function buildReceiptMessage(order: any): string {
  const tenant = authStore.tenant as any;
  const lines = [
    `🧾 *STRUK PEMBAYARAN*`,
    `*${tenant?.name || 'Toko Kami'}*`,
    `----------------------------------------`,
    `No Invoice: ${order.invoice_number}`,
    `Tanggal   : ${formatDate(order.order_date)}`,
    `Pelanggan : ${order.customer?.name || '-'}`,
    `----------------------------------------`,
  ];

  order.items.forEach((item: any) => {
    const unit = item.product?.unit || 'pcs';
    const subtotalFormatted = formatRupiah(item.total);
    const priceFormatted = formatRupiah(item.price || (item.total / item.quantity));
    lines.push(`*${item.product?.name || 'Produk'}*`);
    lines.push(`  ${item.quantity} ${unit} x Rp ${priceFormatted} = Rp ${subtotalFormatted}`);
  });

  lines.push(`----------------------------------------`);
  lines.push(`Subtotal  : Rp ${formatRupiah(order.subtotal)}`);
  if (parseFloat(order.discount) > 0) {
    lines.push(`Diskon    : - Rp ${formatRupiah(order.discount)}`);
  }
  lines.push(`Ongkir    : Rp ${formatRupiah(order.shipping_cost)}`);
  lines.push(`----------------------------------------`);
  lines.push(`*TOTAL     : Rp ${formatRupiah(order.grand_total)}*`);

  // Payments and remaining debt history
  const rec = order.receivable;
  if (rec) {
    if (order.payments && order.payments.length > 0) {
      lines.push(`----------------------------------------`);
      lines.push(`*RIWAYAT PEMBAYARAN:*`);
      order.payments.forEach((p: any) => {
        lines.push(`- ${formatDate(p.payment_date)} (${formatPaymentMethodName(p.payment_method)}): Rp ${formatRupiah(p.amount)}`);
      });
      lines.push(`*TOTAL TERBAYAR: Rp ${formatRupiah(rec.paid_amount)}*`);
    }
    lines.push(`*SISA PIUTANG  : Rp ${formatRupiah(rec.remaining_amount)}*`);
  } else {
    lines.push(`*Metode    : ${getPaymentMethodName(order)}*`);
  }
  
  const stampText = receiptStamp.value.text;
  lines.push(`*Status    : ${stampText} ${stampText === 'LUNAS' ? '✅' : '📌'}*`);
  lines.push(`----------------------------------------`);
  lines.push(`Terima kasih telah berbelanja di toko kami! 🙏`);

  return lines.join('\n');
}

function sendReceiptViaWhatsApp(order: any) {
  const phone = normalizePhone(order.customer?.whatsapp);
  if (!phone) {
    alert('Nomor WhatsApp pelanggan tidak tersedia.');
    return;
  }
  const message = buildReceiptMessage(order);
  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
}

function printReceipt() {
  window.print();
}

// Payment Status Selection Modal
const showPaymentStatusModal = ref(false);
const completedOrderId = ref<number | null>(null);
const completedPaymentStatus = ref<'paid' | 'piutang' | null>(null);
const completedPaymentMethod = ref('cash');
const completedPaymentDueDate = ref(defaultDueDate());

// Record Payment Modal
const isPaymentModalOpen = ref(false);
const paymentOrderId = ref<number | null>(null);
const paymentMaxAmount = ref(0);

async function fetchOrders() {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await api.get('/orders');
    if (response.data.status === 'success') {
      orders.value = response.data.data;
    } else {
      error.value = response.data.message || 'Gagal memuat pesanan';
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal terhubung ke server';
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchOrders();

  if (route.query.open) {
    const orderId = parseInt(route.query.open as string, 10);
    if (!isNaN(orderId)) {
      openDetails(orderId);
    }
  }
});

watch(() => route.query.open, (newOpen) => {
  if (newOpen) {
    const orderId = parseInt(newOpen as string, 10);
    if (!isNaN(orderId)) {
      openDetails(orderId);
    }
  } else {
    showModal.value = false;
    activeOrder.value = null;
  }
});

async function openDetails(id: number) {
  try {
    const response = await api.get(`/orders/${id}`);
    if (response.data.status === 'success') {
      activeOrder.value = response.data.data;
      showReceiptMode.value = false;
      showModal.value = true;
    }
  } catch (err: any) {
    alert('Gagal memuat detail pesanan');
  }
}

function closeModal() {
  showModal.value = false;
  activeOrder.value = null;
  showReceiptMode.value = false;
  if (route.query.open) {
    router.replace({ query: { ...route.query, open: undefined } });
  }
}

function openPaymentModal(order: any) {
  paymentOrderId.value = order.id;
  paymentMaxAmount.value = Number(order.receivable?.remaining_amount || 0);
  isPaymentModalOpen.value = true;
}

function closePaymentModal() {
  isPaymentModalOpen.value = false;
  paymentOrderId.value = null;
  paymentMaxAmount.value = 0;
}

async function handlePaymentSuccess() {
  const activeOrderId = activeOrder.value?.id;

  await fetchOrders();

  if (activeOrderId) {
    await openDetails(activeOrderId);
  }
}

async function updateStatus(id: number, status: string) {
  if (status === 'completed') {
    completedOrderId.value = id;
    showPaymentStatusModal.value = true;
    return;
  }

  try {
    const response = await api.put(`/orders/${id}`, { status });
    if (response.data.status === 'success') {
      const order = orders.value.find(o => o.id === id);
      if (order) {
        order.status = status;
        order.receivable = response.data.data.receivable;
      }
    } else {
      alert(response.data.message || 'Gagal memperbarui status');
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal memperbarui status');
  }
}

async function confirmOrderViaWhatsApp(order: any) {
  let targetOrder = order;
  const paymentDueDate = getConfirmationDueDate(order);
  if (paymentDueDate === null) return;

  if (order.status === 'draft' || paymentDueDate) {
    try {
      const payload: any = { status: 'new' };
      if (paymentDueDate) payload.payment_due_date = paymentDueDate;
      const response = await api.put(`/orders/${order.id}`, payload);
      if (response.data.status === 'success') {
        targetOrder = response.data.data;
        const row = orders.value.find(o => o.id === order.id);
        if (row) {
          row.status = 'new';
          row.payment_due_date = paymentDueDate || row.payment_due_date;
        }
      }
    } catch (err: any) {
      alert(err.response?.data?.message || 'Gagal mengonfirmasi pesanan');
      return;
    }
  }

  const detailResponse = await api.get(`/orders/${order.id}`);
  if (detailResponse.data.status === 'success') {
    targetOrder = detailResponse.data.data;
  }

  openWhatsAppConfirmation(targetOrder);
}

function getConfirmationDueDate(order: any): string | null | undefined {
  if (!['tempo', 'credit'].includes(order.payment_preference)) return undefined;
  if (order.payment_due_date) return order.payment_due_date;

  const value = prompt('Masukkan tanggal jatuh tempo pembayaran (YYYY-MM-DD):', defaultDueDate());
  if (value === null) return null;
  return value.trim() || defaultDueDate();
}

function openWhatsAppConfirmation(order: any) {
  const phone = normalizePhone(order.customer?.whatsapp);
  if (!phone) {
    alert('Nomor WhatsApp pelanggan tidak tersedia.');
    return;
  }

  const message = buildConfirmationMessage(order);
  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
}

function buildConfirmationMessage(order: any): string {
  const tenant = authStore.tenant as any;
  const settings = tenant?.catalog_setting || tenant?.catalogSetting || {};
  const preference = order.payment_preference || 'cash';
  const lines = [
    `Halo ${order.customer?.name || 'Kak'},`,
    '',
    `Pesanan ${order.invoice_number} sudah kami tinjau dan dikonfirmasi.`,
    `Total tagihan: Rp ${formatRupiah(order.grand_total)}`,
    `Preferensi pembayaran: ${formatPaymentPreference(preference)}`,
  ];

  if (preference === 'transfer') {
    lines.push('', 'Silakan transfer ke rekening berikut:', settings.bank_transfer_info || '-');
  } else if (preference === 'qris') {
    lines.push('', 'Silakan bayar melalui QRIS berikut:');
    if (settings.qris_image_url) {
      lines.push(settings.qris_image_url);
    }
    if (settings.qris_info) {
      lines.push(settings.qris_info);
    }
    if (!settings.qris_image_url && !settings.qris_info) {
      lines.push('-');
    }
  } else if (preference === 'tempo' || preference === 'credit') {
    const dueDate = order.payment_due_date ? formatDate(order.payment_due_date) : 'akan dikonfirmasi admin';
    lines.push('', `Pembayaran ${formatPaymentPreference(preference).toLowerCase()} dengan jatuh tempo: ${dueDate}.`);
  }

  lines.push('', 'Mohon balas pesan ini untuk konfirmasi pembayaran atau jika ada perubahan pesanan. Terima kasih.');
  return lines.join('\n');
}

async function submitCompletedStatus() {
  if (!completedOrderId.value || !completedPaymentStatus.value) return;
  const id = completedOrderId.value;
  try {
    const payload: any = {
      status: 'completed',
      payment_status: completedPaymentStatus.value
    };

    if (completedPaymentStatus.value === 'paid') {
      payload.payment_method = completedPaymentMethod.value;
    } else if (completedPaymentStatus.value === 'piutang') {
      payload.payment_due_date = completedPaymentDueDate.value;
    }

    const response = await api.put(`/orders/${id}`, payload);
    if (response.data.status === 'success') {
      const order = orders.value.find(o => o.id === id);
      if (order) {
        order.status = 'completed';
        order.receivable = response.data.data.receivable;
      }
    } else {
      alert(response.data.message || 'Gagal memperbarui status');
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal memperbarui status');
  } finally {
    showPaymentStatusModal.value = false;
    completedOrderId.value = null;
    completedPaymentStatus.value = null;
    completedPaymentMethod.value = 'cash';
    completedPaymentDueDate.value = defaultDueDate();
  }
}

function cancelCompletedStatus() {
  showPaymentStatusModal.value = false;
  completedOrderId.value = null;
  completedPaymentStatus.value = null;
  completedPaymentMethod.value = 'cash';
  completedPaymentDueDate.value = defaultDueDate();
  fetchOrders();
}

function normalizePhone(value?: string): string {
  if (!value) return '';
  let phone = value.replace(/\D/g, '');
  if (phone.startsWith('0')) phone = '62' + phone.slice(1);
  return phone;
}

function defaultDueDate(): string {
  const date = new Date();
  date.setDate(date.getDate() + 7);
  return date.toISOString().slice(0, 10);
}

function getGoogleMapsLink(val: string): string {
  if (!val) return '#';
  const trimmed = val.trim();
  if (trimmed.startsWith('http')) return trimmed;
  const coordPattern = /(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)/;
  const match = trimmed.match(coordPattern);
  if (match) return `https://maps.google.com/?q=${match[1]},${match[2]}`;
  return `https://maps.google.com/?q=${encodeURIComponent(trimmed)}`;
}

function formatRupiah(val: any): string {
  const num = parseFloat(val);
  if (isNaN(num)) return '0';
  return num.toLocaleString('id-ID');
}

function formatDate(dateStr: string): string {
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatPaymentPreference(preference?: string): string {
  const map: Record<string, string> = {
    cash: 'Tunai',
    transfer: 'Transfer Bank',
    qris: 'QRIS',
    tempo: 'Tempo',
    credit: 'Kredit',
  };
  return map[preference || 'cash'] || preference || '-';
}

function getStatusClass(status: string): string {
  if (status === 'completed') return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
  if (status === 'shipped') return 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20';
  if (status === 'processing') return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
  if (status === 'cancelled') return 'bg-red-500/10 text-red-400 border border-red-500/20';
  if (status === 'new') return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
  return 'bg-slate-800 text-slate-400 border border-slate-700';
}
</script>

<style scoped>
@media print {
  /* Hide all dashboard/modal container wrappers */
  body * {
    visibility: hidden;
  }
  
  /* Show only the receipt and its content */
  .printable-receipt-area,
  .printable-receipt-area * {
    visibility: visible !important;
  }
  
  /* Position the receipt at the top left and span full width of the page */
  .printable-receipt-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100% !important;
    max-width: 100% !important;
    background: white !important;
    color: black !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  /* Force display colors on background for printing */
  .printable-receipt-area * {
    color: black !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  /* Hide print buttons */
  .no-print {
    display: none !important;
  }

  /* Custom styling for receipt divider and watermark in print */
  .printable-receipt-area .divider {
    border-color: #000 !important;
  }
  
  .printable-receipt-area .watermark.border-emerald-500 {
    border-color: #10b981 !important;
    color: #10b981 !important;
  }
  
  .printable-receipt-area .watermark.border-amber-500 {
    border-color: #f59e0b !important;
    color: #f59e0b !important;
  }
  
  .printable-receipt-area .watermark.border-red-500 {
    border-color: #ef4444 !important;
    color: #ef4444 !important;
  }

  .printable-receipt-area .watermark {
    opacity: 0.4 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>
