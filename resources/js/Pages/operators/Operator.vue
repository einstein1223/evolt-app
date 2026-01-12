<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';

// --- IMPORT CHART.JS ---
import { Bar, Line } from 'vue-chartjs';
import { 
  Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, 
  LinearScale, PointElement, LineElement, Filler 
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, Filler);

// --- 1. DATA DARI CONTROLLER ---
const props = defineProps({
    stats: { 
        type: Object, 
        default: () => ({ totalStations: 0, activeStations: 0, totalSessionsMonth: 0, totalUsers: 0 }) 
    },
    stations: { type: Array, default: () => [] },
    dbOperatorReports: { type: Array, default: () => [] }, 
    dbUserReports: { type: Array, default: () => [] }
});

// --- 2. LOGIKA PENYARINGAN KHUSUS OPERATOR ---
// Filter: Hapus "Garasi Pak Budi" dari list stasiun operator
const operatorStations = computed(() => {
    return props.stations.filter(station => station.name !== 'Garasi Pak Budi');
});

// Ambil nama station hanya dari list yang sudah difilter (Tanpa Pak Budi)
const myStationNames = computed(() => operatorStations.value.map(s => s.name));

// Filter Laporan: Hanya tampilkan laporan milik station yang ada di list operatorStations
const myReports = computed(() => {
    return props.dbOperatorReports.filter(report => myStationNames.value.includes(report.stationName));
});

// --- 3. DATA UNTUK CHART ---
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' } }
};

const revenueChartData = computed(() => {
    const labels = [...new Set(myReports.value.map(r => r.period))];
    const data = labels.map(period => {
        return myReports.value
            .filter(r => r.period === period)
            .reduce((sum, r) => sum + r.revenue, 0);
    });

    return {
        labels: labels.length ? labels : ['Belum ada data'],
        datasets: [{
            label: 'Total Pendapatan (Rp)',
            backgroundColor: '#00C853',
            borderRadius: 6,
            data: labels.length ? data : [0]
        }]
    };
});

const transactionChartData = computed(() => {
    const labels = [...new Set(myReports.value.map(r => r.period))];
    const data = labels.map(period => {
        return myReports.value
            .filter(r => r.period === period)
            .reduce((sum, r) => sum + r.totalSessions, 0);
    });

    return {
        labels: labels.length ? labels : ['Belum ada data'],
        datasets: [{
            label: 'Jumlah Transaksi (Sesi)',
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
            data: labels.length ? data : [0]
        }]
    };
});

// --- 4. VERIFIKASI PIN ---
const verified = ref(false);
const showVerificationModal = ref(true);
const verificationCode = ref(['', '', '', '', '']);
const displayCode = ref(['', '', '', '', '']);
const verificationError = ref('');

const handleKey = (index, event) => {
  const key = event.key;
  if (key.length === 1 && /^[0-9]$/.test(key)) {
    event.preventDefault();
    verificationCode.value[index] = key;
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    displayCode.value[index] = letters[Math.floor(Math.random() * letters.length)];
    if (index < 4) setTimeout(() => document.getElementById(`code-${index + 1}`).focus(), 10);
  }
};

const verifyCode = () => {
  if (verificationCode.value.join('') === '12345') {
    verified.value = true;
    showVerificationModal.value = false;
  } else {
    verificationError.value = 'PIN Salah! Silakan coba lagi.';
    verificationCode.value = ['', '', '', '', ''];
    document.getElementById('code-0').focus();
  }
};

// --- 5. REAL-TIME POLLING ---
let pollingInterval = null;
const startPolling = () => {
    pollingInterval = setInterval(() => {
        if (verified.value) { 
            router.reload({ 
                only: ['stats', 'dbOperatorReports', 'dbUserReports'], 
                preserveScroll: true 
            });
        }
    }, 5000);
};

onBeforeUnmount(() => {
    if (pollingInterval) clearInterval(pollingInterval);
});

// --- 6. MAP LOGIC ---
let map = null;
const loadLeaflet = () => {
    return new Promise((resolve) => {
        if (window.L) return resolve(window.L);
        const link = document.createElement('link'); link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; document.head.appendChild(link);
        const script = document.createElement('script'); script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'; script.async = true; 
        script.onload = () => resolve(window.L); document.body.appendChild(script);
    });
};
const createPinSvg = (color) => encodeURIComponent(`<svg width="32" height="48" viewBox="0 0 24 36" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C7 0 3.5 3.5 3.5 8.5 3.5 15.5 12 25.5 12 25.5s8.5-10 8.5-17C20.5 3.5 17 0 12 0z" fill="${color}"/><circle cx="12" cy="8.5" r="3.5" fill="white"/></svg>`);

const initMap = async () => {
    // Cek operatorStations, bukan props.stations
    if (!operatorStations.value.length && !map) return;
    try {
        const L = await loadLeaflet();
        const container = L.DomUtil.get('map');
        if(container != null) container._leaflet_id = null;

        // Ambil koordinat dari station PERTAMA milik operator (bukan Pak Budi)
        const initialLat = operatorStations.value.length > 0 ? operatorStations.value[0].lat : 1.126;
        const initialLng = operatorStations.value.length > 0 ? operatorStations.value[0].lng : 104.030;

        map = L.map('map').setView([initialLat, initialLng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        // Loop hanya station milik operator
        operatorStations.value.forEach(s => {
            if (s.lat && s.lng) {
                const color = s.status === 'Tersedia' || s.status === 'Aktif' ? '#00C853' : '#ef4444';
                const iconUrl = `data:image/svg+xml;charset=UTF-8,${createPinSvg(color)}`;
                const icon = L.icon({ iconUrl, iconSize: [28, 42], iconAnchor: [14, 42], popupAnchor: [0, -38] });
                L.marker([s.lat, s.lng], { icon }).addTo(map).bindPopup(`<b>${s.name}</b><br>${s.city}<br>Status: ${s.status}`);
            }
        });
    } catch (e) { console.error("Map Error:", e); }
};

watch(verified, (isVerified) => { 
    if (isVerified) {
        setTimeout(initMap, 500); 
        startPolling(); 
    }
});

// Filter UI
const selectedReportStation = ref('');
const selectedReportMonth = ref('');
const selectedReportYear = ref('');

const reportStationOptions = computed(() => [...new Set(myReports.value.map(r => r.stationName))]);
const reportMonthOptions = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const reportYearOptions = ['2023', '2024', '2025'];

const filteredReports = computed(() => {
    return myReports.value.filter(r => {
        const matchStation = selectedReportStation.value ? r.stationName === selectedReportStation.value : true;
        const matchMonth = selectedReportMonth.value ? r.month === selectedReportMonth.value : true;
        const matchYear = selectedReportYear.value ? r.year === selectedReportYear.value : true;
        return matchStation && matchMonth && matchYear;
    });
});

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const getChargerClass = (type) => {
    if (type === 'Ultra Fast') return 'bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-semibold';
    if (type === 'Fast') return 'bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold';
    return 'bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold';
};

onMounted(() => showVerificationModal.value = true);
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-100 font-sans text-gray-800">
    
    <div class="fixed inset-0 bg-[#B6F500] bg-opacity-95 flex items-center justify-center z-[9999]" v-if="showVerificationModal">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full border-4 border-white text-center">
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Login Operator</h3>
        <p class="text-sm text-gray-500 mb-8">Masukkan PIN Keamanan untuk mengakses dashboard monitoring.</p>
        <form @submit.prevent="verifyCode" class="space-y-6">
          <div class="flex justify-center gap-3">
            <div v-for="(letter, index) in displayCode" :key="index" class="relative w-12 h-14">
              <div class="w-full h-full text-2xl font-bold border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-800 flex items-center justify-center shadow-inner">
                {{ verificationCode[index] ? '•' : '' }}
              </div>
              <input :id="`code-${index}`" :value="displayCode[index]" type="text" maxlength="1" @keydown="handleKey(index, $event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required autocomplete="off" />
            </div>
          </div>
          <p v-if="verificationError" class="text-sm text-red-500 font-bold">{{ verificationError }}</p>
          <button type="submit" class="w-full py-3 bg-[#00C853] text-white rounded-xl text-base font-bold hover:bg-[#00A142] transition shadow-lg">Buka Dashboard</button>
        </form>
      </div>
    </div>

    <div v-if="verified" class="flex flex-col min-h-screen animate-fade-in">
        <nav class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center sticky top-0 z-40 shadow-sm">
            <div class="flex items-center gap-3">
                 <div class="text-2xl font-extrabold text-[#00C853]">E-<span class="text-gray-900">VOLT</span></div>
                 <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full border border-green-200">Operator Panel</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-gray-900">Petugas SPKLU</div>
                    <div class="text-xs text-green-600 flex items-center justify-end gap-1"><span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Online (Live)</div>
                </div>
                <div class="w-10 h-10 bg-gray-100 border border-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold shadow-sm">OP</div>
            </div>
        </nav>

        <main class="flex-grow py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col">
                        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-[#00C853] rounded-full"></span> Peta Stasiun Saya
                        </h2>
                        <div id="map" class="flex-grow rounded-xl bg-gray-100 border border-gray-200 min-h-[300px]"></div>
                    </div>
                    
                    <div class="lg:col-span-1 space-y-4">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <p class="text-sm text-gray-500 font-medium">Stasiun Dikelola</p>
                            <p class="text-5xl font-bold text-gray-900 mt-2">{{ operatorStations.length }}</p> 
                            <p class="text-xs text-gray-400 mt-1">Unit terdaftar</p>
                        </div>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <p class="text-sm text-gray-500 font-medium">Stasiun Aktif</p>
                            <p class="text-5xl font-bold text-green-600 mt-2">{{ props.stats.activeStations }}</p>
                        </div>
                         <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                            <div class="absolute right-0 top-0 opacity-10 text-6xl transform translate-x-2 -translate-y-2"><i class="fas fa-wallet"></i></div>
                            <p class="text-sm text-gray-400 font-medium relative z-10">Total Pendapatan (Bulan Ini)</p>
                            <p class="text-3xl font-bold text-[#B6F500] mt-2 relative z-10">{{ formatRupiah(props.stats.totalSessionsMonth * 25000) }}</p> 
                            <p class="text-xs text-gray-500 mt-1 relative z-10">*Estimasi berdasarkan sesi</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span> Grafik Keuangan
                        </h3>
                        <div class="h-64">
                            <Bar :data="revenueChartData" :options="chartOptions" />
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-purple-500 rounded-full"></span> Transaksi Berjalan (Sesi)
                        </h3>
                        <div class="h-64">
                            <Line :data="transactionChartData" :options="chartOptions" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Monitoring Unit Saya</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama Stasiun</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Lokasi</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Detail Charger</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Jam Ops</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="station in operatorStations" :key="station.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ station.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ station.city }}</td>
                                    <td class="px-6 py-4 space-x-1">
                                        <span v-for="(c, i) in (station.chargers_detail || [])" :key="i" :class="getChargerClass(c.type)">{{ c.type }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ station.operationalHours || '24/7' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="['px-3 py-1 text-xs font-bold rounded-full', (station.status === 'Tersedia' || station.status === 'Aktif') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">{{ station.status }}</span>
                                    </td>
                                </tr>
                                <tr v-if="!operatorStations.length">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada data stasiun (atau stasiun disembunyikan).</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Laporan Keuangan & Sesi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <select v-model="selectedReportStation" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]"><option value="">Semua Stasiun Saya</option><option v-for="s in reportStationOptions" :key="s" :value="s">{{ s }}</option></select>
                            <select v-model="selectedReportMonth" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]"><option value="">Semua Bulan</option><option v-for="m in reportMonthOptions" :key="m" :value="m">{{ m }}</option></select>
                            <select v-model="selectedReportYear" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]"><option value="">Semua Tahun</option><option v-for="y in reportYearOptions" :key="y" :value="y">{{ y }}</option></select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Unit SPKLU</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Domisili</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Periode</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Sesi</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Pendapatan</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="report in filteredReports" :key="report.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><span class="font-bold text-gray-500 mr-1">[{{ report.id }}]</span> {{ report.stationName }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ report.domicile }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ report.period }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ report.totalSessions }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-[#00C853]">{{ formatRupiah(report.revenue) }}</td>
                                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">{{ report.status }}</span></td>
                                </tr>
                                <tr v-if="!filteredReports.length"><td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada laporan untuk stasiun Anda.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
        <Footer />
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>