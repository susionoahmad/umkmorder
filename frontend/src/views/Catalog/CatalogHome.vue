<template>
  <div class="catalog-page" :style="themeVars">

    <!-- Banner Header -->
    <div
      class="catalog-banner relative overflow-hidden border-b py-8 sm:py-14 px-4 sm:px-10 lg:px-24"
      :style="tenant?.settings?.catalog_banner
        ? `background-image: url('${tenant.settings.catalog_banner}'); background-size: cover; background-position: center;`
        : ''"
    >
      <!-- Overlay -->
      <div
        class="absolute inset-0 opacity-80"
        :style="{ background: tenant?.settings?.catalog_banner
          ? `linear-gradient(135deg, var(--bg-page) 0%, rgba(var(--bg-page-rgb,2,6,23),0.85) 60%, transparent 100%)`
          : `linear-gradient(135deg, var(--bg-surface-alt) 0%, transparent 100%)` }"
      ></div>

      <div class="relative max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row items-center gap-8">
          <!-- Logo Avatar -->
          <div
            class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl flex items-center justify-center text-4xl font-bold shadow-lg overflow-hidden shrink-0"
            :style="{ background: `linear-gradient(135deg, var(--theme-primary), var(--theme-secondary))` }"
          >
            <img v-if="tenant?.logo" :src="tenant.logo" :alt="tenant.name" class="w-full h-full object-cover" />
            <span v-else class="text-white">{{ tenant ? tenant.name.charAt(0) : 'U' }}</span>
          </div>

          <div class="flex-1 text-center md:text-left">
            <h1 v-if="tenant" class="text-3xl sm:text-4xl font-extrabold tracking-tight" :style="{ color: 'var(--text-primary)' }">
              {{ tenant.name }}
            </h1>
            <p v-if="tenant" class="mt-2 text-base sm:text-lg max-w-xl" :style="{ color: 'var(--text-secondary)' }">
              {{ tenant.settings?.catalog_description || 'Selamat datang di katalog online kami.' }}
            </p>
            <div v-if="tenant" class="mt-4 flex flex-wrap justify-center md:justify-start gap-4 text-sm" :style="{ color: 'var(--text-secondary)' }">
              <span class="flex items-center gap-1">📍 {{ tenant.address || 'Alamat tidak ditentukan' }}</span>
              <span class="flex items-center gap-1">📞 {{ tenant.phone }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-5xl mx-auto px-3 sm:px-6 py-6 sm:py-10">

      <!-- Loading State -->
      <div v-if="store.isLoading" class="flex flex-col items-center justify-center py-24 space-y-4">
        <div class="w-12 h-12 border-4 border-t-transparent rounded-full animate-spin theme-spinner"></div>
        <p class="text-sub">Memuat produk pilihan...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="store.error" class="rounded-xl p-6 text-center my-12"
        style="background:rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3)">
        <p style="color:#f87171">{{ store.error }}</p>
      </div>

      <!-- Products Grid (marketplace-style) -->
      <div v-else>
        <h2 class="text-xl font-bold mb-5 flex items-center gap-2" :style="{ color: 'var(--text-primary)' }">
          🛒 Daftar Produk
        </h2>

        <div v-if="store.products.length === 0" class="text-center py-24 text-muted">
          Belum ada produk tersedia.
        </div>

        <div class="catalog-products-grid">
          <div
            v-for="product in store.products"
            :key="product.id"
            class="catalog-card product-card rounded-2xl flex flex-col transition duration-300 shadow-md backdrop-blur-md overflow-hidden"
          >
            <!-- Gambar Produk -->
            <div class="catalog-thumb relative overflow-hidden">
              <img
                v-if="product.show_image && product.image_url"
                :src="product.image_url"
                :alt="product.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-4xl">📦</div>
            </div>

            <!-- Info Produk -->
            <div class="flex flex-col flex-1 p-3 gap-1.5">
              <h3 class="text-sm font-bold leading-snug line-clamp-2" :style="{ color: 'var(--text-primary)' }">
                {{ product.name }}
              </h3>

              <!-- Harga Grosir (Tier Pricing) -->
              <div v-if="product.price_tiers && product.price_tiers.length > 0" class="tier-badge-list mt-1 flex flex-col gap-0.5">
                <div
                  v-for="(tier, i) in product.price_tiers"
                  :key="i"
                  class="tier-row text-[10px] flex justify-between items-center px-1.5 py-0.5 rounded"
                  :class="{ 'tier-active': isActiveTier(product, tier, productQty[product.id] || 1) }"
                >
                  <span class="font-semibold">
                    {{ tier.min_qty }}{{ tier.max_qty ? `–${tier.max_qty}` : '+' }}×
                  </span>
                  <span class="font-extrabold theme-accent-text">
                    Rp {{ formatRupiah(tier.unit_price.toString()) }}
                  </span>
                </div>
              </div>

              <!-- Harga Tunggal jika tidak ada tier -->
              <div v-else-if="tenant?.settings?.show_price !== false">
                <p class="theme-accent-text font-extrabold text-sm">
                  Rp {{ formatRupiah(product.price) }}
                </p>
              </div>

              <!-- Harga live berdasarkan qty yang dipilih -->
              <div v-if="product.price_tiers && product.price_tiers.length > 0 && tenant?.settings?.show_price !== false"
                   class="mt-1 pt-1.5 border-t" :style="{ borderColor: 'var(--border-color)' }">
                <div class="flex justify-between items-center">
                  <span class="text-[10px] text-muted">Harga satuan</span>
                  <span class="text-xs font-extrabold theme-accent-text">
                    Rp {{ formatRupiah(resolvedPrice(product).toString()) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Qty Selector + Tambah Keranjang -->
            <div class="px-3 pb-3 flex flex-col gap-2">
              <!-- Qty Picker -->
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    @click="decrementQty(product.id)"
                    class="catalog-qty-btn w-7 h-7 rounded-lg font-bold text-base flex items-center justify-center transition"
                  >−</button>
                  <span class="w-8 text-center text-sm font-extrabold" :style="{ color: 'var(--text-primary)' }">
                    {{ productQty[product.id] || 1 }}
                  </span>
                  <button
                    type="button"
                    @click="incrementQty(product.id)"
                    class="catalog-qty-btn w-7 h-7 rounded-lg font-bold text-base flex items-center justify-center transition"
                  >+</button>
                </div>
                <span v-if="product.price_tiers?.length && tenant?.settings?.show_price !== false"
                      class="text-[10px] text-muted">
                  × {{ productQty[product.id] || 1 }} = <span class="font-bold theme-accent-text">
                    Rp {{ formatRupiah((resolvedPrice(product) * (productQty[product.id] || 1)).toString()) }}
                  </span>
                </span>
              </div>

              <!-- Tombol Tambah -->
              <button
                @click="addToCart(product)"
                class="theme-btn w-full py-2 px-3 rounded-xl text-xs font-bold transition duration-300 shadow-md"
              >
                + Tambah ke Keranjang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Cart Bar -->
    <div
      v-if="cartStore.totalItemsCount > 0"
      class="cart-float fixed bottom-6 left-1/2 -translate-x-1/2 rounded-2xl py-4 px-6 flex items-center gap-8 shadow-xl backdrop-blur-lg max-w-lg w-[calc(100%-2rem)]"
    >
      <div class="flex-1">
        <p class="text-sm text-sub">{{ cartStore.totalItemsCount }} item terpilih</p>
        <p class="text-lg font-extrabold theme-accent-text">Rp {{ formatRupiah(cartStore.totalPrice.toString()) }}</p>
      </div>

      <router-link
        :to="`/${route.params.slug}/checkout`"
        class="theme-btn py-3 px-6 rounded-xl font-bold transition duration-300"
      >
        Lanjut Checkout →
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useCatalogStore } from '@/stores/catalog';
import { useCartStore, resolveUnitPrice } from '@/stores/cart';
import type { Product, PriceTier } from '@/stores/catalog';

const route      = useRoute();
const store      = useCatalogStore();
const cartStore  = useCartStore();

const tenant = computed(() => store.tenant);

// Per-product quantity selector (reactive map: product_id -> qty)
const productQty = reactive<Record<number, number>>({});

function getQty(productId: number): number {
  return productQty[productId] ?? 1;
}
function incrementQty(productId: number) {
  productQty[productId] = getQty(productId) + 1;
}
function decrementQty(productId: number) {
  productQty[productId] = Math.max(1, getQty(productId) - 1);
}

/** Resolve the current unit_price for a product given the selected qty */
function resolvedPrice(product: Product): number {
  const qty = getQty(product.id);
  return resolveUnitPrice(product.price_tiers ?? [], parseFloat(product.price), qty);
}

/** Check if a tier is the currently active one for a given qty */
function isActiveTier(_product: Product, tier: PriceTier, qty: number): boolean {
  const minOk = qty >= tier.min_qty;
  const maxOk = tier.max_qty === null || qty <= tier.max_qty;
  return minOk && maxOk;
}

function addToCart(product: Product) {
  const qty = getQty(product.id);
  cartStore.addToCart(product, qty);
  // Reset qty selector back to 1 after adding
  productQty[product.id] = 1;
}

// Full visual theme definitions
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
  const key = tenant.value?.settings?.theme || 'default';
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

onMounted(() => {
  const slug = route.params.slug as string;
  if (slug) {
    cartStore.useTenant(slug);
    store.fetchCatalog(slug);
  }
});

watch(() => route.params.slug, (slug) => {
  if (typeof slug === 'string') {
    cartStore.useTenant(slug);
    store.fetchCatalog(slug);
  }
});

function formatRupiah(val: string | number): string {
  const num = typeof val === 'string' ? parseFloat(val) : val;
  return num.toLocaleString('id-ID');
}
</script>

<style scoped>
/* ─── Page Root ───────────────────────────────────────── */
.catalog-page {
  min-height: 100vh;
  padding-bottom: 6rem;
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--bg-page);
  color: var(--text-primary);
  transition: background-color 0.4s ease, color 0.3s ease;
}

/* ─── Banner ──────────────────────────────────────────── */
.catalog-banner {
  background-color: var(--bg-surface-alt);
  border-bottom-color: var(--border-color);
  transition: background-color 0.4s ease;
}

/* ─── Cards ───────────────────────────────────────────── */
.catalog-card {
  background-color: var(--bg-surface);
  border: 1px solid var(--border-color);
  transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
}
.catalog-card.product-card:hover {
  border-color: rgba(var(--theme-primary-rgb), 0.5);
  box-shadow: 0 8px 30px rgba(var(--theme-primary-rgb), 0.12);
  transform: translateY(-3px);
}

/* ─── Marketplace Grid ──────────────────────────────── */
.catalog-products-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}
@media (min-width: 480px) {
  .catalog-products-grid { grid-template-columns: repeat(3, 1fr); gap: 12px; }
}
@media (min-width: 768px) {
  .catalog-products-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; }
}

/* ─── Product Thumbnail (square) ─────────────────── */
.catalog-thumb {
  aspect-ratio: 1 / 1;
  width: 100%;
  background-color: var(--bg-surface-alt);
}

/* ─── Tier Pricing Rows ───────────────────────────── */
.tier-badge-list {
  background-color: rgba(var(--theme-primary-rgb), 0.04);
  border: 1px solid rgba(var(--theme-primary-rgb), 0.12);
  border-radius: 8px;
  padding: 4px 6px;
}
.tier-row {
  color: var(--text-muted);
  border-radius: 4px;
  transition: background-color 0.15s;
}
.tier-row.tier-active {
  background-color: rgba(var(--theme-primary-rgb), 0.12);
  color: var(--text-primary) !important;
}

/* ─── Qty Buttons ─────────────────────────────────── */
.catalog-qty-btn {
  background-color: var(--bg-surface-alt);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.15s;
}
.catalog-qty-btn:hover {
  background-color: rgba(var(--theme-primary-rgb), 0.15);
  border-color: rgba(var(--theme-primary-rgb), 0.4);
}

/* ─── Floating Cart Bar ──────────────────────────── */
.cart-float {
  background-color: var(--bg-surface);
  border: 1px solid rgba(var(--theme-primary-rgb), 0.35);
  box-shadow: 0 12px 40px rgba(0,0,0,0.4);
}

/* ─── Text Helpers ───────────────────────────────── */
.text-sub   { color: var(--text-secondary); }
.text-muted { color: var(--text-muted); }

/* ─── Accent & Buttons ───────────────────────────── */
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
.theme-btn:hover {
  filter: brightness(1.12);
  box-shadow: 0 6px 22px rgba(var(--theme-primary-rgb), 0.55);
}

/* ─── Loading Spinner ────────────────────────────── */
.theme-spinner {
  border-color: var(--theme-primary);
  border-top-color: transparent;
}
</style>
