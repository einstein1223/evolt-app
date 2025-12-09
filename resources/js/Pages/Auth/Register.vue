<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';

const form = useForm({
  username: '',
  email: '',
  nomor_telepon: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const showPassword = ref(false);

const submit = () => {
  if (!form.terms) {
    alert('Harap setujui Syarat & Ketentuan');
    return;
  }

  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
    onSuccess: () => router.visit(route('login')),
  });
};
</script>

<template>
  <div class="register-page">
    <img src="/images/logo.jpg" alt="Logo Left" class="side-logo left-logo" />
    <img src="/images/logo.jpg" alt="Logo Right" class="side-logo right-logo" />

    <main class="register-container">
      <!-- LOGO -->
      <div class="logo">
        <span class="green">E-</span><span class="dark">VOLT</span>
      </div>

      <h1 class="welcome-title">Buat Akun Baru</h1>
      <p class="subtitle">Lengkapi data di bawah untuk bergabung.</p>

      <form class="register-form" @submit.prevent="submit">

        <!-- Username -->
        <div class="input-group">
          <label for="username">Username</label>
          <input type="text" id="username" v-model="form.username" placeholder="Masukkan username" required>
          <div v-if="form.errors.username" class="error-message">{{ form.errors.username }}</div>
        </div>

        <!-- Email -->
        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" v-model="form.email" placeholder="nama@email.com" required>
          <div v-if="form.errors.email" class="error-message">{{ form.errors.email }}</div>
        </div>

        <!-- Nomor Telepon -->
        <div class="input-group">
          <label for="nomor_telepon">Nomor Telepon</label>
          <input type="tel" id="nomor_telepon" v-model="form.nomor_telepon" placeholder="08123456789" required>
          <div v-if="form.errors.nomor_telepon" class="error-message">{{ form.errors.nomor_telepon }}</div>
        </div>

        <!-- Password -->
        <div class="input-group">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="form.password" placeholder="******"
              required autocomplete="new-password">
            <button type="button" id="togglePassword" @click="showPassword = !showPassword">
              <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L6.228 6.228" />
              </svg>
            </button>
          </div>
          <div v-if="form.errors.password" class="error-message">{{ form.errors.password }}</div>
        </div>

        <!-- Confirm Password -->
        <div class="input-group">
          <label for="password_confirmation">Konfirmasi Password</label>
          <input type="password" id="password_confirmation" v-model="form.password_confirmation" placeholder="******"
            required autocomplete="new-password">
        </div>

        <!-- Terms -->
        <div class="form-options">
          <div class="remember-me">
            <input type="checkbox" id="terms" v-model="form.terms" required>
            <label for="terms">
              Saya setuju dengan <span class="green-text">Syarat & Ketentuan</span>
            </label>
          </div>
        </div>

        <button type="submit" class="register-btn" :disabled="form.processing">DAFTAR SEKARANG</button>
      </form>

      <div class="login-link">
        Sudah punya akun?
        <Link :href="route('login')">LOGIN DI SINI</Link>
      </div>
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.register-page {
  font-family: 'Poppins', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background-color: #fff;
  position: relative;
  overflow: hidden;
}

.register-container {
  width: 100%;
  max-width: 500px;
  /* Sedikit lebih lebar dari login karena form lebih panjang */
  background-color: #fff;
  border-radius: 24px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  padding: 3rem 2.5rem;
  text-align: center;
  z-index: 10;
}

.logo {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.logo .green {
  color: #84cc16;
}

.logo .dark {
  color: #1f2937;
}

.welcome-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.subtitle {
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 2rem;
}

.register-form {
  text-align: left;
}

.input-group {
  margin-bottom: 1.25rem;
}

.input-group label {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.input-group input {
  width: 100%;
  padding: 0.8rem 1rem;
  font-size: 0.95rem;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  outline: none;
  transition: all 0.2s;
  font-family: 'Poppins', sans-serif;
  background-color: #f9fafb;
}

.input-group input:focus {
  border-color: #84cc16;
  background-color: #fff;
  box-shadow: 0 0 0 3px rgba(132, 204, 22, 0.15);
}

.password-wrapper {
  position: relative;
}

.side-logo {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 300px;
  /* Adjust size as needed */
  height: auto;
  opacity: 0.8;
  /* Optional: adjust opacity if needed */
  z-index: 1;
}

.left-logo {
  left: 80px;
  /* Brought closer from -50px */
}

.right-logo {
  right: 80px;
  /* Brought closer from -50px */
}

@media (max-width: 1024px) {
  .side-logo {
    width: 200px;
    left: 20px;
    /* Adjust for smaller screens */
  }

  .right-logo {
    right: 20px;
    /* Adjust for smaller screens */
  }
}

@media (max-width: 768px) {
  .side-logo {
    display: none;
    /* Hide on smaller screens if they overlap too much */
  }
}

#togglePassword {
  position: absolute;
  top: 50%;
  right: 1rem;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #9ca3af;
  padding: 0;
  display: flex;
}

#togglePassword svg {
  width: 20px;
  height: 20px;
}

.form-options {
  margin-top: 1rem;
  margin-bottom: 1.5rem;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.remember-me label {
  font-size: 0.85rem;
  color: #4b5563;
  margin-bottom: 0;
  cursor: pointer;
}

.remember-me input[type=checkbox] {
  width: 16px;
  height: 16px;
  accent-color: #84cc16;
  cursor: pointer;
}

.green-text {
  color: #84cc16;
  font-weight: 600;
  cursor: pointer;
}

.register-btn {
  width: 100%;
  background-color: #d9ef54;
  /* Warna Lime Button dari Login */
  color: #1f2937;
  font-weight: 700;
  font-size: 1rem;
  border: none;
  border-radius: 10px;
  padding: 0.9rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-family: 'Poppins', sans-serif;
  box-shadow: 0 4px 6px -1px rgba(217, 239, 84, 0.4);
}

.register-btn:hover:not(:disabled) {
  background-color: #cde441;
}

.register-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.login-link {
  font-size: 0.9rem;
  color: #6b7280;
  margin-top: 1.5rem;
}

.login-link a {
  color: #84cc16;
  font-weight: 700;
  text-decoration: none;
  margin-left: 4px;
  transition: color 0.2s;
}

.login-link a:hover {
  color: #65a30d;
  text-decoration: underline;
}

.error-message {
  font-size: 0.75rem;
  color: #dc2626;
  margin-top: 0.25rem;
}

/* Responsive */
@media (max-width: 640px) {
  .register-page {
    padding: 1rem;
  }

  .register-container {
    padding: 2rem 1.5rem;
  }
}
</style>