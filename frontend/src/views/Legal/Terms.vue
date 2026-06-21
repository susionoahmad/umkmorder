<template>
  <div class="landing-root">
    <LandingNavbar />
    
    <div class="legal-page animate-fadeIn">
      <div class="legal-container">
        <div class="legal-header">
          <div class="legal-badge">Ketentuan Layanan</div>
          <h1 class="gradient-text">Syarat & Ketentuan</h1>
          <p>Terakhir diperbarui: 21 Juni 2026</p>
        </div>

        <div class="legal-body">
          <section>
            <h2>1. Penerimaan Ketentuan</h2>
            <p>Dengan membuat akun, mengakses, atau menggunakan platform UMKMOrder, Anda menyatakan bahwa Anda telah membaca, memahami, dan setuju untuk terikat oleh seluruh Syarat & Ketentuan ini. Jika Anda tidak setuju, Anda tidak diizinkan menggunakan layanan kami.</p>
          </section>

          <section>
            <h2>2. Kualifikasi & Akun Pengguna</h2>
            <p>Untuk menggunakan UMKMOrder, Anda harus mendaftar dan memelihara akun aktif dengan informasi yang benar. Anda bertanggung jawab penuh atas keamanan kata sandi Anda dan segala transaksi atau aktivitas yang terjadi di bawah nama akun Anda.</p>
          </section>

          <section>
            <h2>3. Ketentuan Berlangganan & Pembayaran (Subscription Terms)</h2>
            <p>UMKMOrder menyediakan pilihan paket layanan berbayar (Pro & Bisnis) dengan sistem berlangganan. Dengan memilih paket berbayar, Anda menyetujui aturan berikut:</p>
            <ul>
              <li><strong>Siklus Penagihan:</strong> Biaya berlangganan ditagih di muka pada setiap awal siklus pembayaran (bulanan atau tahunan) sesuai metode pembayaran yang dipilih.</li>
              <li><strong>Masa Uji Coba (Trial):</strong> Jika paket Anda menyertakan masa uji coba gratis, Anda dapat menikmati seluruh fitur Pro secara gratis hingga masa uji coba berakhir. Jika tidak ada pembayaran langganan terkonfirmasi setelah masa trial selesai, status akun Anda akan otomatis di-downgrade ke paket Gratis (*Free*) dengan batasan produk/fitur terkait.</li>
              <li><strong>Jatuh Tempo & Suspensi Layanan:</strong> Tagihan langganan yang tidak diselesaikan dalam waktu 7 hari sejak tanggal jatuh tempo akan mengakibatkan pembatasan fitur Pro, penutupan katalog online sementara (*suspension*), atau pemutusan layanan secara otomatis demi keamanan data.</li>
              <li><strong>Pembatalan Langganan:</strong> Anda dapat menghentikan perpanjangan otomatis paket Anda kapan saja langsung melalui dashboard Billing. Setelah pembatalan, akun Anda akan tetap memiliki akses ke fitur Pro hingga sisa hari pada masa siklus pembayaran berakhir.</li>
              <li><strong>Kebijakan Pengembalian Dana (Refund):</strong> Seluruh pembayaran langganan yang telah didebet atau ditransfer ke UMKMOrder bersifat final dan **tidak dapat dikembalikan (non-refundable)** dengan alasan apa pun. Kami tidak memberikan pengembalian dana prorata untuk pembatalan di tengah siklus aktif.</li>
            </ul>
          </section>

          <section>
            <h2>4. Konten Toko & Larangan Aktivitas</h2>
            <p>Pemilik toko (Tenant) bertanggung jawab penuh atas seluruh data produk, gambar, harga, deskripsi, dan kepatuhan hukum atas barang yang dijual melalui katalog online. UMKMOrder melarang keras penjualan barang ilegal, barang palsu, obat-obatan terlarang, senjata api, atau konten yang mengandung unsur pornografi, rasisme, dan perjudian.</p>
          </section>

          <section>
            <h2>5. Pembatasan Tanggung Jawab</h2>
            <p>Dalam batas maksimal yang diizinkan oleh undang-undang, UMKMOrder tidak bertanggung jawab atas kerugian finansial, kehilangan keuntungan, hilangnya data pelanggan, atau kerusakan tidak langsung lainnya yang disebabkan oleh gangguan sistem, integrasi WhatsApp Gateway, atau penyalahgunaan akun oleh pihak yang tidak bertanggung jawab.</p>
          </section>

          <section>
            <h2>6. Perubahan Ketentuan</h2>
            <p>Kami dapat memperbarui Syarat & Ketentuan ini sewaktu-waktu. Kami akan memberitahukan perubahan besar kepada Anda melalui email terdaftar atau notifikasi di dashboard aplikasi. Melanjutkan penggunaan layanan setelah perubahan berlaku berarti Anda menyetujui ketentuan baru tersebut.</p>
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
