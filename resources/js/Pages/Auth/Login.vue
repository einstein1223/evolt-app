<template>
  <div class="login-page">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
      <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-[#CCFF00]/20 rounded-full blur-[100px]"></div>
      <div class="absolute top-[20%] -right-[10%] w-[30%] h-[30%] bg-slate-900/10 rounded-full blur-[80px]"></div>
    </div>

    <main class="login-container">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">
          <span class="text-[#84cc16]">E</span>-VOLT
        </h1>
        <p class="text-slate-500 text-sm mt-2">Welcome back to the Future of Energy</p>
      </div>

      <div v-if="form.errors.email" class="mb-6 bg-red-50 text-red-600 text-sm px-4 py-3 rounded-xl border border-red-100 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        {{ form.errors.email }}
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5">
        <div class="space-y-1.5">
          <label for="email" class="block text-sm font-semibold text-slate-700">Email Address</label>
          <input 
            type="text" 
            id="email" 
            v-model="form.email" 
            required
            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#84cc16] focus:ring-4 focus:ring-[#84cc16]/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium"
            placeholder="name@example.com"
          >
        </div>

        <div class="space-y-1.5">
          <div class="flex justify-between items-center">
            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
            <a href="#" class="text-xs font-semibold text-[#84cc16] hover:underline">Forgot password?</a>
          </div>
          <div class="relative">
            <input 
              :type="isPasswordVisible ? 'text' : 'password'" 
              id="password" 
              v-model="form.password"
              required
              class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#84cc16] focus:ring-4 focus:ring-[#84cc16]/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium pr-12"
              placeholder="••••••••"
            >
            <button 
              type="button" 
              @click="isPasswordVisible = !isPasswordVisible"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors"
            >
              <svg v-if="!isPasswordVisible" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L6.228 6.228" />
              </svg>
            </button>
          </div>
        </div>

        <div class="pt-2">
          <div class="flex items-center mb-6">
            <input 
              id="remember" 
              type="checkbox" 
              v-model="form.remember"
              class="w-4 h-4 text-[#84cc16] border-slate-300 rounded focus:ring-[#84cc16] cursor-pointer"
            >
            <label for="remember" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">Remember me</label>
          </div>

          <button 
            type="submit" 
            :disabled="form.processing"
            class="w-full py-3.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2"
          >
            <span v-if="form.processing">Logging in...</span>
            <span v-else>Sign In</span>
            <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
          </button>
        </div>
      </form>

      <div class="mt-8 text-center border-t border-slate-100 pt-6">
        <p class="text-slate-500 text-sm">
          New to E-Volt? 
          <Link :href="route('register')" class="font-bold text-slate-900 hover:text-[#84cc16] transition-colors ml-1">
            Create an account
          </Link>
        </p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const isPasswordVisible = ref(false);

const handleLogin = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.login-page {
  font-family: 'Plus Jakarta Sans', sans-serif; /* Font modern yang sangat populer untuk UI */
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8fafc; /* Slate-50 background */
  padding: 1.5rem;
  position: relative;
}

.login-container {
  width: 100%;
  max-width: 440px;
  background-color: #ffffff;
  border-radius: 1.5rem; /* Rounded-2xl */
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08); /* Soft shadow */
  padding: 3rem 2.5rem;
  z-index: 10;
  border: 1px solid #f1f5f9; /* Slate-100 border */
}

/* Responsive Adjustments */
@media (max-width: 640px) {
  .login-container {
    padding: 2rem 1.5rem;
    box-shadow: none; /* Remove shadow on mobile for cleaner look */
    background-color: transparent;
    border: none;
  }
  .login-page {
    background-color: #ffffff;
    align-items: flex-start;
    padding-top: 4rem;
  }
}
</style>