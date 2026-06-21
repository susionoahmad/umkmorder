<template>
  <div class="landing-root">
    <LandingNavbar />
    
    <div class="legal-page animate-fadeIn">
      <div class="legal-container contact-container">
        <div class="legal-header">
          <div class="legal-badge">Hubungi Kami</div>
          <h1 class="gradient-text">Hubungi Kami</h1>
          <p>Punya pertanyaan, kendala teknis, atau butuh bantuan integrasi? Tim kami siap membantu Anda.</p>
        </div>

        <div class="contact-grid">
          <!-- Contact Info Card -->
          <div class="contact-info-card">
            <h2>Informasi Kontak</h2>
            <p class="info-desc">Hubungi kami melalui saluran resmi berikut untuk respon yang lebih cepat.</p>
            
            <div class="info-items">
              <div class="info-item">
                <span class="info-icon">🟢</span>
                <div>
                  <p class="info-label">WhatsApp Support</p>
                  <a href="https://wa.me/6281392156513" target="_blank" class="info-link">+62-81-392-156-513</a>
                </div>
              </div>
              
              <div class="info-item">
                <span class="info-icon">✉️</span>
                <div>
                  <p class="info-label">Email Resmi</p>
                  <a href="mailto:susiono.ahmad@gmail.com" class="info-link">susiono.ahmad@gmail.com</a>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Form Card -->
          <div class="contact-form-card">
            <h2>Kirim Pesan</h2>
            <form @submit.prevent="handleSubmit" class="contact-form">
              <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" v-model="form.name" required placeholder="Masukkan nama lengkap Anda" />
              </div>

              <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" v-model="form.email" required placeholder="nama@email.com" />
              </div>

              <div class="form-group">
                <label for="subject">Subjek</label>
                <input type="text" id="subject" v-model="form.subject" required placeholder="Contoh: Bantuan Integrasi WhatsApp" />
              </div>

              <div class="form-group">
                <label for="message">Pesan Anda</label>
                <textarea id="message" v-model="form.message" required rows="5" placeholder="Tuliskan pesan atau pertanyaan Anda di sini..."></textarea>
              </div>

              <button type="submit" class="btn-submit" :disabled="isSubmitting">
                {{ isSubmitting ? 'Mengirim...' : 'Kirim Pesan →' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <LandingFooter />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import LandingNavbar from '@/components/LandingNavbar.vue';
import LandingFooter from '@/components/LandingFooter.vue';
import api from '@/services/api';

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const isSubmitting = ref(false);

const handleSubmit = async () => {
  isSubmitting.value = true;
  try {
    const res = await api.post('/contact', form.value);
    if (res.data.status === 'success') {
      alert(res.data.message || 'Terima kasih! Pesan Anda telah berhasil dikirim. Tim kami akan segera menghubungi Anda.');
      form.value = {
        name: '',
        email: '',
        subject: '',
        message: ''
      };
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal mengirim pesan. Silakan coba lagi.');
  } finally {
    isSubmitting.value = false;
  }
};
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
.contact-container {
  max-width: 1000px;
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
  color: #94a3b8;
  font-size: 16px;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
}
.contact-grid {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 32px;
}
@media (max-width: 768px) {
  .contact-grid {
    grid-template-columns: 1fr;
  }
}
.contact-info-card {
  background: linear-gradient(135deg, #0d1526, #141f3a);
  border: 1px solid rgba(99, 102, 241, 0.25);
  border-radius: 24px;
  padding: 40px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.contact-info-card h2 {
  font-size: 22px;
  font-weight: 800;
  color: #f1f5f9;
  margin-bottom: 12px;
}
.info-desc {
  font-size: 14px;
  color: #94a3b8;
  margin-bottom: 32px;
  line-height: 1.6;
}
.info-items {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.info-item {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}
.info-icon {
  font-size: 24px;
}
.info-label {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}
.info-link {
  font-size: 15px;
  font-weight: 600;
  color: #818cf8;
  text-decoration: none;
  transition: color 0.2s;
}
.info-link:hover {
  color: #c084fc;
}
.info-text {
  font-size: 14px;
  color: #94a3b8;
  line-height: 1.6;
}
.contact-form-card {
  background: #0d1526;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}
.contact-form-card h2 {
  font-size: 22px;
  font-weight: 800;
  color: #f1f5f9;
  margin-bottom: 24px;
}
.contact-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #94a3b8;
}
.form-group input, .form-group textarea {
  background: #030711;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 12px 16px;
  color: #f1f5f9;
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-group input:focus, .form-group textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
}
.btn-submit {
  padding: 14px;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 700;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  border: none;
  cursor: pointer;
  transition: all 0.25s;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
  margin-top: 10px;
}
.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
}
.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}
.gradient-text {
  background: linear-gradient(135deg, #818cf8, #c084fc, #fb7185);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
