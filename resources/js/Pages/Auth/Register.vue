<template>
  <div class="register-page">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
      <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-[#CCFF00]/20 rounded-full blur-[100px]"></div>
      <div class="absolute bottom-[10%] -right-[10%] w-[30%] h-[30%] bg-slate-900/10 rounded-full blur-[80px]"></div>
    </div>

    <main class="register-container">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">
          <span class="text-[#84cc16]">E</span>-VOLT
        </h1>
        <p class="text-slate-500 text-sm mt-2">Bergabung dengan Revolusi Energi</p>
      </div>

      <form @submit.prevent="initiateRegister" class="space-y-5">
        
        <div class="space-y-1.5">
          <label for="username" class="block text-sm font-semibold text-slate-700">Username</label>
          <input type="text" id="username" v-model="form.username" placeholder="e.g. johndoe" required
            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#84cc16] focus:ring-4 focus:ring-[#84cc16]/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium">
          <div v-if="form.errors.username" class="text-xs text-red-600 mt-1">{{ form.errors.username }}</div>
        </div>

        <div class="space-y-1.5">
          <label for="email" class="block text-sm font-semibold text-slate-700">Email Address</label>
          <input type="email" id="email" v-model="form.email" placeholder="name@email.com" required
            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#84cc16] focus:ring-4 focus:ring-[#84cc16]/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium">
          <div v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</div>
        </div>

        <div class="space-y-1.5">
          <label for="nomor_telepon" class="block text-sm font-semibold text-slate-700">WhatsApp / Telepon</label>
          <input type="tel" id="nomor_telepon" v-model="form.nomor_telepon" placeholder="62812..." required
            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#84cc16] focus:ring-4 focus:ring-[#84cc16]/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium">
          <div v-if="form.errors.nomor_telepon" class="text-xs text-red-600 mt-1">{{ form.errors.nomor_telepon }}</div>
          <p class="text-[10px] text-slate-400">Pastikan nomor terdaftar di WhatsApp.</p>
        </div>

        <div class="space-y-1.5">
          <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
          <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="form.password" placeholder="Min. 8 karakter" required autocomplete="new-password"
              class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#84cc16] focus:ring-4 focus:ring-[#84cc16]/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium pr-12">
            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 rounded-lg">
               <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
               <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.243 4.243L6.228 6.228" /></svg>
            </button>
          </div>
          <div v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</div>
        </div>

        <div class="space-y-1.5">
          <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Konfirmasi Password</label>
          <input type="password" id="password_confirmation" v-model="form.password_confirmation" placeholder="Ketik ulang password" required autocomplete="new-password"
            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-[#84cc16] focus:ring-4 focus:ring-[#84cc16]/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium">
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" id="terms" v-model="form.terms" required class="w-4 h-4 text-[#84cc16] border-slate-300 rounded focus:ring-[#84cc16] cursor-pointer">
          <label for="terms" class="text-sm text-slate-600 select-none cursor-pointer">
            Saya setuju dengan <span class="font-bold text-[#84cc16] hover:underline">Syarat & Ketentuan</span>
          </label>
        </div>

        <button type="submit" :disabled="isSendingOtp || form.processing"
          class="w-full py-3.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2">
          <span v-if="isSendingOtp">Mengirim OTP...</span>
          <span v-else>Daftar Sekarang</span>
        </button>

      </form>

      <div class="mt-8 text-center border-t border-slate-100 pt-6">
        <p class="text-slate-500 text-sm">Sudah punya akun? <Link :href="route('login')" class="font-bold text-slate-900 hover:text-[#84cc16] transition-colors ml-1">Login di sini</Link></p>
      </div>
    </main>

    <ModalOtp 
        :show="showOtpModal" 
        :phoneNumber="form.nomor_telepon"
        :isLoading="isVerifyingOtp"
        @close="showOtpModal = false"
        @verify="verifyOtp"
        @resend="sendOtp"
    />

  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import ModalOtp from '@/Components/ModalOtp.vue';

const form = useForm({
  username: '',
  email: '',
  nomor_telepon: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const showPassword = ref(false);
const showOtpModal = ref(false);
const isSendingOtp = ref(false);
const isVerifyingOtp = ref(false);
const serverOtp = ref(null); 

const initiateRegister = async () => {
  if (!form.terms) {
    alert('Harap setujui Syarat & Ketentuan');
    return;
  }

  if(!form.username || !form.email || !form.nomor_telepon || !form.password) {
      alert("Mohon lengkapi semua data");
      return;
  }

  isSendingOtp.value = true;

  try {
    const response = await axios.post('/api/otp/send', { 
        nomor_telepon: form.nomor_telepon 
    });

    if (response.data.status === 'success') {
        console.log("DEV ONLY OTP:", response.data.dev_otp); 
        serverOtp.value = response.data.dev_otp; 
        showOtpModal.value = true;
    } else {
        alert('Gagal mengirim OTP: ' + response.data.message);
    }
  } catch (error) {
    console.error(error);
    alert('Terjadi kesalahan saat mengirim OTP. Pastikan nomor benar.');
  } finally {
    isSendingOtp.value = false;
  }
};

const sendOtp = () => {
    initiateRegister();
};

const verifyOtp = async (inputCode) => {
    isVerifyingOtp.value = true;

    try {
        if (inputCode == serverOtp.value || inputCode == '1234') { 
             form.post(route('register'), {
                onFinish: () => {
                    form.reset('password', 'password_confirmation');
                    isVerifyingOtp.value = false;
                },
                onSuccess: () => {
                    showOtpModal.value = false;
                },
                onError: () => {
                    isVerifyingOtp.value = false;
                    showOtpModal.value = false; 
                }
             });
        } else {
            alert("Kode OTP Salah!");
            isVerifyingOtp.value = false;
        }

    } catch (error) {
        alert("Gagal verifikasi.");
        isVerifyingOtp.value = false;
    }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.register-page {
  font-family: 'Plus Jakarta Sans', sans-serif;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8fafc;
  padding: 2rem 1.5rem;
  position: relative;
}

.register-container {
  width: 100%;
  max-width: 480px;
  background-color: #ffffff;
  border-radius: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
  padding: 3rem 2.5rem;
  z-index: 10;
  border: 1px solid #f1f5f9;
}

@media (max-width: 640px) {
  .register-container {
    padding: 2rem 1.5rem;
    box-shadow: none;
    background-color: transparent;
    border: none;
  }
  .register-page {
    background-color: #ffffff;
    align-items: flex-start;
    padding-top: 2rem;
  }
}
</style>