<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'; // Import 'computed'
import Navbar from '@/Components/NavbarAdmin.vue';
import Footer from '@/Components/Footer.vue';
import Modal from '@/Components/Modal.vue';

// Data statis untuk tabel "Daftar Stasiun" (Tidak ada perubahan substantif)
const stations = ref([
  {
    id: 1,
    name: 'EV Station Batam Centre',
    coords: '1.0833, 104.0333',
    chargers: ['Fast', 'Regular'],
    hours: '24/7',
    lat: 1.0833,
    lng: 104.0333,
    city: 'Batam',
  },
  {
    id: 2,
    name: 'EV Station Nagoya',
    coords: '1.1200, 104.0500',
    chargers: ['Ultra Fast', 'Fast'],
    hours: '06:00-22:00',
    lat: 1.1200,
    lng: 104.0500,
    city: 'Batam',
  },
  {
    id: 3,
    name: 'EV Station Sekupang',
    coords: '1.1000, 103.9500',
    chargers: ['Regular'],
    hours: '08:00-20:00',
    lat: 1.1000,
    lng: 103.9500,
    city: 'Batam',
  },
  {
    id: 4,
    name: 'EV Station Tanjung Uncang',
    coords: '1.1300, 104.0600',
    chargers: ['Fast'],
    hours: '24/7',
    lat: 1.1300,
    lng: 104.0600,
    city: 'Batam',
  },
  {
    id: 5,
    name: 'EV Station Batu Aji',
    coords: '1.0500, 104.0200',
    chargers: ['Ultra Fast'],
    hours: '06:00-22:00',
    lat: 1.0500,
    lng: 104.0200,
    city: 'Batam',
  },
  {
    id: 6,
    name: 'EV Station Lubuk Baja',
    coords: '1.0900, 104.0400',
    chargers: ['Regular', 'Fast'],
    hours: '08:00-20:00',
    lat: 1.0900,
    lng: 104.0400,
    city: 'Batam',
  },
  {
    id: 7,
    name: 'EV Station Baloi',
    coords: '1.1100, 104.0300',
    chargers: ['Fast'],
    hours: '24/7',
    lat: 1.1100,
    lng: 104.0300,
    city: 'Batam',
  },
  {
    id: 8,
    name: 'EV Station Kabil',
    coords: '1.0700, 104.0100',
    chargers: ['Ultra Fast', 'Regular'],
    hours: '06:00-22:00',
    lat: 1.0700,
    lng: 104.0100,
    city: 'Batam',
  },
  {
    id: 9,
    name: 'EV Station Sagulung',
    coords: '1.1400, 104.0700',
    chargers: ['Regular'],
    hours: '08:00-20:00',
    lat: 1.1400,
    lng: 104.0700,
    city: 'Batam',
  },
  {
    id: 10,
    name: 'EV Station Nongsa',
    coords: '1.1600, 104.0800',
    chargers: ['Fast', 'Ultra Fast'],
    hours: '24/7',
    lat: 1.1600,
    lng: 104.0800,
    city: 'Batam',
  },
]);

// Dummy data for operator reports (DIMODIFIKASI untuk menyertakan Bulan/Tahun untuk Filtering)
const operatorReports = ref([
  // Januari 2024
  { id: 1, stationName: 'EV Station Batam Centre', week: 'Minggu 1 (Januari)', totalSessions: 150, revenue: 'Rp 2,500,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 2, stationName: 'EV Station Nagoya', week: 'Minggu 1 (Januari)', totalSessions: 120, revenue: 'Rp 1,800,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 3, stationName: 'EV Station Sekupang', week: 'Minggu 1 (Januari)', totalSessions: 80, revenue: 'Rp 1,200,000', status: 'Tertunda', month: 'Januari', year: '2024' },
  { id: 4, stationName: 'EV Station Tanjung Uncang', week: 'Minggu 1 (Januari)', totalSessions: 95, revenue: 'Rp 1,500,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 5, stationName: 'EV Station Batu Aji', week: 'Minggu 1 (Januari)', totalSessions: 110, revenue: 'Rp 1,700,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 6, stationName: 'EV Station Lubuk Baja', week: 'Minggu 1 (Januari)', totalSessions: 90, revenue: 'Rp 1,350,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 7, stationName: 'EV Station Baloi', week: 'Minggu 1 (Januari)', totalSessions: 75, revenue: 'Rp 1,125,000', status: 'Tertunda', month: 'Januari', year: '2024' },
  { id: 8, stationName: 'EV Station Kabil', week: 'Minggu 1 (Januari)', totalSessions: 85, revenue: 'Rp 1,275,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 9, stationName: 'EV Station Sagulung', week: 'Minggu 1 (Januari)', totalSessions: 65, revenue: 'Rp 975,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 10, stationName: 'EV Station Nongsa', week: 'Minggu 1 (Januari)', totalSessions: 100, revenue: 'Rp 1,500,000', status: 'Tertunda', month: 'Januari', year: '2024' },
  { id: 11, stationName: 'EV Station Batam Centre', week: 'Minggu 2 (Januari)', totalSessions: 160, revenue: 'Rp 2,400,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 12, stationName: 'EV Station Nagoya', week: 'Minggu 2 (Januari)', totalSessions: 130, revenue: 'Rp 1,950,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 13, stationName: 'EV Station Sekupang', week: 'Minggu 2 (Januari)', totalSessions: 85, revenue: 'Rp 1,275,000', status: 'Tertunda', month: 'Januari', year: '2024' },
  { id: 14, stationName: 'EV Station Tanjung Uncang', week: 'Minggu 2 (Januari)', totalSessions: 105, revenue: 'Rp 1,575,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  { id: 15, stationName: 'EV Station Batu Aji', week: 'Minggu 2 (Januari)', totalSessions: 115, revenue: 'Rp 1,725,000', status: 'Terkirim', month: 'Januari', year: '2024' },
  // Data tambahan Februari 2024
  { id: 16, stationName: 'EV Station Batam Centre', week: 'Minggu 1 (Februari)', totalSessions: 170, revenue: 'Rp 2,550,000', status: 'Terkirim', month: 'Februari', year: '2024' },
  { id: 17, stationName: 'EV Station Baloi', week: 'Minggu 1 (Februari)', totalSessions: 80, revenue: 'Rp 1,200,000', status: 'Tertunda', month: 'Februari', year: '2024' },
  // Data tambahan Desember 2023
  { id: 18, stationName: 'EV Station Nagoya', week: 'Minggu 4 (Desember)', totalSessions: 140, revenue: 'Rp 2,100,000', status: 'Terkirim', month: 'Desember', year: '2023' },
]);

// Dummy data for user reports (Tidak ada perubahan)
const userReports = ref([
  { id: 1, userName: 'Ahmad Rahman', email: 'ahmad.rahman@example.com', totalCharges: 25, lastDate: '2024-01-15', status: 'Aktif' },
  { id: 2, userName: 'Siti Nurhaliza', email: 'siti.nurhaliza@example.com', totalCharges: 18, lastDate: '2024-01-14', status: 'Aktif' },
  { id: 3, userName: 'Budi Santoso', email: 'budi.santoso@example.com', totalCharges: 32, lastDate: '2024-01-13', status: 'Aktif' },
  { id: 4, userName: 'Maya Sari', email: 'maya.sari@example.com', totalCharges: 15, lastDate: '2024-01-12', status: 'Tidak Aktif' },
  { id: 5, userName: 'Rizki Pratama', email: 'rizki.pratama@example.com', totalCharges: 28, lastDate: '2024-01-11', status: 'Aktif' },
  { id: 6, userName: 'Dewi Kartika', email: 'dewi.kartika@example.com', totalCharges: 22, lastDate: '2024-01-10', status: 'Aktif' },
  { id: 7, userName: 'Hendra Gunawan', email: 'hendra.gunawan@example.com', totalCharges: 19, lastDate: '2024-01-09', status: 'Aktif' },
  { id: 8, userName: 'Lina Sari', email: 'lina.sari@example.com', totalCharges: 35, lastDate: '2024-01-08', status: 'Aktif' },
  { id: 9, userName: 'Fajar Nugroho', email: 'fajar.nugroho@example.com', totalCharges: 12, lastDate: '2024-01-07', status: 'Tidak Aktif' },
  { id: 10, userName: 'Rina Amelia', email: 'rina.amelia@example.com', totalCharges: 27, lastDate: '2024-01-06', status: 'Aktif' },
  { id: 11, userName: 'Tono Widodo', email: 'tono.widodo@example.com', totalCharges: 30, lastDate: '2024-01-05', status: 'Aktif' },
  { id: 12, userName: 'Sari Indah', email: 'sari.indah@example.com', totalCharges: 16, lastDate: '2024-01-04', status: 'Aktif' },
]);

// MAP LOGIC (UPDATED: Add map with markers for admin stations)
let map = null;

const loadLeaflet = () => {
    return new Promise((resolve, reject) => {
        if (window.L) return resolve(window.L);

        // load CSS
        if (!document.querySelector('link[data-leaflet]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            link.setAttribute('data-leaflet', '1');
            document.head.appendChild(link);
        }

        // load script
        if (!document.querySelector('script[data-leaflet]')) {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.async = true;
            script.setAttribute('data-leaflet', '1');
            script.onload = () => resolve(window.L);
            script.onerror = reject;
            document.body.appendChild(script);
        } else {
            // script already present but window.L may not be ready yet
            const check = () => {
                if (window.L) resolve(window.L);
                else setTimeout(check, 50);
            };
            check();
        }
    });
};

onMounted(async () => {
    try {
        const L = await loadLeaflet();

        // initialize map centered on Batam
        map = L.map('map').setView([1.126, 104.030], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // add markers for all stations with coordinates using dynamic colored icons
        stations.value.filter(s => s.lat && s.lng).forEach(s => {
            const color = getMarkerColor(s.chargers);
            const pinSvg = createPinSvg(color);
            const iconUrl = `data:image/svg+xml;charset=UTF-8,${pinSvg}`;

            const customIcon = L.icon({
                iconUrl,
                iconSize: [28, 42],
                iconAnchor: [14, 42],
                popupAnchor: [0, -38]
            });

            const marker = L.marker([s.lat, s.lng], { icon: customIcon }).addTo(map);
            marker.bindPopup(`<div class="font-medium">${s.name}</div><div class="text-sm text-gray-600">${s.coords}</div><div class="text-sm text-gray-500">Status: Aktif</div><div class="text-sm" style="color: ${getMarkerColor(s.chargers)};">Charger: ${s.chargers.join(', ')}</div>`);
        });
    } catch (err) {
        console.error('Failed to load Leaflet:', err);
    }
});

onBeforeUnmount(() => {
    if (map) {
        map.remove();
        map = null;
    }
});

// Helper functions
const getChargerClass = (type) => {
    if (type === 'Ultra Fast') return 'inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-medium';
    if (type === 'Fast') return 'inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium';
    if (type === 'Regular') return 'inline-block bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium';
    return 'inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium';
};

// Helper function untuk mendapatkan warna marker berdasarkan jenis charger tertinggi
const getMarkerColor = (chargers) => {
    if (chargers.includes('Ultra Fast')) return '#9333ea'; // purple-600
    if (chargers.includes('Fast')) return '#3b82f6'; // blue-500
    if (chargers.includes('Regular')) return '#22c55e'; // green-500
    return '#00C853'; // default green
};

// Helper function untuk membuat SVG pin dengan warna dinamis
const createPinSvg = (color) => {
    return encodeURIComponent(`
        <svg width="32" height="48" viewBox="0 0 24 36" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 0C7 0 3.5 3.5 3.5 8.5 3.5 15.5 12 25.5 12 25.5s8.5-10 8.5-17C20.5 3.5 17 0 12 0z" fill="${color}"/>
            <circle cx="12" cy="8.5" r="3.5" fill="white"/>
        </svg>
    `);
};

// --- LOGIKA MODAL BARU ---

// Modal state
const showModal = ref(false);
const isEditing = ref(false);

// Form data for new station (MODIFIED: Menambahkan operationalHours dan chargers detail)
const newStation = ref({
  name: '',
  operationalHours: '24/7',
  latitude: '',
  longitude: '',
  city: '',
  email: '',
  chargers: [
    { port: 1, type: 'Regular', power: '7.4', kwh: '22', connector: 'Type 2' } // Default value agar mudah diisi
  ],
});

// Function to open modal (no change)
const openAddStationModal = () => {
  isEditing.value = false;
  showModal.value = true;
};

// Function to close modal (MODIFIED: Reset state)
const closeModal = () => {
  showModal.value = false;
  isEditing.value = false;
  // Reset form, mempertahankan minimal satu charger block
  newStation.value = {
    name: '',
    operationalHours: '24/7',
    latitude: '',
    longitude: '',
    city: '',
    email: '',
    chargers: [
      { port: 1, type: 'Regular', power: '7.4', kwh: '22', connector: 'Type 2' }
    ],
  };
};

// Function to add a new charger block (NEW)
const addChargerBlock = () => {
  const newPortNumber = newStation.value.chargers.length + 1;
  newStation.value.chargers.push({
    port: newPortNumber,
    type: '',
    power: '',
    kwh: '',
    connector: ''
  });
};

// Function to remove a charger block (NEW)
const removeChargerBlock = (index) => {
  newStation.value.chargers.splice(index, 1);
  // Re-index port numbers after removal
  newStation.value.chargers.forEach((charger, i) => {
    charger.port = i + 1;
  });
};


// Function to add new station (MODIFIED: Mengambil jenis charger dari array chargers)
const addStation = () => {
  console.log('Adding new station:', newStation.value);

  // Extract unique charger types (Fast, Ultra Fast, Regular) for the main table display
  const stationChargerTypes = [...new Set(newStation.value.chargers.map(c => c.type).filter(Boolean))];

  // For now, just add to local array
  const id = stations.value.length + 1;
  const newStationData = {
    id,
    name: newStation.value.name,
    coords: `${newStation.value.latitude}, ${newStation.value.longitude}`,
    chargers: stationChargerTypes, // Use the extracted types
    hours: newStation.value.operationalHours,
    lat: parseFloat(newStation.value.latitude),
    lng: parseFloat(newStation.value.longitude),
    city: newStation.value.city,
  };
  stations.value.push(newStationData);

  // Add marker to map
  if (map && newStationData.lat && newStationData.lng) {
    const color = getMarkerColor(newStationData.chargers);
    const pinSvg = createPinSvg(color);
    const iconUrl = `data:image/svg+xml;charset=UTF-8,${pinSvg}`;

    const customIcon = L.icon({
      iconUrl,
      iconSize: [28, 42],
      iconAnchor: [14, 42],
      popupAnchor: [0, -38]
    });

    const marker = L.marker([newStationData.lat, newStationData.lng], { icon: customIcon }).addTo(map);
    marker.bindPopup(`<div class="font-medium">${newStationData.name}</div><div class="text-sm text-gray-600">${newStationData.coords}</div><div class="text-sm text-gray-500">Status: Aktif</div><div class="text-sm" style="color: ${getMarkerColor(newStationData.chargers)};">Charger: ${newStationData.chargers.join(', ')}</div>`);
  }

  closeModal();
};

// State to track which station is being edited
const editingStationId = ref(null);

// Function to update station (for editing)
const updateStation = () => {
  console.log('Updating station:', newStation.value);

  // Extract unique charger types
  const stationChargerTypes = [...new Set(newStation.value.chargers.map(c => c.type).filter(Boolean))];

  // Find the station to update by ID
  const index = stations.value.findIndex(s => s.id === editingStationId.value);
  if (index !== -1) {
    stations.value[index] = {
      ...stations.value[index],
      name: newStation.value.name,
      coords: `${newStation.value.latitude}, ${newStation.value.longitude}`,
      chargers: stationChargerTypes,
      hours: newStation.value.operationalHours,
      lat: parseFloat(newStation.value.latitude),
      lng: parseFloat(newStation.value.longitude),
      city: newStation.value.city,
    };
  }

  closeModal();
};

// Function to edit station
const editStation = (station) => {
  console.log('Editing station:', station);
  isEditing.value = true;
  editingStationId.value = station.id;
  // Pre-fill the modal with station data
  newStation.value = {
    name: station.name,
    operationalHours: station.hours,
    latitude: station.lat.toString(),
    longitude: station.lng.toString(),
    city: station.city,
    email: '', // Assuming email is not stored in station data, or add it if available
    chargers: station.chargers.map(type => ({
      port: 1, // Default port, adjust if needed
      type: type,
      power: '', // Default empty, adjust if needed
      kwh: '',
      connector: ''
    }))
  };
  showModal.value = true;
};

// Function to delete station
const deleteStation = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus stasiun ini?')) {
    stations.value = stations.value.filter(station => station.id !== id);
    console.log('Deleted station with id:', id);
  }
};

// --- LOGIKA FILTER LAPORAN OPERATOR BARU ---

// Filter state
const selectedStation = ref('');
const selectedMonth = ref('');
const selectedYear = ref('');

// Custom dropdown states for filters
const isStationFilterOpen = ref(false);
const isMonthFilterOpen = ref(false);
const isYearFilterOpen = ref(false);

// Helper to close all filter dropdowns
const closeAllFilterDropdowns = () => {
  isStationFilterOpen.value = false;
  isMonthFilterOpen.value = false;
  isYearFilterOpen.value = false;
};

const openFilterDropdown = (which) => {
  if (which === 'station') {
    isStationFilterOpen.value = !isStationFilterOpen.value;
    isMonthFilterOpen.value = false;
    isYearFilterOpen.value = false;
  } else if (which === 'month') {
    isMonthFilterOpen.value = !isMonthFilterOpen.value;
    isStationFilterOpen.value = false;
    isYearFilterOpen.value = false;
  } else if (which === 'year') {
    isYearFilterOpen.value = !isYearFilterOpen.value;
    isStationFilterOpen.value = false;
    isMonthFilterOpen.value = false;
  }
};

const selectFilterOption = (field, value) => {
  if (field === 'station') selectedStation.value = value;
  else if (field === 'month') selectedMonth.value = value;
  else if (field === 'year') selectedYear.value = value;
  closeAllFilterDropdowns();
};

// Dummy data untuk pilihan filter (Diambil dari data statis yang sudah ada)
const stationOptions = computed(() => [...new Set(stations.value.map(s => s.name))]);
const monthOptions = ref(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']);
const yearOptions = computed(() => {
    const years = operatorReports.value.map(r => r.year);
    return [...new Set(years)].sort((a, b) => b - a); // Urutkan dari yang terbaru
});

// Computed property untuk memfilter laporan
const filteredOperatorReports = computed(() => {
  return operatorReports.value.filter(report => {
    let stationMatch = true;
    let monthMatch = true;
    let yearMatch = true;

    // Filter Nama Stasiun
    if (selectedStation.value) {
      stationMatch = report.stationName === selectedStation.value;
    }

    // Filter Bulan
    if (selectedMonth.value) {
      monthMatch = report.month === selectedMonth.value;
    }

    // Filter Tahun
    if (selectedYear.value) {
      yearMatch = report.year === selectedYear.value;
    }

    return stationMatch && monthMatch && yearMatch;
  });
});

// Search functionality for stations table
const searchQuery = ref('');

// Computed property untuk memfilter stasiun berdasarkan pencarian
const filteredStations = computed(() => {
  if (!searchQuery.value) {
    return stations.value;
  }
  return stations.value.filter(station =>
    station.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <Navbar />

    <main class="py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Dashboard Admin</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Peta Stasiun Pengisian</h2>
              <div id="map" style="height: 400px; width: 100%;" class="rounded-lg shadow-inner"></div>
            </div>
          </div>

          <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
              <p class="text-sm font-medium text-gray-500">Total Stasiun Aktif</p>
              <p class="text-4xl font-bold text-gray-900 mt-1">{{ stations.length }}</p>
              
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
              <p class="text-sm font-medium text-gray-500">Total Sesi Pengisian (Bulan Ini)</p>
              <p class="text-4xl font-bold text-gray-900 mt-1">2.345</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
              <p class="text-sm font-medium text-gray-500">Pengguna Terdaftar</p>
              <p class="text-4xl font-bold text-gray-900 mt-1">{{ userReports.length }}</p>
            </div>
          </div>
        </div>

        <div class="mt-8 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Stasiun</h3>
            <p class="text-sm text-gray-500 mt-1">Status dan detail semua stasiun yang terdaftar</p>

            <!-- Search Input and Button Row -->
            <div class="flex justify-between items-center mt-4 mb-5">
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari stasiun..."
                  class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition duration-150"
                />
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <button
                @click="openAddStationModal"
                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#00C853] hover:bg-[#00A142] transition duration-150"
              >
                + Tambah Stasiun Baru
              </button>
            </div>
          </div>
          <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Stasiun</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koordinat</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Charger</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Operasional</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="station in filteredStations" :key="station.id" class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ station.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ station.coords }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <span
                      v-for="type in station.chargers"
                      :key="type"
                      :class="getChargerClass(type)"
                    >
                      {{ type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ station.hours }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                    <button @click="editStation(station)" class="text-blue-600 hover:text-blue-800">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="deleteStation(station.id)" class="text-red-600 hover:text-red-800">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="filteredStations.length === 0" class="text-center py-8 text-gray-500 text-sm">
              Tidak ada stasiun yang sesuai dengan pencarian.
            </div>
          </div>
        </div>

        <div class="mt-10 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-start">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">Laporan Operator Mingguan</h3>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- STATION FILTER: custom dropdown -->
              <div class="relative">
                <label for="filter-station" class="block text-sm font-medium text-gray-700">Nama Stasiun</label>
                <div @click.stop="openFilterDropdown('station')"
                     class="mt-1 w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isStationFilterOpen}"
                >
                  <span class="text-gray-800">{{ selectedStation || 'Semua Stasiun' }}</span>
                  <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isStationFilterOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
                <div v-if="isStationFilterOpen" @click.stop class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
                  <div class="py-2">
                    <div @click="selectFilterOption('station', '')" class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150 text-gray-800">
                      Semua Stasiun
                    </div>
                    <div v-for="name in stationOptions" :key="name"
                         @click="selectFilterOption('station', name)"
                         class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                         :class="{'bg-lime-50 font-semibold text-lime-800': selectedStation === name}">
                      {{ name }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- MONTH FILTER: custom dropdown -->
              <div class="relative">
                <label for="filter-month" class="block text-sm font-medium text-gray-700">Bulan</label>
                <div @click.stop="openFilterDropdown('month')"
                     class="mt-1 w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isMonthFilterOpen}"
                >
                  <span class="text-gray-800">{{ selectedMonth || 'Semua Bulan' }}</span>
                  <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isMonthFilterOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
                <div v-if="isMonthFilterOpen" @click.stop class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
                  <div class="py-2">
                    <div @click="selectFilterOption('month', '')" class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150 text-gray-800">
                      Semua Bulan
                    </div>
                    <div v-for="month in monthOptions" :key="month"
                         @click="selectFilterOption('month', month)"
                         class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                         :class="{'bg-lime-50 font-semibold text-lime-800': selectedMonth === month}">
                      {{ month }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- YEAR FILTER: custom dropdown -->
              <div class="relative">
                <label for="filter-year" class="block text-sm font-medium text-gray-700">Tahun</label>
                <div @click.stop="openFilterDropdown('year')"
                     class="mt-1 w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isYearFilterOpen}"
                >
                  <span class="text-gray-800">{{ selectedYear || 'Semua Tahun' }}</span>
                  <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isYearFilterOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
                <div v-if="isYearFilterOpen" @click.stop class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
                  <div class="py-2">
                    <div @click="selectFilterOption('year', '')" class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150 text-gray-800">
                      Semua Tahun
                    </div>
                    <div v-for="year in yearOptions" :key="year"
                         @click="selectFilterOption('year', year)"
                         class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                         :class="{'bg-lime-50 font-semibold text-lime-800': selectedYear === year}">
                      {{ year }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            </div>

          <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stasiun</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minggu/Bulan</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi Total</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="report in filteredOperatorReports" :key="report.id" class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ report.stationName }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ report.week }} ({{ report.month }} {{ report.year }})</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ report.totalSessions }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ report.revenue }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true, 'bg-green-100 text-green-800': report.status === 'Terkirim', 'bg-yellow-100 text-yellow-800': report.status === 'Tertunda'}">
                      {{ report.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="#" class="text-[#00C853] hover:text-[#00A142]">Lihat Detail</a>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="filteredOperatorReports.length === 0" class="text-center py-8 text-gray-500 text-sm">
              Tidak ada laporan operator yang sesuai dengan filter yang dipilih.
            </div>
          </div>
        </div>
      </div>
    </main>

    <Modal :show="showModal" @close="closeModal">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ isEditing ? 'Edit Stasiun' : 'Tambah Stasiun Baru' }}</h3>
        <form @submit.prevent="isEditing ? updateStation() : addStation()" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Stasiun</label>
            <input
              v-model="newStation.name"
              type="text"
              id="name"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-lime-500 focus:border-lime-500"
              required
            />
            </div>

            <div>
            <label for="operationalHours" class="block text-sm font-medium text-gray-700">Jam Operasional</label>
            <input
              v-model="newStation.operationalHours"
              type="text"
              id="operationalHours"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-lime-500 focus:border-lime-500"
              placeholder="Contoh: 24/7 atau 08:00-20:00"
              required
            />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
              <input
                v-model="newStation.latitude"
                type="number"
                step="0.0001"
                id="latitude"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-lime-500 focus:border-lime-500"
                required
              />
            </div>
            <div>
              <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
              <input
                v-model="newStation.longitude"
                type="number"
                step="0.0001"
                id="longitude"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-lime-500 focus:border-lime-500"
                required
              />
            </div>
          </div>

          

          <div class="space-y-4 pt-4 border-t border-gray-200">
            <h4 class="text-base font-medium text-gray-900">Detail Charger</h4>
            <div v-for="(charger, index) in newStation.chargers" :key="index" class="p-4 border border-gray-200 rounded-lg bg-gray-50 space-y-3">
              <div class="flex justify-between items-center">
                <p class="text-sm font-semibold text-gray-700">Port {{ charger.port }}</p>
                <button v-if="newStation.chargers.length > 1" type="button" @click="removeChargerBlock(index)" class="text-red-500 hover:text-red-700 text-sm">Hapus Port</button>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label :for="'chargerType-' + index" class="block text-xs font-medium text-gray-700">Jenis Charger (Tipe)</label>
                  <select
                    v-model="charger.type"
                    :id="'chargerType-' + index"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-lime-500 focus:border-lime-500"
                    required
                  >
                    <option value="">Pilih Tipe</option>
                    <option value="Regular">Regular</option>
                    <option value="Fast">Fast</option>
                    <option value="Ultra Fast">Ultra Fast</option>
                  </select>
                </div>
                <div>
                  <label :for="'power-' + index" class="block text-xs font-medium text-gray-700">Kecepatan Daya (kW)</label>
                  <input
                    v-model="charger.power"
                    type="number"
                    step="0.1"
                    :id="'power-' + index"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-lime-500 focus:border-lime-500"
                    placeholder="Contoh: 7.4"
                    required
                  />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label :for="'kwh-' + index" class="block text-xs font-medium text-gray-700">Kapasitas (kWh)</label>
                  <input
                    v-model="charger.kwh"
                    type="number"
                    step="0.1"
                    :id="'kwh-' + index"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-lime-500 focus:border-lime-500"
                    placeholder="Contoh: 22"
                    required
                  />
                </div>
                <div>
                  <label :for="'connector-' + index" class="block text-xs font-medium text-gray-700">Jenis Konektor Fisik</label>
                  <input
                    v-model="charger.connector"
                    type="text"
                    :id="'connector-' + index"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-lime-500 focus:border-lime-500"
                    placeholder="Contoh: Type 2, CCS"
                    required
                  />
                </div>
              </div>
            </div>

            <div class="text-right">
              <button
              type="button"
              @click="addChargerBlock"
              class="inline-flex items-right text-[#00C853] font-semibold text-sm hover:text-[#00A142] transition duration-300"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
               Tambah Port Charger
            </button>
            </div>
          </div>
          
          <div>
            <label for="city" class="block text-sm font-medium text-gray-700">Kota</label>
            <input
              v-model="newStation.city"
              type="text"
              id="city"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-lime-500 focus:border-lime-500"
              required
            />
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="newStation.email"
              type="email"
              id="email"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-lime-500 focus:border-lime-500"
              required
            />
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-[#00C853] text-white rounded-md text-sm font-medium hover:bg-[#00A142]"
            >
              {{ isEditing ? 'Update Stasiun' : 'Tambah Stasiun' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <Footer />
  </div>
</template>