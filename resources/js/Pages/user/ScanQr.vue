<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Html5QrcodeScanner } from "html5-qrcode";
import { Head, router } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarUser.vue';

const scanResult = ref(null);
let scanner = null;

onMounted(() => {
    // Config Scanner
    scanner = new Html5QrcodeScanner("reader", { 
        fps: 10, 
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    });

    scanner.render(onScanSuccess, onScanFailure);
});

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning setelah dapat hasil biar gak spam
    scanner.clear();
    scanResult.value = decodedText;

    // Logika ketika QR ditemukan
    // Misalnya QR berisi: "STATION-BATAM-01"
    alert(`QR Code Terdeteksi: ${decodedText}`);
    
    // Nanti bisa di-uncomment untuk redirect otomatis ke halaman booking:
    // router.visit('/booking/create?station_code=' + decodedText);
}

function onScanFailure(error) {
    // Biarkan kosong agar console tidak penuh warning saat mencari QR
}

// Matikan kamera saat keluar halaman (PENTING!)
onBeforeUnmount(() => {
    if (scanner) {
        scanner.clear().catch(error => {
            console.error("Failed to clear html5-qrcode scanner. ", error);
        });
    }
});
</script>

<template>
    <Head title="Scan QR Station" />
    <Navbar />

    <div class="min-h-screen bg-slate-900 pt-28 px-4 flex flex-col items-center text-white">
        
        <div class="text-center mb-8">
            <h1 class="text-2xl font-black text-[#CCFF00] mb-2">Scan QR Station</h1>
            <p class="text-slate-400 text-sm">Arahkan kamera ke QR Code pada mesin charger.</p>
        </div>

        <div class="w-full max-w-sm bg-black rounded-3xl overflow-hidden shadow-2xl border-4 border-slate-700 relative">
            <div id="reader" class="w-full h-full"></div>
            
            <div class="absolute top-0 left-0 w-full h-1 bg-[#CCFF00] shadow-[0_0_20px_#CCFF00] animate-scan pointer-events-none"></div>
        </div>

        <div v-if="scanResult" class="mt-6 p-4 bg-lime-500 text-black rounded-xl font-bold text-center w-full max-w-sm">
            <p class="text-xs uppercase mb-1">Code Ditemukan:</p>
            {{ scanResult }}
        </div>

        <button @click="router.visit('/dashboard')" class="mt-8 text-slate-500 hover:text-white text-sm font-medium transition-colors">
            &larr; Kembali ke Dashboard
        </button>

    </div>
</template>

<style>
/* CSS Override untuk merapikan tampilan library Scanner */
#reader { border: none !important; }
#reader__scan_region { background: transparent; }
#reader__dashboard_section_csr button { 
    background: #CCFF00; color: black; border: none; padding: 8px 16px; 
    border-radius: 8px; font-weight: bold; margin-top: 10px; cursor: pointer;
}
#reader__dashboard_section_swaplink { display: none; } /* Sembunyikan link text aneh */

@keyframes scanMove {
    0% { top: 5%; opacity: 0; }
    20% { opacity: 1; }
    80% { opacity: 1; }
    100% { top: 95%; opacity: 0; }
}
.animate-scan {
    animation: scanMove 2s infinite linear;
}
</style>