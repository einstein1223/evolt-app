<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';

// --- STATE ---
const activeTab = ref('cars'); // 'cars' or 'education'
const searchQuery = ref('');
const selectedCar = ref(null);
const showDetailModal = ref(false);

// --- DATA MOBIL LISTRIK (DATABASE MINI) ---
const evDatabase = [
    {
        id: 1,
        brand: 'Hyundai',
        model: 'Ioniq 5',
        variant: 'Signature Long Range',
        image: 'https://imgcdn.oto.com/large/gallery/color/38/2656/hyundai-ioniq-5-color-857434.jpg', // Ganti dengan gambar lokal jika ada
        price: 'Rp 895 Juta',
        specs: {
            range: '481 km',
            battery: '72.6 kWh',
            charging_ac: '11 kW (6-7 Jam)',
            charging_dc: '350 kW (18 Menit 10-80%)',
            socket: 'Type 2 / CCS2',
            acceleration: '7.4s (0-100)'
        },
        description: 'Mobil listrik futuristik dengan fitur V2L yang memungkinkan mobil menjadi power bank berjalan.'
    },
    {
        id: 2,
        brand: 'Wuling',
        model: 'Air EV',
        variant: 'Long Range',
        image: 'https://wuling.id/upload/images/products/air-ev/color/air-ev-galaxy-blue.png',
        price: 'Rp 299 Juta',
        specs: {
            range: '300 km',
            battery: '26.7 kWh',
            charging_ac: '6.6 kW (4 Jam)',
            charging_dc: 'Tidak Support',
            socket: 'GB/T (Butuh Adaptor ke Type 2)',
            acceleration: '-'
        },
        description: 'City car mungil yang sangat cocok untuk mobilitas perkotaan yang padat dan hemat energi.'
    },
    {
        id: 3,
        brand: 'Wuling',
        model: 'Binguo EV',
        variant: 'Premium Range',
        image: 'https://wuling.id/upload/images/products/binguo-ev/color/binguo-ev-mousse-green.png',
        price: 'Rp 372 Juta',
        specs: {
            range: '410 km',
            battery: '37.9 kWh',
            charging_ac: '7 kW',
            charging_dc: 'Support DC Charging',
            socket: 'IEC Type 2 / CCS2',
            acceleration: '-'
        },
        description: 'Hatchback bergaya klasik retro dengan ruang kabin yang luas dan kenyamanan ekstra.'
    },
    {
        id: 4,
        brand: 'BYD',
        model: 'Seal',
        variant: 'Performance AWD',
        image: 'https://asset.kompas.com/crops/O_SzqX-Jg_g7lJk_K_o_K_K_K_K=/0x0:1000x667/750x500/data/photo/2024/02/15/65ce2dfba2b7d.jpg',
        price: 'Rp 719 Juta',
        specs: {
            range: '580 km',
            battery: '82.5 kWh',
            charging_ac: '11 kW',
            charging_dc: '150 kW',
            socket: 'Type 2 / CCS2',
            acceleration: '3.8s (0-100)'
        },
        description: 'Sedan sport listrik dengan performa buas yang mampu menyaingi supercar dalam akselerasi.'
    },
    {
        id: 5,
        brand: 'Tesla',
        model: 'Model 3',
        variant: 'Highland RWD',
        image: 'https://digitalassets.tesla.com/tesla-contents/image/upload/f_auto,q_auto/Model-3-Main-Hero-Desktop-LHD.jpg',
        price: 'Rp 1.5 Miliar (Est)',
        specs: {
            range: '513 km',
            battery: '60 kWh',
            charging_ac: '11 kW',
            charging_dc: '170 kW',
            socket: 'Type 2 / CCS2',
            acceleration: '6.1s (0-100)'
        },
        description: 'Standar emas mobil listrik dunia dengan teknologi autopilot dan efisiensi energi terbaik.'
    },
    {
        id: 6,
        brand: 'Chery',
        model: 'Omoda E5',
        variant: 'Standard',
        image: 'https://chery.co.id/uploads/2024/02/omoda-e5-banner.jpg',
        price: 'Rp 498 Juta',
        specs: {
            range: '430 km',
            battery: '61 kWh',
            charging_ac: '9.9 kW',
            charging_dc: '150 kW',
            socket: 'Type 2 / CCS2',
            acceleration: '7.2s (0-100)'
        },
        description: 'SUV Crossover futuristik dengan desain agresif dan teknologi ADAS yang lengkap.'
    }
];

// --- DATA EDUKASI ---
const educationTopics = [
    {
        title: 'AC vs DC Charging',
        icon: '⚡',
        content: 'AC (Alternating Current) biasanya lambat dan digunakan di rumah. DC (Direct Current) adalah fast charging yang memypass onboard charger mobil untuk pengisian super cepat di SPKLU.',
        color: 'bg-blue-50 text-blue-700'
    },
    {
        title: 'Tipe Konektor',
        icon: '🔌',
        content: 'Di Indonesia, standar umum adalah Type 2 (AC) dan CCS2 (DC). Namun, mobil seperti Wuling Air EV menggunakan GB/T, dan Nissan Leaf lawas menggunakan CHAdeMO.',
        color: 'bg-green-50 text-green-700'
    },
    {
        title: 'Apa itu kWh?',
        icon: '🔋',
        content: 'kWh (Kilowatt-hour) adalah satuan kapasitas "tangki bensin" baterai Anda. Semakin besar kWh, biasanya semakin jauh jarak tempuhnya.',
        color: 'bg-yellow-50 text-yellow-700'
    },
    {
        title: 'Tips Baterai Awet',
        icon: '❤️',
        content: 'Hindari sering membiarkan baterai di bawah 10% atau mengisi penuh 100% setiap hari. Idealnya jaga baterai di antara 20% - 80% untuk penggunaan harian.',
        color: 'bg-purple-50 text-purple-700'
    }
];

// --- LOGIC ---
const filteredCars = computed(() => {
    if (!searchQuery.value) return evDatabase;
    const lower = searchQuery.value.toLowerCase();
    return evDatabase.filter(car => 
        car.brand.toLowerCase().includes(lower) || 
        car.model.toLowerCase().includes(lower)
    );
});

const openDetail = (car) => {
    selectedCar.value = car;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
};
</script>

<template>
    <Head title="EV Pedia - Pengetahuan Mobil Listrik" />

    <div class="min-h-screen flex flex-col bg-gray-50 font-sans text-slate-800">
        <Navbar />

        <main class="flex-grow pt-24 pb-20">
            <div class="bg-slate-900 text-white py-16 px-6 relative overflow-hidden mb-10 rounded-b-[3rem]">
                <div class="absolute top-0 right-0 w-64 h-64 bg-[#CCFF00] rounded-full blur-[100px] opacity-20 transform translate-x-1/2 -translate-y-1/2"></div>
                <div class="max-w-7xl mx-auto text-center relative z-10">
                    <span class="inline-block py-1 px-3 rounded-full bg-[#CCFF00]/20 text-[#CCFF00] text-xs font-bold uppercase tracking-wider mb-4 border border-[#CCFF00]/30">Pusat Pengetahuan</span>
                    <h1 class="text-3xl md:text-5xl font-black mb-4">Dunia <span class="text-[#CCFF00]">Mobil Listrik</span></h1>
                    <p class="text-slate-400 max-w-2xl mx-auto text-lg">Pelajari spesifikasi, bandingkan performa, dan pahami teknologi di balik kendaraan masa depan.</p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-6">
                
                <div class="flex justify-center mb-10">
                    <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100 inline-flex">
                        <button @click="activeTab = 'cars'" 
                            :class="['px-6 py-2.5 rounded-xl text-sm font-bold transition-all', activeTab === 'cars' ? 'bg-[#CCFF00] text-slate-900 shadow-md' : 'text-gray-500 hover:text-gray-900']">
                            Katalog Mobil
                        </button>
                        <button @click="activeTab = 'education'" 
                            :class="['px-6 py-2.5 rounded-xl text-sm font-bold transition-all', activeTab === 'education' ? 'bg-[#CCFF00] text-slate-900 shadow-md' : 'text-gray-500 hover:text-gray-900']">
                            Kamus & Tips
                        </button>
                    </div>
                </div>

                <div v-if="activeTab === 'cars'" class="animate-fade-in">
                    <div class="max-w-md mx-auto mb-10 relative">
                        <input v-model="searchQuery" type="text" placeholder="Cari merk atau model (cth: Ioniq 5)" 
                            class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-200 focus:ring-2 focus:ring-[#CCFF00] focus:border-[#CCFF00] transition shadow-sm">
                        <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="car in filteredCars" :key="car.id" 
                            class="group bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 flex flex-col cursor-pointer transform hover:-translate-y-1"
                            @click="openDetail(car)">
                            
                            <div class="h-48 overflow-hidden relative bg-gray-100">
                                <img :src="car.image" :alt="car.model" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg text-xs font-bold text-slate-900 shadow-sm">
                                    {{ car.brand }}
                                </div>
                            </div>

                            <div class="p-6 flex-grow flex flex-col">
                                <h3 class="text-xl font-bold text-slate-900 mb-1">{{ car.model }}</h3>
                                <p class="text-sm text-gray-500 mb-4">{{ car.variant }}</p>

                                <div class="grid grid-cols-2 gap-3 mb-6">
                                    <div class="bg-gray-50 p-2 rounded-xl text-center">
                                        <div class="text-[10px] text-gray-400 uppercase font-bold">Jarak</div>
                                        <div class="font-bold text-slate-800">{{ car.specs.range }}</div>
                                    </div>
                                    <div class="bg-gray-50 p-2 rounded-xl text-center">
                                        <div class="text-[10px] text-gray-400 uppercase font-bold">Baterai</div>
                                        <div class="font-bold text-slate-800">{{ car.specs.battery }}</div>
                                    </div>
                                </div>

                                <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-100">
                                    <span class="text-[#00C853] font-bold">{{ car.price }}</span>
                                    <span class="text-xs text-gray-400 flex items-center gap-1 group-hover:text-slate-900 transition-colors">
                                        Detail <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredCars.length === 0" class="text-center py-20">
                        <div class="text-6xl mb-4">🚗</div>
                        <h3 class="text-xl font-bold text-gray-900">Mobil tidak ditemukan</h3>
                        <p class="text-gray-500">Coba kata kunci pencarian lain.</p>
                    </div>
                </div>

                <div v-if="activeTab === 'education'" class="animate-fade-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="(topic, index) in educationTopics" :key="index" 
                            :class="['p-8 rounded-3xl border border-transparent transition-all duration-300 hover:shadow-xl', topic.color]">
                            <div class="text-4xl mb-4">{{ topic.icon }}</div>
                            <h3 class="text-xl font-bold mb-3">{{ topic.title }}</h3>
                            <p class="opacity-80 leading-relaxed">{{ topic.content }}</p>
                        </div>
                    </div>

                    <div class="mt-12 bg-slate-900 rounded-[2.5rem] p-8 md:p-12 relative overflow-hidden text-center md:text-left">
                        <div class="absolute top-0 right-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                        <div class="relative z-10 flex flex-col md:flex-row items-center gap-8 justify-between">
                            <div>
                                <h3 class="text-2xl md:text-3xl font-bold text-white mb-2">Ingin tahu lokasi charger yang cocok?</h3>
                                <p class="text-slate-400">Gunakan fitur peta kami untuk menyaring charger berdasarkan tipe soket mobil Anda.</p>
                            </div>
                            <Link href="/dashboard" class="bg-[#CCFF00] text-slate-900 px-8 py-3.5 rounded-xl font-bold hover:bg-[#b3e600] transition transform hover:-translate-y-1 shadow-lg shadow-lime-900/20 whitespace-nowrap">
                                Cari Stasiun Sekarang
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <Footer />

        <Transition name="modal-fade">
            <div v-if="showDetailModal && selectedCar" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="closeDetail"></div>
                <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] overflow-y-auto transform transition-all">
                    
                    <div class="relative h-64 bg-gray-100">
                        <img :src="selectedCar.image" class="w-full h-full object-cover">
                        <button @click="closeDetail" class="absolute top-4 right-4 bg-white/50 backdrop-blur-md p-2 rounded-full hover:bg-white transition text-slate-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/70 to-transparent p-6 pt-20">
                            <span class="bg-[#CCFF00] text-slate-900 text-xs font-bold px-2 py-1 rounded mb-2 inline-block">{{ selectedCar.brand }}</span>
                            <h2 class="text-3xl font-bold text-white">{{ selectedCar.model }}</h2>
                            <p class="text-white/80">{{ selectedCar.variant }}</p>
                        </div>
                    </div>

                    <div class="p-8">
                        <p class="text-gray-600 mb-8 leading-relaxed">{{ selectedCar.description }}</p>

                        <h4 class="font-bold text-lg text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00C853]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Spesifikasi Teknis
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                            <div class="bg-gray-50 p-4 rounded-2xl flex justify-between items-center">
                                <span class="text-sm text-gray-500">Jarak Tempuh</span>
                                <span class="font-bold text-slate-800">{{ selectedCar.specs.range }}</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-2xl flex justify-between items-center">
                                <span class="text-sm text-gray-500">Kapasitas Baterai</span>
                                <span class="font-bold text-slate-800">{{ selectedCar.specs.battery }}</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-2xl flex justify-between items-center">
                                <span class="text-sm text-gray-500">Akselerasi (0-100)</span>
                                <span class="font-bold text-slate-800">{{ selectedCar.specs.acceleration }}</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-2xl flex justify-between items-center">
                                <span class="text-sm text-gray-500">Tipe Soket</span>
                                <span class="font-bold text-slate-800">{{ selectedCar.specs.socket }}</span>
                            </div>
                        </div>

                        <h4 class="font-bold text-lg text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Kemampuan Charging
                        </h4>
                        
                        <div class="space-y-3 mb-8">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 font-bold text-xs">AC</div>
                                <div>
                                    <div class="font-bold text-slate-800">Home Charging</div>
                                    <div class="text-sm text-gray-500">{{ selectedCar.specs.charging_ac }}</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 font-bold text-xs">DC</div>
                                <div>
                                    <div class="font-bold text-slate-800">Fast Charging (SPKLU)</div>
                                    <div class="text-sm text-gray-500">{{ selectedCar.specs.charging_dc }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#f0fdf4] border border-green-200 rounded-xl p-4 text-center">
                            <p class="text-green-800 text-sm font-medium mb-1">Estimasi Harga Baru</p>
                            <p class="text-2xl font-black text-green-700">{{ selectedCar.price }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.5s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-active .relative, .modal-fade-leave-active .relative { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-fade-enter-from .relative, .modal-fade-leave-to .relative { transform: scale(0.95) translateY(20px); }

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>