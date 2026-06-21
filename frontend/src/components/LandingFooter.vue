<template>
  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="brand-icon small">U</div>
        <span class="brand-name">UMKMOrder</span>
      </div>
      <p class="footer-copy">© 2026 UMKMOrder. Dibuat dengan ❤️ untuk UMKM Indonesia.</p>
      <div class="footer-links">
        <router-link to="/privacy">Privasi</router-link>
        <router-link to="/terms">Syarat</router-link>
        <router-link to="/contact">Kontak</router-link>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  border-top: 1px solid rgba(255,255,255,0.06);
  padding: 32px 24px;
  background: #030711;
}
.footer-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
.footer-brand { display: flex; align-items: center; gap: 8px; }
.brand-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex; align-items: center; justify-content: center;
  font-weight: 900; font-size: 18px; color: white;
  box-shadow: 0 4px 15px rgba(99,102,241,0.4);
}
.brand-icon.small { width: 30px; height: 30px; font-size: 14px; border-radius: 8px; }
.brand-name { font-weight: 800; font-size: 18px; color: #f1f5f9; letter-spacing: -0.5px; }
.footer-copy { font-size: 13px; color: #475569; }
.footer-links { display: flex; gap: 20px; }
.footer-links a { font-size: 13px; color: #475569; text-decoration: none; transition: color 0.2s; }
.footer-links a:hover { color: #94a3b8; }

@media (max-width: 768px) {
  .footer-inner { flex-direction: column; text-align: center; }
}
</style>
