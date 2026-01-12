<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    booking: Object,
    user_name: String,
    car_info: String
});

// State Simulasi
const progress = ref(0);
const kwh = ref(0);
const voltage = ref(220);
const ampere = ref(0);
const timeElapsed = ref('00:00');
let chargingInterval = null;
let seconds = 0;

// Format Durasi ke Detik (Simulasi Cepat)
// Misal booking 30 menit -> Simulasi selesai dalam 30 detik
const targetSeconds = 30; 

const startCharging = () => {
    chargingInterval = setInterval(() => {
        seconds++;
        
        // Update Waktu
        const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
        const secs = (seconds % 60).toString().padStart(2, '0');
        timeElapsed.value = `${mins}:${secs}`;

        // Update Progress Bar
        progress.value = Math.min(100, (seconds / targetSeconds) * 100);
        
        // Update Angka Listrik (Simulasi)
        kwh.value = (seconds * 0.5).toFixed(2);
        ampere.value = (Math.random() * (32 - 30) + 30).toFixed(1); // Fluktuasi 30-32A

        // Selesai
        if (seconds >= targetSeconds) {
            clearInterval(chargingInterval);
            alert("Pengisian Selesai! Silakan cabut konektor.");
            router.visit(route('scan.index')); // Kembali ke layar scan awal
        }
    }, 1000);
};

const stopEmergency = () => {
    if(confirm("Hentikan Pengisian Darurat?")) {
        clearInterval(chargingInterval);
        router.visit(route('scan.index'));
    }
};

onMounted(() => {
    setTimeout(startCharging, 1000); // Mulai otomatis setelah 1 detik
});

onBeforeUnmount(() => clearInterval(chargingInterval));
</script>

<template>
    <div class="min-h-screen bg-gray-900 text-white font-mono flex flex-col relative overflow-hidden">
        
        <!-- Background Grid -->
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
        
        <!-- Header Info -->
        <div class="z-10 p-8 flex justify-between items-start border-b border-gray-800 bg-gray-900/80 backdrop-blur-md">
            <div>
                <h1 class="text-[#00C853] text-xl font-bold tracking-widest">E-VOLT STATION</h1>
                <p class="text-gray-400 text-sm mt-1">ID: {{ booking.station_name }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold">{{ user_name }}</h2>
                <p class="text-gray-400 text-sm">{{ car_info }}</p>
                <div class="mt-2 bg-gray-800 px-3 py-1 rounded text-xs text-gray-300 inline-block">
                    Booking: {{ booking.booking_number }}
                </div>
            </div>
        </div>

        <!-- Main Display -->
        <div class="flex-grow flex flex-col items-center justify-center z-10 relative">
            
            <!-- Lingkaran Progress -->
            <div class="relative w-64 h-64 flex items-center justify-center mb-10">
                <!-- Glow Effect -->
                <div class="absolute inset-0 bg-[#00C853] rounded-full blur-[60px] opacity-20 animate-pulse"></div>
                
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="128" cy="128" r="120" stroke="#1f2937" stroke-width="12" fill="none" />
                    <circle cx="128" cy="128" r="120" stroke="#00C853" stroke-width="12" fill="none"
                        stroke-dasharray="754" :stroke-dashoffset="754 - (754 * progress) / 100"
                        class="transition-all duration-1000 ease-linear" />
                </svg>
                <div class="absolute text-center">
                    <span class="text-5xl font-bold block">{{ Math.round(progress) }}%</span>
                    <span class="text-sm text-[#00C853] tracking-widest animate-pulse">CHARGING</span>
                </div>
            </div>

            <!-- Statistik Grid -->
            <div class="grid grid-cols-3 gap-8 text-center w-full max-w-2xl px-4">
                <div class="bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
                    <p class="text-gray-400 text-xs uppercase mb-1">Energi</p>
                    <p class="text-2xl font-bold">{{ kwh }} <span class="text-sm text-gray-500">kWh</span></p>
                </div>
                <div class="bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
                    <p class="text-gray-400 text-xs uppercase mb-1">Durasi</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ timeElapsed }}</p>
                </div>
                <div class="bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
                    <p class="text-gray-400 text-xs uppercase mb-1">Arus</p>
                    <p class="text-2xl font-bold">{{ ampere }} <span class="text-sm text-gray-500">A</span></p>
                </div>
            </div>

        </div>

        <!-- Footer Controls -->
        <div class="z-10 p-8 text-center">
            <button @click="stopEmergency" class="px-8 py-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-full shadow-lg shadow-red-900/50 transition transform active:scale-95 flex items-center justify-center gap-2 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" /></svg>
                STOP EMERGENCY
            </button>
            <p class="text-gray-600 text-xs mt-4">Jangan cabut konektor saat arus masih mengalir.</p>
        </div>

    </div>
</template>