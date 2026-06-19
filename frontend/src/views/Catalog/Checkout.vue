<template>
  <div class="checkout-page" :style="themeVars">
    <div class="checkout-card max-w-xl mx-auto rounded-3xl p-8 shadow-xl backdrop-blur-md">
      <h2 class="text-3xl font-extrabold tracking-tight mb-8" :style="{ color: 'var(--text-primary)' }">
        📝 Form Pemesanan
      </h2>

      <!-- Empty Cart -->
      <div v-if="cartStore.items.length === 0" class="text-center py-12">
        <p class="text-lg mb-6 text-sub">Keranjang belanja Anda kosong.</p>
        <router-link :to="`/${slug}`" class="theme-btn inline-block py-3.5 px-6 rounded-xl font-bold transition duration-300 shadow-md">
          Kembali ke Katalog
        </router-link>
      </div>

      <!-- Filled Cart -->
      <div v-else>
        <!-- Items Summary -->
        <div class="mb-8 pb-6 sep-border border-b">
          <h3 class="text-sm font-semibold uppercase tracking-wider mb-4 text-sub">Ringkasan Pesanan</h3>
          <div class="space-y-4">
            <div
              v-for="item in cartStore.items"
              :key="item.product_id"
              class="item-card flex items-start justify-between rounded-2xl p-4 gap-4"
            >
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-sm truncate" :style="{ color: 'var(--text-primary)' }">{{ item.name }}</h4>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                  <p class="theme-accent-text font-extrabold text-xs">
                    Rp {{ formatRupiah(itemUnitPrice(item)) }}/item
                  </p>
                  <span v-if="item.price_tiers && item.price_tiers.length > 0"
                        class="text-[9px] px-1.5 py-0.5 rounded-full font-bold"
                        style="background:rgba(var(--theme-primary-rgb),0.15); color:var(--theme-primary)">
                    Harga Grosir
                  </span>
                </div>
                <p class="text-xs font-semibold mt-1" :style="{ color: 'var(--text-secondary)' }">
                  Subtotal: Rp {{ formatRupiah(itemSubtotal(item)) }}
                </p>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <button type="button"
                  @click="cartStore.updateQuantity(item.product_id, item.quantity - 1)"
                  class="qty-btn w-8 h-8 rounded-lg font-bold flex items-center justify-center transition">−</button>
                <span class="w-6 text-center text-sm font-extrabold" :style="{ color: 'var(--text-primary)' }">{{ item.quantity }}</span>
                <button type="button"
                  @click="cartStore.updateQuantity(item.product_id, item.quantity + 1)"
                  class="qty-btn w-8 h-8 rounded-lg font-bold flex items-center justify-center transition">+</button>
                <button type="button"
                  @click="cartStore.removeFromCart(item.product_id)"
                  class="w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center transition ml-1"
                  title="Hapus">🗑️</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Checkout Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium mb-2 text-sub">Nama Lengkap</label>
            <input v-model="cartStore.customerName" type="text" required placeholder="Ahmad"
              class="theme-input w-full rounded-xl py-3 px-4 placeholder-muted focus:outline-none transition" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 text-sub">Nomor WhatsApp</label>
            <input v-model="cartStore.customerWhatsapp" type="tel" required placeholder="08987654321"
              class="theme-input w-full rounded-xl py-3 px-4 placeholder-muted focus:outline-none transition" />
            <span class="text-xs mt-1 block text-muted">Pastikan nomor aktif untuk konfirmasi & tagihan via WhatsApp.</span>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 text-sub">Metode Penerimaan</label>
            <select v-model="cartStore.orderType" @change="onOrderTypeChange"
              class="theme-input w-full rounded-xl py-3 px-4 focus:outline-none transition">
              <option value="delivery">Pengiriman Kurir (Delivery)</option>
              <option value="pickup">Ambil Sendiri (Pickup)</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 text-sub">Preferensi Pembayaran</label>
            <select v-model="cartStore.paymentPreference"
              class="theme-input w-full rounded-xl py-3 px-4 focus:outline-none transition">
              <option value="cash">Tunai saat diterima</option>
              <option value="transfer">Transfer Bank</option>
              <option value="qris">QRIS</option>
              <option value="tempo">Tempo</option>
              <option value="credit">Kredit</option>
            </select>
            <span class="text-xs mt-1 block text-muted">Ini hanya preferensi. Admin toko akan mengonfirmasi instruksi pembayaran melalui WhatsApp.</span>
          </div>

          <div v-if="cartStore.orderType === 'delivery'">
            <label class="block text-sm font-medium mb-2 text-sub">Alamat Lengkap</label>
            <textarea v-model="cartStore.customerAddress" required rows="3" placeholder="Sukabumi..."
              class="theme-input w-full rounded-xl py-3 px-4 placeholder-muted focus:outline-none transition resize-none">
            </textarea>
          </div>

          <!-- Google Maps Location Share (delivery only) -->
          <div v-if="cartStore.orderType === 'delivery'" class="location-box rounded-2xl p-4 space-y-3">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-base">📍</span>
              <span class="text-sm font-semibold text-sub">Pin Lokasi via Google Maps</span>
              <span class="text-xs text-muted">(Opsional, membantu kurir menemukan lokasi)</span>
            </div>

            <!-- Detect button -->
            <button
              type="button"
              @click="detectLocation"
              :disabled="geoLoading"
              class="location-btn w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-semibold text-sm transition"
            >
              <span v-if="geoLoading" class="inline-block w-4 h-4 border-2 border-t-transparent rounded-full animate-spin"></span>
              <span v-else>🎯</span>
              {{ geoLoading ? 'Mendeteksi lokasi...' : 'Deteksi Lokasi Otomatis (GPS)' }}
            </button>

            <p v-if="geoError" class="text-xs text-red-400">{{ geoError }}</p>

            <!-- Manual input -->
            <div>
              <label class="block text-xs font-medium text-muted mb-1.5">Atau tempel link Google Maps / koordinat:</label>
              <input
                v-model="cartStore.locationLink"
                @input="onLocationLinkInput"
                type="text"
                placeholder="https://maps.google.com/... atau -6.2088,106.8456"
                class="theme-input w-full rounded-xl py-2.5 px-3 text-sm placeholder-muted focus:outline-none transition"
              />
            </div>

            <!-- Map preview -->
            <div v-if="cartStore.locationLink" class="space-y-2">
              <div v-if="embedUrl" class="map-preview-frame rounded-xl overflow-hidden border" :style="{ borderColor: 'var(--border-color)' }">
                <iframe
                  :src="embedUrl"
                  width="100%"
                  height="160"
                  style="border:0"
                  allowfullscreen
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
              </div>
              <a
                :href="googleMapsLink"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center gap-1.5 text-xs font-semibold theme-accent-text hover:underline"
              >
                <span>🗺️</span> Buka di Google Maps
              </a>
            </div>
          </div>

          <!-- ============================================================ -->
          <!-- SHIPPING COST SECTION -->
          <!-- ============================================================ -->
          <div v-if="cartStore.orderType === 'delivery' && shippingMode !== 'none'" class="shipping-box rounded-2xl p-5 space-y-4">
            <div class="flex items-center gap-2">
              <span class="text-lg">🚚</span>
              <span class="font-bold text-sm" :style="{ color: 'var(--text-primary)' }">Ongkos Kirim</span>
            </div>

            <!-- MODE: ZONE — dropdown pilihan zona -->
            <div v-if="shippingMode === 'zone'">
              <label class="block text-xs font-medium text-muted mb-2">Pilih Zona Pengiriman</label>
              <select
                v-model="cartStore.selectedZone"
                @change="triggerShippingCalc"
                class="theme-input w-full rounded-xl py-2.5 px-3 text-sm focus:outline-none transition"
              >
                <option value="">— Pilih zona —</option>
                <option
                  v-for="zone in shippingZones"
                  :key="zone.name"
                  :value="zone.name"
                >
                  {{ zone.name }} — Rp {{ formatRupiah(zone.cost.toString()) }}
                </option>
              </select>
            </div>

            <!-- MODE: DISTANCE — info jarak -->
            <div v-if="shippingMode === 'distance'" class="text-xs text-muted space-y-1">
              <p>Ongkir dihitung otomatis berdasarkan jarak dari toko ke lokasi Anda.</p>
              <p v-if="storeLocationLabel" class="flex items-center gap-1">
                <span>🏪</span> Lokasi toko: <strong class="text-sub">{{ storeLocationLabel }}</strong>
              </p>
              <div v-if="shippingDistances && shippingDistances.length > 0" class="mt-2">
                <p class="font-semibold text-muted mb-1">Tarif per jarak:</p>
                <div class="space-y-1">
                  <div v-for="(bracket, i) in shippingDistances" :key="i" class="flex justify-between">
                    <span>s.d. {{ bracket.max_km }} km</span>
                    <span class="font-semibold theme-accent-text">
                      {{ bracket.cost === 0 ? 'Gratis' : 'Rp ' + formatRupiah(bracket.cost.toString()) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- MODE: API — placeholder -->
            <div v-if="shippingMode === 'api'" class="text-xs text-muted">
              <p>🔌 Perhitungan ongkir via layanan kurir sedang disiapkan.</p>
            </div>

            <!-- Shipping Calculation Result -->
            <div v-if="cartStore.isCalculatingShipping" class="flex items-center gap-2 text-sm text-muted">
              <span class="inline-block w-4 h-4 border-2 border-t-transparent rounded-full animate-spin" :style="{ borderColor: 'var(--theme-primary)' }"></span>
              Menghitung ongkir...
            </div>

            <div v-else-if="cartStore.shippingError" class="text-xs text-red-400">
              {{ cartStore.shippingError }}
            </div>

            <div v-else-if="shippingCalculated" class="shipping-result rounded-xl p-3 flex items-center justify-between">
              <div class="text-xs text-muted">
                <span v-if="cartStore.shippingDistanceKm">
                  📏 Jarak: ~{{ cartStore.shippingDistanceKm }} km
                </span>
                <span v-else-if="cartStore.shippingZoneResult">
                  📍 Zona: {{ cartStore.shippingZoneResult }}
                </span>
              </div>
              <div class="font-extrabold theme-accent-text text-base">
                {{ cartStore.shippingCost === 0 ? '🎁 Gratis Ongkir' : 'Rp ' + formatRupiah(cartStore.shippingCost.toString()) }}
              </div>
            </div>
          </div>

          <!-- PICKUP: no shipping -->
          <div v-if="cartStore.orderType === 'pickup'" class="shipping-box rounded-2xl p-4 flex items-center gap-3">
            <span class="text-xl">🏪</span>
            <div>
              <p class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">Ambil di Toko</p>
              <p class="text-xs text-muted">Tidak ada ongkos kirim untuk ambil sendiri.</p>
            </div>
          </div>

          <!-- ============================================================ -->
          <!-- ORDER TOTAL BREAKDOWN -->
          <!-- ============================================================ -->
          <div class="total-box rounded-2xl p-5 space-y-2">
            <div class="flex justify-between items-center text-sm">
              <span class="text-sub">Subtotal</span>
              <span class="font-semibold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatRupiah(cartStore.totalPrice.toString()) }}</span>
            </div>
            <div v-if="cartStore.orderType === 'delivery' && shippingMode !== 'none'" class="flex justify-between items-center text-sm">
              <span class="text-sub">Ongkos Kirim</span>
              <span :class="cartStore.shippingCost === 0 ? 'text-green-400 font-semibold' : 'font-semibold'" :style="cartStore.shippingCost > 0 ? { color: 'var(--text-primary)' } : {}">
                {{ cartStore.isCalculatingShipping ? '...' : (cartStore.shippingCost === 0 ? 'Gratis' : 'Rp ' + formatRupiah(cartStore.shippingCost.toString())) }}
              </span>
            </div>
            <div class="sep-border border-t pt-3 flex justify-between items-center">
              <span class="font-bold text-sub">Total Pembayaran</span>
              <span class="text-2xl font-extrabold theme-accent-text">Rp {{ formatRupiah(cartStore.grandTotal.toString()) }}</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-2 text-sub">Catatan Pesanan (Opsional)</label>
            <input v-model="cartStore.notes" type="text" placeholder="Contoh: Packing kardus, dll."
              class="theme-input w-full rounded-xl py-3 px-4 placeholder-muted focus:outline-none transition" />
          </div>

          <div v-if="cartStore.checkoutError"
            class="rounded-xl p-4 text-center text-sm"
            style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#f87171">
            {{ cartStore.checkoutError }}
          </div>

          <!-- Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <router-link :to="`/${slug}`"
              class="back-btn flex-1 text-center py-4 px-6 rounded-xl font-bold transition duration-300 flex items-center justify-center gap-2">
              ← Kembali
            </router-link>
            <button type="submit" :disabled="cartStore.isSubmitting || cartStore.isCalculatingShipping"
              class="theme-btn flex-[2] py-4 px-6 rounded-xl font-extrabold text-lg transition duration-300 shadow-lg disabled:opacity-50">
              {{ cartStore.isSubmitting ? 'Mengirim Pesanan...' : '💬 Kirim via WhatsApp' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCartStore } from '@/stores/cart';
import { useCatalogStore } from '@/stores/catalog';

const route     = useRoute();
const router    = useRouter();
const slug      = route.params.slug as string;
const cartStore = useCartStore();
const catalogStore = useCatalogStore();

// ── Theme ────────────────────────────────────────────────────────────
const THEMES: Record<string, {
  primary: string; secondary: string; primaryRgb: string;
  bgPage: string; bgSurface: string; bgSurfaceAlt: string;
  borderColor: string; textPrimary: string; textSecondary: string; textMuted: string;
}> = {
  default: {
    primary: '#6366f1', secondary: '#8b5cf6', primaryRgb: '99, 102, 241',
    bgPage: '#020617', bgSurface: 'rgba(15,23,42,0.75)', bgSurfaceAlt: '#1e293b',
    borderColor: '#334155', textPrimary: '#f1f5f9', textSecondary: '#94a3b8', textMuted: '#475569',
  },
  red: {
    primary: '#ef4444', secondary: '#b91c1c', primaryRgb: '239, 68, 68',
    bgPage: '#0f0202', bgSurface: 'rgba(26,5,5,0.85)', bgSurfaceAlt: '#1f0808',
    borderColor: '#3d1414', textPrimary: '#fef2f2', textSecondary: '#fca5a5', textMuted: '#7f1d1d',
  },
  emerald: {
    primary: '#10b981', secondary: '#059669', primaryRgb: '16, 185, 129',
    bgPage: '#011208', bgSurface: 'rgba(2,30,14,0.8)', bgSurfaceAlt: '#032d14',
    borderColor: '#064e23', textPrimary: '#ecfdf5', textSecondary: '#6ee7b7', textMuted: '#166534',
  },
  light: {
    primary: '#4f46e5', secondary: '#7c3aed', primaryRgb: '79, 70, 229',
    bgPage: '#f8fafc', bgSurface: 'rgba(255,255,255,0.85)', bgSurfaceAlt: '#f1f5f9',
    borderColor: '#cbd5e1', textPrimary: '#0f172a', textSecondary: '#475569', textMuted: '#94a3b8',
  },
  warm: {
    primary: '#f59e0b', secondary: '#d97706', primaryRgb: '245, 158, 11',
    bgPage: '#0d0900', bgSurface: 'rgba(26,18,0,0.85)', bgSurfaceAlt: '#271e00',
    borderColor: '#3d2f00', textPrimary: '#fffbeb', textSecondary: '#fde68a', textMuted: '#92400e',
  },
  ocean: {
    primary: '#0ea5e9', secondary: '#0284c7', primaryRgb: '14, 165, 233',
    bgPage: '#00060e', bgSurface: 'rgba(0,20,40,0.85)', bgSurfaceAlt: '#001a30',
    borderColor: '#003060', textPrimary: '#e0f2fe', textSecondary: '#7dd3fc', textMuted: '#0c4a6e',
  },
};

const themeVars = computed(() => {
  const key = catalogStore.tenant?.settings?.theme || 'default';
  const t = THEMES[key] ?? THEMES['default'];
  return {
    '--theme-primary':     t.primary,
    '--theme-secondary':   t.secondary,
    '--theme-primary-rgb': t.primaryRgb,
    '--bg-page':           t.bgPage,
    '--bg-surface':        t.bgSurface,
    '--bg-surface-alt':    t.bgSurfaceAlt,
    '--border-color':      t.borderColor,
    '--text-primary':      t.textPrimary,
    '--text-secondary':    t.textSecondary,
    '--text-muted':        t.textMuted,
  };
});

// ── Shipping computed ─────────────────────────────────────────────────
const shippingMode = computed(() =>
  catalogStore.tenant?.settings?.shipping_mode ?? 'none'
);
const shippingZones = computed(() =>
  catalogStore.tenant?.settings?.shipping_zones ?? []
);
const shippingDistances = computed(() =>
  catalogStore.tenant?.settings?.shipping_distances ?? []
);
const storeLocationLabel = computed(() =>
  catalogStore.tenant?.settings?.store_location_label ?? null
);
const shippingCalculated = computed(() =>
  cartStore.shippingMode !== 'none' && !cartStore.isCalculatingShipping
);

// ── Debounce timer for shipping recalc ────────────────────────────────
let shippingTimer: ReturnType<typeof setTimeout> | null = null;

function triggerShippingCalc() {
  if (shippingTimer) clearTimeout(shippingTimer);
  shippingTimer = setTimeout(() => {
    cartStore.calculateShipping(slug);
  }, 600);
}

function onOrderTypeChange() {
  cartStore.shippingCost = 0;
  cartStore.shippingDistanceKm = null;
  cartStore.shippingZoneResult = null;
  if (cartStore.orderType === 'delivery' && shippingMode.value !== 'none') {
    triggerShippingCalc();
  }
}

// ── Geo detection ─────────────────────────────────────────────────────
const geoLoading = ref(false);
const geoError   = ref<string | null>(null);

// Parse the location link into a Google Maps embed URL
const embedUrl = computed(() => {
  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
  if (!apiKey) return null;

  const val = cartStore.locationLink?.trim();
  if (!val) return null;

  const coordPattern = /(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)/;
  const match = val.match(coordPattern);
  if (match) {
    const lat = match[1];
    const lng = match[2];
    return `https://www.google.com/maps/embed/v1/place?key=${apiKey}&q=${lat},${lng}&zoom=17`;
  }

  if (val.includes('maps.google.com') || val.includes('goo.gl/maps') || val.includes('maps.app.goo')) {
    return null;
  }

  return null;
});

const googleMapsLink = computed(() => {
  const val = cartStore.locationLink?.trim();
  if (!val) return '#';
  if (val.startsWith('http')) return val;
  const coordPattern = /(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)/;
  const match = val.match(coordPattern);
  if (match) return `https://maps.google.com/?q=${match[1]},${match[2]}`;
  return `https://maps.google.com/?q=${encodeURIComponent(val)}`;
});

/** Extract lat/lng from a raw coordinate string or link */
function extractCoordsFromString(val: string): { lat: number; lng: number } | null {
  const coordPattern = /(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)/;
  const match = val.match(coordPattern);
  if (match) {
    return { lat: parseFloat(match[1]), lng: parseFloat(match[2]) };
  }
  return null;
}

function onLocationLinkInput() {
  const val = cartStore.locationLink?.trim();
  if (!val) {
    cartStore.customerLat = null;
    cartStore.customerLng = null;
    return;
  }
  const coords = extractCoordsFromString(val);
  if (coords) {
    cartStore.customerLat = coords.lat;
    cartStore.customerLng = coords.lng;
    if (shippingMode.value === 'distance') {
      triggerShippingCalc();
    }
  }
}

function detectLocation() {
  geoError.value = null;
  if (!navigator.geolocation) {
    geoError.value = 'Browser Anda tidak mendukung GPS. Masukkan link Maps secara manual.';
    return;
  }
  geoLoading.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      cartStore.customerLat  = lat;
      cartStore.customerLng  = lng;
      cartStore.locationLink = `${lat.toFixed(6)},${lng.toFixed(6)}`;
      geoLoading.value = false;
      if (shippingMode.value === 'distance') {
        triggerShippingCalc();
      }
    },
    (err) => {
      geoLoading.value = false;
      if (err.code === 1) {
        geoError.value = 'Akses lokasi ditolak. Izinkan browser mengakses lokasi atau isi manual.';
      } else if (err.code === 2) {
        geoError.value = 'Sinyal GPS lemah. Coba di tempat terbuka atau isi manual.';
      } else {
        geoError.value = 'Gagal mendeteksi lokasi. Coba lagi atau isi manual.';
      }
    },
    { timeout: 12000, enableHighAccuracy: true }
  );
}

onMounted(() => {
  if (slug) {
    cartStore.useTenant(slug);
    if (!catalogStore.tenant) {
      catalogStore.fetchCatalog(slug).then(() => {
        // Auto-calculate shipping if mode is zone and zone already selected
        if (shippingMode.value !== 'none') {
          cartStore.calculateShipping(slug);
        }
      });
    } else if (shippingMode.value !== 'none') {
      cartStore.calculateShipping(slug);
    }
  }
});

cartStore.$subscribe(() => { cartStore.persist(); });

// Re-calc shipping when zone selection changes
watch(() => cartStore.selectedZone, () => {
  if (shippingMode.value === 'zone' && cartStore.selectedZone) {
    triggerShippingCalc();
  }
});

async function handleSubmit() {
  if (!slug) return;
  const result = await cartStore.checkout(slug);
  if (result) {
    sessionStorage.setItem(`order-success:${slug}`, JSON.stringify(result));
    router.push(`/${slug}/order-success`);
  }
}

function itemUnitPrice(item: any): number {
  const unitPrice = Number(item.unit_price ?? item.base_price ?? item.price ?? 0);
  return Number.isFinite(unitPrice) ? unitPrice : 0;
}

function itemSubtotal(item: any): number {
  return itemUnitPrice(item) * Number(item.quantity || 0);
}

function formatRupiah(val: string | number): string {
  const num = typeof val === 'number' ? val : parseFloat(val);
  if (!Number.isFinite(num)) return '0';
  return num.toLocaleString('id-ID');
}
</script>

<style scoped>
.checkout-page {
  min-height: 100vh;
  padding: 4rem 1.5rem;
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--bg-page);
  color: var(--text-primary);
  transition: background-color 0.4s ease;
}

.checkout-card {
  background-color: var(--bg-surface);
  border: 1px solid var(--border-color);
  transition: background-color 0.4s ease;
}

.item-card {
  background-color: var(--bg-surface-alt);
  border: 1px solid var(--border-color);
}

.sep-border { border-color: var(--border-color); }
.text-sub   { color: var(--text-secondary); }
.text-muted { color: var(--text-muted); }

.theme-accent-text { color: var(--theme-primary); }

.theme-btn {
  background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(var(--theme-primary-rgb), 0.4);
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
}
.theme-btn:hover:not(:disabled) {
  filter: brightness(1.12);
  box-shadow: 0 6px 22px rgba(var(--theme-primary-rgb), 0.55);
}

.back-btn {
  background-color: var(--bg-surface-alt);
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  text-decoration: none;
}
.back-btn:hover {
  background-color: rgba(var(--theme-primary-rgb), 0.08);
  border-color: rgba(var(--theme-primary-rgb), 0.3);
  color: var(--text-primary);
}

.theme-input {
  background-color: var(--bg-surface-alt);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
}
.theme-input:focus {
  border-color: var(--theme-primary) !important;
  box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.15);
}
.theme-input option { background-color: var(--bg-surface-alt); }

.placeholder-muted::placeholder { color: var(--text-muted); }

.qty-btn {
  background-color: var(--bg-surface-alt);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
}
.qty-btn:hover {
  background-color: rgba(var(--theme-primary-rgb), 0.15);
  border-color: rgba(var(--theme-primary-rgb), 0.4);
}

/* Location box */
.location-box {
  background-color: rgba(var(--theme-primary-rgb), 0.05);
  border: 1px solid rgba(var(--theme-primary-rgb), 0.2);
}

.location-btn {
  background-color: rgba(var(--theme-primary-rgb), 0.1);
  border: 1px solid rgba(var(--theme-primary-rgb), 0.3);
  color: var(--theme-primary);
}
.location-btn:hover:not(:disabled) {
  background-color: rgba(var(--theme-primary-rgb), 0.18);
  border-color: rgba(var(--theme-primary-rgb), 0.5);
}
.location-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Shipping box */
.shipping-box {
  background-color: rgba(var(--theme-primary-rgb), 0.04);
  border: 1px solid rgba(var(--theme-primary-rgb), 0.2);
}

.shipping-result {
  background-color: rgba(var(--theme-primary-rgb), 0.08);
  border: 1px solid rgba(var(--theme-primary-rgb), 0.25);
}

/* Total breakdown box */
.total-box {
  background-color: var(--bg-surface-alt);
  border: 1px solid var(--border-color);
}
</style>
