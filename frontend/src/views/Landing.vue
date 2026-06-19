<template>
  <div class="landing-root">
    <!-- ========== NAVBAR ========== -->
    <header class="navbar">
      <div class="navbar-inner">
        <div class="brand">
          <div class="brand-icon">U</div>
          <span class="brand-name">UMKMOrder</span>
        </div>
        <nav class="nav-links">
          <a href="#fitur">Fitur</a>
          <a href="#cara-kerja">Cara Kerja</a>
          <a href="#harga">Harga</a>
        </nav>
        <div class="nav-actions">
          <router-link v-if="authStore.isAuthenticated" to="/dashboard" class="btn-outline">Dashboard</router-link>
          <template v-else>
            <router-link to="/login" class="btn-outline">Masuk</router-link>
            <router-link to="/register" class="btn-primary">Daftar Gratis →</router-link>
          </template>
        </div>
        <!-- Mobile burger -->
        <button class="burger" @click="mobileOpen = !mobileOpen" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
      <!-- Mobile menu -->
      <div class="mobile-menu" :class="{ open: mobileOpen }">
        <a href="#fitur" @click="mobileOpen=false">Fitur</a>
        <a href="#cara-kerja" @click="mobileOpen=false">Cara Kerja</a>
        <a href="#harga" @click="mobileOpen=false">Harga</a>
        <router-link v-if="authStore.isAuthenticated" to="/dashboard" @click="mobileOpen=false">Dashboard</router-link>
        <template v-else>
          <router-link to="/login" @click="mobileOpen=false">Masuk</router-link>
          <router-link to="/register" class="mobile-cta" @click="mobileOpen=false">Daftar Gratis</router-link>
        </template>
      </div>
    </header>

    <!-- ========== HERO ========== -->
    <section class="hero">
      <div class="hero-bg-orb orb1"></div>
      <div class="hero-bg-orb orb2"></div>
      <div class="hero-inner">
        <div class="hero-badge">
          <span class="badge-dot"></span>
          Platform Pesanan, Pelanggan, Pembayaran & Piutang
        </div>
        <h1 class="hero-title">
          Kelola Order UMKM,<br>
          <span class="gradient-text">bukan sekadar katalog online</span>
        </h1>
        <p class="hero-desc">
          UMKMOrder membantu Anda menerima pesanan dari katalog online, menyimpan data pelanggan, mencatat pembayaran, dan memantau piutang dalam satu aplikasi sederhana.
        </p>
        <div class="hero-cta">
          <router-link :to="authStore.isAuthenticated ? '/dashboard' : '/register'" class="btn-hero-primary">
            {{ authStore.isAuthenticated ? 'Ke Dashboard' : 'Mulai Gratis Sekarang' }}
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>
          <a href="#cara-kerja" class="btn-hero-ghost">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10,8 16,12 10,16"/></svg>
            Lihat Demo
          </a>
        </div>
        <p class="hero-note">✓ Gratis selamanya &nbsp;·&nbsp; ✓ Setup 5 menit &nbsp;·&nbsp; ✓ Tanpa kartu kredit</p>

        <!-- Hero Preview -->
        <div class="hero-preview">
          <div class="preview-browser">
            <div class="preview-bar">
              <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
              <div class="preview-url">{{ currentHost }}/<strong>toko-anda</strong></div>
            </div>
            <div class="preview-content">
              <div class="preview-store-header">
                <div class="preview-store-avatar">🥚</div>
                <div>
                  <div class="preview-store-name">Kurnia Telur</div>
                  <div class="preview-store-tagline">Telur Asin Premium · Surabaya</div>
                </div>
              </div>
              <div class="preview-products">
                <div class="preview-product" v-for="p in previewProducts" :key="p.name">
                  <div class="pp-emoji">{{ p.emoji }}</div>
                  <div class="pp-info">
                    <div class="pp-name">{{ p.name }}</div>
                    <div class="pp-price">{{ p.price }}</div>
                  </div>
                  <button class="pp-add">+</button>
                </div>
              </div>
              <div class="preview-wa-btn">
                <span>🟢</span> Pesan via WhatsApp
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== LOGO BAR ========== -->
    <section class="logo-bar">
      <p class="logo-bar-label">Dipercaya ribuan UMKM Indonesia</p>
      <div class="logo-items">
        <span>🏪 Warung Makan</span>
        <span>🧴 Produk Rumahan</span>
        <span>🌾 Agribisnis</span>
        <span>👗 Fashion Lokal</span>
        <span>🍰 Bakeri & Kue</span>
        <span>🐟 Seafood Segar</span>
      </div>
    </section>

    <!-- ========== FITUR ========== -->
    <section id="fitur" class="section-features">
      <div class="section-inner">
        <div class="section-badge">Fitur Unggulan</div>
        <h2 class="section-title">Semua yang Anda butuhkan,<br>dalam satu platform</h2>
        <p class="section-desc">Didesain khusus untuk kebutuhan UMKM — bukan platform besar yang membingungkan.</p>

        <div class="features-grid">
          <div class="feature-card featured">
            <div class="feature-icon" style="background: linear-gradient(135deg,#6366f1,#8b5cf6)">🏪</div>
            <h3>Toko Online Sendiri</h3>
            <p>Dapatkan link katalog unik <strong>{{ currentHost }}/nama-toko</strong> yang bisa dibagikan ke pelanggan kapan saja.</p>
            <ul class="feature-list">
              <li>✓ Desain profesional & mobile-friendly</li>
              <li>✓ Pelanggan hanya melihat toko Anda</li>
              <li>✓ Aktif 24 jam sehari</li>
            </ul>
          </div>
          <div class="feature-card">
            <div class="feature-icon" style="background: linear-gradient(135deg,#10b981,#059669)">📱</div>
            <h3>Order via WhatsApp</h3>
            <p>Setiap pesanan langsung masuk ke nomor WhatsApp Anda. Tidak perlu belajar sistem baru.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon" style="background: linear-gradient(135deg,#f59e0b,#d97706)">📦</div>
            <h3>Manajemen Produk</h3>
            <p>Tambah, edit, nonaktifkan produk dengan mudah. Upload foto, atur harga dan stok kapan saja.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon" style="background: linear-gradient(135deg,#ef4444,#dc2626)">💰</div>
            <h3>Catat Piutang</h3>
            <p>Lacak cicilan dan sisa tagihan pelanggan. Tidak ada lagi utang yang lupa dicatat di buku.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon" style="background: linear-gradient(135deg,#3b82f6,#2563eb)">📊</div>
            <h3>Laporan & Analitik</h3>
            <p>Lihat produk terlaris, grafik penjualan, dan tren pengunjung katalog Anda setiap harinya.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon" style="background: linear-gradient(135deg,#ec4899,#be185d)">🔔</div>
            <h3>Notifikasi Otomatis</h3>
            <p>Kirim invoice dan pengingat pembayaran ke pelanggan lewat WhatsApp secara otomatis.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== CARA KERJA ========== -->
    <section id="cara-kerja" class="section-how">
      <div class="section-inner">
        <div class="section-badge">Cara Kerja</div>
        <h2 class="section-title">Mulai berjualan dalam<br><span class="gradient-text">5 menit</span></h2>
        <div class="steps-grid">
          <div class="step-card" v-for="(step, i) in steps" :key="i">
            <div class="step-number">{{ i + 1 }}</div>
            <div class="step-icon">{{ step.icon }}</div>
            <h3>{{ step.title }}</h3>
            <p>{{ step.desc }}</p>
          </div>
        </div>
        <div class="how-cta">
          <router-link :to="authStore.isAuthenticated ? '/dashboard' : '/register'" class="btn-hero-primary">
            {{ authStore.isAuthenticated ? 'Ke Dashboard' : 'Mulai Sekarang — Gratis!' }}
          </router-link>
        </div>
      </div>
    </section>

    <!-- ========== HARGA ========== -->
    <section id="harga" class="section-pricing">
      <div class="section-inner">
        <div class="section-badge">Harga Transparan</div>
        <h2 class="section-title">Mulai gratis, upgrade kapan saja</h2>
        <div class="pricing-grid">
          <div class="price-card" v-for="plan in plans" :key="plan.name" :class="{ popular: plan.popular }">
            <div v-if="plan.popular" class="popular-badge">⭐ Paling Populer</div>
            <div class="price-name">{{ plan.name }}</div>
            <div class="price-amount">
              {{ plan.price }}
              <span class="price-period" v-if="plan.period">{{ plan.period }}</span>
            </div>
            <p class="price-desc">{{ plan.desc }}</p>
            <ul class="price-features">
              <li v-for="f in plan.features" :key="f">
                <span class="check">✓</span> {{ f }}
              </li>
            </ul>
            <ul v-if="plan.restrictions?.length" class="price-features price-restrictions">
              <li v-for="f in plan.restrictions" :key="f">
                <span class="cross">x</span> {{ f }}
              </li>
            </ul>
            <router-link :to="authStore.isAuthenticated ? '/dashboard' : '/register'" :class="plan.popular ? 'btn-hero-primary' : 'btn-outline price-btn'">
              {{ authStore.isAuthenticated ? 'Ke Dashboard' : plan.cta }}
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== CTA BOTTOM ========== -->
    <section class="section-final-cta">
      <div class="final-cta-inner">
        <h2>Siap membuka toko online Anda?</h2>
        <p>Bergabunglah dengan UMKM Indonesia yang sudah menggunakan UMKMOrder untuk menerima order lebih mudah.</p>
        <router-link :to="authStore.isAuthenticated ? '/dashboard' : '/register'" class="btn-hero-primary btn-large">
          {{ authStore.isAuthenticated ? 'Ke Dashboard' : 'Daftar Gratis Sekarang' }}
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </router-link>
        <p class="final-note">Gratis selamanya · Tidak perlu kartu kredit · Setup 5 menit</p>
      </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer class="footer">
      <div class="footer-inner">
        <div class="footer-brand">
          <div class="brand-icon small">U</div>
          <span class="brand-name">UMKMOrder</span>
        </div>
        <p class="footer-copy">© 2025 UMKMOrder. Dibuat dengan ❤️ untuk UMKM Indonesia.</p>
        <div class="footer-links">
          <a href="#">Privasi</a>
          <a href="#">Syarat</a>
          <a href="#">Kontak</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const mobileOpen = ref(false);
const currentHost = computed(() => typeof window !== 'undefined' ? window.location.host : 'umkmorder.id');

const previewProducts = [
  { emoji: '🥚', name: 'Telur Asin Original', price: 'Rp 2.500/butir' },
  { emoji: '🥚', name: 'Telur Asin Asap', price: 'Rp 3.000/butir' },
  { emoji: '📦', name: 'Paket 10 Butir', price: 'Rp 22.000' },
];

const steps = [
  { icon: '📝', title: 'Daftar Akun', desc: 'Isi nama toko, nomor WhatsApp, dan pilih slug URL toko Anda. Selesai dalam 1 menit.' },
  { icon: '📦', title: 'Tambah Produk', desc: 'Upload foto, tulis nama dan harga. Produk langsung tampil di katalog online Anda.' },
  { icon: '🔗', title: 'Bagikan Link', desc: 'Share link toko ke pelanggan via WhatsApp, Instagram, atau story. Mulai terima order!' },
  { icon: '💬', title: 'Order Masuk WA', desc: 'Setiap order otomatis dikirim ke WhatsApp Anda. Konfirmasi & proses dengan mudah.' },
];

const plans = [
  {
    name: 'Gratis',
    price: 'Rp 0',
    period: '/bulan',
    desc: 'UMKM yang baru mulai go online.',
    popular: false,
    cta: 'Mulai Gratis',
    features: [
      '1 Katalog Online',
      'Maksimal 20 Produk Aktif',
      'Hingga 100 Order per Bulan',
      'WhatsApp Order',
      'Data Pelanggan Otomatis',
      'Dashboard Dasar',
      'Lokasi Pengiriman (Google Maps)',
      'Ongkir Manual & Jarak',
    ],
    restrictions: [
      'Manajemen Piutang',
      'Reminder WhatsApp',
      'Laporan Lengkap',
      'Multi User',
    ],
  },
  {
    name: 'Pro',
    price: 'Rp 49.000',
    period: '/bulan',
    desc: 'UMKM aktif yang ingin tumbuh lebih cepat.',
    popular: true,
    cta: 'Upgrade Sekarang',
    features: [
      'Produk Aktif Tak Terbatas',
      'Order Tak Terbatas',
      'Terima Pesanan dari Katalog Online',
      'Manajemen Piutang',
      'Catat Pembayaran Sebagian',
      'Tempo & Jatuh Tempo',
      '1 Klik Reminder WhatsApp',
      'Ongkir Berdasarkan Jarak',
      'Laporan Penjualan',
      'Laporan Piutang',
      'QR Code Katalog',
      'Statistik Pengunjung Katalog',
      'Export Data',
    ],
    restrictions: [],
  },
  {
    name: 'Bisnis',
    price: 'Custom',
    period: '',
    desc: 'Distributor, multi cabang, dan tim penjualan.',
    popular: false,
    cta: 'Hubungi Kami',
    features: [
      'Semua Fitur Pro',
      'Multi User / Tim',
      'Role & Permission',
      'Reminder WhatsApp Otomatis',
      'Integrasi WA Gateway',
      'API Access',
      'Multi Cabang',
      'Custom Branding',
      'Onboarding Pribadi',
      'Priority Support',
    ],
    restrictions: [],
  },
];
</script>

<style scoped>
/* ===== RESET & BASE ===== */
.landing-root {
  min-height: 100vh;
  background: #030711;
  color: #e2e8f0;
  font-family: 'Inter', system-ui, sans-serif;
  overflow-x: hidden;
}

/* ===== NAVBAR ===== */
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(3, 7, 17, 0.85);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.navbar-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
  gap: 32px;
}
.brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.brand-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex; align-items: center; justify-content: center;
  font-weight: 900; font-size: 18px; color: white;
  box-shadow: 0 4px 15px rgba(99,102,241,0.4);
}
.brand-icon.small { width: 30px; height: 30px; font-size: 14px; border-radius: 8px; }
.brand-name { font-weight: 800; font-size: 18px; color: #f1f5f9; letter-spacing: -0.5px; }
.nav-links { display: flex; gap: 28px; margin-left: auto; }
.nav-links a {
  font-size: 14px; font-weight: 500; color: #94a3b8;
  text-decoration: none; transition: color 0.2s;
}
.nav-links a:hover { color: #e2e8f0; }
.nav-actions { display: flex; align-items: center; gap: 10px; }
.btn-outline {
  padding: 8px 18px; border-radius: 10px; font-size: 14px; font-weight: 600;
  border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1;
  text-decoration: none; transition: all 0.2s; background: transparent;
}
.btn-outline:hover { background: rgba(255,255,255,0.06); color: #f1f5f9; }
.btn-primary {
  padding: 8px 18px; border-radius: 10px; font-size: 14px; font-weight: 700;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white; text-decoration: none; transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(99,102,241,0.35);
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.45); }
.burger {
  display: none; flex-direction: column; gap: 5px;
  background: none; border: none; cursor: pointer; padding: 4px; margin-left: auto;
}
.burger span { display: block; width: 22px; height: 2px; background: #94a3b8; border-radius: 2px; }
.mobile-menu {
  display: none; flex-direction: column; gap: 4px;
  padding: 12px 24px 16px; border-top: 1px solid rgba(255,255,255,0.06);
}
.mobile-menu.open { display: flex; }
.mobile-menu a {
  padding: 10px 12px; font-size: 15px; font-weight: 500;
  color: #94a3b8; text-decoration: none; border-radius: 8px; transition: all 0.2s;
}
.mobile-menu a:hover { background: rgba(255,255,255,0.05); color: #e2e8f0; }
.mobile-cta {
  margin-top: 8px; text-align: center;
  background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
  color: white !important; font-weight: 700 !important;
}

/* ===== HERO ===== */
.hero {
  position: relative;
  padding: 80px 24px 60px;
  overflow: hidden;
  text-align: center;
}
.hero-bg-orb {
  position: absolute; border-radius: 50%; pointer-events: none;
  filter: blur(100px); opacity: 0.35;
}
.orb1 { width: 500px; height: 500px; background: #6366f1; top: -100px; left: -100px; }
.orb2 { width: 400px; height: 400px; background: #8b5cf6; bottom: -100px; right: -50px; }
.hero-inner { max-width: 900px; margin: 0 auto; position: relative; z-index: 1; }
.hero-badge {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 6px 16px; border-radius: 100px;
  background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3);
  font-size: 13px; font-weight: 600; color: #a5b4fc; margin-bottom: 28px;
  animation: fadeSlideDown 0.5s ease;
}
.badge-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: #6366f1; animation: pulse 2s infinite;
}
.hero-title {
  font-size: clamp(36px, 6vw, 68px);
  font-weight: 900; line-height: 1.1; letter-spacing: -2px;
  margin-bottom: 24px; color: #f8fafc;
  animation: fadeSlideDown 0.6s ease 0.1s both;
}
.gradient-text {
  background: linear-gradient(135deg, #818cf8, #c084fc, #fb7185);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-desc {
  font-size: 18px; color: #94a3b8; max-width: 620px; margin: 0 auto 36px;
  line-height: 1.7; animation: fadeSlideDown 0.6s ease 0.2s both;
}
.hero-cta {
  display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;
  margin-bottom: 20px; animation: fadeSlideDown 0.6s ease 0.3s both;
}
.btn-hero-primary {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 28px; border-radius: 12px; font-size: 16px; font-weight: 700;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white; text-decoration: none; transition: all 0.25s;
  box-shadow: 0 8px 25px rgba(99,102,241,0.4);
}
.btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 35px rgba(99,102,241,0.5); }
.btn-hero-ghost {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 28px; border-radius: 12px; font-size: 16px; font-weight: 600;
  border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1;
  text-decoration: none; transition: all 0.25s; background: rgba(255,255,255,0.04);
}
.btn-hero-ghost:hover { background: rgba(255,255,255,0.08); color: #f1f5f9; }
.hero-note {
  font-size: 13px; color: #475569; margin-bottom: 56px;
  animation: fadeSlideDown 0.6s ease 0.4s both;
}

/* Hero preview */
.hero-preview { animation: fadeSlideDown 0.8s ease 0.5s both; }
.preview-browser {
  max-width: 480px; margin: 0 auto;
  background: #0f172a; border-radius: 16px;
  border: 1px solid rgba(255,255,255,0.08);
  box-shadow: 0 40px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(99,102,241,0.15);
  overflow: hidden;
}
.preview-bar {
  background: #1e293b; padding: 10px 16px;
  display: flex; align-items: center; gap: 10px;
}
.dot { width: 10px; height: 10px; border-radius: 50%; }
.dot.red { background: #ef4444; }
.dot.yellow { background: #f59e0b; }
.dot.green { background: #10b981; }
.preview-url {
  flex: 1; background: #0f172a; border-radius: 6px;
  padding: 4px 10px; font-size: 11px; color: #64748b; text-align: center;
}
.preview-url strong { color: #a5b4fc; }
.preview-content { padding: 20px; }
.preview-store-header {
  display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
}
.preview-store-avatar {
  width: 44px; height: 44px; border-radius: 12px;
  background: linear-gradient(135deg,#fbbf24,#f59e0b);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px;
}
.preview-store-name { font-weight: 700; font-size: 15px; color: #f1f5f9; }
.preview-store-tagline { font-size: 12px; color: #64748b; margin-top: 2px; }
.preview-products { display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; }
.preview-product {
  display: flex; align-items: center; gap: 12px;
  background: #1e293b; border-radius: 10px; padding: 10px 12px;
  border: 1px solid rgba(255,255,255,0.05);
}
.pp-emoji { font-size: 20px; width: 28px; text-align: center; }
.pp-info { flex: 1; }
.pp-name { font-size: 13px; font-weight: 600; color: #e2e8f0; }
.pp-price { font-size: 12px; color: #10b981; font-weight: 600; margin-top: 2px; }
.pp-add {
  width: 28px; height: 28px; border-radius: 8px;
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  color: white; border: none; font-size: 18px; font-weight: 700;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
}
.preview-wa-btn {
  background: linear-gradient(135deg, #16a34a, #15803d);
  color: white; font-size: 14px; font-weight: 700;
  padding: 12px; border-radius: 10px; text-align: center;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}

/* ===== LOGO BAR ===== */
.logo-bar {
  border-top: 1px solid rgba(255,255,255,0.06);
  border-bottom: 1px solid rgba(255,255,255,0.06);
  padding: 28px 24px; text-align: center;
  background: rgba(255,255,255,0.02);
}
.logo-bar-label { font-size: 13px; color: #475569; margin-bottom: 20px; }
.logo-items {
  display: flex; flex-wrap: wrap; justify-content: center; gap: 12px;
}
.logo-items span {
  padding: 8px 18px; border-radius: 100px;
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
  font-size: 14px; color: #64748b; font-weight: 500;
}

/* ===== SECTIONS ===== */
.section-inner { max-width: 1100px; margin: 0 auto; padding: 0 24px; }
.section-badge {
  display: inline-block; padding: 4px 14px; border-radius: 100px;
  background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.25);
  font-size: 12px; font-weight: 700; color: #a5b4fc; letter-spacing: 0.5px;
  text-transform: uppercase; margin-bottom: 16px;
}
.section-title {
  font-size: clamp(28px, 4vw, 44px); font-weight: 900;
  letter-spacing: -1px; color: #f8fafc; margin-bottom: 16px; line-height: 1.15;
}
.section-desc { font-size: 16px; color: #64748b; max-width: 560px; line-height: 1.7; }

/* ===== FEATURES ===== */
.section-features { padding: 100px 0; }
.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px; margin-top: 48px;
}
.feature-card {
  background: #0d1526; border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.06);
  padding: 28px; transition: all 0.3s;
}
.feature-card:hover {
  border-color: rgba(99,102,241,0.3);
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.feature-card.featured {
  grid-column: span 3;
  display: grid; grid-template-columns: auto 1fr;
  gap: 24px; align-items: start;
  background: linear-gradient(135deg, #0f1629, #141f3a);
  border-color: rgba(99,102,241,0.25);
}
.feature-card.featured h3 { font-size: 22px; margin-bottom: 8px; }
.feature-icon {
  width: 52px; height: 52px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 24px; margin-bottom: 16px; flex-shrink: 0;
}
.feature-card.featured .feature-icon { margin-bottom: 0; }
.feature-card h3 { font-size: 17px; font-weight: 700; color: #f1f5f9; margin-bottom: 8px; }
.feature-card p { font-size: 14px; color: #64748b; line-height: 1.6; }
.feature-list { list-style: none; padding: 0; margin-top: 14px; display: flex; flex-direction: column; gap: 6px; }
.feature-list li { font-size: 13px; color: #94a3b8; }

/* ===== HOW IT WORKS ===== */
.section-how {
  padding: 100px 0;
  background: rgba(99,102,241,0.03);
  border-top: 1px solid rgba(255,255,255,0.05);
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.steps-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 20px; margin: 48px 0;
}
.step-card {
  text-align: center; padding: 32px 20px;
  background: #0d1526; border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.06);
  position: relative;
}
.step-number {
  position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
  width: 28px; height: 28px; border-radius: 50%;
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  color: white; font-size: 13px; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
}
.step-icon { font-size: 36px; margin-bottom: 12px; margin-top: 8px; }
.step-card h3 { font-size: 15px; font-weight: 700; color: #f1f5f9; margin-bottom: 8px; }
.step-card p { font-size: 13px; color: #64748b; line-height: 1.6; }
.how-cta { text-align: center; }

/* ===== PRICING ===== */
.section-pricing { padding: 100px 0; }
.pricing-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 20px; margin-top: 48px; align-items: start;
}
.price-card {
  background: #0d1526; border-radius: 24px;
  border: 1px solid rgba(255,255,255,0.08);
  padding: 32px; position: relative;
  transition: all 0.3s;
}
.price-card:hover { transform: translateY(-4px); }
.price-card.popular {
  background: linear-gradient(135deg, #0f1629, #141f3a);
  border-color: rgba(99,102,241,0.4);
  box-shadow: 0 0 0 1px rgba(99,102,241,0.2), 0 20px 60px rgba(99,102,241,0.15);
}
.popular-badge {
  position: absolute; top: -13px; left: 50%; transform: translateX(-50%);
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  color: white; font-size: 12px; font-weight: 700;
  padding: 4px 16px; border-radius: 100px; white-space: nowrap;
}
.price-name { font-size: 18px; font-weight: 800; color: #f1f5f9; margin-bottom: 8px; }
.price-amount {
  font-size: 36px; font-weight: 900; color: #f8fafc;
  margin-bottom: 4px; letter-spacing: -1px;
}
.price-period { font-size: 16px; font-weight: 500; color: #64748b; }
.price-desc { font-size: 14px; color: #64748b; margin-bottom: 24px; line-height: 1.5; }
.price-features { list-style: none; padding: 0; margin-bottom: 28px; display: flex; flex-direction: column; gap: 10px; }
.price-features li { font-size: 14px; color: #94a3b8; display: flex; align-items: center; gap: 8px; }
.price-restrictions { margin-top: -12px; }
.price-restrictions li { color: #64748b; }
.check { color: #6366f1; font-weight: 700; }
.cross { color: #ef4444; font-weight: 800; }
.price-btn { display: block; text-align: center; }

/* ===== FINAL CTA ===== */
.section-final-cta {
  padding: 100px 24px; text-align: center;
  background: radial-gradient(ellipse at center, rgba(99,102,241,0.12) 0%, transparent 70%);
  border-top: 1px solid rgba(255,255,255,0.05);
}
.final-cta-inner { max-width: 600px; margin: 0 auto; }
.final-cta-inner h2 { font-size: clamp(28px, 4vw, 44px); font-weight: 900; color: #f8fafc; margin-bottom: 16px; letter-spacing: -1px; }
.final-cta-inner p { font-size: 16px; color: #64748b; line-height: 1.7; margin-bottom: 36px; }
.btn-large { padding: 18px 36px; font-size: 18px; }
.final-note { margin-top: 20px; font-size: 13px; color: #334155; }

/* ===== FOOTER ===== */
.footer {
  border-top: 1px solid rgba(255,255,255,0.06);
  padding: 32px 24px;
}
.footer-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
.footer-brand { display: flex; align-items: center; gap: 8px; }
.footer-copy { font-size: 13px; color: #334155; }
.footer-links { display: flex; gap: 20px; }
.footer-links a { font-size: 13px; color: #475569; text-decoration: none; transition: color 0.2s; }
.footer-links a:hover { color: #94a3b8; }

/* ===== ANIMATIONS ===== */
@keyframes fadeSlideDown {
  from { opacity: 0; transform: translateY(-16px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(0.8); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .features-grid { grid-template-columns: repeat(2, 1fr); }
  .feature-card.featured { grid-column: span 2; }
  .steps-grid { grid-template-columns: repeat(2, 1fr); }
  .pricing-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .nav-links, .nav-actions { display: none; }
  .burger { display: flex; }
  .features-grid { grid-template-columns: 1fr; }
  .feature-card.featured { grid-column: span 1; grid-template-columns: 1fr; }
  .steps-grid { grid-template-columns: 1fr; }
  .pricing-grid { grid-template-columns: 1fr; }
  .footer-inner { flex-direction: column; text-align: center; }
}
</style>
