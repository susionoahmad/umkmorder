<template>
  <div class="landing-root">
    <LandingNavbar />
    
    <div class="legal-page animate-fadeIn">
      <div class="legal-container">
        <div class="legal-header">
          <div class="legal-badge">Privasi Pengguna</div>
          <h1 class="gradient-text">Kebijakan Privasi</h1>
          <p>Terakhir diperbarui: 21 Juni 2026</p>
        </div>

        <div class="legal-body">
          <section>
            <h2>1. Pengantar</h2>
            <p>Selamat datang di UMKMOrder. Kami sangat menghargai kepercayaan Anda dan berkomitmen untuk melindungi informasi pribadi Anda. Kebijakan Privasi ini membantu Anda memahami bagaimana kami mengumpulkan, menggunakan, dan menjaga data Anda saat Anda mengakses platform kami.</p>
          </section>

          <section>
            <h2>2. Informasi Yang Kami Kumpulkan</h2>
            <p>Kami mengumpulkan data yang diperlukan untuk mendukung fungsionalitas aplikasi dan memberikan layanan terbaik kepada Anda:</p>
            <ul>
              <li><strong>Data Registrasi:</strong> Nama lengkap, alamat email, nomor WhatsApp bisnis, nama toko, dan kredensial akun Anda.</li>
              <li><strong>Data Katalog & Produk:</strong> Foto, deskripsi, harga, dan informasi inventaris produk yang diunggah ke toko online Anda.</li>
              <li><strong>Data Pelanggan & Pesanan:</strong> Informasi pembeli Anda (nama, alamat pengiriman, nomor WhatsApp) dan detail riwayat transaksi/piutang yang Anda catat.</li>
              <li><strong>Informasi Teknis:</strong> Alamat IP, jenis peramban (browser), dan perilaku navigasi pengunjung pada katalog toko Anda untuk kebutuhan analitik.</li>
            </ul>
          </section>

          <section>
            <h2>3. Penggunaan Informasi</h2>
            <p>Kami menggunakan data yang dikumpulkan untuk tujuan operasional dan pengembangan platform:</p>
            <ul>
              <li>Menyediakan link katalog online aktif `umkmorder.id/nama-toko`.</li>
              <li>Meneruskan pesanan pelanggan langsung ke kontak WhatsApp Anda secara instan.</li>
              <li>Mengirimkan laporan penjualan, analitik pengunjung, dan pengingat tagihan (invoice) otomatis.</li>
              <li>Mengelola siklus berlangganan (subscription) dan menagih biaya langganan paket Pro/Bisnis.</li>
              <li>Mendeteksi, mencegah, dan menangani aktivitas mencurigakan atau pelanggaran ketentuan platform.</li>
            </ul>
          </section>

          <section>
            <h2>4. Keamanan Data</h2>
            <p>Kami menerapkan standar keamanan industri termasuk protokol enkripsi SSL (Secure Sockets Layer) untuk melindungi pengiriman informasi pribadi Anda. Namun, harap dipahami bahwa tidak ada metode transmisi internet atau penyimpanan elektronik yang 100% aman.</p>
          </section>

          <section>
            <h2>5. Berbagi Data dengan Pihak Ketiga</h2>
            <p>UMKMOrder tidak menjual, menyewakan, atau memperdagangkan data Anda kepada pihak ketiga. Kami hanya membagikan data Anda dengan penyedia pihak ketiga tepercaya untuk keperluan operasional (seperti payment gateway, kurir logistik yang Anda aktifkan, atau layanan WhatsApp Gateway resmi).</p>
          </section>

          <section>
            <h2>6. Hak Pengguna</h2>
            <p>Anda berhak memperbarui, mengubah, atau menghapus informasi pribadi dan produk Anda kapan saja langsung melalui Dashboard UMKMOrder. Jika Anda memutuskan untuk menghapus akun Anda secara permanen, silakan hubungi tim dukungan kami melalui halaman kontak.</p>
          </section>
        </div>
      </div>
    </div>

    <LandingFooter />
  </div>
</template>

<script setup lang="ts">
import LandingNavbar from '@/components/LandingNavbar.vue';
import LandingFooter from '@/components/LandingFooter.vue';
</script>

<style scoped>
.landing-root {
  min-height: 100vh;
  background: #030711;
  color: #e2e8f0;
  font-family: 'Inter', system-ui, sans-serif;
}
.legal-page {
  padding: 80px 24px;
  background: #030711;
}
.legal-container {
  max-width: 800px;
  margin: 0 auto;
}
.legal-header {
  text-align: center;
  margin-bottom: 48px;
}
.legal-badge {
  display: inline-block;
  padding: 4px 14px;
  border-radius: 100px;
  background: rgba(99,102,241,0.15);
  border: 1px solid rgba(99,102,241,0.25);
  font-size: 11px;
  font-weight: 700;
  color: #a5b4fc;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 16px;
}
.legal-header h1 {
  font-size: clamp(32px, 5vw, 48px);
  font-weight: 900;
  margin-bottom: 12px;
  letter-spacing: -1px;
}
.legal-header p {
  color: #64748b;
  font-size: 14px;
}
.legal-body {
  background: #0d1526;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 24px;
  padding: 40px;
  line-height: 1.8;
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.legal-body section {
  margin-bottom: 36px;
}
.legal-body section:last-child {
  margin-bottom: 0;
}
.legal-body h2 {
  font-size: 20px;
  font-weight: 700;
  color: #f1f5f9;
  margin-bottom: 16px;
  border-left: 3px solid #6366f1;
  padding-left: 12px;
}
.legal-body p {
  font-size: 15px;
  color: #94a3b8;
  margin-bottom: 16px;
}
.legal-body ul {
  list-style: none;
  padding: 0;
  margin-bottom: 16px;
}
.legal-body li {
  font-size: 14px;
  color: #94a3b8;
  margin-bottom: 8px;
  padding-left: 20px;
  position: relative;
}
.legal-body li::before {
  content: "✓";
  color: #6366f1;
  position: absolute;
  left: 0;
  font-weight: bold;
}
.gradient-text {
  background: linear-gradient(135deg, #818cf8, #c084fc, #fb7185);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
@media (max-width: 640px) {
  .legal-body {
    padding: 24px;
  }
}
</style>
