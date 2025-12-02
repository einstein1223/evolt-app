<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3'; // Import router untuk reload
import Footer from '@/Components/Footer.vue';

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

// --- 2. VERIFIKASI PIN (12345) ---
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

// --- 3. REAL-TIME POLLING (AUTO REFRESH DATA) ---
// Ini akan membuat dashboard operator "hidup" dan update sendiri
let pollingInterval = null;

const startPolling = () => {
    // Cek data baru setiap 5 detik (5000ms)
    pollingInterval = setInterval(() => {
        if (verified.value) { // Hanya refresh jika sudah login
            router.reload({ 
                only: ['stats', 'dbOperatorReports', 'dbUserReports'], // Hanya ambil data yang berubah
                preserveScroll: true // Jangan scroll ke atas saat refresh
            });
        }
    }, 5000);
};

onBeforeUnmount(() => {
    // Matikan timer saat pindah halaman agar tidak membebani browser
    if (pollingInterval) clearInterval(pollingInterval);
});


// --- 4. MAP LOGIC ---
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
    if (!props.stations.length && !map) return;
    try {
        const L = await loadLeaflet();
        const container = L.DomUtil.get('map');
        if(container != null) container._leaflet_id = null;

        map = L.map('map').setView([1.126, 104.030], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        props.stations.forEach(s => {
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
        startPolling(); // Mulai Auto Refresh saat berhasil login
    }
});

// --- 5. FILTER LOGIC ---
const selectedReportStation = ref('');
const selectedReportMonth = ref('');
const selectedReportYear = ref('');

const reportStationOptions = computed(() => [...new Set(props.dbOperatorReports.map(r => r.stationName))]);
const reportMonthOptions = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const reportYearOptions = ['2023', '2024', '2025'];

const filteredReports = computed(() => {
    return props.dbOperatorReports.filter(r => {
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
    
    <!-- MODAL VERIFIKASI -->
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

    <!-- KONTEN DASHBOARD -->
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
                
                <!-- 1. PETA & STATISTIK -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"><span class="w-1.5 h-6 bg-[#00C853] rounded-full"></span> Peta Sebaran Stasiun</h2>
                        <div id="map" style="height: 350px; width: 100%;" class="rounded-xl bg-gray-100 border border-gray-200"></div>
                    </div>
                    <div class="lg:col-span-1 space-y-4">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <p class="text-sm text-gray-500 font-medium">Total Stasiun</p>
                            <p class="text-5xl font-bold text-gray-900 mt-2">{{ props.stats.totalStations }}</p>
                        </div>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <p class="text-sm text-gray-500 font-medium">Stasiun Aktif</p>
                            <p class="text-5xl font-bold text-green-600 mt-2">{{ props.stats.activeStations }}</p>
                        </div>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                            <div class="absolute -right-6 -top-6 w-20 h-20 bg-blue-50 rounded-full z-0"></div>
                            <p class="text-sm text-gray-500 font-medium z-10 relative">Transaksi Bulan Ini</p>
                            <div class="flex items-end gap-2 mt-2 z-10 relative">
                                <p class="text-5xl font-bold text-blue-600">{{ props.stats.totalSessionsMonth }}</p>
                                <span class="text-xs text-gray-400 mb-2 animate-pulse">● Live update</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. STATUS STASIUN -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50"><h3 class="text-lg font-bold text-gray-900">Status Operasional Stasiun</h3></div>
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
                                <tr v-for="station in props.stations" :key="station.id" class="hover:bg-gray-50 transition">
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
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. LAPORAN OPERATOR MINGGUAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Laporan Operator Mingguan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <select v-model="selectedReportStation" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]"><option value="">Semua Stasiun</option><option v-for="s in reportStationOptions" :key="s" :value="s">{{ s }}</option></select>
                            <select v-model="selectedReportMonth" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]"><option value="">Semua Bulan</option><option v-for="m in reportMonthOptions" :key="m" :value="m">{{ m }}</option></select>
                            <select v-model="selectedReportYear" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]"><option value="">Semua Tahun</option><option v-for="y in reportYearOptions" :key="y" :value="y">{{ y }}</option></select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">ID/Nama SPKLU</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Pemilik</th>
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
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ report.owner }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ report.domicile }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ report.period }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ report.totalSessions }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-[#00C853]">{{ formatRupiah(report.revenue) }}</td>
                                    <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">{{ report.status }}</span></td>
                                </tr>
                                <tr v-if="!filteredReports.length"><td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada laporan.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. PENGGUNA TERDAFTAR -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50"><h3 class="text-lg font-bold text-gray-900">Pengguna Terdaftar</h3></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Total Charging</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Terakhir Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="user in props.dbUserReports" :key="user.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ user.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ user.email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><span class="px-2 py-1 bg-gray-100 rounded text-xs font-bold text-gray-700">{{ user.totalCharges }}x</span></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ user.lastActive }}</td>
                                </tr>
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