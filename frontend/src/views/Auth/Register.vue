<template>
  <div class="register-root">
    <!-- Background orbs -->
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>

    <!-- Top bar -->
    <div class="top-bar">
      <router-link to="/" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali
      </router-link>
      <div class="brand">
        <div class="brand-icon">U</div>
        <span>UMKMOrder</span>
      </div>
      <div class="already-member">
        Sudah punya akun?
        <router-link to="/login">Masuk</router-link>
      </div>
    </div>

    <!-- Wizard container -->
    <div class="wizard-wrapper">
      <!-- Step indicator -->
      <div class="step-indicator">
        <div
          v-for="(label, i) in stepLabels"
          :key="i"
          class="step-item"
          :class="{ active: step === i + 1, done: step > i + 1 }"
        >
          <div class="step-bubble">
            <svg v-if="step > i + 1" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
            <span v-else>{{ i + 1 }}</span>
          </div>
          <span class="step-label">{{ label }}</span>
          <div v-if="i < stepLabels.length - 1" class="step-line" :class="{ filled: step > i + 1 }"></div>
        </div>
      </div>

      <!-- Card -->
      <div class="wizard-card">
        <!-- ===== STEP 1: Info Pemilik ===== -->
        <transition name="slide-fade" mode="out-in">
        <div v-if="step === 1" key="step1">
          <div class="card-header">
            <div class="card-icon">👤</div>
            <h2>Info Pemilik</h2>
            <p>Data akun untuk masuk ke dashboard Anda.</p>
          </div>
          <form @submit.prevent="nextStep" class="form-grid">
            <div class="form-group">
              <label>Nama Lengkap <span class="req">*</span></label>
              <input
                v-model="form.ownerName"
                type="text" required
                placeholder="contoh: Budi Santoso"
                :class="{ error: errors.ownerName }"
                @input="clearError('ownerName')"
              />
              <span class="error-msg" v-if="errors.ownerName">{{ errors.ownerName }}</span>
            </div>

            <div class="form-group">
              <label>Email Bisnis <span class="req">*</span></label>
              <input
                v-model="form.email"
                type="email" required
                placeholder="email@tokoandasaya.com"
                :class="{ error: errors.email }"
                @input="clearError('email')"
              />
              <span class="error-msg" v-if="errors.email">{{ errors.email }}</span>
            </div>

            <div class="form-group">
              <label>Nomor WhatsApp <span class="req">*</span></label>
              <div class="input-with-prefix">
                <span class="prefix">+62</span>
                <input
                  v-model="form.phone"
                  type="tel" required
                  placeholder="81234567890"
                  :class="{ error: errors.phone }"
                  @input="clearError('phone')"
                />
              </div>
              <span class="hint">Nomor ini digunakan untuk menerima notifikasi order.</span>
              <span class="error-msg" v-if="errors.phone">{{ errors.phone }}</span>
            </div>

            <div class="form-group">
              <label>Password <span class="req">*</span></label>
              <div class="password-wrapper">
                <input
                  v-model="form.password"
                  :type="showPass ? 'text' : 'password'"
                  required minlength="8"
                  placeholder="Minimal 8 karakter"
                  :class="{ error: errors.password }"
                  @input="clearError('password')"
                />
                <button type="button" class="toggle-pass" @click="showPass = !showPass" tabindex="-1">
                  {{ showPass ? '🙈' : '👁️' }}
                </button>
              </div>
              <!-- Password strength -->
              <div class="pass-strength" v-if="form.password">
                <div class="strength-bars">
                  <div v-for="n in 4" :key="n" class="strength-bar" :class="{ filled: passwordStrength >= n }"></div>
                </div>
                <span class="strength-label" :class="strengthClass">{{ strengthLabel }}</span>
              </div>
              <span class="error-msg" v-if="errors.password">{{ errors.password }}</span>
            </div>

            <div class="form-group">
              <label>Konfirmasi Password <span class="req">*</span></label>
              <input
                v-model="form.passwordConfirm"
                :type="showPass ? 'text' : 'password'"
                required
                placeholder="Ulangi password"
                :class="{ error: errors.passwordConfirm }"
                @input="clearError('passwordConfirm')"
              />
              <span class="error-msg" v-if="errors.passwordConfirm">{{ errors.passwordConfirm }}</span>
            </div>

            <button type="submit" class="btn-next" :disabled="isSubmitting">
              Lanjut: Info Toko
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
          </form>
        </div>

        <!-- ===== STEP 2: Info Toko ===== -->
        <div v-else-if="step === 2" key="step2">
          <div class="card-header">
            <div class="card-icon">🏪</div>
            <h2>Info Toko</h2>
            <p>Atur identitas toko online Anda yang akan dilihat pelanggan.</p>
          </div>
          <form @submit.prevent="nextStep" class="form-grid">
            <div class="form-group">
              <label>Nama Toko <span class="req">*</span></label>
              <input
                v-model="form.storeName"
                type="text" required
                placeholder="contoh: Toko Kurnia Telur"
                :class="{ error: errors.storeName }"
                @input="onStoreNameInput"
              />
              <span class="error-msg" v-if="errors.storeName">{{ errors.storeName }}</span>
            </div>

            <div class="form-group">
              <label>Link Toko (Slug) <span class="req">*</span></label>
              <div class="slug-wrapper">
                <span class="slug-prefix">{{ currentHost }}/</span>
                <input
                  v-model="form.slug"
                  type="text" required
                  placeholder="nama-toko"
                  :class="{ error: errors.slug, checking: isCheckingSlug, valid: slugAvailable === true, taken: slugAvailable === false }"
                  @input="onSlugInput"
                />
              </div>
              <div class="slug-status" v-if="form.slug.length >= 3">
                <span v-if="isCheckingSlug" class="checking">⏳ Mengecek ketersediaan...</span>
                <span v-else-if="slugAvailable === true" class="available">✓ Tersedia! Link ini bisa digunakan.</span>
                <span v-else-if="slugAvailable === false" class="taken">✗ Sudah dipakai, coba nama lain.</span>
              </div>
              <span class="hint">Gunakan huruf kecil, angka, dan strip. Contoh: <strong>toko-saya</strong></span>
              <span class="error-msg" v-if="errors.slug">{{ errors.slug }}</span>
            </div>

            <div class="form-group">
              <label>Deskripsi Toko</label>
              <textarea
                v-model="form.storeDesc"
                rows="3"
                placeholder="Ceritakan sedikit tentang toko Anda..."
              ></textarea>
            </div>

            <div class="form-group">
              <label>Alamat Toko</label>
              <input
                v-model="form.address"
                type="text"
                placeholder="contoh: Jl. Pasar Lama No. 12, Surabaya"
              />
            </div>

            <div class="btn-row">
              <button type="button" class="btn-back" @click="step--">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Kembali
              </button>
              <button type="submit" class="btn-next" :disabled="isSubmitting || slugAvailable === false">
                Lanjut: Pilih Plan
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
              </button>
            </div>
          </form>
        </div>

        <!-- ===== STEP 3: Pilih Plan ===== -->
        <div v-else-if="step === 3" key="step3">
          <div class="card-header">
            <div class="card-icon">🚀</div>
            <h2>Pilih Paket</h2>
            <p>Mulai gratis, upgrade kapan saja setelah toko Anda berkembang.</p>
          </div>

          <div class="plan-options">
            <div
              v-for="plan in planOptions"
              :key="plan.value"
              class="plan-option"
              :class="{ selected: form.plan === plan.value }"
              @click="form.plan = plan.value"
            >
              <div class="plan-radio">
                <div class="radio-inner" v-if="form.plan === plan.value"></div>
              </div>
              <div class="plan-info">
                <div class="plan-top">
                  <span class="plan-name">{{ plan.name }}</span>
                  <span class="plan-badge" v-if="plan.badge" :style="{ background: plan.badgeColor }">{{ plan.badge }}</span>
                  <span class="plan-price">{{ plan.price }}</span>
                </div>
                <p class="plan-desc">{{ plan.desc }}</p>
                <ul class="plan-features">
                  <li v-for="f in plan.features" :key="f">✓ {{ f }}</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Summary -->
          <div class="summary-box">
            <h3>Ringkasan Pendaftaran</h3>
            <div class="summary-rows">
              <div class="summary-row">
                <span>Nama Pemilik</span>
                <strong>{{ form.ownerName }}</strong>
              </div>
              <div class="summary-row">
                <span>Email</span>
                <strong>{{ form.email }}</strong>
              </div>
              <div class="summary-row">
                <span>Nama Toko</span>
                <strong>{{ form.storeName }}</strong>
              </div>
              <div class="summary-row">
                <span>Link Toko</span>
                <strong class="slug-preview">{{ currentHost }}/{{ form.slug }}</strong>
              </div>
              <div class="summary-row">
                <span>Paket</span>
                <strong>{{ planOptions.find(p => p.value === form.plan)?.name }}</strong>
              </div>
            </div>
          </div>

          <!-- Error global -->
          <div class="error-alert" v-if="globalError">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ globalError }}
          </div>

          <div class="terms-check">
            <input type="checkbox" id="terms" v-model="form.agreeTerms" />
            <label for="terms">Saya setuju dengan <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a> UMKMOrder.</label>
          </div>

          <div class="btn-row">
            <button type="button" class="btn-back" @click="step--">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
              Kembali
            </button>
            <button
              class="btn-next btn-register"
              @click="handleRegister"
              :disabled="isSubmitting || !form.agreeTerms"
            >
              <span v-if="isSubmitting" class="spinner"></span>
              <span>{{ isSubmitting ? 'Mendaftarkan...' : 'Buat Toko Sekarang 🎉' }}</span>
            </button>
          </div>
        </div>

        <!-- ===== STEP 4: SUCCESS ===== -->
        <div v-else-if="step === 4" key="step4" class="success-screen">
          <div class="success-anim">🎉</div>
          <h2>Toko Anda Siap!</h2>
          <p>Selamat datang, <strong>{{ form.ownerName }}</strong>! Toko <strong>{{ form.storeName }}</strong> berhasil dibuat.</p>
          <div class="success-link-box">
            <span class="success-link-label">Link toko Anda:</span>
            <div class="success-link">{{ currentHost }}/{{ form.slug }}</div>
          </div>
          <div class="success-actions">
            <router-link to="/dashboard" class="btn-next">
              Masuk Dashboard
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </router-link>
          </div>
        </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const authStore = useAuthStore();
const currentHost = computed(() => typeof window !== 'undefined' ? window.location.host : 'umkmorder.id');

const step = ref(1);
const isSubmitting = ref(false);
const showPass = ref(false);
const isCheckingSlug = ref(false);
const slugAvailable = ref<boolean | null>(null);
const globalError = ref('');
let slugTimeout: ReturnType<typeof setTimeout> | null = null;

const stepLabels = ['Info Pemilik', 'Info Toko', 'Pilih Paket'];

const form = ref({
  ownerName: '',
  email: '',
  phone: '',
  password: '',
  passwordConfirm: '',
  storeName: '',
  slug: '',
  storeDesc: '',
  address: '',
  plan: 'free',
  agreeTerms: false,
});

const errors = ref<Record<string, string>>({});

function clearError(field: string) {
  delete errors.value[field];
}

// Password strength
const passwordStrength = computed(() => {
  const p = form.value.password;
  if (!p) return 0;
  let score = 0;
  if (p.length >= 8) score++;
  if (/[A-Z]/.test(p)) score++;
  if (/[0-9]/.test(p)) score++;
  if (/[^A-Za-z0-9]/.test(p)) score++;
  return score;
});
const strengthLabel = computed(() => {
  const labels = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
  return labels[passwordStrength.value] || '';
});
const strengthClass = computed(() => {
  const classes = ['', 'weak', 'fair', 'strong', 'very-strong'];
  return classes[passwordStrength.value] || '';
});

// Slug auto-generate from store name
function toSlug(name: string) {
  return name.toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .substring(0, 50);
}

function onStoreNameInput() {
  clearError('storeName');
  if (form.value.storeName) {
    form.value.slug = toSlug(form.value.storeName);
    debouncedCheckSlug();
  }
}

function onSlugInput() {
  clearError('slug');
  form.value.slug = toSlug(form.value.slug);
  debouncedCheckSlug();
}

function debouncedCheckSlug() {
  slugAvailable.value = null;
  if (slugTimeout) clearTimeout(slugTimeout);
  if (form.value.slug.length < 3) return;
  slugTimeout = setTimeout(checkSlugAvailability, 600);
}

async function checkSlugAvailability() {
  if (!form.value.slug || form.value.slug.length < 3) return;
  isCheckingSlug.value = true;
  try {
    const res = await api.get(`/check-slug?slug=${form.value.slug}`);
    slugAvailable.value = res.data.available;
  } catch {
    // If API doesn't exist yet, assume available
    slugAvailable.value = true;
  } finally {
    isCheckingSlug.value = false;
  }
}

// Step validation
function validateStep1(): boolean {
  const e: Record<string, string> = {};
  if (!form.value.ownerName.trim()) e.ownerName = 'Nama tidak boleh kosong.';
  if (!form.value.email.trim()) e.email = 'Email tidak boleh kosong.';
  if (!form.value.phone.trim()) e.phone = 'Nomor WhatsApp tidak boleh kosong.';
  else if (!/^\d{8,15}$/.test(form.value.phone.replace(/\D/g, ''))) e.phone = 'Nomor WhatsApp tidak valid.';
  if (form.value.password.length < 8) e.password = 'Password minimal 8 karakter.';
  if (form.value.password !== form.value.passwordConfirm) e.passwordConfirm = 'Password tidak cocok.';
  errors.value = e;
  return Object.keys(e).length === 0;
}

function validateStep2(): boolean {
  const e: Record<string, string> = {};
  if (!form.value.storeName.trim()) e.storeName = 'Nama toko tidak boleh kosong.';
  if (!form.value.slug.trim()) e.slug = 'Slug tidak boleh kosong.';
  else if (form.value.slug.length < 3) e.slug = 'Slug minimal 3 karakter.';
  else if (slugAvailable.value === false) e.slug = 'Slug sudah dipakai, pilih yang lain.';
  errors.value = e;
  return Object.keys(e).length === 0;
}

function nextStep() {
  if (step.value === 1 && !validateStep1()) return;
  if (step.value === 2 && !validateStep2()) return;
  step.value++;
}

async function handleRegister() {
  if (!form.value.agreeTerms) return;
  isSubmitting.value = true;
  globalError.value = '';
  try {
    const payload = {
      name: form.value.ownerName,
      email: form.value.email,
      phone: '+62' + form.value.phone.replace(/\D/g, ''),
      password: form.value.password,
      password_confirmation: form.value.passwordConfirm,
      tenant_name: form.value.storeName,
      slug: form.value.slug,
      address: form.value.address,
      subscription_plan: form.value.plan,
    };
    const res = await api.post('/register', payload);
    if (res.data.status === 'success') {
      // Auto-login after register
      const { token, user, tenant } = res.data.data;
      authStore.setSession(token, user, tenant);
      step.value = 4;
    } else {
      globalError.value = res.data.message || 'Pendaftaran gagal, coba lagi.';
    }
  } catch (err: any) {
    const errData = err.response?.data;
    if (errData?.errors) {
      const firstError = Object.values(errData.errors)[0];
      globalError.value = Array.isArray(firstError) ? firstError[0] as string : String(firstError);
    } else {
      globalError.value = errData?.message || 'Terjadi kesalahan. Silakan coba lagi.';
    }
  } finally {
    isSubmitting.value = false;
  }
}

const planOptions = [
  {
    value: 'free',
    name: 'Gratis',
    price: 'Rp 0/bln',
    badge: '',
    badgeColor: '',
    desc: 'Cocok untuk memulai dan memvalidasi bisnis online Anda.',
    features: ['1 Katalog Online', 'Hingga 20 Produk', 'WhatsApp Order', 'Dashboard Dasar'],
  },
  {
    value: 'pro',
    name: 'Pro',
    price: 'Rp 49rb/bln',
    badge: '🔥 Populer',
    badgeColor: 'linear-gradient(135deg,#6366f1,#8b5cf6)',
    desc: 'Untuk UMKM aktif yang ingin tumbuh lebih cepat.',
    features: ['Produk Tak Terbatas', 'Manajemen Piutang', 'Laporan & Analitik', 'Notifikasi WA Otomatis'],
  },
];
</script>

<style scoped>
/* ===== ROOT ===== */
.register-root {
  min-height: 100vh;
  background: #030711;
  color: #e2e8f0;
  font-family: 'Inter', system-ui, sans-serif;
  padding-bottom: 60px;
  position: relative;
  overflow-x: hidden;
}
.orb {
  position: fixed; border-radius: 50%; pointer-events: none;
  filter: blur(120px); opacity: 0.2;
}
.orb1 { width: 600px; height: 600px; background: #6366f1; top: -200px; left: -200px; }
.orb2 { width: 500px; height: 500px; background: #8b5cf6; bottom: -150px; right: -150px; }

/* ===== TOP BAR ===== */
.top-bar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 32px; border-bottom: 1px solid rgba(255,255,255,0.06);
  background: rgba(3,7,17,0.7); backdrop-filter: blur(10px);
  position: sticky; top: 0; z-index: 10;
}
.back-link {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 14px; font-weight: 500; color: #64748b; text-decoration: none;
  transition: color 0.2s;
}
.back-link:hover { color: #94a3b8; }
.brand {
  display: flex; align-items: center; gap: 8px;
  font-weight: 800; font-size: 17px; color: #f1f5f9;
}
.brand-icon {
  width: 34px; height: 34px; border-radius: 9px;
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  display: flex; align-items: center; justify-content: center;
  font-weight: 900; font-size: 16px;
}
.already-member { font-size: 14px; color: #475569; }
.already-member a { color: #818cf8; font-weight: 600; text-decoration: none; }
.already-member a:hover { color: #a5b4fc; }

/* ===== WIZARD WRAPPER ===== */
.wizard-wrapper {
  max-width: 680px; margin: 48px auto 0; padding: 0 20px;
  position: relative; z-index: 1;
}

/* ===== STEP INDICATOR ===== */
.step-indicator {
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 36px; gap: 0;
}
.step-item {
  display: flex; align-items: center; gap: 8px; position: relative;
}
.step-bubble {
  width: 34px; height: 34px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700;
  background: rgba(255,255,255,0.06);
  border: 2px solid rgba(255,255,255,0.1);
  color: #475569; transition: all 0.3s; flex-shrink: 0;
}
.step-item.active .step-bubble {
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  border-color: transparent; color: white;
  box-shadow: 0 4px 15px rgba(99,102,241,0.4);
}
.step-item.done .step-bubble {
  background: linear-gradient(135deg,#10b981,#059669);
  border-color: transparent; color: white;
}
.step-label {
  font-size: 13px; font-weight: 600;
  color: #334155; white-space: nowrap;
}
.step-item.active .step-label { color: #a5b4fc; }
.step-item.done .step-label { color: #34d399; }
.step-line {
  width: 60px; height: 2px;
  background: rgba(255,255,255,0.07); margin: 0 12px; flex-shrink: 0;
  transition: background 0.3s;
}
.step-line.filled { background: linear-gradient(90deg,#6366f1,#10b981); }

/* ===== CARD ===== */
.wizard-card {
  background: #0d1526;
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 28px;
  padding: 40px;
  box-shadow: 0 30px 80px rgba(0,0,0,0.4), 0 0 0 1px rgba(99,102,241,0.08);
}

/* ===== CARD HEADER ===== */
.card-header { text-align: center; margin-bottom: 36px; }
.card-icon { font-size: 48px; margin-bottom: 12px; }
.card-header h2 { font-size: 28px; font-weight: 800; color: #f8fafc; margin-bottom: 8px; letter-spacing: -0.5px; }
.card-header p { font-size: 15px; color: #64748b; }

/* ===== FORM ===== */
.form-grid { display: flex; flex-direction: column; gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 14px; font-weight: 600; color: #94a3b8; }
.req { color: #f87171; }
.form-group input,
.form-group textarea {
  background: #060d1f; border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 12px; padding: 12px 16px;
  font-size: 15px; color: #e2e8f0;
  outline: none; transition: all 0.2s;
  font-family: inherit; width: 100%; box-sizing: border-box;
}
.form-group input:focus,
.form-group textarea:focus {
  border-color: rgba(99,102,241,0.5);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.form-group input.error { border-color: rgba(239,68,68,0.5); }
.form-group textarea { resize: vertical; }
.error-msg { font-size: 12px; color: #f87171; margin-top: 2px; }
.hint { font-size: 12px; color: #475569; margin-top: 2px; }

/* Input with prefix */
.input-with-prefix {
  display: flex; align-items: center;
  background: #060d1f; border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 12px; overflow: hidden;
  transition: all 0.2s;
}
.input-with-prefix:focus-within {
  border-color: rgba(99,102,241,0.5);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.prefix {
  padding: 12px 12px 12px 16px;
  font-size: 15px; color: #64748b; font-weight: 600;
  border-right: 1px solid rgba(255,255,255,0.06);
  flex-shrink: 0;
}
.input-with-prefix input {
  flex: 1; background: transparent; border: none !important;
  box-shadow: none !important; border-radius: 0 !important;
}

/* Password */
.password-wrapper {
  position: relative; display: flex;
}
.password-wrapper input { padding-right: 44px; }
.toggle-pass {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; font-size: 16px; padding: 4px;
}
.pass-strength { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
.strength-bars { display: flex; gap: 4px; }
.strength-bar { width: 40px; height: 4px; border-radius: 2px; background: rgba(255,255,255,0.08); transition: background 0.3s; }
.strength-bar.filled:nth-child(1) { background: #ef4444; }
.strength-bar.filled:nth-child(2) { background: #f59e0b; }
.strength-bar.filled:nth-child(3) { background: #3b82f6; }
.strength-bar.filled:nth-child(4) { background: #10b981; }
.strength-label { font-size: 12px; font-weight: 600; }
.strength-label.weak { color: #ef4444; }
.strength-label.fair { color: #f59e0b; }
.strength-label.strong { color: #3b82f6; }
.strength-label.very-strong { color: #10b981; }

/* Slug */
.slug-wrapper {
  display: flex; align-items: center;
  background: #060d1f; border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 12px; overflow: hidden;
  transition: all 0.2s;
}
.slug-wrapper:focus-within {
  border-color: rgba(99,102,241,0.5);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.slug-prefix {
  padding: 12px 12px 12px 16px; font-size: 14px;
  color: #475569; font-weight: 500; flex-shrink: 0;
  border-right: 1px solid rgba(255,255,255,0.06);
}
.slug-wrapper input {
  flex: 1; background: transparent; border: none !important;
  box-shadow: none !important; border-radius: 0 !important;
  font-family: 'Courier New', monospace;
}
.slug-status { margin-top: 6px; font-size: 13px; font-weight: 600; }
.slug-status .checking { color: #94a3b8; }
.slug-status .available { color: #10b981; }
.slug-status .taken { color: #ef4444; }

/* Buttons */
.btn-next {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; padding: 14px 24px; border-radius: 12px;
  background: linear-gradient(135deg,#6366f1,#8b5cf6);
  color: white; font-size: 16px; font-weight: 700;
  border: none; cursor: pointer; transition: all 0.25s;
  box-shadow: 0 6px 20px rgba(99,102,241,0.35);
  text-decoration: none;
}
.btn-next:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(99,102,241,0.45); }
.btn-next:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-register { font-size: 17px; }
.btn-back {
  display: flex; align-items: center; gap: 6px;
  padding: 14px 20px; border-radius: 12px;
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
  color: #94a3b8; font-size: 15px; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
}
.btn-back:hover { background: rgba(255,255,255,0.07); color: #e2e8f0; }
.btn-row { display: flex; gap: 12px; }
.btn-row .btn-next { flex: 1; }

/* Plans */
.plan-options { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }
.plan-option {
  display: flex; gap: 16px; align-items: flex-start;
  padding: 20px; border-radius: 16px;
  border: 2px solid rgba(255,255,255,0.07);
  background: #060d1f; cursor: pointer; transition: all 0.2s;
}
.plan-option:hover { border-color: rgba(99,102,241,0.25); }
.plan-option.selected {
  border-color: rgba(99,102,241,0.5);
  background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(139,92,246,0.05));
  box-shadow: 0 0 0 1px rgba(99,102,241,0.2), 0 8px 20px rgba(99,102,241,0.1);
}
.plan-radio {
  width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0;
  border: 2px solid rgba(255,255,255,0.15); margin-top: 2px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.plan-option.selected .plan-radio { border-color: #6366f1; background: rgba(99,102,241,0.1); }
.radio-inner { width: 10px; height: 10px; border-radius: 50%; background: #6366f1; }
.plan-info { flex: 1; }
.plan-top { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap; }
.plan-name { font-size: 17px; font-weight: 800; color: #f1f5f9; }
.plan-badge {
  font-size: 11px; font-weight: 700; padding: 2px 10px;
  border-radius: 100px; color: white;
}
.plan-price { font-size: 15px; font-weight: 700; color: #818cf8; margin-left: auto; }
.plan-desc { font-size: 13px; color: #64748b; margin-bottom: 10px; line-height: 1.5; }
.plan-features { list-style: none; padding: 0; display: flex; flex-wrap: wrap; gap: 6px 16px; }
.plan-features li { font-size: 12px; color: #94a3b8; }

/* Summary */
.summary-box {
  background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.15);
  border-radius: 16px; padding: 20px; margin-bottom: 20px;
}
.summary-box h3 { font-size: 14px; font-weight: 700; color: #94a3b8; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
.summary-rows { display: flex; flex-direction: column; gap: 10px; }
.summary-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
.summary-row span { color: #64748b; }
.summary-row strong { color: #e2e8f0; font-weight: 600; }
.slug-preview { color: #818cf8; font-family: 'Courier New', monospace; }

/* Terms */
.terms-check {
  display: flex; align-items: flex-start; gap: 10px;
  margin-bottom: 20px; font-size: 13px; color: #64748b; line-height: 1.5;
}
.terms-check input[type="checkbox"] {
  margin-top: 2px; flex-shrink: 0; accent-color: #6366f1;
  width: 16px; height: 16px; cursor: pointer;
}
.terms-check a { color: #818cf8; text-decoration: none; }
.terms-check a:hover { color: #a5b4fc; }

/* Error alert */
.error-alert {
  display: flex; align-items: center; gap: 10px;
  background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);
  border-radius: 12px; padding: 14px 16px;
  font-size: 14px; color: #f87171; margin-bottom: 16px;
}

/* Spinner */
.spinner {
  width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white; border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Success */
.success-screen { text-align: center; padding: 20px 0; }
.success-anim { font-size: 72px; margin-bottom: 20px; animation: popIn 0.5s cubic-bezier(0.175,0.885,0.32,1.275); }
.success-screen h2 { font-size: 32px; font-weight: 900; color: #f8fafc; margin-bottom: 12px; }
.success-screen p { font-size: 16px; color: #64748b; line-height: 1.7; margin-bottom: 28px; }
.success-link-box {
  background: rgba(99,102,241,0.08); border: 1px solid rgba(99,102,241,0.2);
  border-radius: 14px; padding: 16px 24px; margin-bottom: 32px; text-align: center;
}
.success-link-label { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px; }
.success-link { font-size: 18px; font-weight: 700; color: #818cf8; font-family: 'Courier New', monospace; }
.success-actions { display: flex; justify-content: center; }
@keyframes popIn {
  from { transform: scale(0); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

/* Slide transition */
.slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.3s ease; }
.slide-fade-enter-from { opacity: 0; transform: translateX(30px); }
.slide-fade-leave-to { opacity: 0; transform: translateX(-30px); }

/* Responsive */
@media (max-width: 640px) {
  .wizard-card { padding: 24px 20px; border-radius: 20px; }
  .top-bar { padding: 14px 16px; }
  .already-member { display: none; }
  .step-line { width: 30px; margin: 0 6px; }
  .step-label { display: none; }
  .btn-row { flex-direction: column-reverse; }
  .btn-row .btn-next { width: 100%; }
}
</style>
