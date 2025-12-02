<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarAdmin.vue';
import Footer from '@/Components/Footer.vue';
import Modal from '@/Components/Modal.vue';

// --- 1. TERIMA DATA DARI CONTROLLER ---
const props = defineProps({
    stations: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
    // Data Laporan & User dari Database (Realtime)
    dbUserReports: { type: Array, default: () => [] },
    dbOperatorReports: { type: Array, default: () => [] },
});

// --- 2. MAP LOGIC (LEAFLET) ---
let map = null;
const loadLeaflet = () => {
    return new Promise((resolve, reject) => {
        if (window.L) return resolve(window.L);
        if (!document.querySelector('link[data-leaflet]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; link.setAttribute('data-leaflet', '1');
            document.head.appendChild(link);
        }
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'; script.async = true; script.setAttribute('data-leaflet', '1');
        script.onload = () => resolve(window.L);
        document.body.appendChild(script);
    });
};

const getMarkerColor = (chargersDetail) => {
    if (!chargersDetail || !Array.isArray(chargersDetail)) return '#00C853';
    const types = chargersDetail.map(c => c.type);
    if (types.includes('Ultra Fast')) return '#9333ea';
    if (types.includes('Fast')) return '#3b82f6';
    return '#00C853'; 
};

const createPinSvg = (color) => encodeURIComponent(`<svg width="32" height="48" viewBox="0 0 24 36" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C7 0 3.5 3.5 3.5 8.5 3.5 15.5 12 25.5 12 25.5s8.5-10 8.5-17C20.5 3.5 17 0 12 0z" fill="${color}"/><circle cx="12" cy="8.5" r="3.5" fill="white"/></svg>`);

onMounted(async () => {
    try {
        const L = await loadLeaflet();
        map = L.map('map').setView([1.126, 104.030], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        props.stations.forEach(s => {
            if (s.lat && s.lng) {
                const color = getMarkerColor(s.chargers_detail);
                const iconUrl = `data:image/svg+xml;charset=UTF-8,${createPinSvg(color)}`;
                const customIcon = L.icon({ iconUrl, iconSize: [28, 42], iconAnchor: [14, 42], popupAnchor: [0, -38] });
                const marker = L.marker([s.lat, s.lng], { icon: customIcon }).addTo(map);
                
                const chargerTypes = (s.chargers_detail || []).map(c => c.type).join(', ');
                marker.bindPopup(`
                    <div class="font-bold text-sm">${s.name}</div>
                    <div class="text-xs text-gray-600">${s.city}</div>
                    <div class="text-xs font-semibold mt-1 text-green-700">${chargerTypes}</div>
                `);
            }
        });
    } catch (err) { console.error(err); }
});

// --- 3. FORM STASIUN (CRUD) ---
const showModal = ref(false);
const isEditing = ref(false);
const searchQuery = ref('');

const newStation = ref({
  name: '', operationalHours: '24/7', latitude: '', longitude: '', city: '',
  chargers: [ { port: 1, type: 'Regular', power: '7.4', kwh: '22', connector: 'Type 2' } ],
});

const openAddStationModal = () => { isEditing.value = false; showModal.value = true; };
const closeModal = () => { showModal.value = false; };
const addChargerBlock = () => { newStation.value.chargers.push({ port: newStation.value.chargers.length + 1, type: 'Regular', power: '', kwh: '', connector: '' }); };
const removeChargerBlock = (index) => { newStation.value.chargers.splice(index, 1); };

const addStation = () => {
    router.post(route('admin.station.store'), newStation.value, {
        onSuccess: () => { 
            alert('Stasiun Berhasil Disimpan!'); 
            closeModal(); 
            // Reset form
            newStation.value = { name: '', operationalHours: '24/7', latitude: '', longitude: '', city: '', chargers: [{ port: 1, type: 'Regular', power: '', kwh: '', connector: '' }] }; 
        },
        onError: () => { alert('Gagal menyimpan. Cek data input.'); }
    });
};

const deleteStation = (id) => {
    if (confirm('Hapus stasiun ini permanen?')) { 
        router.delete(route('admin.station.destroy', id)); 
    }
};

const filteredStations = computed(() => {
  if (!searchQuery.value) return props.stations;
  return props.stations.filter(station => station.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});


// --- 4. LOGIC FILTER LAPORAN (MENGGUNAKAN DATA DARI DB) ---
const selectedReportStation = ref('');
const selectedReportMonth = ref('');
const selectedReportYear = ref('');

// Dropdown Options diambil dari data DB yang ada
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

// Statistik Atas (Hitung Realtime)
const totalSessionsAll = computed(() => {
    return props.dbOperatorReports.reduce((acc, curr) => acc + curr.totalSessions, 0);
});

// UI Helper
const getChargerClass = (type) => {
    if (type === 'Ultra Fast') return 'bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs';
    if (type === 'Fast') return 'bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs';
    return 'bg-green-100 text-green-800 px-2 py-1 rounded text-xs';
};
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
</script>

<template>
  <div class="min-h-screen bg-gray-100 font-sans text-gray-800">
    <Navbar />

    <main class="py-10 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto space-y-10">
        
        <!-- HEADER -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
            <span class="px-4 py-1 bg-gray-200 rounded-full text-sm font-semibold text-gray-600">Mode: Realtime Database</span>
        </div>

        <!-- 1. INFO & MAP -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
             <h2 class="text-lg font-bold text-gray-900 mb-4">Peta Stasiun Pengisian</h2>
             <div id="map" style="height: 400px; width: 100%;" class="rounded-xl bg-gray-100"></div>
          </div>
          <div class="lg:col-span-1 space-y-4">
             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500 font-medium">Total Stasiun Aktif</p>
                <p class="text-5xl font-bold text-gray-900 mt-2">{{ props.stations.length }}</p>
             </div>
             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500 font-medium">Total Sesi Pengisian</p>
                <!-- Angka ini dihitung otomatis dari data booking yang masuk -->
                <p class="text-5xl font-bold text-gray-900 mt-2">{{ totalSessionsAll }}</p>
             </div>
             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500 font-medium">Pengguna Terdaftar</p>
                <p class="text-5xl font-bold text-gray-900 mt-2">{{ props.dbUserReports.length }}</p>
             </div>
          </div>
        </div>

        <!-- 2. DAFTAR STASIUN (CRUD) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-4 border-b border-gray-100">
             <div>
                 <h3 class="text-lg font-bold text-gray-900">Daftar Stasiun</h3>
                 <p class="text-sm text-gray-500">Manajemen data stasiun charging</p>
             </div>
             <div class="flex gap-3 w-full md:w-auto">
                 <div class="relative flex-grow md:flex-grow-0">
                    <input v-model="searchQuery" type="text" placeholder="Cari stasiun..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-full focus:ring-[#00C853] focus:border-[#00C853]">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                 </div>
                 <button @click="openAddStationModal" class="bg-[#00C853] text-white px-5 py-2 rounded-lg text-sm font-bold hover:bg-[#00A142] shadow-md transition flex items-center gap-2 whitespace-nowrap">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                     Tambah Stasiun
                 </button>
             </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama Stasiun</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Lokasi</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Tipe Charger</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Jam Ops</th>
                  <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="station in filteredStations" :key="station.id" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 font-semibold text-gray-900">{{ station.name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ parseFloat(station.lat).toFixed(4) }}, {{ parseFloat(station.lng).toFixed(4) }}</td>
                  <td class="px-6 py-4 space-x-1">
                      <span v-for="(c, i) in (station.chargers_detail || [])" :key="i" :class="getChargerClass(c.type)">{{ c.type }}</span>
                      <span v-if="!station.chargers_detail?.length" class="text-xs text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ station.operationalHours || '24/7' }}</td>
                  <td class="px-6 py-4 text-center flex justify-center gap-3">
                      <button @click="deleteStation(station.id)" class="text-red-500 hover:text-red-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 3. LAPORAN OPERATOR (DATA REALTIME) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Laporan Operator Mingguan</h3>
                
                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <select v-model="selectedReportStation" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]">
                        <option value="">Semua Stasiun</option>
                        <option v-for="s in reportStationOptions" :key="s" :value="s">{{ s }}</option>
                    </select>
                    <select v-model="selectedReportMonth" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]">
                        <option value="">Semua Bulan</option>
                        <option v-for="m in reportMonthOptions" :key="m" :value="m">{{ m }}</option>
                    </select>
                    <select v-model="selectedReportYear" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#00C853] focus:border-[#00C853]">
                        <option value="">Semua Tahun</option>
                        <option v-for="y in reportYearOptions" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama SPKLU</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Domisili</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Periode</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Sesi (Total)</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Pendapatan Bersih</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="report in filteredReports" :key="report.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ report.stationName }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ report.domicile }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ report.week }} ({{ report.month }} {{ report.year }})</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ report.totalSessions }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ formatRupiah(report.revenue) }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs font-bold rounded-full', report.status === 'Terkirim' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                                    {{ report.status }}
                                </span>
                            </td>
                        </tr>
                         <tr v-if="filteredReports.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada transaksi booking yang terekam.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. PENGGUNA TERDAFTAR (REAL TIME) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Pengguna Terdaftar</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama Pengguna</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Total Charging</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Terakhir Aktif</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="user in props.dbUserReports" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ user.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ user.email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ user.totalCharges }}x</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ user.lastActive }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 text-xs font-bold rounded-full', user.status === 'Aktif' ? 'bg-blue-100 text-blue-700' : user.status === 'Baru' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600']">
                                    {{ user.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

      </div>
    </main>

    <!-- MODAL FORM TAMBAH STASIUN (SAMA SEPERTI SEBELUMNYA) -->
    <Modal :show="showModal" @close="closeModal">
      <div class="p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Stasiun Baru</h3>
        <form @submit.prevent="addStation" class="space-y-4">
             <div>
                <label class="block text-sm font-medium">Nama Stasiun</label>
                <input v-model="newStation.name" type="text" class="w-full border rounded p-2" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Kota</label>
                    <input v-model="newStation.city" type="text" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Jam Ops</label>
                    <input v-model="newStation.operationalHours" type="text" class="w-full border rounded p-2" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Latitude</label>
                    <input v-model="newStation.latitude" type="number" step="any" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Longitude</label>
                    <input v-model="newStation.longitude" type="number" step="any" class="w-full border rounded p-2" required>
                </div>
            </div>

            <div class="border-t pt-4 mt-2">
                <label class="block text-sm font-bold mb-2">Daftar Port Charger</label>
                <div v-for="(charger, idx) in newStation.chargers" :key="idx" class="bg-gray-50 p-3 rounded mb-2 border relative">
                    <div class="flex justify-between mb-2">
                        <span class="text-xs font-bold text-gray-500">Port {{ idx + 1 }}</span>
                        <button type="button" @click="removeChargerBlock(idx)" class="text-red-500 text-xs hover:underline" v-if="newStation.chargers.length > 1">Hapus</button>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="text-xs text-gray-500">Tipe</label>
                            <select v-model="charger.type" class="w-full border rounded p-1 text-sm">
                                <option value="Regular">Regular</option>
                                <option value="Fast">Fast</option>
                                <option value="Ultra Fast">Ultra Fast</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Power (kW)</label>
                            <input v-model="charger.power" type="number" class="w-full border rounded p-1 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Konektor</label>
                            <input v-model="charger.connector" type="text" placeholder="Type 2" class="w-full border rounded p-1 text-sm">
                        </div>
                    </div>
                </div>
                <button type="button" @click="addChargerBlock" class="text-[#00C853] text-sm font-bold hover:underline">+ Tambah Port Lain</button>
            </div>

            <div class="flex justify-end gap-3 mt-6 border-t pt-4">
                <button type="button" @click="closeModal" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-6 py-2 bg-[#00C853] text-white rounded font-bold hover:bg-[#00A142]">Simpan Data</button>
            </div>
        </form>
      </div>
    </Modal>

    <Footer />
  </div>
</template>