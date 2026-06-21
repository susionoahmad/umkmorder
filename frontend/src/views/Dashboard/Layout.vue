<template>
  <div class="min-h-screen font-sans flex dashboard-root" :style="themeVars">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between shrink-0 hidden md:flex">
      <div>
        <!-- Logo Header -->
        <div class="h-20 px-6 border-b border-slate-850 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg theme-logo-bg flex items-center justify-center font-black text-lg shadow-md">
            U
          </div>
          <span class="font-extrabold text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-slate-100 to-slate-400">
            UMKMOrder
          </span>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-2">
          <router-link 
            v-for="item in navItems" 
            :key="item.path" 
            :to="item.path" 
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 text-sm font-semibold hover:bg-slate-800/50 hover:text-slate-200"
            exact-active-class="theme-active-nav text-white shadow-lg"
          >
            <span class="text-lg">{{ item.icon }}</span>
            <span>{{ item.label }}</span>
            <!-- Notification Badge -->
            <span 
              v-if="item.path === '/dashboard/orders' && unprocessedCount > 0" 
              class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white animate-pulse"
            >
              {{ unprocessedCount }}
            </span>
          </router-link>
        </nav>
      </div>

      <!-- Footer / Back to Hub -->
      <div class="p-4 border-t border-slate-850 space-y-2">
        <router-link 
          to="/"
          class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-850 transition duration-200 text-xs font-bold text-slate-400"
        >
          <span>🏠</span>
          <span>Halaman Utama</span>
        </router-link>

        <button 
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-500/10 text-red-400 transition duration-200 text-xs font-bold"
        >
          <span>🚪</span>
          <span>Keluar Akun</span>
        </button>
      </div>
    </aside>

    <!-- Mobile Sidebar Drawer -->
    <div v-if="isMobileMenuOpen" class="fixed inset-0 z-50 md:hidden flex">
      <!-- Backdrop Overlay -->
      <div 
        @click="isMobileMenuOpen = false" 
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity duration-300"
      ></div>

      <!-- Drawer Panel -->
      <aside class="relative w-72 bg-slate-900 border-r border-slate-800 flex flex-col justify-between shrink-0 h-full max-w-[80vw] z-10 transition-transform duration-300 shadow-2xl animate-[slide-in_0.2s_ease-out]">
        <div>
          <!-- Drawer Header -->
          <div class="h-20 px-6 border-b border-slate-850 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg theme-logo-bg flex items-center justify-center font-black text-lg shadow-md text-white">
                U
              </div>
              <span class="font-extrabold text-lg tracking-tight text-slate-100">
                UMKMOrder
              </span>
            </div>
            <!-- Close Button -->
            <button 
              @click="isMobileMenuOpen = false" 
              class="p-2 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-slate-100 transition"
            >
              ✕
            </button>
          </div>

          <!-- Navigation Links -->
          <nav class="p-4 space-y-2">
            <router-link 
              v-for="item in navItems" 
              :key="item.path" 
              :to="item.path" 
              @click="isMobileMenuOpen = false"
              class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 text-sm font-semibold hover:bg-slate-800/50 hover:text-slate-200"
              exact-active-class="theme-active-nav text-white shadow-lg"
            >
              <span class="text-lg">{{ item.icon }}</span>
              <span>{{ item.label }}</span>
              <!-- Notification Badge for Mobile -->
              <span 
                v-if="item.path === '/dashboard/orders' && unprocessedCount > 0" 
                class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white animate-pulse"
              >
                {{ unprocessedCount }}
              </span>
            </router-link>
          </nav>
        </div>

        <!-- Footer / Back to Hub -->
        <div class="p-4 border-t border-slate-850 space-y-2">
          <router-link 
            to="/"
            @click="isMobileMenuOpen = false"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-slate-850 transition duration-200 text-xs font-bold text-slate-400"
          >
            <span>🏠</span>
            <span>Halaman Utama</span>
          </router-link>

          <button 
            @click="handleLogout(); isMobileMenuOpen = false"
            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-red-500/10 text-red-400 transition duration-200 text-xs font-bold"
          >
            <span>🚪</span>
            <span>Keluar Akun</span>
          </button>
        </div>
      </aside>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Topbar Header -->
      <header class="h-20 border-b border-slate-850 bg-slate-900/60 backdrop-blur-md px-6 flex items-center justify-between z-10">
        <!-- Mobile Menu Trigger & Title -->
        <div class="flex items-center gap-4">
          <button 
            @click="isMobileMenuOpen = true" 
            class="md:hidden p-2 rounded-xl transition theme-menu-trigger text-sm"
          >
            <span>☰</span>
          </button>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">{{ currentTitle }}</h2>
        </div>

        <!-- Topbar Right Items -->
        <div class="flex items-center gap-5">
          <!-- Notification Bell -->
          <div class="relative">
            <button 
              @click.stop="toggleBellDropdown" 
              class="w-10 h-10 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-850 flex items-center justify-center text-lg relative transition duration-200 focus:outline-none"
              title="Notifikasi"
            >
              <span>🔔</span>
              <span 
                v-if="unprocessedCount > 0 || recentPaidReceivables.length > 0" 
                class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center font-extrabold text-[10px] animate-bounce shadow-md border border-slate-900"
              >
                {{ unprocessedCount + recentPaidReceivables.length }}
              </span>
            </button>

            <!-- Dropdown Popover -->
            <div 
              v-if="isBellDropdownOpen" 
              class="absolute right-0 mt-3 w-80 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-4 space-y-3 z-50 animate-[slide-in_0.15s_ease-out] theme-card"
            >
              <!-- Header -->
              <div class="flex justify-between items-center pb-2 border-b border-slate-850">
                <span class="font-bold text-sm text-slate-200">Notifikasi ({{ unprocessedCount + recentPaidReceivables.length }})</span>
                <router-link 
                  to="/dashboard/orders" 
                  @click="isBellDropdownOpen = false"
                  class="text-[10px] font-bold text-indigo-400 hover:text-indigo-300 transition"
                >
                  Lihat Semua
                </router-link>
              </div>

              <!-- Scrollable content -->
              <div class="max-h-72 overflow-y-auto space-y-1">

                <!-- Unprocessed Orders Section -->
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest pt-1">Pesanan Belum Diproses ({{ unprocessedCount }})</p>
                <div v-if="latestUnprocessedOrders.length === 0" class="py-3 text-center text-xs text-slate-600">
                  Semua pesanan sudah ditangani 👍
                </div>
                <div 
                  v-for="order in latestUnprocessedOrders" 
                  :key="'ord-'+order.id" 
                  @click="handleNotificationClick(order.id)"
                  class="cursor-pointer hover:bg-slate-800/50 p-2.5 rounded-xl transition duration-150"
                >
                  <div class="flex justify-between items-start">
                    <span class="font-bold text-xs text-slate-200">{{ order.invoice_number }}</span>
                    <span class="text-[9px] text-slate-500 font-semibold">{{ formatTimeAgo(order.created_at || order.order_date) }}</span>
                  </div>
                  <div class="text-xs text-slate-400 mt-0.5 font-medium">{{ order.customer?.name || 'Pelanggan' }}</div>
                  <div class="flex justify-between items-center mt-1.5">
                    <span class="text-xs font-bold text-indigo-400">Rp {{ formatRupiah(order.grand_total) }}</span>
                    <span 
                      :class="[
                        'text-[9px] font-bold px-1.5 py-0.5 rounded border uppercase tracking-wide',
                        order.status === 'draft'
                          ? 'text-slate-400 bg-slate-700/50 border-slate-600'
                          : 'text-amber-400 bg-amber-500/10 border-amber-500/15'
                      ]"
                    >
                      {{ order.status === 'draft' ? 'Draft' : 'Baru' }}
                    </span>
                  </div>
                </div>

                <!-- Paid Receivables Section -->
                <template v-if="recentPaidReceivables.length > 0">
                  <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest pt-3 border-t border-slate-850/50">Pembayaran Lunas (24 Jam Terakhir)</p>
                  <div 
                    v-for="payment in recentPaidReceivables" 
                    :key="'pay-'+payment.id" 
                    @click="handleNotificationClick(payment.order_id)"
                    class="cursor-pointer hover:bg-slate-800/50 p-2.5 rounded-xl transition duration-150"
                  >
                    <div class="flex justify-between items-start">
                      <span class="font-bold text-xs text-slate-200">{{ payment.invoice_number }}</span>
                      <span class="text-[9px] text-emerald-400 font-bold bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/15">✓ Lunas</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5 font-medium">{{ payment.customer_name }}</div>
                    <div class="text-xs font-black text-emerald-400 mt-1">Rp {{ formatRupiah(payment.paid_amount) }}</div>
                  </div>
                </template>

              </div>
            </div>
          </div>

          <!-- User Info -->
          <div class="flex items-center gap-3">
            <div class="flex flex-col items-end text-xs hidden sm:flex">
              <span class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ authStore.user?.name }}</span>
              <span class="font-medium capitalize" :style="{ color: 'var(--text-muted)' }">{{ authStore.user?.role }} • {{ authStore.tenant?.name }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold theme-accent-text shadow-sm">
              {{ authStore.user?.name.charAt(0) }}
            </div>
          </div>
        </div>
      </header>

      <!-- Dashboard View Port -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8">
        <router-view />
      </main>
    </div>

    <!-- Floating Toast Notifications -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none">
      <div 
        v-for="toast in activeToasts" 
        :key="toast.id" 
        :class="[
          'pointer-events-auto bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-2xl flex gap-4 items-start border-l-4 animate-[slide-in-right_0.25s_ease-out] theme-card',
          toast.type === 'payment' ? 'border-l-emerald-500' : 'border-l-indigo-500'
        ]"
      >
        <div class="text-2xl mt-0.5">{{ toast.type === 'payment' ? '💰' : '🛍️' }}</div>
        <div class="flex-1 space-y-1 text-left">
          <div class="flex justify-between items-start">
            <h4 class="font-bold text-sm text-slate-100">
              {{ toast.type === 'payment' ? '💳 Pembayaran Lunas!' : '🛍️ Pesanan Baru Masuk!' }}
            </h4>
            <button @click="dismissToast(toast.id)" class="text-slate-500 hover:text-slate-300 font-bold text-xs ml-2">✕</button>
          </div>

          <!-- Order Toast -->
          <template v-if="toast.type === 'order'">
            <p class="text-xs text-slate-400">
              <strong>{{ toast.customerName }}</strong> membuat pesanan <strong>{{ toast.invoiceNumber }}</strong>.
            </p>
            <div class="flex justify-between items-center pt-2">
              <div class="flex items-center gap-2">
                <span class="text-xs font-black text-indigo-400">Rp {{ formatRupiah(toast.grandTotal) }}</span>
                <span 
                  :class="[
                    'text-[9px] font-bold px-1.5 py-0.5 rounded border uppercase',
                    toast.status === 'draft' ? 'text-slate-400 bg-slate-700/50 border-slate-600' : 'text-amber-400 bg-amber-500/10 border-amber-500/15'
                  ]"
                >
                  {{ toast.status === 'draft' ? 'Draft' : 'Baru' }}
                </span>
              </div>
              <button 
                @click="handleNotificationClick(toast.orderId); dismissToast(toast.id)" 
                class="py-1 px-2.5 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-[10px] transition shadow-md"
              >
                Lihat Detail
              </button>
            </div>
          </template>

          <!-- Payment Toast -->
          <template v-else>
            <p class="text-xs text-slate-400">
              <strong>{{ toast.customerName }}</strong> — pesanan <strong>{{ toast.invoiceNumber }}</strong> telah lunas.
            </p>
            <div class="flex justify-between items-center pt-2">
              <span class="text-xs font-black text-emerald-400">Rp {{ formatRupiah(toast.paidAmount) }}</span>
              <button 
                @click="handleNotificationClick(toast.orderId); dismissToast(toast.id)" 
                class="py-1 px-2.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[10px] transition shadow-md"
              >
                Lihat Detail
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Modal Upgrade ke Pro -->
    <div 
      v-if="authStore.showUpgradeModal" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md transition-all duration-300"
    >
      <div class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl flex flex-col max-h-[90vh] text-slate-200 overflow-hidden">
        <!-- Background glowing orbs -->
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- 1. Checking Pending State Loading -->
        <div v-if="isCheckingPending" class="flex flex-col items-center justify-center py-16 text-center space-y-4 my-auto">
          <div class="w-10 h-10 border-3 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
          <p class="text-xs text-slate-400">Memeriksa status langganan...</p>
        </div>

        <!-- 2. Pending Payment View -->
        <div v-else-if="pendingInvoice" class="flex flex-col h-full overflow-hidden space-y-4">
          <!-- Header -->
          <div class="flex items-start justify-between pb-2 shrink-0">
            <div class="space-y-1">
              <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider">
                <span>⏳</span> Menunggu Verifikasi
              </div>
              <h3 class="text-xl sm:text-2xl font-black text-slate-100">Konfirmasi Pembayaran</h3>
            </div>
            <button 
              @click="authStore.showUpgradeModal = false" 
              class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-750 flex items-center justify-center text-slate-400 hover:text-slate-200 transition"
            >
              ✕
            </button>
          </div>

          <!-- Body (Scrollable) -->
          <div class="flex-1 overflow-y-auto pr-1 space-y-4 min-h-0">
            <div class="bg-slate-950/50 border border-slate-800 rounded-2xl p-4 text-xs sm:text-sm space-y-3">
              <p class="text-slate-350 leading-relaxed">
                Permintaan upgrade Anda telah terdaftar dengan invoice <strong class="text-slate-200">#{{ pendingInvoice.invoice_number }}</strong>. Fitur Pro akan aktif otomatis setelah pembayaran Anda diverifikasi oleh Super Admin.
              </p>
              <div class="flex items-center justify-between border-t border-slate-850 pt-3">
                <span class="text-xs text-slate-455">Status Tagihan:</span>
                <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase">Belum Lunas</span>
              </div>
            </div>

            <!-- Payment Instructions -->
            <div class="space-y-3">
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Metode Pembayaran</label>
              
              <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 flex flex-col items-center justify-center text-center space-y-4">
                <div class="w-full flex flex-col sm:flex-row items-center gap-4 justify-center bg-slate-900/60 p-3 rounded-xl border border-slate-850">
                  <div class="w-24 h-24 bg-white border border-slate-200 rounded-lg p-1.5 flex items-center justify-center shrink-0 shadow-md">
                    <img v-if="platformSettings?.admin_qris_image_url" :src="platformSettings.admin_qris_image_url" alt="QRIS" class="w-full h-full object-contain" />
                    <div v-else class="grid grid-cols-4 gap-1 w-full h-full opacity-90 p-1 bg-slate-950 rounded">
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                    </div>
                  </div>
                  <div class="text-left space-y-1">
                    <p class="text-xs font-bold text-slate-300">📱 Scan QRIS E-Wallet</p>
                    <p class="text-[10px] text-slate-400">Scan QR di atas dengan GoPay, OVO, Dana, atau m-Banking.</p>
                    <div class="border-t border-slate-800 pt-1 mt-1">
                      <p class="text-xs font-bold text-slate-300">🏦 Informasi Transfer Bank</p>
                      <div class="whitespace-pre-line text-xs font-black text-indigo-400 leading-normal">
                        {{ platformSettings?.admin_bank_transfer_info || "772-0988-123\nBank BCA — PT UMKM Order Indonesia" }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer (Fixed) -->
          <div class="border-t border-slate-800 pt-4 space-y-3 shrink-0">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-400">Nominal Transfer:</span>
              <span class="text-base font-black text-emerald-450">Rp {{ Number(pendingInvoice.amount).toLocaleString('id-ID') }}</span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
              <button 
                type="button" 
                @click="authStore.showUpgradeModal = false" 
                class="flex-1 py-2.5 px-4 rounded-xl border border-slate-800 hover:bg-slate-850 font-bold text-xs sm:text-sm transition text-center"
              >
                Tutup & Kembali
              </button>
              
              <a 
                :href="'https://wa.me/' + (platformSettings?.support_whatsapp || '6281234567890') + '?text=' + encodeURIComponent('Halo Admin, saya ingin konfirmasi pembayaran untuk upgrade Pro:\n\n• Toko: ' + (authStore.tenant?.name || '') + '\n• Invoice: #' + pendingInvoice.invoice_number + '\n• Nominal: Rp ' + Number(pendingInvoice.amount).toLocaleString('id-ID') + '\n\nMohon segera diaktifkan. Terima kasih!')"
                target="_blank"
                class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs sm:text-sm transition duration-200 flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/10 text-center"
              >
                <span>📲</span> Konfirmasi via WA
              </a>
            </div>
            <p class="text-[9px] text-center text-slate-500">Klik "Konfirmasi via WA" untuk mempercepat proses persetujuan oleh Super Admin.</p>
          </div>
        </div>

        <!-- 3. Main Form View -->
        <div v-else class="flex flex-col h-full overflow-hidden space-y-4">
          <!-- Header -->
          <div class="flex items-start justify-between pb-2 shrink-0">
            <div class="space-y-0.5">
              <div class="inline-flex items-center gap-1 py-0.5 px-2 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-[10px] font-bold uppercase tracking-wider">
                <span>⚡</span> Pro Upgrade
              </div>
              <h3 class="text-lg sm:text-xl font-black text-slate-100">Upgrade ke Paket Pro</h3>
            </div>
            <button 
              @click="authStore.showUpgradeModal = false" 
              :disabled="isUpgrading"
              class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-750 flex items-center justify-center text-slate-400 hover:text-slate-200 transition disabled:opacity-40"
            >
              ✕
            </button>
          </div>

          <!-- Body (Scrollable) -->
          <div class="flex-1 overflow-y-auto pr-1 space-y-4 min-h-0">
            <!-- Benefits -->
            <div class="space-y-2 bg-slate-950/40 border border-slate-800/80 rounded-xl p-3 text-xs">
              <div class="flex items-start gap-2">
                <span class="text-indigo-400 font-bold shrink-0">✓</span>
                <p><strong class="text-slate-200">QR Code Katalog Kustom</strong> — Buat dan unduh QR code katalog toko Anda.</p>
              </div>
              <div class="flex items-start gap-2">
                <span class="text-indigo-400 font-bold shrink-0">✓</span>
                <p><strong class="text-slate-200">Laporan &amp; Piutang Lengkap</strong> — Pantau penjualan, omzet harian, dan piutang.</p>
              </div>
              <div class="flex items-start gap-2">
                <span class="text-indigo-400 font-bold shrink-0">✓</span>
                <p><strong class="text-slate-200">Tagihan WhatsApp 1-Klik</strong> — Kirim pengingat tagihan instan ke pelanggan.</p>
              </div>
              <div class="flex items-start gap-2">
                <span class="text-indigo-400 font-bold shrink-0">✓</span>
                <p><strong class="text-slate-200">Analisis Performa Toko</strong> — Grafik pengunjung harian dan rasio konversi.</p>
              </div>
            </div>

            <!-- Payment Options -->
            <div class="space-y-2">
              <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Metode Pembayaran</label>
              <div class="grid grid-cols-2 gap-2">
                <button 
                  type="button" 
                  @click="selectedPaymentMethod = 'qris'"
                  :class="[
                    'py-2 px-3 rounded-xl border font-bold text-xs transition-all flex items-center justify-center gap-1.5',
                    selectedPaymentMethod === 'qris' 
                      ? 'bg-indigo-500/10 border-indigo-500 text-indigo-400 shadow-md shadow-indigo-500/5' 
                      : 'bg-slate-950/50 border-slate-800 text-slate-400 hover:bg-slate-800/50 hover:text-slate-300'
                  ]"
                >
                  <span>📱</span> QRIS (E-Wallet)
                </button>
                <button 
                  type="button" 
                  @click="selectedPaymentMethod = 'bank'"
                  :class="[
                    'py-2 px-3 rounded-xl border font-bold text-xs transition-all flex items-center justify-center gap-1.5',
                    selectedPaymentMethod === 'bank' 
                      ? 'bg-indigo-500/10 border-indigo-500 text-indigo-400 shadow-md shadow-indigo-500/5' 
                      : 'bg-slate-950/50 border-slate-800 text-slate-400 hover:bg-slate-800/50 hover:text-slate-300'
                  ]"
                >
                  <span>🏦</span> Transfer Bank
                </button>
              </div>

              <!-- Payment Details Display -->
              <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 flex flex-col items-center justify-center text-center">
                <div v-if="selectedPaymentMethod === 'qris'" class="space-y-1.5 flex flex-col items-center">
                  <div class="w-24 h-24 bg-white border border-slate-200 rounded-lg p-1.5 flex items-center justify-center shadow-md animate-fadeIn">
                    <img v-if="platformSettings?.admin_qris_image_url" :src="platformSettings.admin_qris_image_url" alt="QRIS" class="w-full h-full object-contain" />
                    <div v-else class="grid grid-cols-4 gap-1 w-full h-full opacity-90 p-1 bg-slate-950 rounded">
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                      <div class="bg-slate-950 rounded-sm"></div>
                      <div class="bg-white rounded-sm"></div>
                    </div>
                  </div>
                  <div class="space-y-0.5">
                    <p class="text-[10px] font-black text-slate-200">{{ platformSettings?.admin_qris_info || "QRIS GPN UMKM-ORDER" }}</p>
                    <p class="text-[9px] text-slate-500">Scan QR Code ini menggunakan aplikasi e-wallet Anda</p>
                  </div>
                </div>
                <div v-else class="space-y-1 py-1.5 animate-fadeIn">
                  <p class="text-[10px] text-slate-400">Silakan transfer pembayaran ke rekening berikut:</p>
                  <div class="bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl inline-block text-left">
                    <div class="whitespace-pre-line text-xs font-black text-indigo-400 leading-normal">
                      {{ platformSettings?.admin_bank_transfer_info || "772-0988-123\nBank BCA — PT UMKM Order Indonesia" }}
                    </div>
                  </div>
                  <p class="text-[9px] text-slate-500 font-medium">Pembayaran akan dikonfirmasi manual oleh Super Admin.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer (Fixed) -->
          <div class="border-t border-slate-800 pt-3 space-y-3 shrink-0">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-400">Total Pembayaran:</span>
              <span class="text-base font-black text-emerald-450">
                Rp {{ proPlan ? formatRupiah(proPlan.monthly_price) : '49.000' }} 
                <span class="text-[10px] font-medium text-slate-500">/ bulan</span>
              </span>
            </div>

            <div v-if="upgradeError" class="bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-bold p-2.5 rounded-lg text-center">
              ⚠ {{ upgradeError }}
            </div>

            <button 
              type="button" 
              @click="handleUpgrade" 
              :disabled="isUpgrading"
              class="w-full theme-btn py-3 rounded-xl font-extrabold text-sm transition duration-200 flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20"
            >
              <div v-if="isUpgrading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <span v-else>⚡ Aktifkan Paket Pro Sekarang</span>
            </button>
            <p class="text-[9px] text-center text-slate-500">Dengan mengklik tombol di atas, Anda menyetujui Ketentuan Layanan kami.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isMobileMenuOpen = ref(false);

// Upgrade to Pro State & Functions
const isUpgrading = ref(false);
const selectedPaymentMethod = ref('qris');
const upgradeError = ref<string | null>(null);

const isCheckingPending = ref(false);
const pendingInvoice = ref<any>(null);
const platformSettings = ref<any>(null);
const proPlan = ref<any>(null);

async function checkPendingUpgrade() {
  if (!authStore.token) return;
  isCheckingPending.value = true;
  pendingInvoice.value = null;
  try {
    const res = await api.get('/tenant/settings');
    if (res.data.status === 'success') {
      pendingInvoice.value = res.data.data.pending_invoice;
      platformSettings.value = res.data.data.platform_settings;
      proPlan.value = res.data.data.pro_plan;
    }
  } catch (err) {
    console.error('Failed to check pending upgrade status:', err);
  } finally {
    isCheckingPending.value = false;
  }
}

watch(() => authStore.showUpgradeModal, (isOpen) => {
  if (isOpen) {
    checkPendingUpgrade();
  }
});

function playSuccessChime() {
  try {
    const audioCtx = new (window.AudioContext || (window as any).webkitAudioContext)();
    const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
    notes.forEach((freq, index) => {
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(freq, audioCtx.currentTime + index * 0.1);
      
      gain.gain.setValueAtTime(0, audioCtx.currentTime + index * 0.1);
      gain.gain.linearRampToValueAtTime(0.12, audioCtx.currentTime + index * 0.1 + 0.05);
      gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + index * 0.1 + 0.4);
      
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start(audioCtx.currentTime + index * 0.1);
      osc.stop(audioCtx.currentTime + index * 0.1 + 0.4);
    });
  } catch (e) {
    console.error('Audio Context success chime failed', e);
  }
}

async function handleUpgrade() {
  if (isUpgrading.value) return;
  isUpgrading.value = true;
  upgradeError.value = null;
  
  // Simulate payment processing step for premium feel
  await new Promise(resolve => setTimeout(resolve, 2000));
  
  try {
    const response = await api.post('/tenant/upgrade-pro');
    if (response.data.status === 'success') {
      pendingInvoice.value = response.data.data.invoice;
      playSuccessChime();
    } else {
      upgradeError.value = response.data.message || 'Gagal memproses upgrade';
    }
  } catch (err: any) {
    if (err.response?.status === 400 && err.response?.data?.data?.invoice) {
      pendingInvoice.value = err.response.data.data.invoice;
    } else {
      upgradeError.value = err.response?.data?.message || 'Terjadi kesalahan saat memproses upgrade.';
    }
  } finally {
    isUpgrading.value = false;
  }
}

const navItems = [
  { path: '/dashboard', label: 'Ringkasan', icon: '📊' },
  { path: '/dashboard/products', label: 'Daftar Produk', icon: '🛍️' },
  { path: '/dashboard/orders', label: 'Daftar Pesanan', icon: '📝' },
  { path: '/dashboard/receivables', label: 'Laporan', icon: '💰' },
  { path: '/dashboard/analytics', label: 'Analisis Katalog', icon: '📈' },
  { path: '/dashboard/catalog-online', label: 'Katalog Online', icon: '🌐' },
];

const unprocessedCount = ref(0);
const latestUnprocessedOrders = ref<any[]>([]);
const recentPaidReceivables = ref<any[]>([]);
const isBellDropdownOpen = ref(false);
const activeToasts = ref<any[]>([]);
let toastIdCounter = 0;
let isInitialLoad = true;
let pollInterval: any = null;

function closeBellDropdown() {
  isBellDropdownOpen.value = false;
}

function toggleBellDropdown() {
  isBellDropdownOpen.value = !isBellDropdownOpen.value;
}

function formatTimeAgo(dateStr: string): string {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);
  
  if (seconds < 60) return 'Baru saja';
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m ago`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}j ago`;
  
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

function playNotificationChime() {
  try {
    const audioCtx = new (window.AudioContext || (window as any).webkitAudioContext)();
    
    // First tone (higher frequency, shorter)
    const osc1 = audioCtx.createOscillator();
    const gain1 = audioCtx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
    gain1.gain.setValueAtTime(0, audioCtx.currentTime);
    gain1.gain.linearRampToValueAtTime(0.15, audioCtx.currentTime + 0.05);
    gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
    osc1.connect(gain1);
    gain1.connect(audioCtx.destination);
    
    // Second tone (slightly later, higher pitch)
    const osc2 = audioCtx.createOscillator();
    const gain2 = audioCtx.createGain();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // A5
    gain2.gain.setValueAtTime(0, audioCtx.currentTime + 0.1);
    gain2.gain.linearRampToValueAtTime(0.15, audioCtx.currentTime + 0.15);
    gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.4);
    osc2.connect(gain2);
    gain2.connect(audioCtx.destination);
    
    osc1.start(audioCtx.currentTime);
    osc1.stop(audioCtx.currentTime + 0.3);
    
    osc2.start(audioCtx.currentTime + 0.1);
    osc2.stop(audioCtx.currentTime + 0.4);
  } catch (e) {
    console.error('Audio Context chime failed', e);
  }
}

async function fetchNotifications() {
  if (!authStore.token) return;
  try {
    const response = await api.get('/dashboard/notifications');
    if (response.data.status === 'success') {
      const data = response.data.data;
      const newCount = data.unprocessed_count;

      if (!isInitialLoad) {
        // New unprocessed orders
        if (newCount > unprocessedCount.value) {
          const existingIds = latestUnprocessedOrders.value.map((o: any) => o.id);
          const newOrders = (data.latest_unprocessed_orders || []).filter((o: any) => !existingIds.includes(o.id));
          if (newOrders.length > 0) {
            playNotificationChime();
            newOrders.forEach((order: any) => showNewOrderToast(order, 'order'));
          }
        }

        // New paid receivables
        const existingPaidIds = recentPaidReceivables.value.map((r: any) => r.id);
        const newPaid = (data.recent_paid_receivables || []).filter((r: any) => !existingPaidIds.includes(r.id));
        if (newPaid.length > 0) {
          playNotificationChime();
          newPaid.forEach((r: any) => showNewOrderToast(r, 'payment'));
        }
      }

      unprocessedCount.value = newCount;
      latestUnprocessedOrders.value = data.latest_unprocessed_orders || [];
      recentPaidReceivables.value = data.recent_paid_receivables || [];
      isInitialLoad = false;
    }
  } catch (err) {
    console.error('Failed to fetch notifications', err);
  }
}

function showNewOrderToast(data: any, type: 'order' | 'payment') {
  const id = toastIdCounter++;
  if (type === 'order') {
    activeToasts.value.push({
      id, type: 'order',
      orderId: data.id,
      invoiceNumber: data.invoice_number,
      customerName: data.customer?.name || 'Pelanggan',
      grandTotal: data.grand_total,
      status: data.status,
    });
  } else {
    activeToasts.value.push({
      id, type: 'payment',
      orderId: data.order_id,
      invoiceNumber: data.invoice_number,
      customerName: data.customer_name,
      paidAmount: data.paid_amount,
    });
  }
  setTimeout(() => dismissToast(id), 10000);
}

function dismissToast(id: number) {
  activeToasts.value = activeToasts.value.filter(t => t.id !== id);
}

function handleNotificationClick(orderId: number) {
  isBellDropdownOpen.value = false;
  router.push({ path: '/dashboard/orders', query: { open: orderId } });
}

function formatRupiah(val: any): string {
  const num = parseFloat(val);
  if (isNaN(num)) return '0';
  return num.toLocaleString('id-ID');
}

function startPolling() {
  if (pollInterval) return;
  fetchNotifications();
  pollInterval = setInterval(() => {
    if (!document.hidden && authStore.token) {
      fetchNotifications();
    }
  }, 20000);
}

function stopPolling() {
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
  }
}

onMounted(() => {
  window.addEventListener('click', closeBellDropdown);
  startPolling();
});

onUnmounted(() => {
  window.removeEventListener('click', closeBellDropdown);
  stopPolling();
});

watch(() => route.path, () => {
  isBellDropdownOpen.value = false;
});

const THEMES: Record<string, {
  primary: string; secondary: string; primaryRgb: string;
  bgPage: string; bgSurface: string; bgSurfaceAlt: string;
  borderColor: string; textPrimary: string; textSecondary: string; textMuted: string;
}> = {
  default: {
    primary: '#6366f1', secondary: '#8b5cf6', primaryRgb: '99, 102, 241',
    bgPage: '#020617', bgSurface: 'rgba(15,23,42,0.75)', bgSurfaceAlt: '#1e293b',
    borderColor: '#334155', textPrimary: '#f8fafc', textSecondary: '#94a3b8', textMuted: '#475569',
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
  const tenant = authStore.tenant as any;
  const key = tenant?.catalog_setting?.theme || 'default';
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

const currentTitle = computed(() => {
  const path = route.path;
  if (path === '/dashboard/products') return 'Kelola Produk';
  if (path === '/dashboard/orders') return 'Kelola Pesanan';
  if (path === '/dashboard/receivables') return 'Laporan';
  if (path === '/dashboard/analytics') return 'Analisis Katalog';
  if (path === '/dashboard/catalog-online' || path === '/dashboard/settings') return 'Katalog Online';
  return 'Ringkasan Bisnis';
});

async function handleLogout() {
  stopPolling();
  unprocessedCount.value = 0;
  latestUnprocessedOrders.value = [];
  recentPaidReceivables.value = [];
  await authStore.logout();
  router.push('/login');
}
</script>

<style>
/* Global styles for the themed owner dashboard */
.dashboard-root {
  background-color: var(--bg-page) !important;
  color: var(--text-primary) !important;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Sidebar and header backgrounds */
.dashboard-root aside {
  background-color: var(--bg-surface-alt) !important;
  border-color: var(--border-color) !important;
}
.dashboard-root aside .border-b,
.dashboard-root aside .border-t {
  border-color: var(--border-color) !important;
}

.dashboard-root header {
  background-color: var(--bg-surface) !important;
  border-color: var(--border-color) !important;
}

/* Sidebar logo text color override */
.dashboard-root aside .font-extrabold.text-transparent {
  background-clip: unset !important;
  -webkit-background-clip: unset !important;
  background-image: none !important;
  color: var(--text-primary) !important;
}

/* Navigation & Button overrides */
.dashboard-root aside nav a:not(.theme-active-nav) {
  color: var(--text-secondary) !important;
}
.dashboard-root aside nav a:not(.theme-active-nav):hover {
  background-color: rgba(var(--theme-primary-rgb), 0.1) !important;
  color: var(--text-primary) !important;
}

.dashboard-root aside button:hover,
.dashboard-root aside a:not(.theme-active-nav):hover {
  background-color: rgba(var(--theme-primary-rgb), 0.1) !important;
}
.dashboard-root aside .text-slate-400 {
  color: var(--text-secondary) !important;
}

.dashboard-root header .text-slate-200 {
  color: var(--text-primary) !important;
}
.dashboard-root header .text-slate-500 {
  color: var(--text-muted) !important;
}
.dashboard-root header .w-10.h-10 {
  background-color: var(--bg-surface-alt) !important;
  border-color: var(--border-color) !important;
}

/* General Cards, Containers, Tables, Forms in child views */
.dashboard-root .bg-slate-900,
.dashboard-root .bg-slate-900\/60,
.dashboard-root .bg-slate-900\/40,
.dashboard-root .bg-slate-850 {
  background-color: var(--bg-surface) !important;
}

.dashboard-root .border-slate-800,
.dashboard-root .border-slate-700,
.dashboard-root .border-slate-850 {
  border-color: var(--border-color) !important;
}

/* Standard Texts mapping */
.dashboard-root .text-slate-100,
.dashboard-root .text-slate-200 {
  color: var(--text-primary) !important;
}

.dashboard-root .text-slate-300,
.dashboard-root .text-slate-400 {
  color: var(--text-secondary) !important;
}

.dashboard-root .text-slate-500,
.dashboard-root .text-slate-600 {
  color: var(--text-muted) !important;
}

/* Accent override (replacing indigo buttons/actions) */
.dashboard-root .text-indigo-400,
.dashboard-root .text-indigo-500,
.dashboard-root .text-indigo-600 {
  color: var(--theme-primary) !important;
}

.dashboard-root .bg-indigo-500,
.dashboard-root .bg-indigo-600 {
  background-color: var(--theme-primary) !important;
  color: white !important;
}

.dashboard-root .bg-indigo-500\/10 {
  background-color: rgba(var(--theme-primary-rgb), 0.1) !important;
}

.dashboard-root .bg-indigo-500\/20 {
  background-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}

.dashboard-root .border-indigo-500\/25,
.dashboard-root .border-indigo-500\/30 {
  border-color: rgba(var(--theme-primary-rgb), 0.3) !important;
}

/* Inputs, Textareas, Selects styling inside themed dashboard */
.dashboard-root input[type="text"],
.dashboard-root input[type="tel"],
.dashboard-root input[type="number"],
.dashboard-root input[type="email"],
.dashboard-root input[type="password"],
.dashboard-root textarea,
.dashboard-root select {
  background-color: var(--bg-page) !important;
  border-color: var(--border-color) !important;
  color: var(--text-primary) !important;
}

/* input[type=date] — browser native date picker juga harus ikut tema */
.dashboard-root input[type="date"],
.dashboard-root input[type="time"],
.dashboard-root input[type="datetime-local"] {
  background-color: var(--bg-page) !important;
  border-color: var(--border-color) !important;
  color: var(--text-primary) !important;
  color-scheme: light dark;
}
.dashboard-root input[type="date"]:focus,
.dashboard-root input[type="time"]:focus,
.dashboard-root input[type="datetime-local"]:focus {
  border-color: var(--theme-primary) !important;
  box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.15) !important;
}

/* bg-slate-950 (very dark hardcoded) — map ke surface */
.dashboard-root .bg-slate-950 {
  background-color: var(--bg-page) !important;
}

/* Modal dialog card yang muncul di atas overlay */
.dashboard-root .fixed.inset-0 > div[class*="bg-slate-"],
.dashboard-root .fixed.inset-0 > div[class*="rounded-"] {
  background-color: var(--bg-surface) !important;
  border-color: var(--border-color) !important;
}

/* Pastikan placeholder mengikuti tema */
.dashboard-root input::placeholder,
.dashboard-root textarea::placeholder {
  color: var(--text-muted) !important;
  opacity: 1;
}

/* Custom styles for themed components */
.dashboard-root .theme-accent-text {
  color: var(--theme-primary) !important;
}

.dashboard-root .theme-btn {
  background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary)) !important;
  color: white !important;
  box-shadow: 0 4px 15px rgba(var(--theme-primary-rgb), 0.35) !important;
}
.dashboard-root .theme-btn:hover:not(:disabled) {
  filter: brightness(1.1) !important;
  box-shadow: 0 6px 20px rgba(var(--theme-primary-rgb), 0.45) !important;
}

.dashboard-root .theme-btn-secondary {
  background-color: rgba(var(--theme-primary-rgb), 0.1) !important;
  color: var(--theme-primary) !important;
  border: 1px solid rgba(var(--theme-primary-rgb), 0.25) !important;
}
.dashboard-root .theme-btn-secondary:hover:not(:disabled) {
  background-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}

.dashboard-root .theme-spinner {
  border-color: var(--theme-primary) !important;
  border-top-color: transparent !important;
}

.dashboard-root .border-indigo-500 {
  border-color: var(--theme-primary) !important;
}
.dashboard-root .border-t-transparent {
  border-top-color: transparent !important;
}
.dashboard-root .focus\:border-indigo-500:focus {
  border-color: var(--theme-primary) !important;
}
.dashboard-root .text-indigo-600 {
  color: var(--theme-primary) !important;
}
.dashboard-root .focus\:ring-indigo-600:focus {
  --tw-ring-color: var(--theme-primary) !important;
  border-color: var(--theme-primary) !important;
}
.dashboard-root .text-indigo-400 {
  color: var(--theme-primary) !important;
}
.dashboard-root .text-indigo-300 {
  color: var(--theme-primary) !important;
}
.dashboard-root .bg-indigo-500\/10 {
  background-color: rgba(var(--theme-primary-rgb), 0.1) !important;
}
.dashboard-root .bg-indigo-500\/15 {
  background-color: rgba(var(--theme-primary-rgb), 0.15) !important;
}
.dashboard-root .bg-indigo-500\/20 {
  background-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}
.dashboard-root .hover\:bg-indigo-500\/10:hover {
  background-color: rgba(var(--theme-primary-rgb), 0.1) !important;
}
.dashboard-root .hover\:bg-indigo-500\/20:hover {
  background-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}
.dashboard-root .hover\:border-indigo-500:hover {
  border-color: var(--theme-primary) !important;
}
.dashboard-root .hover\:text-indigo-400:hover {
  color: var(--theme-primary) !important;
}
.dashboard-root .shadow-indigo-600\/15 {
  box-shadow: 0 4px 6px -1px rgba(var(--theme-primary-rgb), 0.15), 0 2px 4px -2px rgba(var(--theme-primary-rgb), 0.15) !important;
}
.dashboard-root .border-indigo-500\/20 {
  border-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}
.dashboard-root .border-indigo-500\/25 {
  border-color: rgba(var(--theme-primary-rgb), 0.25) !important;
}
.dashboard-root .border-indigo-500\/30 {
  border-color: rgba(var(--theme-primary-rgb), 0.3) !important;
}
.dashboard-root .bg-indigo-600\/20 {
  background-color: rgba(var(--theme-primary-rgb), 0.2) !important;
}
.dashboard-root .from-indigo-500\/20 {
  --tw-enter-from: rgba(var(--theme-primary-rgb), 0.2) !important;
}
.dashboard-root .to-purple-500\/20 {
  --tw-enter-to: rgba(var(--theme-secondary-rgb, 139, 92, 246), 0.2) !important;
}

/* Theme picker card */
.dashboard-root .theme-card {
  background-color: var(--bg-surface) !important;
  border-color: var(--border-color) !important;
}

/* Menu trigger button on mobile */
.dashboard-root .theme-menu-trigger {
  background-color: var(--bg-surface-alt) !important;
  border: 1px solid var(--border-color) !important;
  color: var(--text-primary) !important;
}
.dashboard-root .theme-menu-trigger:hover {
  background-color: rgba(var(--theme-primary-rgb), 0.1) !important;
}
</style>

<style scoped>
.theme-logo-bg {
  background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
}
.theme-active-nav {
  background-color: var(--theme-primary) !important;
  box-shadow: 0 10px 15px -3px rgba(var(--theme-primary-rgb), 0.3), 0 4px 6px -4px rgba(var(--theme-primary-rgb), 0.3) !important;
}
.theme-accent-text {
  color: var(--theme-primary);
}

@keyframes slide-in-right {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
@keyframes slide-in {
  from {
    transform: translateY(10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>
