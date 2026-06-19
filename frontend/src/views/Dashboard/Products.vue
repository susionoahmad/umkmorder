<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h3 class="text-xl font-bold text-slate-200">Daftar Produk</h3>
      <button 
        @click="openAddModal" 
        class="theme-btn py-2.5 px-4 rounded-xl font-bold text-sm transition duration-200 shadow-md"
      >
        + Tambah Produk
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <div class="w-12 h-12 border-4 border-t-transparent rounded-full animate-spin theme-spinner"></div>
      <p class="text-slate-400">Memuat produk...</p>
    </div>

    <!-- Error Alert -->
    <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 text-center text-red-400">
      {{ error }}
    </div>

    <div v-else class="space-y-6">
      <div v-if="products.length === 0" class="text-center py-16 bg-slate-900/40 border border-slate-800 rounded-2xl text-slate-500 text-sm">
        Belum ada produk di toko Anda. Klik "Tambah Produk" di atas.
      </div>

      <!-- Products Grid (marketplace-style) -->
      <div v-else class="products-grid">
        <div 
          v-for="product in products" 
          :key="product.id"
          class="product-card bg-slate-900/60 border border-slate-800 rounded-xl flex flex-col hover:border-slate-700 hover:shadow-lg transition-all duration-200 overflow-hidden"
        >
          <!-- Gambar Produk -->
          <div class="product-thumb relative bg-slate-950 overflow-hidden">
            <img 
              v-if="product.image_url" 
              :src="product.image_url" 
              :alt="product.name" 
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-3xl text-slate-700">📦</div>
            <!-- Badge status -->
            <span
              class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded-full"
              :class="product.is_active ? 'bg-emerald-500/90 text-white' : 'bg-slate-700/90 text-slate-300'"
            >
              {{ product.is_active ? 'Aktif' : 'Draft' }}
            </span>
            <!-- Badge gambar tersembunyi -->
            <span
              v-if="product.image_url && product.show_image === false"
              class="absolute top-2 right-2 rounded-full bg-slate-950/85 px-1.5 py-0.5 text-[9px] font-bold text-slate-400 border border-slate-700"
            >
              👁‍🗨 Hidden
            </span>
          </div>

          <!-- Info Produk -->
          <div class="flex flex-col flex-1 p-3 gap-1.5">
            <h4 class="font-bold text-slate-100 text-sm leading-snug line-clamp-2">{{ product.name }}</h4>
            <p class="text-[10px] text-slate-600 font-mono">{{ product.sku || '—' }}</p>
            <p class="theme-accent-text font-extrabold text-sm mt-auto pt-1">
              Rp {{ formatRupiah(product.price) }}
            </p>
          </div>

          <!-- Aksi -->
          <div class="flex border-t border-slate-800">
            <button 
              @click="openEditModal(product)"
              class="flex-1 py-2 text-xs font-semibold theme-accent-text hover:bg-[rgba(var(--theme-primary-rgb),0.1)] transition flex items-center justify-center gap-1"
            >
              ✏️ Edit
            </button>
            <div class="w-px bg-slate-800"></div>
            <button 
              @click="handleDelete(product.id)"
              class="flex-1 py-2 text-xs font-semibold text-red-400 hover:bg-red-500/10 transition flex items-center justify-center gap-1"
            >
              🗑️ Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Modal (Add / Edit) -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-sm overflow-y-auto">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3rem)] shadow-2xl flex flex-col">
        <h3 class="text-2xl font-bold text-slate-100 px-6 pt-6 sm:px-8 sm:pt-8 shrink-0">
          {{ isEditMode ? 'Edit Produk' : 'Tambah Produk Baru' }}
        </h3>

        <form @submit.prevent="saveProduct" class="mt-6 px-6 pb-6 sm:px-8 sm:pb-8 space-y-4 overflow-y-auto min-h-0 product-modal-scroll">
          <div>
            <label class="block text-sm font-semibold text-slate-400 mb-2">Nama Produk</label>
            <input 
              v-model="form.name" 
              type="text" 
              required
              placeholder="Contoh: Telur Asin Masir" 
              class="w-full bg-slate-950 border border-slate-850 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">SKU</label>
              <input 
                v-model="form.sku" 
                type="text" 
                placeholder="TA-MASIR" 
                class="w-full bg-slate-950 border border-slate-850 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Harga (Rp)</label>
              <input 
                v-model="form.price" 
                type="number" 
                required
                min="0"
                placeholder="3500" 
                class="w-full bg-slate-950 border border-slate-850 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-400 mb-2">Deskripsi</label>
            <textarea 
              v-model="form.description" 
              rows="3"
              placeholder="Tulis deskripsi detail produk di sini..." 
              class="w-full bg-slate-950 border border-slate-850 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition resize-none"
            ></textarea>
          </div>

          <div class="space-y-3">
            <label class="block text-sm font-semibold text-slate-400">Gambar Produk</label>
            <div v-if="form.image_url" class="rounded-2xl overflow-hidden border border-slate-800 bg-slate-950">
              <img :src="form.image_url" :alt="form.name || 'Gambar produk'" class="w-full h-40 object-cover" />
            </div>
            <label
              class="flex items-center justify-center rounded-xl border border-dashed border-slate-700 bg-slate-950 py-3 px-4 text-sm font-semibold text-slate-400 cursor-pointer hover:border-indigo-500 hover:text-indigo-400 transition"
              :class="uploadingImage ? 'opacity-60 pointer-events-none' : ''"
            >
              <span>{{ uploadingImage ? 'Mengunggah gambar...' : (form.image_url ? 'Ganti Gambar' : 'Pilih Gambar') }}</span>
              <input
                type="file"
                accept="image/jpeg,image/png,image/webp,image/gif"
                class="hidden"
                :disabled="uploadingImage"
                @change="handleImageUpload"
              />
            </label>
            <button
              v-if="form.image_url"
              type="button"
              @click="removeImage"
              class="text-xs font-semibold text-red-400 hover:text-red-300 transition"
            >
              Hapus gambar
            </button>
            <p v-if="uploadImageError" class="text-xs text-red-400">{{ uploadImageError }}</p>
          </div>

          <div class="flex items-center gap-2 py-2">
            <input 
              v-model="form.show_image" 
              id="show_image" 
              type="checkbox" 
              class="w-5 h-5 rounded bg-slate-950 border-slate-850 text-indigo-600 focus:ring-indigo-600"
            />
            <label for="show_image" class="text-sm font-semibold text-slate-400 cursor-pointer">Tampilkan gambar produk di katalog</label>
          </div>

          <div class="flex items-center gap-2 py-2">
            <input 
              v-model="form.is_active" 
              id="is_active" 
              type="checkbox" 
              class="w-5 h-5 rounded bg-slate-950 border-slate-850 text-indigo-600 focus:ring-indigo-600"
            />
            <label for="is_active" class="text-sm font-semibold text-slate-400 cursor-pointer">Tampilkan di katalog publik</label>
          </div>

          <!-- ==================== HARGA GROSIR SECTION ==================== -->
          <div class="border border-slate-800 rounded-2xl overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 bg-slate-900/80 border-b border-slate-800">
              <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-200">💰 Harga Grosir</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/15 text-indigo-400 font-semibold">Opsional</span>
              </div>
              <button
                type="button"
                @click="addTierRow"
                class="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1"
              >
                + Tambah Tier
              </button>
            </div>

            <!-- Tier Table -->
            <div v-if="form.price_tiers.length > 0" class="divide-y divide-slate-800">
              <!-- Header row -->
              <div class="grid grid-cols-[1fr_1fr_1.2fr_auto] gap-2 px-4 py-2 bg-slate-950/40">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Qty Min</span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Qty Max</span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Harga/Item (Rp)</span>
                <span></span>
              </div>
              <!-- Tier rows -->
              <div
                v-for="(tier, i) in form.price_tiers"
                :key="i"
                class="grid grid-cols-[1fr_1fr_1.2fr_auto] gap-2 px-4 py-2.5 items-center"
              >
                <input
                  v-model.number="tier.min_qty"
                  type="number"
                  min="1"
                  placeholder="1"
                  class="w-full bg-slate-950 border border-slate-700 rounded-lg py-1.5 px-2 text-sm text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition"
                />
                <div class="relative">
                  <input
                    v-model="tier.max_qty_input"
                    type="number"
                    min="1"
                    placeholder="∞"
                    class="w-full bg-slate-950 border border-slate-700 rounded-lg py-1.5 px-2 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-indigo-500 transition"
                  />
                </div>
                <input
                  v-model.number="tier.unit_price"
                  type="number"
                  min="0"
                  placeholder="0"
                  class="w-full bg-slate-950 border border-slate-700 rounded-lg py-1.5 px-2 text-sm text-slate-100 placeholder-slate-700 focus:outline-none focus:border-indigo-500 transition"
                />
                <button
                  type="button"
                  @click="removeTierRow(i)"
                  class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/25 text-red-400 flex items-center justify-center transition text-xs"
                  title="Hapus tier"
                >✕</button>
              </div>
            </div>

            <!-- Empty state -->
            <div v-else class="px-4 py-5 text-center text-xs text-slate-600">
              Tidak ada harga grosir. Klik "+ Tambah Tier" untuk mengatur harga bertingkat.
            </div>

            <!-- Tier validation error -->
            <div v-if="tierError" class="px-4 py-2.5 bg-red-500/10 border-t border-red-500/20 text-xs text-red-400">
              ⚠️ {{ tierError }}
            </div>

            <!-- Hint -->
            <div class="px-4 py-3 bg-slate-950/30 border-t border-slate-800 text-[10px] text-slate-600 leading-relaxed">
              💡 Biarkan <strong class="text-slate-500">Qty Max</strong> kosong (∞) untuk tier "tak terbatas".
              Harga Grosir akan menggantikan harga satuan di atas secara otomatis saat qty sesuai.
            </div>
          </div>
          <!-- ==================== END HARGA GROSIR ==================== -->

          <div v-if="modalError" class="bg-red-500/10 border border-red-500/30 rounded-xl p-3 text-center text-xs text-red-400">
            {{ modalError }}
          </div>

          <!-- Buttons -->
          <div class="sticky bottom-0 -mx-6 sm:-mx-8 px-6 sm:px-8 pt-4 pb-1 bg-slate-900 border-t border-slate-800 flex gap-4">
            <button 
              type="button" 
              @click="closeModal"
              class="flex-1 py-3.5 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 transition font-bold text-sm"
            >
              Batal
            </button>
            <button 
              type="submit"
              :disabled="isSaving"
              class="theme-btn flex-1 py-3.5 px-4 rounded-xl font-bold text-sm transition duration-200 disabled:opacity-50"
            >
              {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api, { uploadFile } from '@/services/api';

const isLoading = ref(true);
const error = ref(null as string | null);
const products = ref([] as any[]);

// Modal states
const showModal = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);
const modalError = ref(null as string | null);
const activeProductId = ref(null as number | null);
const uploadingImage = ref(false);
const uploadImageError = ref(null as string | null);

const form = ref({
  name: '',
  sku: '',
  price: '',
  description: '',
  image_url: '',
  show_image: true,
  is_active: true,
  price_tiers: [] as { min_qty: number; max_qty_input: string; unit_price: number }[],
});

// Tier error
const tierError = ref<string | null>(null);

async function fetchProducts() {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await api.get('/products');
    if (response.data.status === 'success') {
      products.value = response.data.data;
    } else {
      error.value = response.data.message || 'Gagal memuat produk';
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal terhubung ke server';
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchProducts();
});

function openAddModal() {
  isEditMode.value = false;
  activeProductId.value = null;
  modalError.value = null;
  tierError.value = null;
  form.value = {
    name: '',
    sku: '',
    price: '',
    description: '',
    image_url: '',
    show_image: true,
    is_active: true,
    price_tiers: [],
  };
  uploadImageError.value = null;
  showModal.value = true;
}

function openEditModal(product: any) {
  isEditMode.value = true;
  activeProductId.value = product.id;
  modalError.value = null;
  tierError.value = null;
  form.value = {
    name: product.name,
    sku: product.sku || '',
    price: parseFloat(product.price).toString(),
    description: product.description || '',
    image_url: product.image_url || '',
    show_image: product.show_image !== false,
    is_active: !!product.is_active,
    price_tiers: (product.price_tiers || []).map((t: any) => ({
      min_qty: t.min_qty,
      max_qty_input: t.max_qty !== null && t.max_qty !== undefined ? String(t.max_qty) : '',
      unit_price: parseFloat(t.unit_price),
    })),
  };
  uploadImageError.value = null;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  tierError.value = null;
}

/** Tier management helpers */
function addTierRow() {
  form.value.price_tiers.push({ min_qty: 1, max_qty_input: '', unit_price: 0 });
  tierError.value = null;
}
function removeTierRow(i: number) {
  form.value.price_tiers.splice(i, 1);
  tierError.value = null;
}

/** Client-side overlap validation */
function validateTiers(): string | null {
  const tiers = form.value.price_tiers;
  if (!tiers.length) return null;

  for (let i = 0; i < tiers.length; i++) {
    const minA = tiers[i].min_qty;
    const maxA = tiers[i].max_qty_input !== '' && tiers[i].max_qty_input !== null
      ? parseInt(tiers[i].max_qty_input as string)
      : null;

    if (minA < 1) return 'Qty Minimum harus minimal 1.';
    if (maxA !== null && maxA <= minA) return 'Qty Maksimum harus lebih besar dari Qty Minimum.';

    for (let j = 0; j < tiers.length; j++) {
      if (i === j) continue;
      const minB = tiers[j].min_qty;
      const maxB = tiers[j].max_qty_input !== '' && tiers[j].max_qty_input !== null
        ? parseInt(tiers[j].max_qty_input as string)
        : null;
      const effectiveMaxA = maxA ?? Number.MAX_SAFE_INTEGER;
      const effectiveMaxB = maxB ?? Number.MAX_SAFE_INTEGER;
      if (minA <= effectiveMaxB && minB <= effectiveMaxA) {
        return 'Rentang qty bertabrakan (overlap). Pastikan setiap tier tidak saling tumpang tindih.';
      }
    }
  }
  return null;
}

async function handleImageUpload(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  uploadingImage.value = true;
  uploadImageError.value = null;
  try {
    const response = await uploadFile('/upload/product-image', file);
    if (response.data.status === 'success') {
      form.value.image_url = response.data.url;
      form.value.show_image = true;
    } else {
      uploadImageError.value = response.data.message || 'Gagal mengunggah gambar produk';
    }
  } catch (err: any) {
    uploadImageError.value = err.response?.data?.message || 'Gagal mengunggah gambar produk';
  } finally {
    uploadingImage.value = false;
    input.value = '';
  }
}

function removeImage() {
  form.value.image_url = '';
  form.value.show_image = false;
}

async function saveProduct() {
  // Validate tiers before submitting
  tierError.value = validateTiers();
  if (tierError.value) return;

  isSaving.value = true;
  modalError.value = null;
  try {
    const tiersPayload = form.value.price_tiers.map(t => ({
      min_qty:    t.min_qty,
      max_qty:    t.max_qty_input !== '' && t.max_qty_input !== null ? parseInt(t.max_qty_input as string) : null,
      unit_price: t.unit_price,
    }));

    const payload = {
      name:        form.value.name,
      sku:         form.value.sku,
      price:       parseFloat(form.value.price),
      description: form.value.description,
      image_url:   form.value.image_url,
      show_image:  form.value.show_image,
      is_active:   form.value.is_active,
      price_tiers: tiersPayload,
    };

    let response;
    if (isEditMode.value && activeProductId.value) {
      response = await api.put(`/products/${activeProductId.value}`, payload);
    } else {
      response = await api.post('/products', payload);
    }

    if (response.data.status === 'success') {
      showModal.value = false;
      fetchProducts(); // Refresh list
    } else {
      modalError.value = response.data.message || 'Gagal menyimpan produk';
    }
  } catch (err: any) {
    modalError.value = err.response?.data?.message || 'Terjadi kesalahan input data';
  } finally {
    isSaving.value = false;
  }
}

async function handleDelete(id: number) {
  if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) return;
  try {
    const response = await api.delete(`/products/${id}`);
    if (response.data.status === 'success') {
      fetchProducts();
    } else {
      alert(response.data.message || 'Gagal menghapus produk');
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus produk');
  }
}

function formatRupiah(val: any): string {
  const num = parseFloat(val);
  if (isNaN(num)) return '0';
  return num.toLocaleString('id-ID');
}
</script>

<style scoped>
.product-modal-scroll {
  scrollbar-width: thin;
  scrollbar-color: #475569 transparent;
}

.product-modal-scroll::-webkit-scrollbar {
  width: 8px;
}

.product-modal-scroll::-webkit-scrollbar-thumb {
  background-color: #475569;
  border-radius: 999px;
}

.product-modal-scroll::-webkit-scrollbar-track {
  background: transparent;
}

/* ── Marketplace Grid ───────────────────────── */
.products-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

@media (min-width: 480px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
  }
}

@media (min-width: 768px) {
  .products-grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
  }
}

@media (min-width: 1280px) {
  .products-grid {
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
  }
}

/* ── Product Thumbnail (square) ─────────────── */
.product-thumb {
  aspect-ratio: 1 / 1;
  width: 100%;
}

/* ── Product Card hover lift ─────────────────── */
.product-card:hover {
  transform: translateY(-2px);
}
</style>
