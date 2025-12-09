<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';

// Helper to check if mobile
const isMobile = () => window.innerWidth < 768;
import { router } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';
import axios from 'axios';

// --- 1. TERIMA DATA DARI DATABASE ---
const props = defineProps({
    dbStations: { type: Array, default: () => [] },
    dbBrands: { type: Array, default: () => [] },
    dbTypes: { type: Array, default: () => [] },
    dbLocations: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) }
});

// --- 2. JAM REALTIME ---
const currentTime = ref(new Date());
let timerInterval = null;

const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
    });
});

const updateTime = () => { currentTime.value = new Date(); };

// --- 3. STATE MANAGEMENT ---
const showSearchModal = ref(false);
const showConfirmationModal = ref(false);
const showQrisPaymentModal = ref(false);
const showReceiptModal = ref(false);
const selectedStationId = ref(null);
const selectedStation = computed(() => props.dbStations.find(s => s.id === selectedStationId.value) || null);

const hasStartedBooking = ref(false);
const activeDropdown = ref(null);
const isProcessingPayment = ref(false);

// State untuk Kode Transaksi Unik (Baru)
const transactionCode = ref('');

// Form Booking State
const selectedPort = ref('');
const selectedDuration = ref('30');
const selectedStartTime = ref('');
const displayTime = computed(() => selectedStartTime.value || 'Pilih Waktu');

// --- 4. SMART TIME LOGIC ---
const timeToMinutes = (timeStr) => {
    if (!timeStr) return 0;
    const [h, m] = timeStr.split(':').map(Number);
    return h * 60 + m;
};

const timeOptions = computed(() => {
    const options = [];
    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();
    const existingBookings = selectedStation.value?.todayBookings || [];

    for (let h = 0; h < 24; h++) {
        for (let m of [0, 30]) {
            const timeStr = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
            const slotStart = h * 60 + m;
            let isDisabled = false;
            let statusText = '';

            if (slotStart < currentMinutes - 15) { isDisabled = true; statusText = '(Lewat)'; }

            if (!isDisabled && selectedPort.value) {
                const isClash = existingBookings.some(b => {
                    if (b.port_type !== selectedPort.value) return false;
                    const start = timeToMinutes(b.start_time);
                    const end = start + b.duration_minutes;
                    return slotStart >= start && slotStart < end;
                });
                if (isClash) { isDisabled = true; statusText = '(Penuh)'; }
            }
            options.push({ label: timeStr, value: timeStr, disabled: isDisabled, status: statusText });
        }
    }

    // Include the currently selected start time if it's not already in options
    if (selectedStartTime.value && !options.find(o => o.value === selectedStartTime.value)) {
        const [h, m] = selectedStartTime.value.split(':').map(Number);
        const slotStart = h * 60 + m;
        let isDisabled = false;
        let statusText = '';

        if (slotStart < currentMinutes - 15) { isDisabled = true; statusText = '(Lewat)'; }

        if (!isDisabled && selectedPort.value) {
            const isClash = existingBookings.some(b => {
                if (b.port_type !== selectedPort.value) return false;
                const start = timeToMinutes(b.start_time);
                const end = start + b.duration_minutes;
                return slotStart >= start && slotStart < end;
            });
            if (isClash) { isDisabled = true; statusText = '(Penuh)'; }
        }
        options.push({ label: selectedStartTime.value, value: selectedStartTime.value, disabled: isDisabled, status: statusText });
    }

    // Sort options by time
    options.sort((a, b) => {
        const [ah, am] = a.value.split(':').map(Number);
        const [bh, bm] = b.value.split(':').map(Number);
        return (ah * 60 + am) - (bh * 60 + bm);
    });

    return options;
});

const setNextAvailableTime = () => {
    const available = timeOptions.value.find(t => !t.disabled);
    selectedStartTime.value = available ? available.value : '';
};

watch(selectedPort, () => {
    const currentOption = timeOptions.value.find(t => t.value === selectedStartTime.value);
    if (!currentOption || currentOption.disabled) setNextAvailableTime();
});

// Watch for modal states to prevent background scroll on mobile and handle back button
// Watch for modal states to prevent background scroll on mobile and handle back button
const handleBodyScroll = () => {
    const anyModalOpen = showConfirmationModal.value || showQrisPaymentModal.value;
    if (isMobile() && anyModalOpen) {
        document.body.classList.add('modal-open');
    } else {
        document.body.classList.remove('modal-open');
    }
};

watch([showConfirmationModal, showQrisPaymentModal], ([confirmationNew, qrisNew]) => {
    handleBodyScroll();

    const anyModalOpen = confirmationNew || qrisNew;
    // Handle back button to close modal instead of navigating away
    if (anyModalOpen) {
        window.history.pushState(null, '', window.location.href);
        window.addEventListener('popstate', handleBackButton);
    } else {
        window.removeEventListener('popstate', handleBackButton);
    }
});

// Function to handle back button press when modal is open
const handleBackButton = (event) => {
    if (showConfirmationModal.value) {
        event.preventDefault();
        cancelProcess();
        window.history.pushState(null, '', window.location.href);
    }
};

// --- 5. JARAK & SORTING ---
const formState = ref({ brand: props.filters.brand || '', type: props.filters.type || '', domicile: props.filters.domicile || '', station: props.filters.station || '' });
const domicileCoordinates = computed(() => {
    const coords = {};
    if (props.dbLocations) props.dbLocations.forEach(l => { if (l.lat && l.lng) coords[l.name] = { lat: parseFloat(l.lat), lng: parseFloat(l.lng) }; });
    return coords;
});
const calculateDistance = (lat1, lon1, lat2, lon2) => {
    if (!lat1 || !lon1 || !lat2 || !lon2) return 999.9;
    const R = 6371; const dLat = (lat2 - lat1) * (Math.PI / 180); const dLon = (lon2 - lon1) * (Math.PI / 180);
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)); return parseFloat((R * c).toFixed(1));
};

const recommendedStations = computed(() => {
    let result = props.dbStations.map(s => ({ ...s }));
    if (formState.value.station && formState.value.station !== 'Pilih Stasiun') result = result.filter(s => s.name === formState.value.station);
    let center = { lat: 1.1301, lng: 104.0529 };
    if (formState.value.domicile && domicileCoordinates.value[formState.value.domicile]) center = domicileCoordinates.value[formState.value.domicile];
    result = result.map(station => {
        const dist = calculateDistance(center.lat, center.lng, parseFloat(station.lat), parseFloat(station.lng));
        return { ...station, realDistance: dist, distance: dist + ' km' };
    });

    if (selectedStationId.value) {
        result.sort((a, b) => {
            if (a.id === selectedStationId.value) return -1;
            if (b.id === selectedStationId.value) return 1;
            return a.realDistance - b.realDistance;
        });
    } else {
        result.sort((a, b) => a.realDistance - b.realDistance);
    }

    return result;
});
const nearestStations = computed(() => recommendedStations.value);

// --- 6. MAP LOGIC ---
let map = null;
const loadLeaflet = () => {
    return new Promise((resolve) => {
        if (window.L) return resolve(window.L);
        const link = document.createElement('link'); link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; document.head.appendChild(link);
        const script = document.createElement('script'); script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'; script.async = true; script.onload = () => resolve(window.L); document.body.appendChild(script);
    });
};
const createPinSvg = (color, isPrivate) => {
    const iconShape = isPrivate
        ? `<path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z" fill="white" transform="translate(0, 4) scale(0.8)"/>`
        : `<path d="M13 10V3L4 14h7v7l9-11h-7z" fill="white" transform="translate(6, 6) scale(0.6)"/>`;
    return encodeURIComponent(`<svg width="32" height="48" viewBox="0 0 24 36" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C7 0 3.5 3.5 3.5 8.5 3.5 15.5 12 25.5 12 25.5s8.5-10 8.5-17C20.5 3.5 17 0 12 0z" fill="${color}"/>${iconShape}</svg>`);
};
const getMarkerColor = (station) => {
    if (station.is_private) return '#F97316';
    if (station.chargers && station.chargers.includes('Fast')) return '#3b82f6';
    return '#00C853';
};

const selectStationFromMap = (id) => {
    selectedStationId.value = id;
};

// FUNGSI SAAT TOMBOL "PESAN" DI CARD DIKLIK
const reserveStation = (id) => {
    selectedStationId.value = id;
    if (selectedStation.value) {
        // Reset form state for fresh modal experience
        selectedPort.value = '';
        selectedDuration.value = selectedStation.value.is_private ? '2' : '30';
        selectedStartTime.value = '';
        activeDropdown.value = null;

        // Always set default start time to exact current device time
        const now = new Date();
        const currentTimeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
        selectedStartTime.value = currentTimeStr;

        selectedPort.value = 'port-1';
        showConfirmationModal.value = true;
        hasStartedBooking.value = true;
    }
};

onMounted(async () => {
    window.addEventListener('resize', handleBodyScroll);
    timerInterval = setInterval(updateTime, 1000);
    const params = new URLSearchParams(window.location.search);
    if (params.has('domicile')) formState.value.domicile = params.get('domicile');
    try {
        const L = await loadLeaflet();
        let centerMap = [1.126, 104.030];
        if (formState.value.domicile && domicileCoordinates.value[formState.value.domicile]) { const c = domicileCoordinates.value[formState.value.domicile]; centerMap = [c.lat, c.lng]; }
        map = L.map('map', { zoomControl: false }).setView(centerMap, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

        props.dbStations.forEach(s => {
            if (s.lat && s.lng) {
                const iconUrl = `data:image/svg+xml;charset=UTF-8,${createPinSvg(getMarkerColor(s), s.is_private)}`;
                const icon = L.icon({ iconUrl, iconSize: [32, 48], iconAnchor: [16, 48], popupAnchor: [0, -40] });
                const marker = L.marker([s.lat, s.lng], { icon }).addTo(map);

                // Klik pin -> update list di bawah (tanpa scroll halaman)
                marker.on('click', () => {
                    selectStationFromMap(s.id);
                });

                const googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${s.lat},${s.lng}`;
                const typeLabel = s.is_private ? '🏠 Mitra Host' : '⚡ Official Station';
                const btnColor = s.is_private ? 'bg-orange-500 hover:bg-orange-600' : 'bg-[#00C853] hover:bg-[#00A142]';

                marker.bindPopup(`
                    <div class="font-sans text-left w-[200px] sm:w-[220px]">
                        <div class="p-3">
                            <!-- Title -->
                            <h3 class="font-bold text-gray-900 text-sm leading-tight mb-1 truncate">${s.name}</h3>
                            <p class="text-xs text-gray-500 mb-3 flex items-start gap-1">
                                <i class="fas fa-map-marker-alt mt-0.5 text-gray-400 shrink-0"></i>
                                <span class="line-clamp-2 leading-relaxed">${s.location}</span>
                            </p>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <a href="${googleMapsUrl}" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-1 py-2 px-2 bg-white border border-gray-200 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-50 hover:text-gray-900 hover:border-gray-300 transition no-underline">
                                    <i class="fas fa-location-arrow"></i> Rute
                                </a>
                                <button onclick="document.dispatchEvent(new CustomEvent('book-station', {detail: ${s.id}}))"
                                        class="flex-1 flex items-center justify-center gap-1 py-2 px-2 ${btnColor} text-white text-xs font-bold rounded-lg shadow-sm hover:shadow-md hover:opacity-90 transition transform active:scale-95">
                                    <i class="fas fa-bolt"></i> Pesan
                                </button>
                            </div>
                        </div>
                    </div>
                `);
            }
        });
        document.addEventListener('book-station', (e) => reserveStation(e.detail));
    } catch (e) { console.error(e); }
});

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
    window.removeEventListener('resize', handleBodyScroll);
});

// --- 7. BOOKING HELPERS ---
const parsePowerValue = (str) => { const m = (str || '50').match(/([0-9]+)/); return m ? Number(m[1]) : 50; };
const estimatedDurationMinutes = computed(() => { if (!selectedStation.value || !selectedDuration.value) return 0; return Math.max(15, Math.round((Number(selectedDuration.value) / parsePowerValue(selectedStation.value.power)) * 60 * 1.1)); });
const estimatedEndTime = computed(() => { if (!selectedStartTime.value) return '-'; const [h, m] = selectedStartTime.value.split(':').map(Number); const endM = (h * 60 + m + estimatedDurationMinutes.value); return `${String(Math.floor(endM / 60) % 24).padStart(2, '0')}:${String(endM % 60).padStart(2, '0')}`; });
const priceBreakdown = computed(() => {
    if (!selectedStation.value) return { pricePerKwh: 0, price: 0, service: 0, ppn: 0, total: 0 };
    const basePrice = Number(selectedStation.value.price) || 50000;
    const serviceFee = Number(selectedStation.value.serviceFee) || (selectedStation.value.is_private ? 2000 : 5000);
    let unitPrice = 0, totalPriceEnergy = 0;
    if (selectedStation.value.is_private) { unitPrice = basePrice; totalPriceEnergy = unitPrice * Number(selectedDuration.value); } else { unitPrice = Math.round(basePrice / 30); totalPriceEnergy = unitPrice * (Number(selectedDuration.value) || 1); }
    const ppn = Math.round((totalPriceEnergy + serviceFee) * 0.11);
    return { pricePerUnit: unitPrice, price: totalPriceEnergy, service: serviceFee, ppn, total: totalPriceEnergy + serviceFee + ppn };
});
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const calculateTotalFormatted = computed(() => formatRupiah(priceBreakdown.value.total));
const durationOptions = computed(() => {
    if (selectedStation.value?.is_private) {
        return [{ label: 'Darurat (2 Jam)', value: '2', desc: 'Cukup untuk ke SPKLU terdekat' }, { label: 'Setengah Hari (4 Jam)', value: '4', desc: 'Isi daya santai' }, { label: 'Overnight (8 Jam)', value: '8', desc: 'Isi penuh saat tidur malam' }, { label: 'Full Day (12 Jam)', value: '12', desc: 'Parkir & Cas seharian' }];
    } else {
        return [{ label: '20 kWh', value: '20', desc: '~30 Menit' }, { label: '30 kWh', value: '30', desc: '~45 Menit' }, { label: '40 kWh', value: '40', desc: '~1 Jam' }, { label: '50 kWh', value: '50', desc: 'Full Charge' }];
    }
});
const availablePorts = computed(() => {
    if (!selectedStation.value) return [];
    const chargers = selectedStation.value.chargers || ['Fast'];
    return chargers.map((charger, index) => {
        let portLabel = `Port ${index + 1}`;
        if (charger.includes('Ultra Fast')) portLabel = `⚡ DC CCS2 (Ultra Fast)`;
        else if (charger.includes('Fast')) portLabel = `⚡ DC CHAdeMO (Fast)`;
        else if (selectedStation.value.is_private) portLabel = `🔌 AC Home (Type 2)`;
        else portLabel = `🔌 AC Type 2 (Regular)`;
        return { id: `port-${index + 1}`, label: portLabel, value: `port-${index + 1}`, type: charger };
    });
});
const portOptions = computed(() => availablePorts.value);

// --- 8. ACTIONS & PAYMENT ---
const toggleDropdown = (name) => { activeDropdown.value = activeDropdown.value === name ? null : name; };
const selectOption = (f, v) => { if (f === 'port') selectedPort.value = v; else if (f === 'duration') selectedDuration.value = v; else if (f === 'time') selectedStartTime.value = v; else formState.value[f] = v; activeDropdown.value = null; };
const openModal = () => { showSearchModal.value = true; };
const closeModal = () => { showSearchModal.value = false; };
const submitSearch = () => closeModal();
const cancelProcess = () => { showConfirmationModal.value = false; showQrisPaymentModal.value = false; selectedStationId.value = null; hasStartedBooking.value = false; isProcessingPayment.value = false; activeDropdown.value = null; };
const proceedToPayment = () => { if (!selectedPort.value) return alert("Pilih port"); showConfirmationModal.value = false; showQrisPaymentModal.value = true; };
const closeReceiptModal = () => { showReceiptModal.value = false; selectedStationId.value = null; };
const openPrintStruk = () => { showReceiptModal.value = false; window.location.href = `/print-struk?station=${encodeURIComponent(JSON.stringify(selectedStation.value))}&total=${encodeURIComponent(calculateTotalFormatted.value)}`; };

const confirmPayment = () => {
    isProcessingPayment.value = true;
    const now = new Date();
    const dateStr = now.toISOString().split('T')[0];
    const fullBookingTime = `${dateStr} ${selectedStartTime.value}:00`;
    setTimeout(() => {
        const durasiStr = selectedStation.value.is_private ? `${selectedDuration.value} Jam` : `${estimatedDurationMinutes.value} menit`;
        const bookingData = { booking_number: selectedStation.value.bookingNumber || 'BK-' + Date.now(), station_name: selectedStation.value.name, location: selectedStation.value.location || '-', port_type: selectedPort.value, duration: durasiStr, total_price: Number(priceBreakdown.value.total), booking_time: fullBookingTime };

        axios.post(route('booking.store'), bookingData).then((response) => {
            // 1. TANGKAP BOOKING NUMBER ASLI DARI DATABASE
            const newBookingCode = response.data.data.booking_number;
            transactionCode.value = newBookingCode; // Simpan ke state

            router.reload({ only: ['dbStations'] });
            isProcessingPayment.value = false;
            showQrisPaymentModal.value = false;
            showReceiptModal.value = true;
        }).catch((e) => {
            isProcessingPayment.value = false;
            alert("Gagal: " + (e.response?.data?.message || "Error sistem"));
        });
    }, 2000);
};

const dragOffset = ref(0); const isDragging = ref(false); let startY = 0;
const onTouchStart = (e) => { isDragging.value = true; startY = e.touches[0].clientY; };
const onTouchMove = (e) => { if (!isDragging.value) return; const diff = e.touches[0].clientY - startY; if (diff > 0) dragOffset.value = diff; };
const onTouchEnd = () => { isDragging.value = false; if (dragOffset.value > 100) { if (showSearchModal.value) closeModal(); else cancelProcess(); } else dragOffset.value = 0; };
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 font-sans text-gray-800">
        <Navbar />
        <main class="flex-grow pt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center mb-4">
                    <div
                        class="bg-white px-4 py-2 rounded-full shadow-sm border border-gray-100 flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div><span
                            class="text-sm font-bold text-gray-700 font-mono">{{ formattedTime }}</span>
                    </div>
                </div>
                <!-- SEARCH BAR -->
                <div class="flex flex-col md:flex-row justify-end items-start md:items-center mb-6">
                    <div class="relative w-full md:w-72 group">
                        <input type="text" placeholder="Filter Lokasi..."
                            class="w-full p-3.5 pl-12 border border-gray-200 rounded-2xl bg-white shadow-sm focus:ring-2 focus:ring-[#00C853] cursor-pointer"
                            @click="openModal" readonly
                            :value="formState.domicile ? `Area: ${formState.domicile}` : 'Cari Stasiun...'">
                        <i
                            class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-[#00C853]"></i>
                    </div>
                </div>

                <!-- MAP -->
                <div
                    class="relative w-full mb-8 rounded-3xl shadow-xl overflow-hidden border border-white h-72 md:h-[450px] bg-[#e9f5ff] z-0">
                    <div id="map" class="w-full h-full z-10"></div>
                </div>

                <!-- LIST STATIONS -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Stasiun <span
                            class="text-[#00C853]">Terdekat</span></h2><span
                        class="text-xs sm:text-sm font-medium text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200 shadow-sm">{{
                            nearestStations.length }} Ditemukan</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    <div v-for="station in nearestStations" :key="station.id" :id="`station-card-${station.id}`"
                        class="bg-white p-5 sm:p-6 rounded-3xl shadow-md hover:shadow-xl border border-transparent transition-all duration-300 flex flex-col relative group cursor-pointer"
                        :class="{ 'ring-4 ring-[#00C853] ring-opacity-50 bg-green-50 transform -translate-y-2': selectedStationId === station.id }">
                        <div v-if="station.is_private"
                            class="absolute top-4 right-4 bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-1 rounded-full border border-orange-200 flex items-center gap-1 z-10">
                            <i class="fas fa-home"></i> Mitra Host
                        </div>
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight mb-1 pr-16">{{
                                    station.name }}</h2>
                                <div class="flex items-center text-xs sm:text-sm text-gray-500"><i
                                        class="fas fa-map-marker-alt mr-1.5 text-gray-400"></i><span
                                        class="font-bold text-[#00C853]">{{ station.distance }}</span></div>
                            </div>
                        </div>
                        <div class="space-y-3 mb-6 text-sm sm:text-base bg-gray-50 p-4 rounded-2xl">
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 mb-2"><span
                                    class="text-gray-600">Biaya Layanan</span><span
                                    class="font-semibold text-gray-800">{{ formatRupiah(station.serviceFee) }}</span>
                            </div>
                            <div class="flex items-center justify-between"><span class="text-gray-600"><i
                                        class="fas fa-bolt w-5 mr-2 text-yellow-500"></i> Tipe</span><span
                                    class="font-semibold text-gray-800">{{ station.chargers ? station.chargers[0] :
                                        'Fast' }}</span></div>
                            <div class="flex items-center justify-between"><span class="text-gray-600"><i
                                        class="fas fa-tachometer-alt w-5 mr-2 text-blue-500"></i> Daya</span><span
                                    class="font-semibold text-gray-800">{{ station.power }}</span></div>
                        </div>
                        <div class="mt-auto">
                            <button @click="reserveStation(station.id)" :disabled="!station.isBookable"
                                :class="['w-full py-3.5 rounded-lg sm:rounded-2xl font-semibold text-sm sm:text-base transition-all shadow-lg flex items-center justify-center gap-2',
                                    station.isBookable ? (station.is_private ? 'bg-orange-500 text-white hover:bg-orange-600' : 'bg-[#00C853] text-white hover:bg-[#00A142]') : 'bg-gray-200 text-gray-400 cursor-not-allowed']">
                                <i class="fas fa-ticket-alt"></i> <span>{{ station.isBookable ? 'Pesan Sekarang' :
                                    'Penuh' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />

        <!-- MODALS (SEARCH) -->
        <Transition name="slide-up">
            <div v-if="showSearchModal"
                class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm flex items-end sm:items-center justify-center z-[99999] p-0 sm:p-4"
                @click.self="closeModal">
                <div
                    class="bg-white w-full h-auto sm:max-w-2xl rounded-t-[2rem] sm:rounded-3xl p-6 sm:p-8 shadow-2xl flex flex-col relative">
                    <div class="w-full h-8 absolute top-0 left-0 z-50 flex justify-center items-center sm:hidden"
                        @touchstart="onTouchStart" @touchmove="onTouchMove" @touchend="onTouchEnd">
                        <div class="w-14 h-1.5 bg-gray-300 rounded-full"></div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 mt-4 sm:mt-0">Filter Pencarian</h3>
                    <form @submit.prevent="submitSearch" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative dropdown-container"><label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Merk Mobil</label>
                                <div @click="toggleDropdown('brand')" class="dropdown-trigger"
                                    :class="{ 'active': activeDropdown === 'brand' }"><span class="truncate">{{
                                        formState.brand || 'Pilih Merk' }}</span><i
                                        class="fas fa-chevron-down text-xs transition-transform"
                                        :class="{ 'rotate-180': activeDropdown === 'brand' }"></i></div>
                                <div v-if="activeDropdown === 'brand'" class="dropdown-menu">
                                    <div v-for="opt in brandOptions" :key="opt" @click="selectOption('brand', opt)"
                                        class="dropdown-item" :class="{ 'selected': formState.brand === opt }">{{ opt }}
                                    </div>
                                </div>
                            </div>
                            <div class="relative dropdown-container"><label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Domisili</label>
                                <div @click="toggleDropdown('domicile')" class="dropdown-trigger"
                                    :class="{ 'active': activeDropdown === 'domicile' }"><span class="truncate">{{
                                        formState.domicile || 'Pilih Lokasi' }}</span><i
                                        class="fas fa-chevron-down text-xs transition-transform"
                                        :class="{ 'rotate-180': activeDropdown === 'domicile' }"></i></div>
                                <div v-if="activeDropdown === 'domicile'" class="dropdown-menu">
                                    <div v-for="opt in domicileOptions" :key="opt"
                                        @click="selectOption('domicile', opt)" class="dropdown-item"
                                        :class="{ 'selected': formState.domicile === opt }">{{ opt }}</div>
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-[#00C853] text-white font-bold px-8 py-3.5 rounded-xl hover:bg-[#00A142] transition shadow-lg mt-4">Terapkan
                            Filter</button>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- MODAL CONFIRMATION -->
        <Transition name="slide-up">
            <div v-if="showConfirmationModal && selectedStation"
                class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm flex items-end sm:items-center justify-center z-[99999] p-0 md:p-8"
                @click.self="cancelProcess">
                <div class="bg-white w-full max-h-[90vh] flex flex-col rounded-t-[2rem] sm:rounded-3xl shadow-2xl md:max-w-4xl relative"
                    :style="{ transform: isDragging ? `translateY(${dragOffset}px)` : '' }">
                    <div class="flex-none px-6 pt-6 pb-2 sm:px-8 sm:pt-8">
                        <div class="w-full h-8 absolute top-0 left-0 z-50 flex justify-center items-center sm:hidden"
                            @touchstart="onTouchStart" @touchmove="onTouchMove" @touchend="onTouchEnd">
                            <div class="w-14 h-1.5 bg-gray-300 rounded-full"></div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center mt-4 sm:mt-0">Konfirmasi Pesanan</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto px-6 sm:px-8 custom-scrollbar pb-4">
                        <div class="space-y-4 mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div class="relative dropdown-container"><label
                                        class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Waktu
                                        Mulai</label>
                                    <div @click="toggleDropdown('time')" class="dropdown-trigger"
                                        :class="{ 'active': activeDropdown === 'time' }"><span
                                            class="text-gray-800 truncate flex items-center gap-2"><i
                                                class="fas fa-clock text-gray-400"></i> {{ displayTime }}</span><i
                                            class="fas fa-chevron-down text-gray-400 text-xs transition-transform"
                                            :class="{ 'rotate-180': activeDropdown === 'time' }"></i></div>
                                    <div v-if="activeDropdown === 'time'" class="dropdown-menu">
                                        <div v-for="time in timeOptions" :key="time.value"
                                            @click="!time.disabled && selectOption('time', time.value)"
                                            class="dropdown-item flex justify-between items-center"
                                            :class="{ 'selected': selectedStartTime === time.value, 'bg-red-50 text-red-400 cursor-not-allowed opacity-60': time.disabled, 'hover:bg-green-50': !time.disabled }">
                                            <span>{{ time.value }}</span><span v-if="time.disabled"
                                                class="text-xs font-bold text-red-500">{{ time.status }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative dropdown-container"><label
                                        class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Port
                                        Charging</label>
                                    <div @click="toggleDropdown('port')" class="dropdown-trigger"
                                        :class="{ 'active': activeDropdown === 'port' }"><span
                                            class="text-gray-800 truncate">{{selectedPort ? portOptions.find(p =>
                                                p.value === selectedPort)?.label : 'Pilih Port'}}</span><i
                                            class="fas fa-chevron-down text-gray-400 text-xs transition-transform"
                                            :class="{ 'rotate-180': activeDropdown === 'port' }"></i></div>
                                    <div v-if="activeDropdown === 'port'" class="dropdown-menu">
                                        <div v-for="port in portOptions" :key="port.value"
                                            @click="selectOption('port', port.value)" class="dropdown-item"
                                            :class="{ 'selected': selectedPort === port.value }">{{ port.label }}</div>
                                    </div>
                                </div>
                                <div class="relative dropdown-container"><label
                                        class="block text-xs font-bold text-gray-500 uppercase mb-1.5">{{
                                            selectedStation.is_private ? 'Paket Durasi' : 'Target Energi (kWh)' }}</label>
                                    <div @click="toggleDropdown('duration')" class="dropdown-trigger"
                                        :class="{ 'active': activeDropdown === 'duration' }"><span
                                            class="text-gray-800 truncate">{{durationOptions.find(d => d.value ===
                                                selectedDuration)?.label || 'Pilih Paket'}}</span><i
                                            class="fas fa-chevron-down text-gray-400 text-xs transition-transform"
                                            :class="{ 'rotate-180': activeDropdown === 'duration' }"></i></div>
                                    <div v-if="activeDropdown === 'duration'" class="dropdown-menu">
                                        <div v-for="duration in durationOptions" :key="duration.value"
                                            @click="selectOption('duration', duration.value)"
                                            class="dropdown-item flex flex-col items-start"
                                            :class="{ 'selected': selectedDuration === duration.value }"><span
                                                class="font-bold">{{ duration.label }}</span><span v-if="duration.desc"
                                                class="text-[10px] text-gray-500">{{ duration.desc }}</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-4 space-y-2 border border-gray-100">
                                <div
                                    class="flex justify-between items-center text-sm text-gray-500 pb-2 border-b border-gray-200 mb-2">
                                    <span>Rincian Pembayaran</span>
                                </div>
                                <div class="flex justify-between items-center text-sm"><span class="text-gray-500">{{
                                    selectedStation.is_private ? 'Harga per Jam' : 'Harga per kWh' }}</span><span
                                        class="font-medium text-gray-800">{{ formatRupiah(priceBreakdown.pricePerUnit)
                                        }}</span></div>
                                <div class="flex justify-between items-center text-sm"><span class="text-gray-600">{{
                                    selectedStation.is_private ? `Sewa (${selectedDuration} Jam)` : `Energi
                                        (${selectedDuration} kWh)` }}</span><span class="font-medium text-gray-800">{{
                                            formatRupiah(priceBreakdown.price) }}</span></div>
                                <div class="flex justify-between items-center text-sm"><span class="text-gray-600">Biaya
                                        Layanan (Admin)</span><span class="font-medium text-gray-800">{{
                                            formatRupiah(priceBreakdown.service) }}</span></div>
                                <div class="flex justify-between items-center text-sm"><span class="text-gray-600">PPN
                                        (11%)</span><span class="font-medium text-gray-800">{{
                                            formatRupiah(priceBreakdown.ppn) }}</span></div>
                                <div class="flex justify-between items-center pt-3 border-t border-gray-200 mt-2"><span
                                        class="font-bold text-gray-900 text-base">Total Pembayaran</span><span
                                        class="font-extrabold text-xl text-[#00C853]">{{ calculateTotalFormatted
                                        }}</span></div>
                            </div>
                        </div>
                        <div class="pt-6 flex justify-end space-x-3 mt-4 pb-4">
                            <button type="button" @click="cancelProcess"
                                class="px-6 py-3 border rounded-lg hover:bg-gray-50 font-bold text-gray-500">Batal</button>
                            <button type="button" @click="proceedToPayment"
                                :disabled="!selectedPort || estimatedDurationMinutes === 0"
                                class="bg-[#00C853] text-white px-8 py-3 rounded-lg hover:bg-[#00A142] disabled:bg-gray-300 shadow-md font-bold transition">Lanjut
                                Bayar</button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- MODAL QRIS (PAYMENT) -->
        <Transition name="fade">
            <div v-if="showQrisPaymentModal && selectedStation"
                class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm flex items-center justify-center z-[99999] p-4"
                @click.self="cancelProcess">
                <div class="bg-white rounded-3xl p-8 shadow-2xl w-full max-w-md text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-[#00C853]"></div>
                    <h3 class="text-xl font-bold mb-6 text-gray-900">Scan untuk Membayar</h3>
                    <div class="bg-white p-2 rounded-xl shadow-inner border border-gray-100 inline-block mb-6 relative">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=QRIS_SIMULATION"
                            alt="QRIS" class="w-48 h-48 rounded-lg">
                        <div v-if="isProcessingPayment"
                            class="absolute inset-0 bg-white/90 flex flex-col items-center justify-center rounded-lg backdrop-blur-sm transition-opacity duration-300">
                            <div
                                class="animate-spin rounded-full h-12 w-12 border-4 border-[#00C853] border-t-transparent mb-3">
                            </div><span class="text-sm font-bold text-[#00C853] animate-pulse">Memverifikasi...</span>
                        </div>
                    </div>
                    <div class="space-y-2 mb-8">
                        <p class="text-gray-500 text-sm">Total yang harus dibayar:</p>
                        <p class="text-4xl font-extrabold text-[#00C853]">{{ calculateTotalFormatted }}</p>
                    </div>
                    <button @click="confirmPayment" :disabled="isProcessingPayment"
                        class="w-full py-4 bg-[#00C853] text-white rounded-xl text-lg font-bold shadow-lg hover:bg-[#00A142] active:scale-95 transition flex items-center justify-center gap-2 disabled:bg-gray-400 disabled:scale-100"><span
                            v-if="!isProcessingPayment">Saya Sudah Bayar</span><span v-else>Sedang
                            Memproses...</span></button>
                    <button @click="cancelProcess" class="mt-4 text-gray-400 text-sm hover:text-red-500 underline"
                        :disabled="isProcessingPayment">Batalkan Transaksi</button>
                </div>
            </div>
        </Transition>

        <!-- MODAL RECEIPT -->
        <Transition name="fade">
            <div v-if="showReceiptModal && selectedStation"
                class="fixed inset-0 bg-[#00C853] flex flex-col items-center justify-center z-[99999] p-4">
                <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-8 text-center relative overflow-hidden">
                    <div
                        class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                        <i class="fas fa-check text-4xl text-[#00C853]"></i>
                    </div>
                    <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Pembayaran Sukses!</h2>

                    <!-- LOGIKA QR CODE VS RESI MANUAL (TETANGGA) -->
                    <div v-if="!selectedStation.is_private">
                        <p class="text-gray-500 mb-6 text-sm">Silakan scan QR Code ini pada mesin charging.</p>
                        <div
                            class="bg-gray-50 p-4 rounded-2xl border-2 border-dashed border-[#00C853] inline-block mb-6">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=ACCESS_TOKEN_${transactionCode || selectedStation.bookingNumber}`"
                                alt="QR Access" class="w-40 h-40 mix-blend-multiply">
                            <p class="text-xs font-mono text-gray-400 mt-2 uppercase tracking-widest">Token Akses</p>
                        </div>
                    </div>
                    <div v-else
                        class="bg-orange-50 p-5 rounded-2xl border-2 border-orange-200 text-left mb-6 relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 bg-orange-200 text-orange-800 text-[10px] font-bold px-2 py-1 rounded-bl-lg">
                            MITRA HOST</div>
                        <h4 class="font-bold text-orange-800 mb-3 flex items-center gap-2 text-lg"><i
                                class="fas fa-home"></i> Bukti Reservasi</h4>
                        <div class="space-y-2 text-sm text-gray-700 border-b border-orange-200 pb-3 mb-3">
                            <div class="flex justify-between"><span class="text-gray-500">Lokasi:</span> <span
                                    class="font-bold">{{ selectedStation.name }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Pemilik:</span> <span
                                    class="font-bold">{{ selectedStation.owner_name || 'Host E-VOLT' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Status:</span> <span
                                    class="text-green-600 font-black tracking-wide">LUNAS</span></div>
                        </div>
                        <div class="text-center p-3 bg-white rounded-xl border border-orange-100 shadow-sm">
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest block mb-1">Kode
                                Booking</span>
                            <div class="text-2xl font-mono font-black text-gray-900 tracking-widest">{{ (transactionCode
                                || selectedStation.bookingNumber).replace('BK-', '') }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button @click="openPrintStruk"
                            class="w-full py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 shadow-lg transition flex items-center justify-center gap-2"><i
                                class="fas fa-print"></i> Cetak Struk</button>
                        <button @click="closeReceiptModal"
                            class="w-full py-3 text-gray-600 font-bold bg-gray-100 rounded-xl hover:bg-gray-200 transition">Tutup</button>
                    </div>

                    <button @click="router.visit(route('status.charging'))"
                        class="w-full mt-3 py-3 bg-[#00C853] text-white font-bold rounded-xl hover:bg-[#00A142] shadow-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-charging-station"></i> Lihat Status Charging
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* STYLE SAMA */
.dropdown-trigger {
    width: 100%;
    padding: 0.875rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    background-color: #ffffff;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 200ms;
}

.dropdown-trigger.active {
    border-color: #00C853;
    box-shadow: 0 0 0 2px rgba(0, 200, 83, 0.2);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    margin-top: 0.5rem;
    width: 100%;
    background-color: #ffffff;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid #f3f4f6;
    z-index: 100;
    max-height: 15rem;
    overflow-y: auto;
}

.dropdown-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #374151;
}

.dropdown-item:hover,
.dropdown-item.selected {
    background-color: #f0fdf4;
    color: #00C853;
    font-weight: 600;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 10px;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.3s cubic-bezier(0.33, 1, 0.68, 1), opacity 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
    opacity: 1;
}

@media (min-width: 640px) {

    .slide-up-enter-from,
    .slide-up-leave-to {
        transform: scale(0.95);
        opacity: 0;
    }
}

/* LEAFLET POPUP OVERRIDES */
:deep(.leaflet-popup-content-wrapper) {
    border-radius: 1rem;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

:deep(.leaflet-popup-content) {
    margin: 0 !important;
    width: auto !important;
}

:deep(.leaflet-popup-tip) {
    background: white;
}

:deep(.leaflet-container a.leaflet-popup-close-button) {
    top: 8px;
    right: 8px;
    color: white;
    padding: 4px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    font: 16px/14px Tahoma, Verdana, sans-serif;
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.2s;
}

:deep(.leaflet-container a.leaflet-popup-close-button:hover) {
    color: white;
    background: rgba(0, 0, 0, 0.4);
}
</style>

<style>
/* Global styles for modal background scroll prevention */
.modal-open {
    overflow: hidden;
}
</style>
