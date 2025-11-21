<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import Navbar from '@/Components/NavbarUser.vue'; // Pastikan path sesuai
import Footer from '@/Components/Footer.vue';

// -----------------------------------------------------------------------------------
// 1. STATE & MODALS
// -----------------------------------------------------------------------------------
const showSearchModal = ref(false);
const showConfirmationModal = ref(false);
const showQrisPaymentModal = ref(false);
const showReceiptModal = ref(false);
const selectedStation = ref(null);
const hasStartedBooking = ref(false);

// State Dropdown (Satu variabel untuk semua)
const activeDropdown = ref(null);

const selectedPort = ref('');
const selectedDuration = ref('60');

// -----------------------------------------------------------------------------------
// 2. DATA & FILTER LOGIC
// -----------------------------------------------------------------------------------
const formState = ref({
    brand: '',
    type: '', 
    date: new Date().toISOString().split('T')[0],
    domicile: '', 
    station: '', 
    time: '12:00',
});

const brandOptions = ['Nissan','Toyota','Wuling','Hyundai','Tesla','BYD','Kia'];
const typeOptions = ['SUV', 'City Car', 'Hatchback', 'Sedan', 'MPV'];

// DATA KOORDINAT (PENTING UNTUK JARAK)
const domicileCoordinates = {
  'Batam Center': { lat: 1.1301, lng: 104.0529 },
  'Nagoya':       { lat: 1.1365, lng: 104.0138 },
  'Harbour Bay':  { lat: 1.1540, lng: 103.9935 },
  'Sekupang':     { lat: 1.1147, lng: 103.9325 },
  'Batu Aji':     { lat: 1.0354, lng: 103.9637 },
  'Lubuk Baja':   { lat: 1.1335, lng: 104.0114 },
  'Tiban':        { lat: 1.1095, lng: 103.9893 },
  'Kabil':        { lat: 1.0812, lng: 104.1072 },
  'Batu Ampar':   { lat: 1.1685, lng: 104.0001 },
  'Galang':       { lat: 0.7576, lng: 104.2327 },
  'Bulang':       { lat: 0.9794, lng: 103.9364 }
};
const domicileOptions = Object.keys(domicileCoordinates);

// Data Stations
const stations = ref([
    { id: 1, name: 'SPKLU Nagoya Hill', location: 'Nagoya Hill, Batam', distance: '2.5km', chargers: ['Fast'], power: '80 kW', status: 'Tersedia', bookingTime: '2025-10-22 12:00', duration: '60 menit', price: 50000, serviceFee: 10000, isBookable: true, bookingNumber: 'BK1001', lat: 1.1324, lng: 104.0383 },
    { id: 2, name: 'SPKLU Mega Mall Batam', location: 'Mega Mall, Batam Center', distance: '1.2km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 12:30', duration: '60 menit', price: 40000, serviceFee: 8000, isBookable: true, bookingNumber: 'BK1002', lat: 1.1225, lng: 104.0417 },
    { id: 3, name: 'SPKLU Harbour Bay', location: 'Harbour Bay, Batam', distance: '3.8km', chargers: ['Fast', 'Ultra Fast'], power: '100 kW', status: 'Penuh', bookingTime: '2025-10-22 14:00', duration: '30 menit', price: 120000, serviceFee: 15000, isBookable: false, bookingNumber: 'BK1003', lat: 1.1145, lng: 104.0522 },
    { id: 4, name: 'SPKLU Batam Center', location: 'Batam Center', distance: '0.5km', chargers: ['Regular'], power: '50 kW', status: 'Tersedia', bookingTime: '2025-10-22 10:00', duration: '90 menit', price: 60000, serviceFee: 12000, isBookable: true, bookingNumber: 'BK1004', lat: 1.1278, lng: 104.0302 },
    { id: 5, name: 'SPKLU Batam City Square', location: 'Batam City Square', distance: '1.8km', chargers: ['Regular'], power: '11 kW', status: 'Penuh', bookingTime: '2025-10-22 10:00', duration: '90 menit', price: 30000, serviceFee: 8000, isBookable: false, bookingNumber: 'BK1005', lat: 1.1210, lng: 104.0335 },
    { id: 6, name: 'SPKLU Kepri Mall', location: 'Kepri Mall', distance: '2.1km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 11:00', duration: '60 menit', price: 45000, serviceFee: 9000, isBookable: true, bookingNumber: 'BK1006', lat: 1.1122, lng: 104.0450 },
    { id: 7, name: 'SPKLU Batam View', location: 'Batam View', distance: '4.0km', chargers: ['Ultra Fast'], power: '150 kW', status: 'Tersedia', bookingTime: '2025-10-22 13:00', duration: '45 menit', price: 90000, serviceFee: 10000, isBookable: true, bookingNumber: 'BK1007', lat: 1.1390, lng: 104.0480 },
    { id: 9, name: 'SPKLU Tiban', location: 'Tiban', distance: '5.5km', chargers: ['Fast'], power: '50 kW', status: 'Penuh', bookingTime: '2025-10-22 15:00', duration: '30 menit', price: 80000, serviceFee: 10000, isBookable: false, bookingNumber: 'BK1009', lat: 1.0956, lng: 104.0103 },
    { id: 10, name: 'SPKLU Sekupang', location: 'Sekupang', distance: '7.2km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 08:00', duration: '60 menit', price: 35000, serviceFee: 7000, isBookable: true, bookingNumber: 'BK1010', lat: 1.0552, lng: 103.9824 },
    { id: 11, name: 'SPKLU Batu Ampar', location: 'Batu Ampar', distance: '6.0km', chargers: ['Regular'], power: '11 kW', status: 'Tersedia', bookingTime: '2025-10-22 10:30', duration: '90 menit', price: 30000, serviceFee: 7000, isBookable: true, bookingNumber: 'BK1011', lat: 1.0873, lng: 104.0128 },
    { id: 12, name: 'SPKLU Nagoya City', location: 'Nagoya City', distance: '2.8km', chargers: ['Fast'], power: '80 kW', status: 'Tersedia', bookingTime: '2025-10-22 12:45', duration: '60 menit', price: 100000, serviceFee: 12000, isBookable: true, bookingNumber: 'BK1012', lat: 1.1290, lng: 104.0405 },
    { id: 13, name: 'SPKLU Batam Harbor', location: 'Batam Harbor', distance: '4.5km', chargers: ['Fast'], power: '100 kW', status: 'Penuh', bookingTime: '2025-10-22 14:30', duration: '30 menit', price: 150000, serviceFee: 15000, isBookable: false, bookingNumber: 'BK1013', lat: 1.1067, lng: 104.0622 },
    { id: 14, name: 'SPKLU Gajah Mada', location: 'Gajah Mada, Batam', distance: '1.5km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 09:30', duration: '60 menit', price: 40000, serviceFee: 8000, isBookable: true, bookingNumber: 'BK1014', lat: 1.1255, lng: 104.0280 },
    { id: 15, name: 'SPKLU Waterfront City', location: 'Waterfront City', distance: '3.2km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 11:15', duration: '60 menit', price: 45000, serviceFee: 9000, isBookable: true, bookingNumber: 'BK1015', lat: 1.1312, lng: 104.0588 },
]);
const stationOptions = computed(() => stations.value.map(s => s.name));

// --- RUMUS HITUNG JARAK (Haversine) ---
const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371; 
    const dLat = (lat2 - lat1) * (Math.PI / 180);
    const dLon = (lon2 - lon1) * (Math.PI / 180);
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
};

// --- COMPUTED UTAMA (SORTING OTOMATIS) ---
const recommendedStations = computed(() => {
    let result = stations.value.map(s => ({...s})); // Clone array

    // 1. Filter Status
    result = result.filter(s => s.status === 'Tersedia');

    // 2. Filter Nama Stasiun (jika user pilih spesifik)
    if (formState.value.station && formState.value.station !== 'Pilih Stasiun') {
        result = result.filter(s => s.name === formState.value.station);
    }

    // 3. Sorting Jarak (Inti Fitur)
    if (formState.value.domicile && domicileCoordinates[formState.value.domicile]) {
        const center = domicileCoordinates[formState.value.domicile];
        result = result.map(station => {
            const realDist = calculateDistance(center.lat, center.lng, station.lat, station.lng);
            return {
                ...station,
                realDistance: realDist,
                distance: realDist.toFixed(1) + ' km' // Update text jarak di kartu
            };
        });
        // Urutkan dari terdekat
        result.sort((a, b) => a.realDistance - b.realDistance);
    } else {
        // Default sort (sesuai data awal)
        result.sort((a, b) => parseFloat(a.distance) - parseFloat(b.distance));
    }
    return result;
});


// -----------------------------------------------------------------------------------
// 3. BOOKING & PAYMENT LOGIC
// -----------------------------------------------------------------------------------
const durationOptions = [
    { label: '30 menit', value: '30', multiplier: 0.5 },
    { label: '1 jam', value: '60', multiplier: 1 },
    { label: '1.5 jam', value: '90', multiplier: 1.5 },
    { label: '2 jam', value: '120', multiplier: 2 }
];

const availablePorts = computed(() => {
    if (!selectedStation.value) return [];
    return selectedStation.value.chargers.map((charger, index) => ({
        id: `port-${index + 1}`,
        label: `Port ${index + 1} - ${charger}`,
        value: `port-${index + 1}`,
        type: charger,
        power: selectedStation.value.power,
        status: selectedStation.value.status
    }));
});
const portOptions = computed(() => availablePorts.value);

const calculateTotal = (price, fee) => price + fee;
const formatRupiah = (amount) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);

const calculateTotalFormatted = computed(() => {
    if (selectedStation.value) {
        const durationMultiplier = durationOptions.find(d => d.value === selectedDuration.value)?.multiplier || 1;
        const adjustedPrice = selectedStation.value.price * durationMultiplier;
        const adjustedFee = selectedStation.value.serviceFee * durationMultiplier;
        return formatRupiah(calculateTotal(adjustedPrice, adjustedFee));
    }
    return '';
});

const formatBookingDate = (dateTime) => {
    if(!dateTime) return '';
    const [date, time] = dateTime.split(' ');
    return `${date} ${time}`;
};


// -----------------------------------------------------------------------------------
// 4. ACTION HANDLERS (DROPDOWN & MODALS)
// -----------------------------------------------------------------------------------
const toggleDropdown = (name) => {
    activeDropdown.value = activeDropdown.value === name ? null : name;
};

const selectOption = (field, value) => {
    if (field === 'port') {
        selectedPort.value = value;
    } else if (field === 'duration') {
        selectedDuration.value = value;
    } else {
        formState.value[field] = value;
    }
    activeDropdown.value = null;
};

const openModal = () => showSearchModal.value = true;
const closeModal = () => showSearchModal.value = false;
const submitSearch = () => { console.log('Searching...', formState.value); closeModal(); };

const reserveStation = (stationId) => {
    const station = stations.value.find(s => s.id === stationId);
    if (station && station.isBookable) {
        selectedStation.value = station;
        showConfirmationModal.value = true;
        hasStartedBooking.value = true;
    }
};

const cancelProcess = () => {
    showConfirmationModal.value = false;
    showQrisPaymentModal.value = false;
    selectedStation.value = null;
    selectedPort.value = '';
    selectedDuration.value = '60';
    hasStartedBooking.value = false;
};

const proceedToPayment = () => {
    showConfirmationModal.value = false;
    showQrisPaymentModal.value = true;
};

const confirmPayment = () => {
    showQrisPaymentModal.value = false;
    showReceiptModal.value = true;
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
    selectedStation.value = null;
};

const openPrintStruk = () => {
    showReceiptModal.value = false;
    window.location.href = `/print-struk?station=${encodeURIComponent(JSON.stringify(selectedStation.value))}&total=${encodeURIComponent(calculateTotalFormatted.value)}`;
};

// -----------------------------------------------------------------------------------
// 5. LEAFLET MAP
// -----------------------------------------------------------------------------------
let map = null;
const loadLeaflet = () => {
    return new Promise((resolve, reject) => {
        if (window.L) return resolve(window.L);
        if (!document.querySelector('link[data-leaflet]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            link.setAttribute('data-leaflet', '1');
            document.head.appendChild(link);
        }
        if (!document.querySelector('script[data-leaflet]')) {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.async = true;
            script.setAttribute('data-leaflet', '1');
            script.onload = () => resolve(window.L);
            script.onerror = reject;
            document.body.appendChild(script);
        } else {
            const check = () => window.L ? resolve(window.L) : setTimeout(check, 50);
            check();
        }
    });
};

const createPinSvg = (color) => encodeURIComponent(`<svg width="32" height="48" viewBox="0 0 24 36" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C7 0 3.5 3.5 3.5 8.5 3.5 15.5 12 25.5 12 25.5s8.5-10 8.5-17C20.5 3.5 17 0 12 0z" fill="${color}"/><circle cx="12" cy="8.5" r="3.5" fill="white"/></svg>`);
const getMarkerColor = (chargers) => chargers.includes('Ultra Fast') ? '#9333ea' : chargers.includes('Fast') ? '#3b82f6' : '#00C853';

// -----------------------------------------------------------------------------------
// 6. LIFECYCLE HOOKS (TERMASUK BACA URL DARI DASHBOARD)
// -----------------------------------------------------------------------------------
onMounted(async () => {
    // A. Close Dropdown on Click Outside
    document.body.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) activeDropdown.value = null;
    });

    // B. BACA DATA DARI URL (INI KUNCINYA)
    // Kode ini akan mengambil 'domicile' yang dikirim dari Dashboard
    const params = new URLSearchParams(window.location.search);
    if (params.has('domicile')) formState.value.domicile = params.get('domicile');
    if (params.has('brand')) formState.value.brand = params.get('brand');
    if (params.has('type')) formState.value.type = params.get('type');
    if (params.has('station')) formState.value.station = params.get('station');

    // C. LOAD MAP
    try {
        const L = await loadLeaflet();
        map = L.map('map').setView([1.126, 104.030], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Render Markers
        stations.value.filter(s => s.status === 'Tersedia' && s.lat && s.lng).forEach(s => {
            const color = getMarkerColor(s.chargers);
            const iconUrl = `data:image/svg+xml;charset=UTF-8,${createPinSvg(color)}`;
            const customIcon = L.icon({ iconUrl, iconSize: [28, 42], iconAnchor: [14, 42], popupAnchor: [0, -38] });
            const marker = L.marker([s.lat, s.lng], { icon: customIcon }).addTo(map);
            marker.bindPopup(`<div class="font-medium">${s.name}</div><div class="text-sm text-gray-600">${s.location}</div>`);
        });
    } catch (err) {
        console.error('Failed to load Leaflet:', err);
    }
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <Navbar />

        <main class="flex-grow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

                <div class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6">
                    <div class="relative w-full md:w-64">
                        <input 
                            type="text" 
                            placeholder="Cari Stasiun Lain..." 
                            class="w-full p-3 pl-4 border border-gray-300 rounded-xl focus:ring-[#00C853] focus:border-[#00C853] cursor-pointer"
                            @click="openModal"
                            readonly
                            :value="formState.station || 'Filter Pencarian...'"
                        >
                        <i class="fas fa-search absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div class="relative w-full mb-8 rounded-xl shadow-lg overflow-hidden border border-gray-200" style="height: 450px; background-color: #e9f5ff;">
                    <div id="map" class="w-full h-full z-0"></div>
                </div>

                <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                    {{ formState.domicile ? `Rekomendasi di Sekitar ${formState.domicile}` : 'Semua Stasiun Charging' }}
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="station in recommendedStations" :key="station.id"
                         class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 transition duration-300 hover:shadow-xl relative flex flex-col"
                         :class="{ 'border-[#00C853] ring-1 ring-[#00C853]': selectedStation && selectedStation.id === station.id }">

                        <h2 class="text-xl font-semibold text-gray-900 mb-1 truncate">{{ station.name }}</h2>
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div class="flex items-center truncate mr-2">
                                <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                                {{ station.location }}
                            </div>
                            <span class="whitespace-nowrap font-bold text-[#00C853]">{{ station.distance }}</span>
                        </div>

                        <div class="space-y-2 mb-6 text-sm flex-grow">
                            <div class="grid grid-cols-2 gap-4 items-center text-gray-700">
                                <div class="flex items-center font-medium text-gray-900">
                                    <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                                    <span>Jenis Charger:</span>
                                </div>
                                <div class="text-left font-medium">{{ station.chargers.join(', ') }} • {{ station.power }}</div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 space-y-2 mb-4 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Harga:</span>
                                <span class="font-medium">{{ formatRupiah(station.price) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Layanan:</span>
                                <span class="font-medium">{{ formatRupiah(station.serviceFee) }}</span>
                            </div>
                            <div class="flex justify-between items-center font-semibold text-lg p-3 rounded-lg shadow-sm" style="background: linear-gradient(180deg,#f7ffe6,#e6ffb3);">
                                <span class="text-gray-900">Total:</span>
                                <span class="text-gray-900">{{ formatRupiah(calculateTotal(station.price, station.serviceFee)) }}</span>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <button
                                @click="reserveStation(station.id)"
                                :disabled="!station.isBookable"
                                :class="[
                                    'w-full py-3 rounded-xl text-white font-semibold transition duration-200 shadow-md flex items-center justify-center space-x-2',
                                    station.isBookable ? 'bg-[#00C853] hover:bg-[#00A142]' : 'bg-gray-300 text-gray-600 cursor-not-allowed'
                                ]"
                            >
                                <i class="fas fa-ticket-alt mr-2"></i>
                                <span>{{ station.isBookable ? 'Pesan Tiket' : 'Penuh' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="recommendedStations.length === 0" class="text-center py-12 text-gray-500">
                    Tidak ada stasiun yang cocok dengan filter Anda.
                </div>

            </div>
        </main>

        <Footer />

        <Transition name="fade">
            <div v-if="showSearchModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[99999] p-4 overflow-y-auto" @click.self="closeModal">
                <div class="bg-white rounded-xl p-8 shadow-2xl w-full max-w-2xl transform transition-all duration-300 min-h-[400px]">
                    <h3 class="text-2xl font-medium text-gray-900 mb-6">Filter Pencarian</h3>
                    
                    <form @submit.prevent="submitSearch" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Merk Mobil</label>
                                <div @click.stop="toggleDropdown('brand')" class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white" :class="{'ring-2 ring-lime-500': activeDropdown === 'brand'}">
                                    <span>{{ formState.brand || 'Pilih Merk' }}</span>
                                    <i class="fas fa-chevron-down text-gray-400" :class="{'rotate-180': activeDropdown === 'brand'}"></i>
                                </div>
                                <div v-if="activeDropdown === 'brand'" class="absolute top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-gray-100 z-50 max-h-48 overflow-y-auto">
                                    <div v-for="opt in brandOptions" :key="opt" @click="selectOption('brand', opt)" class="px-4 py-2 hover:bg-lime-50 cursor-pointer" :class="{'bg-lime-50 font-bold': formState.brand === opt}">{{ opt }}</div>
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Mobil</label>
                                <div @click.stop="toggleDropdown('type')" class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white" :class="{'ring-2 ring-lime-500': activeDropdown === 'type'}">
                                    <span>{{ formState.type || 'Pilih Tipe' }}</span>
                                    <i class="fas fa-chevron-down text-gray-400" :class="{'rotate-180': activeDropdown === 'type'}"></i>
                                </div>
                                <div v-if="activeDropdown === 'type'" class="absolute top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-gray-100 z-50 max-h-48 overflow-y-auto">
                                    <div v-for="opt in typeOptions" :key="opt" @click="selectOption('type', opt)" class="px-4 py-2 hover:bg-lime-50 cursor-pointer" :class="{'bg-lime-50 font-bold': formState.type === opt}">{{ opt }}</div>
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Domisili</label>
                                <div @click.stop="toggleDropdown('domicile')" class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white" :class="{'ring-2 ring-lime-500': activeDropdown === 'domicile'}">
                                    <span>{{ formState.domicile || 'Pilih Domisili' }}</span>
                                    <i class="fas fa-chevron-down text-gray-400" :class="{'rotate-180': activeDropdown === 'domicile'}"></i>
                                </div>
                                <div v-if="activeDropdown === 'domicile'" class="absolute top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-gray-100 z-50 max-h-48 overflow-y-auto">
                                    <div v-for="opt in domicileOptions" :key="opt" @click="selectOption('domicile', opt)" class="px-4 py-2 hover:bg-lime-50 cursor-pointer" :class="{'bg-lime-50 font-bold': formState.domicile === opt}">{{ opt }}</div>
                                </div>
                            </div>

                        </div>

                        <div class="pt-6 flex justify-end space-x-3">
                            <button type="button" @click="closeModal" class="px-6 py-3 text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                            <button type="submit" class="bg-[#00C853] text-white px-8 py-3 rounded-lg hover:bg-[#00A142] shadow-md">Terapkan Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="showConfirmationModal && selectedStation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[99999] p-4 overflow-y-auto" @click.self="cancelProcess">
                <div class="bg-white rounded-lg p-6 shadow-2xl w-full max-w-lg transform transition-all duration-300">
                    <h3 class="text-xl font-medium text-gray-900 mb-6">Detail Pemesanan</h3>

                    <div class="relative mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Port Charging</label>
                        <div @click.stop="toggleDropdown('port')" class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white" :class="{'ring-2 ring-lime-500': activeDropdown === 'port'}">
                            <span>{{ selectedPort ? portOptions.find(p => p.value === selectedPort)?.label : 'Pilih Port' }}</span>
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                        <div v-if="activeDropdown === 'port'" class="absolute top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-gray-100 z-50 max-h-48 overflow-y-auto">
                            <div v-for="port in portOptions" :key="port.value" @click="selectOption('port', port.value)" class="px-4 py-2 hover:bg-lime-50 cursor-pointer" :class="{'bg-lime-50 font-bold': selectedPort === port.value}">{{ port.label }}</div>
                        </div>
                    </div>

                    <div class="relative mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Durasi</label>
                        <div @click.stop="toggleDropdown('duration')" class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white" :class="{'ring-2 ring-lime-500': activeDropdown === 'duration'}">
                            <span>{{ durationOptions.find(d => d.value === selectedDuration)?.label || 'Pilih Durasi' }}</span>
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                        <div v-if="activeDropdown === 'duration'" class="absolute top-full mt-1 w-full bg-white rounded-xl shadow-xl border border-gray-100 z-50 max-h-48 overflow-y-auto">
                            <div v-for="duration in durationOptions" :key="duration.value" @click="selectOption('duration', duration.value)" class="px-4 py-2 hover:bg-lime-50 cursor-pointer" :class="{'bg-lime-50 font-bold': selectedDuration === duration.value}">{{ duration.label }}</div>
                        </div>
                    </div>

                    <div class="space-y-3 text-gray-700 border-t pt-4">
                        <p><strong>Stasiun:</strong> {{ selectedStation.name }}</p>
                        <div class="flex justify-between items-center bg-lime-50 p-3 rounded-lg mt-2">
                             <span class="text-gray-900 font-medium">Total:</span>
                             <span class="text-xl font-bold text-[#00C853]">{{ calculateTotalFormatted }}</span>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end space-x-3 mt-4">
                        <button @click="cancelProcess" class="px-6 py-3 border rounded-lg hover:bg-gray-50">Batal</button>
                        <button @click="proceedToPayment" :disabled="!selectedPort" class="bg-[#00C853] text-white px-8 py-3 rounded-lg hover:bg-[#00A142] disabled:bg-gray-300 shadow-md">Bayar Sekarang</button>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="showQrisPaymentModal && selectedStation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[99999] p-4" @click.self="cancelProcess">
                <div class="bg-white rounded-lg p-6 shadow-2xl w-full max-w-2xl flex flex-col items-center">
                    <h3 class="text-xl font-bold mb-4">Scan QRIS</h3>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=QRIS_DUMMY" alt="QRIS" class="mb-4 shadow-lg p-2 border rounded">
                    <p class="text-gray-600 mb-4">Silakan scan QR code di atas untuk menyelesaikan pembayaran.</p>
                    <p class="text-2xl font-bold text-[#00C853] mb-6">{{ calculateTotalFormatted }}</p>
                    <button @click="confirmPayment" class="w-full py-3 bg-[#00C853] text-white rounded-xl font-bold shadow-md hover:bg-[#00A142]">Konfirmasi Pembayaran Selesai</button>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="showReceiptModal && selectedStation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[99999] p-4" @click.self="closeReceiptModal">
                <div class="bg-white rounded-xl p-6 shadow-2xl w-full max-w-lg text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-3xl text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h3>
                    <p class="text-gray-600 mb-6">Kode Booking: <span class="font-mono font-bold text-black">{{ selectedStation.bookingNumber }}</span></p>
                    <button @click="openPrintStruk" class="w-full py-3 bg-[#00C853] text-white font-bold rounded-xl shadow-md hover:bg-[#00A142]">
                        <i class="fas fa-download mr-2"></i> Unduh Struk
                    </button>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.fa-bolt { color: #f59e0b; }
.fa-map-marker-alt { color: #6b7280; }
</style>