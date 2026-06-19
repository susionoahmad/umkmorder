<template>
  <div class="space-y-6 max-w-3xl" :style="themeVars">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <div class="w-12 h-12 border-4 border-t-transparent rounded-full animate-spin theme-spinner"></div>
      <p class="text-slate-400">Memuat pengaturan...</p>
    </div>

    <!-- Error Alert -->
    <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 text-center text-red-400">
      {{ error }}
    </div>

    <div class="space-y-6" v-else>
      <!-- FORM CARD -->
      <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 shadow-xl backdrop-blur-md">
        <form @submit.prevent="saveSettings" class="space-y-8">

          <!-- Data Toko -->
          <section class="space-y-5">
            <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Data Toko</h3>
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-semibold text-slate-400 mb-2">Nama Toko</label>
                <input v-model="form.name" type="text" required placeholder="Kurnia Telur"
                  class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-600 focus:outline-none transition" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-400 mb-2">Slug Katalog</label>
                <input v-model="form.slug" type="text" required placeholder="kurnia-telur"
                  class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-600 focus:outline-none transition" />
                <p class="text-xs text-slate-500 mt-1">{{ currentHost }}/<span class="theme-accent-text font-bold">{{ form.slug || 'slug-toko' }}</span></p>
              </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-semibold text-slate-400 mb-2">Nomor WhatsApp Order</label>
                <input v-model="form.phone" type="tel" required placeholder="6281234567890"
                  class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-600 focus:outline-none transition" />
              </div>
              <!-- Logo Upload -->
              <div>
                <label class="block text-sm font-semibold text-slate-400 mb-2">Logo Toko</label>
                <div class="flex items-center gap-3">
                  <!-- Preview -->
                  <div class="w-14 h-14 rounded-xl border border-slate-700 bg-slate-950 flex items-center justify-center overflow-hidden shrink-0">
                    <img v-if="form.logo" :src="form.logo" class="w-full h-full object-cover" @error="form.logo = ''" />
                    <span v-else class="text-2xl">🏪</span>
                  </div>
                  <!-- Upload button -->
                  <div class="flex-1">
                    <label class="cursor-pointer">
                      <div class="theme-btn-secondary flex items-center gap-2 py-2.5 px-4 rounded-xl text-sm font-semibold transition"
                        :class="uploadingLogo ? 'opacity-60 pointer-events-none' : ''">
                        <span v-if="uploadingLogo">⏳ Mengunggah...</span>
                        <span v-else>📁 Pilih Gambar Logo</span>
                      </div>
                      <input
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="sr-only"
                        :disabled="uploadingLogo"
                        @change="handleLogoUpload"
                      />
                    </label>
                    <p class="text-xs text-slate-500 mt-1.5">Max 2MB • JPG, PNG, WebP</p>
                    <p v-if="uploadLogoError" class="text-xs text-red-400 mt-1">{{ uploadLogoError }}</p>
                  </div>
                  <!-- Remove button -->
                  <button v-if="form.logo" type="button" @click="form.logo = ''" title="Hapus Logo"
                    class="p-2 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition">
                    🗑️
                  </button>
                </div>
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Alamat Toko</label>
              <textarea v-model="form.address" rows="2" placeholder="Alamat toko atau area layanan..."
                class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-600 focus:outline-none transition resize-none"></textarea>
            </div>
          </section>

          <!-- Katalog Publik -->
          <section class="space-y-5 border-t border-slate-800 pt-8">
            <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Katalog Publik</h3>

            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Judul Katalog</label>
              <input v-model="form.catalog_title" type="text" required placeholder="Katalog Online Toko Kami"
                class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-600 focus:outline-none transition" />
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Deskripsi Katalog</label>
              <textarea v-model="form.catalog_description" rows="3" placeholder="Tulis deskripsi singkat toko Anda..."
                class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-600 focus:outline-none transition resize-none"></textarea>
            </div>

            <!-- Banner Upload -->
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Banner Katalog</label>
              <!-- Preview -->
              <div v-if="form.catalog_banner" class="mb-3 rounded-xl overflow-hidden border border-slate-700 relative group">
                <img :src="form.catalog_banner" class="w-full h-32 object-cover" @error="form.catalog_banner = ''" />
                <div class="absolute inset-0 bg-slate-950/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                  <button type="button" @click="form.catalog_banner = ''"
                    class="px-4 py-2 bg-red-500/90 hover:bg-red-600 text-white text-sm font-bold rounded-lg transition">
                    🗑️ Hapus Banner
                  </button>
                </div>
              </div>
              <!-- Upload zone -->
              <label class="cursor-pointer block">
                <div class="border-2 border-dashed border-slate-700 hover:border-slate-500 rounded-xl p-5 text-center transition"
                  :class="uploadingBanner ? 'opacity-60 pointer-events-none' : ''">
                  <div v-if="uploadingBanner" class="space-y-2">
                    <div class="w-8 h-8 border-4 border-t-transparent rounded-full animate-spin theme-spinner mx-auto"></div>
                    <p class="text-sm text-slate-400">Mengunggah banner...</p>
                  </div>
                  <div v-else class="space-y-1">
                    <p class="text-2xl">🖼️</p>
                    <p class="text-sm font-semibold text-slate-300">{{ form.catalog_banner ? 'Ganti Banner' : 'Unggah Banner' }}</p>
                    <p class="text-xs text-slate-500">Max 5MB • JPG, PNG, WebP • Ukuran ideal: 1200×400px</p>
                  </div>
                </div>
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp,image/gif"
                  class="sr-only"
                  :disabled="uploadingBanner"
                  @change="handleBannerUpload"
                />
              </label>
              <p v-if="uploadBannerError" class="text-xs text-red-400 mt-1.5">{{ uploadBannerError }}</p>
            </div>

            <!-- ═══════════════════════════════════════════════════
                 VISUAL THEME PICKER
            ════════════════════════════════════════════════════ -->
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-3">Tema Tampilan Katalog</label>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <button
                  v-for="(t, key) in THEMES"
                  :key="key"
                  type="button"
                  @click="form.theme = key"
                  class="theme-card relative flex flex-col items-center gap-2 p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer overflow-hidden text-center"
                  :class="form.theme === key ? 'border-2' : 'border-slate-800'"
                  :style="form.theme === key
                    ? { borderColor: t.primary, boxShadow: `0 0 20px rgba(${t.primaryRgb},0.35)` }
                    : {}"
                >
                  <!-- Mini page preview -->
                  <div class="w-full h-14 rounded-xl overflow-hidden relative mb-1" :style="{ backgroundColor: t.bgPage }">
                    <!-- Simulated header -->
                    <div class="absolute top-1 left-1 right-1 h-3 rounded-md opacity-80" :style="{ backgroundColor: t.bgSurfaceAlt }"></div>
                    <!-- Simulated cards -->
                    <div class="absolute bottom-1 left-1 w-[45%] h-4 rounded-md opacity-60" :style="{ backgroundColor: t.bgSurface, borderLeft: `3px solid ${t.primary}` }"></div>
                    <div class="absolute bottom-1 right-1 w-[45%] h-4 rounded-md opacity-60" :style="{ backgroundColor: t.bgSurface, borderLeft: `3px solid ${t.primary}` }"></div>
                    <!-- Simulated accent button -->
                    <div class="absolute bottom-2.5 right-2 w-4 h-2 rounded-sm" :style="{ background: `linear-gradient(90deg, ${t.primary}, ${t.secondary})` }"></div>
                    <!-- Selected checkmark -->
                    <div v-if="form.theme === key"
                      class="absolute top-0.5 right-0.5 w-4 h-4 rounded-full flex items-center justify-center text-white text-[9px] font-black"
                      :style="{ background: t.primary }">✓</div>
                  </div>

                  <!-- Theme label -->
                  <span class="text-base">{{ t.emoji }}</span>
                  <div class="leading-tight">
                    <p class="text-xs font-bold text-slate-200">{{ t.label }}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ t.description }}</p>
                  </div>
                </button>
              </div>
              <p class="text-xs text-slate-500 mt-3">
                Pratinjau tema ditampilkan secara langsung di atas. Klik tema untuk memilih, lalu simpan.
              </p>
            </div>

            <!-- Auto WA Redirect -->
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Auto Redirect WhatsApp</label>
              <select v-model="form.auto_whatsapp_redirect"
                class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 focus:outline-none transition">
                <option :value="false">Nonaktif — Pelanggan klik manual</option>
                <option :value="true">Aktif — Redirect otomatis 10 detik</option>
              </select>
            </div>

            <div class="flex flex-wrap gap-6 pt-1">
              <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                <input v-model="form.show_price" id="show_price" type="checkbox"
                  class="theme-checkbox w-5 h-5 rounded bg-slate-950 border-slate-700 focus:ring-0 cursor-pointer" />
                <span class="text-sm font-semibold">Tampilkan Harga</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                <input v-model="form.catalog_enabled" id="catalog_enabled" type="checkbox"
                  class="theme-checkbox w-5 h-5 rounded bg-slate-950 border-slate-700 focus:ring-0 cursor-pointer" />
                <span class="text-sm font-semibold">Aktifkan Katalog</span>
              </label>
            </div>
          </section>

          <!-- ═════════════════════════════════════════════════════════
               PENGATURAN ONGKOS KIRIM
          ══════════════════════════════════════════════════════════ -->
          <section class="space-y-5 border-t border-slate-800 pt-8">
            <div class="flex items-center gap-2">
              <span class="text-xl">🚚</span>
              <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Pengaturan Ongkos Kirim</h3>
            </div>

            <!-- Mode selector -->
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Mode Ongkir</label>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <button
                  v-for="mode in shippingModes"
                  :key="mode.value"
                  type="button"
                  @click="form.shipping_mode = mode.value as 'none' | 'zone' | 'distance' | 'api'"
                  class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all text-center cursor-pointer"
                  :class="form.shipping_mode === mode.value
                    ? 'border-indigo-500 bg-indigo-500/10'
                    : 'border-slate-700 bg-slate-950 hover:border-slate-600'"
                >
                  <span class="text-2xl">{{ mode.icon }}</span>
                  <span class="text-xs font-bold text-slate-200">{{ mode.label }}</span>
                  <span class="text-[10px] text-slate-500 leading-tight">{{ mode.desc }}</span>
                </button>
              </div>
            </div>

            <!-- MODE: DISTANCE — lokasi toko + tarif per jarak -->
            <div v-if="form.shipping_mode === 'distance'" class="space-y-4 bg-slate-950/50 border border-slate-800 rounded-2xl p-5">
              <p class="text-xs text-slate-400">Isi koordinat toko Anda dan atur tarif per bracket jarak (km). Ongkir dihitung otomatis menggunakan jarak garis lurus dari toko ke customer.</p>

              <!-- ══ Lokasi Toko ══ -->
              <div class="space-y-3">
                <label class="block text-xs font-semibold text-slate-400">Lokasi Toko</label>

                <!-- Tombol GPS detect -->
                <button
                  type="button"
                  @click="detectStoreLocation"
                  :disabled="storeGeoLoading"
                  class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold transition"
                  :class="storeGeoLoading
                    ? 'bg-slate-800 text-slate-500 cursor-not-allowed'
                    : 'bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/25'"
                >
                  <span v-if="storeGeoLoading" class="inline-block w-4 h-4 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></span>
                  <span v-else>🎯</span>
                  {{ storeGeoLoading ? 'Mendeteksi lokasi GPS...' : 'Deteksi Otomatis via GPS' }}
                </button>

                <p v-if="storeGeoError" class="text-xs text-red-400 flex items-center gap-1">
                  <span>⚠️</span> {{ storeGeoError }}
                </p>

                <!-- Divider -->
                <div class="flex items-center gap-2">
                  <div class="flex-1 border-t border-slate-800"></div>
                  <span class="text-[10px] text-slate-600 uppercase tracking-wider">atau tempel link Google Maps</span>
                  <div class="flex-1 border-t border-slate-800"></div>
                </div>

                <!-- Google Maps Link input -->
                <div>
                  <input
                    v-model="storeMapsLink"
                    @input="parseStoreMapsLink"
                    @paste="onStoreLinkPaste"
                    type="text"
                    placeholder="https://maps.google.com/... atau -7.5761,110.8189"
                    class="theme-input w-full bg-slate-900 border border-slate-700 rounded-xl py-2.5 px-3 text-slate-100 text-sm placeholder-slate-600 focus:outline-none transition"
                  />
                  <p class="text-[11px] text-slate-600 mt-1">Paste link share dari Google Maps, koordinat, atau klik kanan peta → "Salin koordinat".</p>
                </div>

                <!-- Koordinat aktif -->
                <div v-if="form.store_latitude && form.store_longitude"
                  class="flex items-center gap-3 bg-emerald-500/8 border border-emerald-500/20 rounded-xl p-3"
                >
                  <span class="text-lg">📍</span>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-emerald-400">Koordinat Toko Terdeteksi</p>
                    <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ form.store_latitude?.toFixed(6) }}, {{ form.store_longitude?.toFixed(6) }}</p>
                  </div>
                  <a
                    :href="`https://maps.google.com/?q=${form.store_latitude},${form.store_longitude}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold shrink-0 transition"
                  >🗺️ Cek</a>
                  <button type="button" @click="clearStoreLocation"
                    class="text-slate-600 hover:text-red-400 transition text-sm shrink-0" title="Hapus koordinat">×</button>
                </div>

                <!-- Map preview iframe -->
                <div v-if="storeEmbedUrl" class="rounded-xl overflow-hidden border border-slate-700 relative">
                  <iframe
                    :src="storeEmbedUrl"
                    width="100%"
                    height="200"
                    style="border:0; display:block"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                  ></iframe>
                  <div class="absolute top-2 right-2">
                    <span class="text-[10px] bg-slate-900/80 text-slate-400 px-2 py-1 rounded-lg backdrop-blur">Preview Peta Toko</span>
                  </div>
                </div>
              </div>

              <!-- Label lokasi -->
              <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Label Lokasi Toko (opsional)</label>
                <input v-model="form.store_location_label" type="text" placeholder="Jl. Slamet Riyadi No. 5, Solo"
                  class="theme-input w-full bg-slate-900 border border-slate-700 rounded-xl py-2.5 px-3 text-slate-100 text-sm placeholder-slate-600 focus:outline-none transition" />
                <p class="text-[11px] text-slate-600 mt-1">Label ini ditampilkan ke customer saat checkout sebagai info lokasi asal pengiriman.</p>
              </div>

              <!-- Distance brackets -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="text-xs font-semibold text-slate-400">Tarif Per Jarak</label>
                  <button type="button" @click="addDistanceBracket"
                    class="text-xs px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/25 font-semibold transition">
                    + Tambah Bracket
                  </button>
                </div>
                <div class="space-y-2">
                  <div v-for="(bracket, i) in form.shipping_distances" :key="i" class="flex gap-2 items-center">
                    <div class="flex-1">
                      <div class="flex items-center gap-1">
                        <input v-model.number="bracket.max_km" type="number" min="0" step="0.1" placeholder="Max km"
                          class="theme-input w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-3 text-slate-100 text-xs placeholder-slate-600 focus:outline-none transition" />
                        <span class="text-slate-500 text-xs shrink-0">km →</span>
                      </div>
                    </div>
                    <div class="flex-1">
                      <input v-model.number="bracket.cost" type="number" min="0" step="1000" placeholder="Tarif (Rp)"
                        class="theme-input w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-3 text-slate-100 text-xs placeholder-slate-600 focus:outline-none transition" />
                    </div>
                    <button type="button" @click="removeDistanceBracket(i)"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition shrink-0">🗑️</button>
                  </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-2">Contoh: 10km → Rp 0 (gratis), 20km → Rp 10.000, 30km → Rp 20.000. Urutkan dari terkecil ke terbesar.</p>
              </div>
            </div>

            <!-- MODE: ZONE — daftar zona + tarif -->
            <div v-if="form.shipping_mode === 'zone'" class="space-y-4 bg-slate-950/50 border border-slate-800 rounded-2xl p-5">
              <p class="text-xs text-slate-400">Tambahkan zona pengiriman (misal: nama kota/kecamatan) dan tarif ongkir untuk masing-masing zona. Customer akan memilih zona saat checkout.</p>
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="text-xs font-semibold text-slate-400">Daftar Zona</label>
                  <button type="button" @click="addZone"
                    class="text-xs px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/25 font-semibold transition">
                    + Tambah Zona
                  </button>
                </div>
                <div class="space-y-2">
                  <div v-for="(zone, i) in form.shipping_zones" :key="i" class="flex gap-2 items-center">
                    <input v-model="zone.name" type="text" placeholder="Nama zona (misal: Solo)"
                      class="theme-input flex-1 bg-slate-900 border border-slate-700 rounded-lg py-2 px-3 text-slate-100 text-xs placeholder-slate-600 focus:outline-none transition" />
                    <input v-model.number="zone.cost" type="number" min="0" step="1000" placeholder="Tarif (Rp)"
                      class="theme-input w-32 bg-slate-900 border border-slate-700 rounded-lg py-2 px-3 text-slate-100 text-xs placeholder-slate-600 focus:outline-none transition" />
                    <button type="button" @click="removeZone(i)"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition shrink-0">🗑️</button>
                  </div>
                  <div v-if="!form.shipping_zones?.length" class="text-xs text-slate-500 text-center py-3">
                    Belum ada zona. Klik "+ Tambah Zona" untuk mulai.
                  </div>
                </div>
              </div>
            </div>

            <!-- MODE: API — info placeholder -->
            <div v-if="form.shipping_mode === 'api'" class="bg-slate-950/50 border border-slate-800 rounded-2xl p-5">
              <div class="flex items-center gap-3">
                <span class="text-2xl">🔌</span>
                <div>
                  <p class="text-sm font-semibold text-slate-300">Integrasi API Kurir</p>
                  <p class="text-xs text-slate-500 mt-0.5">Fitur integrasi dengan Biteship / RajaOngkir sedang dalam pengembangan. Untuk saat ini, pilih mode NONE, ZONE, atau DISTANCE.</p>
                </div>
              </div>
            </div>

            <!-- MODE: NONE -->
            <div v-if="form.shipping_mode === 'none'" class="bg-slate-950/50 border border-slate-800 rounded-2xl p-4 flex items-center gap-3">
              <span class="text-xl">🎁</span>
              <p class="text-xs text-slate-400">Gratis ongkir / tanpa biaya pengiriman. Customer tidak perlu membayar ongkir.</p>
            </div>
          </section>

          <section class="space-y-5 border-t border-slate-800 pt-8">
            <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Instruksi Pembayaran</h3>
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Info Rekening Transfer</label>
              <textarea
                v-model="form.bank_transfer_info"
                rows="3"
                placeholder="Contoh: BCA 1234567890 a.n. Toko Anda"
                class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-600 focus:outline-none transition resize-none"></textarea>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-400 mb-2">Info QRIS</label>
              <div v-if="form.qris_image_url" class="mb-3 flex flex-col sm:flex-row gap-4 rounded-2xl border border-slate-800 bg-slate-950 p-4">
                <img :src="form.qris_image_url" alt="QRIS" class="w-40 h-40 object-contain rounded-xl bg-white p-2 shrink-0" />
                <div class="flex-1 min-w-0 space-y-3">
                  <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">Link QRIS</p>
                    <div class="rounded-xl border border-slate-800 bg-slate-900 px-3 py-2 text-xs font-mono theme-accent-text truncate">
                      {{ form.qris_image_url }}
                    </div>
                  </div>
                  <div class="flex flex-wrap gap-2">
                    <button type="button" @click="copyQrisLink"
                      class="py-2 px-3 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs font-bold transition">
                      {{ qrisLinkCopied ? 'Tersalin' : 'Salin Link QRIS' }}
                    </button>
                    <button type="button" @click="removeQrisImage"
                      class="py-2 px-3 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 text-xs font-bold transition">
                      Hapus QRIS
                    </button>
                  </div>
                </div>
              </div>
              <label class="cursor-pointer block mb-3">
                <div class="border-2 border-dashed border-slate-700 hover:border-slate-500 rounded-xl p-4 text-center transition"
                  :class="uploadingQris ? 'opacity-60 pointer-events-none' : ''">
                  <div v-if="uploadingQris" class="space-y-2">
                    <div class="w-7 h-7 border-4 border-t-transparent rounded-full animate-spin theme-spinner mx-auto"></div>
                    <p class="text-sm text-slate-400">Mengunggah QRIS...</p>
                  </div>
                  <div v-else class="space-y-1">
                    <p class="text-sm font-semibold text-slate-300">{{ form.qris_image_url ? 'Ganti QRIS Statis' : 'Upload QRIS Statis' }}</p>
                    <p class="text-xs text-slate-500">Max 3MB - JPG, PNG, WebP</p>
                  </div>
                </div>
                <input
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="sr-only"
                  :disabled="uploadingQris"
                  @change="handleQrisUpload"
                />
              </label>
              <p v-if="uploadQrisError" class="text-xs text-red-400 mb-2">{{ uploadQrisError }}</p>
              <textarea
                v-model="form.qris_info"
                rows="3"
                placeholder="Catatan QRIS tambahan, misalnya nama merchant atau instruksi setelah scan"
                class="theme-input w-full bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 text-slate-100 placeholder-slate-600 focus:outline-none transition resize-none"></textarea>
            </div>
          </section>

          <!-- Alerts -->
          <div v-if="saveError" class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-center text-sm text-red-400">{{ saveError }}</div>
          <div v-if="saveSuccess" class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4 text-center text-sm text-emerald-400">✅ Pengaturan berhasil diperbarui!</div>

          <button type="submit" :disabled="isSaving"
            class="theme-btn w-full py-4 px-6 rounded-xl font-extrabold text-lg transition duration-300 shadow-lg disabled:opacity-50">
            {{ isSaving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
          </button>
        </form>
      </div>

      <!-- CATALOG LINK & QR CODE -->
      <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 shadow-xl backdrop-blur-md space-y-6">
        <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider">Link & QR Code Katalog</h3>
        <div>
          <label class="block text-sm font-semibold text-slate-400 mb-2">Link Katalog Publik</label>
          <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-0 bg-slate-950 border border-slate-700 rounded-xl py-3 px-4 theme-accent-text font-mono text-sm truncate">{{ catalogUrl }}</div>
            <button type="button" @click="copyLink"
              class="shrink-0 py-3 px-4 rounded-xl transition font-bold text-sm"
              :class="linkCopied ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700'">
              {{ linkCopied ? '✓ Tersalin!' : 'Salin Link' }}
            </button>
            <button type="button" @click="shareViaWhatsApp" :disabled="!form.slug"
              class="shrink-0 py-3 px-4 rounded-xl font-bold text-sm transition disabled:opacity-40 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/25">
              💬 Bagikan WA
            </button>
          </div>
        </div>

        <!-- QR Code: Locked for Free plan -->
        <div class="relative">
          <label class="block text-sm font-semibold text-slate-400 mb-3">QR Code Katalog</label>

          <!-- FREE PLAN LOCK -->
          <div v-if="!authStore.isPro"
            class="relative overflow-hidden rounded-2xl border border-indigo-500/20 bg-slate-950/70 p-6"
          >
            <!-- blurred preview -->
            <div class="flex flex-col sm:flex-row gap-6 items-start opacity-30 blur-sm pointer-events-none select-none" aria-hidden="true">
              <div class="w-48 h-48 bg-slate-800 border border-slate-700 rounded-2xl flex items-center justify-center shrink-0">
                <span class="text-6xl">📷</span>
              </div>
              <div class="flex flex-col gap-3 pt-1">
                <div class="w-40 h-10 rounded-xl bg-slate-700"></div>
                <div class="w-36 h-10 rounded-xl bg-slate-700"></div>
              </div>
            </div>
            <!-- Lock overlay -->
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-center px-4">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-2xl shadow-lg shadow-indigo-500/30">
                🔒
              </div>
              <p class="text-sm font-bold text-slate-200">QR Code Katalog — Fitur Pro</p>
              <p class="text-xs text-slate-400 max-w-xs">Upgrade ke Paket Pro untuk membuat & mengunduh QR Code katalog Anda.</p>
              <router-link to="/dashboard/settings"
                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl font-bold text-white text-sm transition-all shadow-md shadow-indigo-500/30 hover:-translate-y-0.5"
                style="background: linear-gradient(135deg, #6366f1, #8b5cf6)"
              >⚡ Upgrade ke Pro</router-link>
            </div>
          </div>

          <!-- PRO: QR Code normal -->
          <div v-else class="flex flex-col sm:flex-row gap-6 items-start">
            <div class="w-48 h-48 bg-slate-950 border border-slate-700 rounded-2xl flex items-center justify-center overflow-hidden shrink-0">
              <div v-if="qrLoading" class="flex flex-col items-center gap-2">
                <div class="w-8 h-8 border-3 border-t-transparent rounded-full animate-spin theme-spinner"></div>
                <p class="text-xs text-slate-500">Membuat QR...</p>
              </div>
              <img v-else-if="qrImage" :src="qrImage" alt="QR Code Katalog" class="w-full h-full object-contain p-2" />
              <div v-else class="text-center p-4">
                <p class="text-4xl mb-2">📷</p>
                <p class="text-xs text-slate-500">Simpan pengaturan lalu muat QR Code</p>
              </div>
            </div>
            <div class="flex flex-col gap-3 justify-start pt-1">
              <button type="button" @click="loadQrCode" :disabled="qrLoading || !form.slug"
                class="theme-btn-secondary py-3 px-5 rounded-xl font-semibold text-sm transition disabled:opacity-40">
                🔄 Muat / Perbarui QR
              </button>
              <button type="button" @click="downloadQr" :disabled="!qrImage"
                class="py-3 px-5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/25 font-semibold text-sm transition disabled:opacity-40">
                ⬇️ Unduh QR Code
              </button>
              <p class="text-xs text-slate-500 max-w-xs leading-relaxed">
                QR Code mengarah ke <span class="text-slate-300">{{ catalogUrl }}</span>.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted, nextTick } from 'vue';
import api, { uploadFile } from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const originalTheme = ref('default');


const isLoading   = ref(true);
const isSaving    = ref(false);
const error       = ref<string | null>(null);
const saveError   = ref<string | null>(null);
const saveSuccess = ref(false);
const linkCopied  = ref(false);
const qrImage     = ref<string | null>(null);
const qrLoading   = ref(false);
const qrFormat    = ref<'png' | 'svg'>('png');

// Upload state
const uploadingLogo   = ref(false);
const uploadingBanner = ref(false);
const uploadingQris   = ref(false);
const uploadLogoError   = ref<string | null>(null);
const uploadBannerError = ref<string | null>(null);
const uploadQrisError   = ref<string | null>(null);
const qrisLinkCopied = ref(false);

const form = ref({
  name: '', slug: '', phone: '', address: '', logo: '',
  catalog_title: '', catalog_description: '', catalog_banner: '',
  bank_transfer_info: '', qris_info: '', qris_image_url: '',
  theme: 'default', show_price: true, catalog_enabled: true, auto_whatsapp_redirect: false,
  // Shipping fields
  shipping_mode: 'none' as 'none' | 'zone' | 'distance' | 'api',
  store_latitude: null as number | null,
  store_longitude: null as number | null,
  store_location_label: '',
  shipping_zones: [] as Array<{ name: string; cost: number }>,
  shipping_distances: [] as Array<{ max_km: number; cost: number }>,
});

// Shipping mode options
const shippingModes = [
  { value: 'none',     icon: '🎁', label: 'NONE',     desc: 'Tanpa ongkir' },
  { value: 'zone',     icon: '📍', label: 'ZONE',     desc: 'Per zona/kota' },
  { value: 'distance', icon: '📏', label: 'DISTANCE', desc: 'Per jarak (km)' },
  { value: 'api',      icon: '🔌', label: 'API',      desc: 'Biteship/Raja Ongkir' },
];

function addDistanceBracket() {
  form.value.shipping_distances.push({ max_km: 0, cost: 0 });
}
function removeDistanceBracket(i: number) {
  form.value.shipping_distances.splice(i, 1);
}
function addZone() {
  form.value.shipping_zones.push({ name: '', cost: 0 });
}
function removeZone(i: number) {
  form.value.shipping_zones.splice(i, 1);
}

// ══════════════════════════════════════════════════════════════════════════════════
// Store Location Auto-Detection
// ══════════════════════════════════════════════════════════════════════════════════
const storeGeoLoading = ref(false);
const storeGeoError   = ref<string | null>(null);
const storeMapsLink   = ref('');

/** Computed: Google Maps embed URL from current store coordinates */
const storeEmbedUrl = computed(() => {
  const apiKey = (import.meta as any).env?.VITE_GOOGLE_MAPS_API_KEY;
  const lat = form.value.store_latitude;
  const lng = form.value.store_longitude;
  if (!lat || !lng) return null;
  if (apiKey) {
    return `https://www.google.com/maps/embed/v1/place?key=${apiKey}&q=${lat},${lng}&zoom=16`;
  }
  // Fallback: OpenStreetMap embed (no API key required)
  return `https://www.openstreetmap.org/export/embed.html?bbox=${lng - 0.005},${lat - 0.005},${lng + 0.005},${lat + 0.005}&layer=mapnik&marker=${lat},${lng}`;
});

/** Detect store location via browser GPS */
function detectStoreLocation() {
  storeGeoError.value = null;
  if (!navigator.geolocation) {
    storeGeoError.value = 'Browser Anda tidak mendukung GPS.';
    return;
  }
  storeGeoLoading.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      form.value.store_latitude  = parseFloat(pos.coords.latitude.toFixed(7));
      form.value.store_longitude = parseFloat(pos.coords.longitude.toFixed(7));
      storeMapsLink.value = `${form.value.store_latitude},${form.value.store_longitude}`;
      storeGeoLoading.value = false;
    },
    (err) => {
      storeGeoLoading.value = false;
      if (err.code === 1)      storeGeoError.value = 'Akses lokasi ditolak. Izinkan browser mengakses GPS.';
      else if (err.code === 2) storeGeoError.value = 'Sinyal GPS lemah. Coba di tempat terbuka.';
      else                     storeGeoError.value = 'Gagal mendeteksi lokasi. Coba lagi.';
    },
    { timeout: 12000, enableHighAccuracy: true }
  );
}

/**
 * Parse various Google Maps URL / coordinate formats:
 * - "lat,lng"                      e.g. "-7.576149,110.818935"
 * - "@lat,lng,zoom"                e.g. "@-7.576,110.818,17z"
 * - "?q=lat,lng"                   query params
 * - "!3d{lat}!4d{lng}"             path segments (long share URL)
 * - "maps.google.com/place/..."    place URL with ll= param
 */
function extractCoordsFromMapsLink(val: string): { lat: number; lng: number } | null {
  if (!val) return null;

  // Plain "lat,lng" or "lat, lng"
  const plainCoord = val.trim().match(/^(-?\d{1,3}\.\d+)[,\s]+(-?\d{1,3}\.\d+)$/);
  if (plainCoord) return { lat: parseFloat(plainCoord[1]), lng: parseFloat(plainCoord[2]) };

  // "@lat,lng,zoom"
  const atCoord = val.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
  if (atCoord) return { lat: parseFloat(atCoord[1]), lng: parseFloat(atCoord[2]) };

  // "!3d{lat}!4d{lng}" (long encoded share link)
  const encodedCoord = val.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
  if (encodedCoord) return { lat: parseFloat(encodedCoord[1]), lng: parseFloat(encodedCoord[2]) };

  // "?q=lat,lng" or "?ll=lat,lng"
  const qParam = val.match(/[?&](?:q|ll)=(-?\d+\.\d+),(-?\d+\.\d+)/);
  if (qParam) return { lat: parseFloat(qParam[1]), lng: parseFloat(qParam[2]) };

  // Any two decimal numbers in the URL as fallback
  const fallback = val.match(/(-?\d{1,3}\.\d{4,})[,/\s]+(-?\d{1,3}\.\d{4,})/);
  if (fallback) return { lat: parseFloat(fallback[1]), lng: parseFloat(fallback[2]) };

  return null;
}

function parseStoreMapsLink() {
  const coords = extractCoordsFromMapsLink(storeMapsLink.value);
  if (coords) {
    form.value.store_latitude  = parseFloat(coords.lat.toFixed(7));
    form.value.store_longitude = parseFloat(coords.lng.toFixed(7));
    storeGeoError.value = null;
  }
}

// Handle paste event — give DOM time to update value before parsing
function onStoreLinkPaste(event: ClipboardEvent) {
  const pasted = event.clipboardData?.getData('text') || '';
  nextTick(() => {
    storeMapsLink.value = pasted.trim();
    parseStoreMapsLink();
  });
}

function clearStoreLocation() {
  form.value.store_latitude  = null;
  form.value.store_longitude = null;
  storeMapsLink.value = '';
  storeGeoError.value = null;
}

// Sync storeMapsLink when settings are loaded
function syncStoreMapsLink() {
  if (form.value.store_latitude && form.value.store_longitude) {
    storeMapsLink.value = `${form.value.store_latitude},${form.value.store_longitude}`;
  }
}

// 6 full visual themes — each defines full page appearance
const THEMES: Record<string, {
  emoji: string; label: string; description: string;
  primary: string; secondary: string; primaryRgb: string;
  bgPage: string; bgSurface: string; bgSurfaceAlt: string;
  borderColor: string;
}> = {
  default: {
    emoji: '🌙', label: 'Midnight Indigo', description: 'Gelap elegan, ungu',
    primary: '#6366f1', secondary: '#8b5cf6', primaryRgb: '99, 102, 241',
    bgPage: '#020617', bgSurface: 'rgba(15,23,42,0.75)', bgSurfaceAlt: '#1e293b', borderColor: '#334155',
  },
  red: {
    emoji: '🌶️', label: 'Spicy Crimson', description: 'Berani, merah membara',
    primary: '#ef4444', secondary: '#b91c1c', primaryRgb: '239, 68, 68',
    bgPage: '#0f0202', bgSurface: 'rgba(26,5,5,0.85)', bgSurfaceAlt: '#1f0808', borderColor: '#3d1414',
  },
  emerald: {
    emoji: '🌿', label: 'Deep Forest', description: 'Segar, hijau alam rimbun',
    primary: '#10b981', secondary: '#059669', primaryRgb: '16, 185, 129',
    bgPage: '#011208', bgSurface: 'rgba(2,30,14,0.8)', bgSurfaceAlt: '#032d14', borderColor: '#064e23',
  },
  light: {
    emoji: '☀️', label: 'Clean Light', description: 'Bersih, latar terang minimalis',
    primary: '#4f46e5', secondary: '#7c3aed', primaryRgb: '79, 70, 229',
    bgPage: '#f8fafc', bgSurface: 'rgba(255,255,255,0.85)', bgSurfaceAlt: '#f1f5f9', borderColor: '#cbd5e1',
  },
  warm: {
    emoji: '🔥', label: 'Warm Amber', description: 'Hangat, nuansa keemasan',
    primary: '#f59e0b', secondary: '#d97706', primaryRgb: '245, 158, 11',
    bgPage: '#0d0900', bgSurface: 'rgba(26,18,0,0.85)', bgSurfaceAlt: '#271e00', borderColor: '#3d2f00',
  },
  ocean: {
    emoji: '🌊', label: 'Deep Ocean', description: 'Tenang, biru lautan dalam',
    primary: '#0ea5e9', secondary: '#0284c7', primaryRgb: '14, 165, 233',
    bgPage: '#00060e', bgSurface: 'rgba(0,20,40,0.85)', bgSurfaceAlt: '#001a30', borderColor: '#003060',
  },
};

const currentOrigin = computed(() => typeof window !== 'undefined' ? window.location.origin : 'https://umkmorder.id');
const currentHost   = computed(() => typeof window !== 'undefined' ? window.location.host : 'umkmorder.id');
const catalogUrl    = computed(() =>
  form.value.slug ? `${currentOrigin.value}/${form.value.slug}` : `${currentOrigin.value}/slug-toko`
);

const themeVars = computed(() => {
  const key = form.value.theme || 'default';
  const t = THEMES[key] ?? THEMES['default'];
  return {
    '--theme-primary':     t.primary,
    '--theme-secondary':   t.secondary,
    '--theme-primary-rgb': t.primaryRgb,
  };
});

async function fetchSettings() {
  isLoading.value = true;
  error.value = null;
  try {
    const res = await api.get('/tenant/settings');
    if (res.data.status === 'success') {
      const { tenant, catalog_setting: cs = {} } = res.data.data;
      form.value = {
        name: tenant.name || '', slug: tenant.slug || '',
        phone: tenant.phone || '', address: tenant.address || '', logo: tenant.logo || '',
        catalog_title: cs.catalog_title || tenant.name || '',
        catalog_description: cs.catalog_description || '',
        catalog_banner: cs.catalog_banner || '',
        bank_transfer_info: cs.bank_transfer_info || '',
        qris_info: cs.qris_info || '',
        qris_image_url: cs.qris_image_url || '',
        theme: cs.theme || 'default',
        show_price: cs.show_price !== false,
        catalog_enabled: cs.catalog_enabled !== false,
        auto_whatsapp_redirect: cs.auto_whatsapp_redirect === true,
        // Shipping fields
        shipping_mode: cs.shipping_mode || 'none',
        store_latitude: cs.store_latitude ?? null,
        store_longitude: cs.store_longitude ?? null,
        store_location_label: cs.store_location_label || '',
        shipping_zones: cs.shipping_zones || [],
        shipping_distances: cs.shipping_distances || [],
      };
      originalTheme.value = cs.theme || 'default';
      syncStoreMapsLink();
      if (form.value.slug) loadQrCode();
    } else { error.value = res.data.message || 'Gagal memuat pengaturan'; }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal terhubung ke server';
  } finally { isLoading.value = false; }
}
onMounted(fetchSettings);

function getUploadErrorMessage(error: any, fallback: string): string {
  const data = error.response?.data;
  return data?.errors?.file?.[0] || data?.message || fallback;
}

async function handleLogoUpload(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  uploadingLogo.value = true;
  uploadLogoError.value = null;

  try {
    const res = await uploadFile('/upload/logo', file);
    if (res.data.status === 'success') {
      form.value.logo = res.data.url;
    } else {
      uploadLogoError.value = res.data.message || 'Gagal mengunggah logo';
    }
  } catch (e: any) {
    uploadLogoError.value = getUploadErrorMessage(e, 'Gagal mengunggah logo');
  } finally {
    uploadingLogo.value = false;
    input.value = '';
  }
}

async function handleBannerUpload(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  uploadingBanner.value = true;
  uploadBannerError.value = null;

  try {
    const res = await uploadFile('/upload/banner', file);
    if (res.data.status === 'success') {
      form.value.catalog_banner = res.data.url;
    } else {
      uploadBannerError.value = res.data.message || 'Gagal mengunggah banner';
    }
  } catch (e: any) {
    uploadBannerError.value = getUploadErrorMessage(e, 'Gagal mengunggah banner');
  } finally {
    uploadingBanner.value = false;
    input.value = '';
  }
}

async function handleQrisUpload(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  uploadingQris.value = true;
  uploadQrisError.value = null;

  try {
    const res = await uploadFile('/upload/qris', file);
    if (res.data.status === 'success') {
      form.value.qris_image_url = res.data.url;
      if (!form.value.qris_info) {
        form.value.qris_info = res.data.url;
      }
    } else {
      uploadQrisError.value = res.data.message || 'Gagal mengunggah QRIS';
    }
  } catch (e: any) {
    uploadQrisError.value = getUploadErrorMessage(e, 'Gagal mengunggah QRIS');
  } finally {
    uploadingQris.value = false;
    input.value = '';
  }
}

function removeQrisImage() {
  form.value.qris_image_url = '';
  if (form.value.qris_info?.startsWith('http')) {
    form.value.qris_info = '';
  }
}

async function saveSettings() {
  isSaving.value = true; saveError.value = null; saveSuccess.value = false;
  try {
    const res = await api.put('/tenant/settings', form.value);
    if (res.data.status === 'success') {
      const tenant = res.data.data.tenant;
      authStore.tenant = tenant;
      localStorage.setItem('auth_tenant', JSON.stringify(tenant));
      originalTheme.value = form.value.theme;
      saveSuccess.value = true;
      setTimeout(() => { saveSuccess.value = false; }, 4000);
    } else { saveError.value = res.data.message || 'Gagal menyimpan'; }
  } catch (e: any) {
    saveError.value = e.response?.data?.message || 'Terjadi kesalahan input data';
  } finally { isSaving.value = false; }
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(catalogUrl.value);
    linkCopied.value = true;
    setTimeout(() => { linkCopied.value = false; }, 2500);
  } catch {}
}

async function copyQrisLink() {
  if (!form.value.qris_image_url) return;
  try {
    await navigator.clipboard.writeText(form.value.qris_image_url);
    qrisLinkCopied.value = true;
    setTimeout(() => { qrisLinkCopied.value = false; }, 2500);
  } catch {}
}

function shareViaWhatsApp() {
  if (!form.value.slug) return;

  const shopName = form.value.name || 'Toko kami';
  const title = form.value.catalog_title || `Katalog ${shopName}`;
  const message =
    `Halo! 👋\n\n` +
    `Yuk lihat ${title}:\n` +
    `${catalogUrl.value}\n\n` +
    `Pesan langsung lewat link katalog ya! 🛒`;

  const url = `https://wa.me/?text=${encodeURIComponent(message)}`;
  window.open(url, '_blank', 'noopener,noreferrer');
}

async function loadQrCode() {
  if (!form.value.slug || !authStore.isPro) return;
  qrLoading.value = true; qrImage.value = null;
  try {
    const res = await api.get('/catalog/qr');
    if (res.data.status === 'success') { qrImage.value = res.data.qr_image; qrFormat.value = res.data.format; }
  } catch (e: any) { console.error('QR load failed:', e.message); }
  finally { qrLoading.value = false; }
}

function downloadQr() {
  if (!qrImage.value) return;
  const ext = qrFormat.value === 'svg' ? 'svg' : 'png';
  const link = document.createElement('a');
  link.href = qrImage.value;
  link.download = `qrcode-${form.value.slug || 'toko'}.${ext}`;
  document.body.appendChild(link); link.click(); document.body.removeChild(link);
}

// Watch theme changes to instantly update dashboard root visual styles
watch(() => form.value.theme, (newTheme) => {
  const tenant = authStore.tenant as any;
  if (tenant) {
    if (!tenant.catalog_setting) {
      tenant.catalog_setting = {};
    }
    tenant.catalog_setting.theme = newTheme;
  }
});

// Restore original saved theme when leaving the settings page
onUnmounted(() => {
  const tenant = authStore.tenant as any;
  if (tenant) {
    if (!tenant.catalog_setting) {
      tenant.catalog_setting = {};
    }
    tenant.catalog_setting.theme = originalTheme.value;
  }
});
</script>

<style scoped>
.theme-spinner {
  border-color: var(--theme-primary);
  border-top-color: transparent;
}
.theme-accent-text { color: var(--theme-primary); }
.theme-btn {
  background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(var(--theme-primary-rgb), 0.35);
  cursor: pointer;
}
.theme-btn:hover:not(:disabled) {
  filter: brightness(1.1);
  box-shadow: 0 6px 20px rgba(var(--theme-primary-rgb), 0.45);
}
.theme-btn-secondary {
  background-color: rgba(var(--theme-primary-rgb), 0.1);
  color: var(--theme-primary);
  border: 1px solid rgba(var(--theme-primary-rgb), 0.25);
  cursor: pointer;
}
.theme-btn-secondary:hover:not(:disabled) {
  background-color: rgba(var(--theme-primary-rgb), 0.2);
}
.theme-input:focus {
  border-color: var(--theme-primary) !important;
  box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.15);
}
.theme-checkbox { accent-color: var(--theme-primary); }

/* Theme picker card */
.theme-card {
  background-color: #0f172a;
  transition: transform 0.15s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.theme-card:hover { transform: translateY(-2px); }
</style>
