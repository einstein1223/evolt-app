<script setup>
import { onMounted, ref } from 'vue';
import { Html5QrcodeScanner } from "html5-qrcode";
import axios from 'axios';

const scanResult = ref(null);
const scanStatus = ref('idle'); // idle, scanning, verifying, success, error
const errorMessage = ref('');
const successData = ref({});
let scanner = null;

const onScanSuccess = (decodedText, decodedResult) => {
    // Cegah scan berulang kali saat loading
    if (scanStatus.value === 'verifying' || scanStatus.value === 'success') return;

    // Hentikan sementara
    scanStatus.value = 'verifying';
    
    // Panggil Backend
    axios.post(route('scan.verify'), { token: decodedText })
        .then(res => {
            scanStatus.value = 'success';
            successData.value = res.data.data;
            playSuccessSound();
        })
        .catch(err => {
            scanStatus.value = 'error';
            errorMessage.value = err.response?.data?.message || "QR Code Tidak Valid";
            playErrorSound();
        });
};

const resetScan = () => {
    scanStatus.value = 'idle';
    scanResult.value = null;
    errorMessage.value = '';
    successData.value = {};
};

// Audio Feedback
const playSuccessSound = () => {
    const audio = new Audio('https://actions.google.com/sounds/v1/cartoon/cartoon_boing.ogg'); // Suara contoh
    audio.play();
};
const playErrorSound = () => {
    const audio = new Audio('https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg');
    audio.play();
};

onMounted(() => {
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    scanner = new Html5QrcodeScanner("reader", config, /* verbose= */ false);
    scanner.render(onScanSuccess);
});
</script>

<template>
    <div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center p-4 font-mono text-white relative overflow-hidden">
        
        <!-- Background Animation -->
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
            <div class="absolute top-10 left-10 w-32 h-32 bg-[#00C853] rounded-full blur-[80px]"></div>
            <div class="absolute bottom-10 right-10 w-40 h-40 bg-blue-500 rounded-full blur-[100px]"></div>
        </div>

        <!-- Header Mesin -->
        <div class="z-10 text-center mb-8">
            <h1 class="text-3xl font-bold tracking-widest text-[#00C853] drop-shadow-lg">E-VOLT ACCESS POINT</h1>
            <p class="text-gray-400 text-sm mt-2">Arahkan QR Code Transaksi ke Kamera</p>
        </div>

        <!-- AREA SCANNER -->
        <div v-show="scanStatus === 'idle' || scanStatus === 'error'" class="relative z-10 w-full max-w-md bg-black border-4 border-gray-800 rounded-3xl overflow-hidden shadow-2xl">
            <!-- Div ini akan diisi oleh kamera -->
            <div id="reader" class="w-full h-full"></div>
            
            <!-- Overlay Error -->
            <div v-if="scanStatus === 'error'" class="absolute inset-0 bg-red-900/80 flex flex-col items-center justify-center p-6 text-center backdrop-blur-sm">
                <div class="w-20 h-20 bg-red-600 rounded-full flex items-center justify-center mb-4 animate-bounce">
                    <i class="fas fa-times text-4xl text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">AKSES DITOLAK</h2>
                <p class="text-red-200 mt-2">{{ errorMessage }}</p>
                <button @click="resetScan" class="mt-6 px-6 py-2 bg-white text-red-900 font-bold rounded-full hover:bg-gray-200 transition">Coba Lagi</button>
            </div>
        </div>

        <!-- AREA SUKSES (TIKET) -->
        <div v-if="scanStatus === 'success'" class="z-10 w-full max-w-md bg-white text-gray-900 rounded-3xl p-8 shadow-2xl transform transition-all scale-100">
            <div class="text-center">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-5xl text-[#00C853]"></i>
                </div>
                <h2 class="text-3xl font-extrabold text-[#00C853] mb-1">AKSES DITERIMA</h2>
                <p class="text-gray-500 text-sm">Silakan mulai pengisian daya</p>
            </div>

            <div class="mt-6 border-t-2 border-dashed border-gray-300 pt-6 space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Pengguna</span>
                    <span class="font-bold">{{ successData.user }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Lokasi</span>
                    <span class="font-bold text-right">{{ successData.station }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Durasi</span>
                    <span class="font-bold text-[#00C853]">{{ successData.duration }}</span>
                </div>
            </div>

            <div class="mt-8">
                <button @click="resetScan" class="w-full py-4 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition shadow-lg">Scan Berikutnya</button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-gray-600 text-xs">
            System ID: EV-STATION-001 • Online
        </div>

    </div>
</template>

<style>
/* Custom Style untuk Library Scanner agar sesuai tema gelap */
#reader {
    border: none !important;
}
#reader__scan_region {
    background: transparent !important;
}
#reader video {
    object-fit: cover;
    border-radius: 1.5rem;
}
</style>