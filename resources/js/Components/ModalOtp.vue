<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: Boolean,
    phoneNumber: String,
    isLoading: Boolean, // Loading state saat verifikasi
});

const emit = defineEmits(['close', 'verify', 'resend']);

const otpCode = ref('');
const timer = ref(60); // 60 detik hitung mundur
let intervalId = null;

// Mulai hitung mundur saat modal muncul
const startTimer = () => {
    timer.value = 60;
    if (intervalId) clearInterval(intervalId);
    intervalId = setInterval(() => {
        if (timer.value > 0) {
            timer.value--;
        } else {
            clearInterval(intervalId);
        }
    }, 1000);
};

// Handle Input (Hanya angka)
const handleInput = (e) => {
    otpCode.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 6); // Max 6 digit
};

const submitVerify = () => {
    if (otpCode.value.length >= 4) {
        emit('verify', otpCode.value);
    }
};

const handleResend = () => {
    emit('resend');
    startTimer();
};

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
</script>

<template>
    <Transition name="modal">
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

            <div class="relative bg-white w-full max-w-sm rounded-3xl p-8 shadow-2xl transform transition-all text-center">
                <div class="w-16 h-16 bg-lime-100 text-lime-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>

                <h3 class="text-2xl font-black text-slate-900 mb-2">Verifikasi OTP</h3>
                <p class="text-sm text-slate-500 mb-6">
                    Kode rahasia telah dikirim ke WhatsApp <br> 
                    <span class="font-bold text-slate-800">{{ phoneNumber }}</span>
                </p>

                <div class="mb-6">
                    <input 
                        type="text" 
                        v-model="otpCode"
                        @input="handleInput"
                        placeholder="• • • •"
                        class="w-full text-center text-3xl font-bold tracking-[0.5em] py-3 border-b-2 border-slate-200 focus:border-[#84cc16] outline-none transition-colors text-slate-800 bg-transparent placeholder-slate-300"
                        maxlength="6"
                        autofocus
                    >
                </div>

                <button 
                    @click="submitVerify" 
                    :disabled="otpCode.length < 4 || isLoading"
                    class="w-full py-3.5 rounded-xl bg-slate-900 text-white font-bold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                >
                    <span v-if="isLoading">Memproses...</span>
                    <span v-else>Verifikasi</span>
                </button>

                <div class="mt-6 text-sm text-slate-500">
                    Belum terima kode? 
                    <button 
                        v-if="timer === 0" 
                        @click="handleResend"
                        class="text-[#84cc16] font-bold hover:underline"
                    >
                        Kirim Ulang
                    </button>
                    <span v-else class="text-slate-400 font-medium">
                        Tunggu {{ timer }}s
                    </span>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* Transisi Halus */
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .relative { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-leave-active .relative { transition: transform 0.3s ease-in; }
.modal-enter-from .relative, .modal-leave-to .relative { transform: scale(0.9) translateY(20px); }
</style>