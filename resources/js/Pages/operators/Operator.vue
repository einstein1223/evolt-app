<script setup>
import { ref, onMounted, onBeforeUnmount, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';

// --- IMPORT CHART.JS ---
import { Bar } from 'vue-chartjs';
import { 
  Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, 
  LinearScale 
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    stats: { 
        type: Object, 
        default: () => ({ totalStations: 0, activeStations: 0, totalSessionsMonth: 0, totalUsers: 0 }) 
    },
    stations: { type: Array, default: () => [] },
    dbOperatorReports: { type: Array, default: () => [] },
    dbQueues: { type: Array, default: () => [] } 
});

// --- 1. STATE MANAGEMENT ---
const showProfileMenu = ref(false);

// --- 2. FILTER HOST TETANGGA ---
const operatorStations = computed(() => {
    const excluded = ['Garasi Pak Budi', 'Garasi Pak Hasan'];
    return (props.stations || []).filter(station => {
        const name = station.name.trim();
        return !excluded.includes(name);
    });
});

const myStationNames = computed(() => operatorStations.value.map(s => s.name.toLowerCase().trim()));

// --- 3. REKAPITULASI KEUANGAN ---
const myReports = computed(() => {
    return (props.dbOperatorReports || [])
        .filter(report => myStationNames.value.includes(report.stationName.toLowerCase().trim()))
        .reverse();
});

// --- 4. ANTREAN REALTIME (FIX FILTER & DATA) ---
const myQueues = computed(() => {
    if (!props.dbQueues || props.dbQueues.length === 0) return [];
    
    // Menggunakan filter yang mengabaikan perbedaan huruf besar/kecil
    return props.dbQueues.filter(q => 
        myStationNames.value.includes(q.stationName.toLowerCase().trim())
    );
});

// --- 5. MAP LOGIC ---
let map = null;
const loadLeaflet = () => {
    return new Promise((resolve) => {
        if (window.L) return resolve(window.L);
        const link = document.createElement('link'); link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; document.head.appendChild(link);
        const script = document.createElement('script'); script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'; script.async = true; 
        script.onload = () => resolve(window.L); document.body.appendChild(script);
    });
};

const initMap = async () => {
    if (!operatorStations.value.length) return;
    try {
        const L = await loadLeaflet();
        if (map) map.remove();
        const first = operatorStations.value[0];
        map = L.map('map', { zoomControl: false }).setView([first.lat || 1.126, first.lng || 104.030], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        operatorStations.value.forEach(s => {
            const color = (s.status === 'Tersedia' || s.status === 'Aktif') ? '#16a34a' : '#EF4444';
            L.circleMarker([s.lat, s.lng], { radius: 7, fillColor: color, color: '#fff', weight: 2, fillOpacity: 1 }).addTo(map).bindPopup(`<b>${s.name}</b>`);
        });
    } catch (e) { console.error("Map Error:", e); }
};

// --- 6. DATA CHART ---
const revenueChartData = computed(() => {
    const last7 = myReports.value.slice(0, 7).reverse();
    return {
        labels: last7.map(r => r.period),
        datasets: [{
            label: 'Pendapatan',
            backgroundColor: '#16a34a',
            borderRadius: 4,
            data: last7.map(r => r.revenue)
        }]
    };
});

// PERBAIKAN FATAL ERROR: Intl.NumberFormat
const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0 
    }).format(val);
};

// --- 7. REALTIME POLLING ---
let refreshInterval = null;
const startPolling = () => {
    refreshInterval = setInterval(() => {
        router.reload({ 
            only: ['dbQueues', 'stats', 'dbOperatorReports'], 
            preserveScroll: true,
            preserveState: true 
        });
    }, 10000); 
};

onMounted(() => {
    console.log("DEBUG - Antrean dari Server:", props.dbQueues);
    nextTick(() => initMap());
    startPolling();
});

onBeforeUnmount(() => {
    if (map) map.remove();
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 pb-12">
        <nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-1">
                    <span class="text-2xl font-black text-[#16a34a]">E</span>
                    <span class="text-2xl font-black text-slate-900">- VOLT</span>
                    <span class="ml-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-l pl-3 border-slate-200">Operator Dashboard</span>
                </div>

                <div class="flex items-center gap-4 relative">
                    <div class="text-right hidden sm:block">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Live Monitoring</p>
                        <p class="text-sm font-bold text-slate-900">Petugas SPKLU</p>
                    </div>
                    
                    <div class="relative">
                        <button 
                            @click="showProfileMenu = !showProfileMenu"
                            class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-all border border-slate-200 shadow-sm"
                        >
                            <i class="fas fa-user-shield"></i>
                        </button>

                        <transition name="fade">
                            <div v-if="showProfileMenu" 
                                 class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-[100]"
                            >
                                <div class="px-4 py-3 border-b border-slate-50">
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Akses Operator</p>
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ $page.props.auth.user.name }}</p>
                                </div>
                                <button @click="router.visit('/profile')" class="w-full text-left px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                                    <i class="fas fa-id-card text-slate-400"></i> Detail Profil
                                </button>
                                <button @click="router.post(route('logout'))" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center gap-3">
                                    <i class="fas fa-power-off"></i> Keluar Sistem
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto p-6 space-y-10" @click="showProfileMenu = false">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-center">
                    <h3 class="text-[10px] font-black mb-4 uppercase text-slate-400 tracking-widest text-center">Revenue Analytics</h3>
                    <div class="h-40">
                        <Bar :data="revenueChartData" :options="{ responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }" />
                    </div>
                </div>
                <div class="lg:col-span-2 bg-white p-2 rounded-[2.5rem] border border-slate-200 shadow-sm relative overflow-hidden h-[300px]">
                    <div id="map" class="w-full h-full rounded-[2rem]"></div>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-900">Antrean Kendaraan Realtime</h3>
                        <p class="text-xs text-slate-500 font-medium">Monitoring plat nomor kendaraan yang sedang menunggu/mengisi.</p>
                    </div>
                    <span class="bg-blue-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase animate-pulse">Live Update</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-4 border-r border-slate-100">Nama Stasiun</th>
                                <th class="px-8 py-4 border-r border-slate-100">Plat Nomor Kendaraan</th>
                                <th class="px-8 py-4 border-r border-slate-100">Estimasi / Jam Masuk</th>
                                <th class="px-8 py-4 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="q in myQueues" :key="q.id" class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-8 py-5 border-r border-slate-100 font-bold text-slate-900">{{ q.stationName }}</td>
                                <td class="px-8 py-5 border-r border-slate-100">
                                    <span class="bg-slate-900 text-[#B6F500] px-4 py-2 rounded-xl font-mono font-black tracking-widest text-base shadow-lg border-2 border-slate-700">
                                        {{ q.plateNumber || q.plate_number }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 border-r border-slate-100 text-sm font-medium text-slate-600 text-center">
                                    <i class="far fa-clock mr-2 text-slate-300"></i> {{ q.entryTime || 'Ready' }} WIB
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span :class="q.status === 'Pengisian' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" 
                                          class="text-[10px] font-black px-4 py-1.5 rounded-full uppercase">
                                        {{ q.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="myQueues.length === 0">
                                <td colspan="4" class="px-8 py-12 text-center text-slate-400 italic">Tidak ada aktivitas antrean saat ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xl font-black text-slate-900">Daftar Status Unit SPKLU</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-4 border-r border-slate-100">Nama Unit</th>
                                <th class="px-8 py-4 border-r border-slate-100">Informasi Lokasi</th>
                                <th class="px-8 py-4 text-right">Kondisi Sistem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="s in operatorStations" :key="s.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5 border-r border-slate-100 font-bold text-slate-900">{{ s.name }}</td>
                                <td class="px-8 py-5 border-r border-slate-100 text-sm text-slate-500">{{ s.locationType || 'Public Area' }}</td>
                                <td class="px-8 py-5 text-right">
                                    <span :class="s.status === 'Tersedia' || s.status === 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" 
                                          class="text-[10px] font-black px-4 py-1.5 rounded-full uppercase">
                                        {{ s.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50 font-black text-xl text-slate-900">Rekapitulasi Keuangan</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-4 border-r border-slate-100">Stasiun</th>
                                <th class="px-8 py-4 border-r border-slate-100 text-center">Periode</th>
                                <th class="px-8 py-4 border-r border-slate-100 text-center">Sesi</th>
                                <th class="px-8 py-4 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="report in myReports" :key="report.id">
                                <td class="px-8 py-5 border-r border-slate-100">
                                    <p class="font-bold text-slate-900">{{ report.stationName }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase font-black">{{ report.domicile }}</p>
                                </td>
                                <td class="px-8 py-5 border-r border-slate-100 text-center text-xs font-bold uppercase text-slate-500">{{ report.period }}</td>
                                <td class="px-8 py-5 border-r border-slate-100 text-center font-black text-slate-900">{{ report.totalSessions }}</td>
                                <td class="px-8 py-5 text-right font-black text-[#16a34a] text-lg">{{ formatRupiah(report.revenue) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <Footer />
    </div>
</template>

<style scoped>
#map { filter: none; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>