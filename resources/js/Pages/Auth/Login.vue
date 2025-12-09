<template>
  <div class="login-page">
    <img src="/images/logo.jpg" alt="Logo Left" class="side-logo left-logo" />
    <img src="/images/logo.jpg" alt="Logo Right" class="side-logo right-logo" />

    <main class="login-container">
      <div class="logo">
        <span class="green">E</span><span class="dark">-VOLT</span>
      </div>
      <h1 class="welcome-title">Welcome back to the E-Volt Community</h1>

      <div v-if="form.errors.email" class="error-message">
        {{ form.errors.email }}
      </div>

      <form class="login-form" @submit.prevent="handleLogin">
        <div class="input-group">
          <label for="email">Email or Username</label>
          <input type="text" id="email" name="email" v-model="form.email" required>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input :type="isPasswordVisible ? 'text' : 'password'" id="password" name="password" v-model="form.password"
              required>
            <button type="button" id="togglePassword" @click="isPasswordVisible = !isPasswordVisible">
              <svg v-if="!isPasswordVisible" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
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
        </div>

        <div class="form-options">
          <div class="remember-me">
            <input type="checkbox" id="remember" name="remember" v-model="form.remember">
            <label for="remember">Remember me</label>
          </div>
          <button type="submit" class="login-btn" :disabled="form.processing">LOG IN</button>
        </div>
      </form>

      <div class="signup-link">
        No Account yet?
        <Link :href="route('register')">SIGN UP</Link>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
// 1. Import useForm DAN Link
import { useForm, Link } from '@inertiajs/vue3';

// 2. Ganti state management biasa dengan useForm
const form = useForm({
  email: '',      // Default kosong
  password: '',   // Default kosong
  remember: false,
});

const isPasswordVisible = ref(false);

// 3. Buat fungsi handleLogin untuk mengirim form
// 2. FUNGSI LOGIN
const handleLogin = () => {
  // Mengirim data ke route login Laravel
  form.post(route('login'), {
    onFinish: () => form.reset('password'), // Reset password jika gagal/selesai
  });
};
</script>

<style scoped>
/* CSS Anda tidak perlu diubah, saya ringkas di sini agar tidak terlalu panjang */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.login-page {
  font-family: 'Poppins', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background-color: #fff;
  position: relative;
  overflow: hidden;
}

.login-container {
  width: 100%;
  max-width: 460px;
  background-color: #fff;
  border-radius: 24px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, .1);
  padding: 2.5rem;
  text-align: center;
  z-index: 10;
}

.logo {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: .5rem
}

.logo .green {
  color: #84cc16
}

.logo .dark {
  color: #4b5563
}

.welcome-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1.5rem
}

.social-login {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem
}

.social-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: .75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background-color: #fff;
  color: #374151;
  font-size: .9rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color .2s ease, border-color .2s ease
}

.social-btn:hover {
  background-color: #f9fafb;
  border-color: #9ca3af
}

.social-btn img {
  width: 20px;
  height: 20px
}

.login-form {
  text-align: left
}

.input-group {
  margin-bottom: 1rem
}

.input-group label {
  display: block;
  font-size: .875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: .5rem
}

.input-group input {
  width: 100%;
  padding: .8rem 1rem;
  font-size: .95rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
  font-family: 'Poppins', sans-serif
}

.input-group input:focus {
  border-color: #84cc16;
  box-shadow: 0 0 0 3px rgba(132, 204, 22, .2)
}

.password-wrapper {
  position: relative
}

#togglePassword {
  position: absolute;
  top: 50%;
  right: 1rem;
  transform: translateY(-50%);
  background: 0 0;
  border: none;
  cursor: pointer;
  color: #6b7280;
  padding: 0;
  display: flex;
  align-items: center
}

#togglePassword svg {
  width: 20px;
  height: 20px
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
  margin-bottom: 1.5rem
}

.remember-me {
  display: flex;
  align-items: center;
  gap: .5rem
}

.remember-me label {
  font-size: .9rem;
  color: #4b5563;
  margin-bottom: 0;
  cursor: pointer
}

.remember-me input[type=checkbox] {
  width: 18px;
  height: 18px;
  accent-color: #84cc16;
  cursor: pointer
}

.login-btn {
  background-color: #d9ef54;
  color: #1f2937;
  font-weight: 700;
  font-size: .95rem;
  border: none;
  border-radius: 8px;
  padding: .8rem 2rem;
  cursor: pointer;
  transition: background-color .2s ease;
  font-family: 'Poppins', sans-serif
}

.login-btn:hover {
  background-color: #cde441
}

.signup-link {
  font-size: .9rem;
  color: #6b7280;
  margin-top: 1rem
}

.signup-link a {
  color: #84cc16;
  font-weight: 600;
  text-decoration: none;
  transition: text-decoration .2s
}

.signup-link a:hover {
  text-decoration: underline
}

.error-message {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
  padding: .75rem 1.25rem;
  margin-bottom: 1rem;
  border-radius: .25rem;
  text-align: left;
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
</style>