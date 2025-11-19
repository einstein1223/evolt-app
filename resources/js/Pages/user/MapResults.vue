<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';

import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';
import PrintStrukPembayaran from '@/Pages/PrintStrukPembayaran.vue';

// -----------------------------------------------------------------------------------

const showSearchModal = ref(false);
const showConfirmationModal = ref(false);
const showQrisPaymentModal = ref(false); // Modal baru untuk pembayaran QRIS
const showReceiptModal = ref(false);    // Modal untuk struk akhir
const showPrintModal = ref(false);     // Modal/page untuk tampilan print struk
const selectedStation = ref(null);
const hasStartedBooking = ref(false); // Track if user has started booking process

// New reactive variables for port and duration selection
const selectedPort = ref('');
const selectedDuration = ref('60'); // Default to 1 hour (60 minutes)
const durationOptions = [
    { label: '30 menit', value: '30', multiplier: 0.5 },
    { label: '1 jam', value: '60', multiplier: 1 },
    { label: '1.5 jam', value: '90', multiplier: 1.5 },
    { label: '2 jam', value: '120', multiplier: 2 }
];

// Port options based on available ports
const portOptions = computed(() => {
    return availablePorts.value.map(port => ({
        label: `Port ${port.id.split('-')[1]} - ${port.type} (${port.power})`,
        value: port.id
    }));
});

// Computed property for available ports based on selected station
const availablePorts = computed(() => {
    if (!selectedStation.value) return [];
    return selectedStation.value.chargers.map((charger, index) => ({
        id: `port-${index + 1}`,
        type: charger,
        power: selectedStation.value.power,
        status: selectedStation.value.status
    }));
});

const anyModalOpen = computed(() => showSearchModal.value || showConfirmationModal.value || showQrisPaymentModal.value || showReceiptModal.value);

const formState = ref({
    brand: 'Nissan',
    type: 'SUV', 
    date: new Date().toISOString().split('T')[0],
    domicile: 'Batam Center', 
    station: 'SPKLU Mega Mall', 
    time: '12:00',
});

// Reuse LandingPage custom dropdown data/logic to provide smooth, scrollable dropdowns
const brandOptions = ['Nissan','Toyota','Wuling','Hyundai','Tesla','BYD','Kia'];
const typeOptions = ['SUV', 'City Car', 'Hatchback', 'Sedan', 'MPV'];
const domicileOptions = ['Batam Center','Nagoya','Harbour Bay','Sekupang','Batu Aji','Lubuk Baja','Tiban','Kabil','Batu Ampar'];
const stationOptions = ['SPKLU Mega Mall','SPKLU Grand Batam Mall','SPKLU Nagoya Hill','SPKLU Harbour Bay','SPKLU Batam Center','SPKLU Batam City Square','SPKLU Kepri Mall','SPKLU Batam View','SPKLU Nagoya City'];

const isBrandOpen = ref(false);
const isTypeOpen = ref(false);
const isDomicileOpen = ref(false);
const isStationOpen = ref(false);
const isPortOpen = ref(false);
const isDurationOpen = ref(false);

const closeAllCustomDropdowns = () => {
    isBrandOpen.value = false;
    isTypeOpen.value = false;
    isDomicileOpen.value = false;
    isStationOpen.value = false;
    isPortOpen.value = false;
    isDurationOpen.value = false;
};

const openOnly = (which) => {
    if (which === 'brand') {
        isBrandOpen.value = !isBrandOpen.value;
        isTypeOpen.value = false;
        isDomicileOpen.value = false;
        isStationOpen.value = false;
        isPortOpen.value = false;
        isDurationOpen.value = false;
    } else if (which === 'type') {
        isTypeOpen.value = !isTypeOpen.value;
        isBrandOpen.value = false;
        isDomicileOpen.value = false;
        isStationOpen.value = false;
        isPortOpen.value = false;
        isDurationOpen.value = false;
    } else if (which === 'domicile') {
        isDomicileOpen.value = !isDomicileOpen.value;
        isBrandOpen.value = false;
        isTypeOpen.value = false;
        isStationOpen.value = false;
        isPortOpen.value = false;
        isDurationOpen.value = false;
    } else if (which === 'station') {
        isStationOpen.value = !isStationOpen.value;
        isBrandOpen.value = false;
        isTypeOpen.value = false;
        isDomicileOpen.value = false;
        isPortOpen.value = false;
        isDurationOpen.value = false;
    } else if (which === 'port') {
        isPortOpen.value = !isPortOpen.value;
        isBrandOpen.value = false;
        isTypeOpen.value = false;
        isDomicileOpen.value = false;
        isStationOpen.value = false;
        isDurationOpen.value = false;
    } else if (which === 'duration') {
        isDurationOpen.value = !isDurationOpen.value;
        isBrandOpen.value = false;
        isTypeOpen.value = false;
        isDomicileOpen.value = false;
        isStationOpen.value = false;
        isPortOpen.value = false;
    }

    // keep date/time closed when toggling custom dropdowns
    isDateDropdownOpen.value = false;
    isTimeDropdownOpen.value = false;
};

const selectOption = (field, value) => {
    if (field === 'port') {
        selectedPort.value = value;
    } else if (field === 'duration') {
        selectedDuration.value = value;
    } else {
        formState.value[field] = value;
    }
    closeAllCustomDropdowns();
};

const openModal = () => {
    showSearchModal.value = true;
};

const closeModal = () => {
    showSearchModal.value = false;
};

const submitSearch = () => {
    console.log('Searching with:', formState.value);
    closeModal();
};

const stations = ref([
    { id: 1, name: 'SPKLU Nagoya Hill', location: 'Nagoya Hill, Batam', distance: '2.5km', chargers: ['Fast'], power: '80 kW', status: 'Tersedia', bookingTime: '2025-10-22 12:00', duration: '60 menit', price: 50000, serviceFee: 10000, isBookable: true, bookingNumber: 'BK1001', lat: 1.1324, lng: 104.0383 },
    { id: 2, name: 'SPKLU Mega Mall Batam', location: 'Mega Mall, Batam Center', distance: '1.2km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 12:30', duration: '60 menit', price: 40000, serviceFee: 8000, isBookable: true, bookingNumber: 'BK1002', lat: 1.1225, lng: 104.0417 },
    { id: 3, name: 'SPKLU Harbour Bay', location: 'Harbour Bay, Batam', distance: '3.8km', chargers: ['Fast', 'Ultra Fast'], power: '100 kW', status: 'Penuh', bookingTime: '2025-10-22 14:00', duration: '30 menit', price: 120000, serviceFee: 15000, isBookable: false, bookingNumber: 'BK1003', lat: 1.1145, lng: 104.0522 },
    { id: 4, name: 'SPKLU Batam Center', location: 'Batam Center', distance: '0.5km', chargers: ['Regular'], power: '50 kW', status: 'Tersedia', bookingTime: '2025-10-22 10:00', duration: '90 menit', price: 60000, serviceFee: 12000, isBookable: true, bookingNumber: 'BK1004', lat: 1.1278, lng: 104.0302 },
    { id: 5, name: 'SPKLU Batam City Square', location: 'Batam City Square', distance: '1.8km', chargers: ['Regular'], power: '11 kW', status: 'Penuh', bookingTime: '2025-10-22 10:00', duration: '90 menit', price: 30000, serviceFee: 8000, isBookable: false, bookingNumber: 'BK1005', lat: 1.1210, lng: 104.0335 },
    { id: 6, name: 'SPKLU Kepri Mall', location: 'Kepri Mall', distance: '2.1km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 11:00', duration: '60 menit', price: 45000, serviceFee: 9000, isBookable: true, bookingNumber: 'BK1006', lat: 1.1122, lng: 104.0450 },
    { id: 7, name: 'SPKLU Batam View', location: 'Batam View', distance: '4.0km', chargers: ['Ultra Fast'], power: '150 kW', status: 'Tersedia', bookingTime: '2025-10-22 13:00', duration: '45 menit', price: 90000, serviceFee: 10000, isBookable: true, bookingNumber: 'BK1007', lat: 1.1390, lng: 104.0480 },
    // SPKLU Kabil removed per request
    { id: 9, name: 'SPKLU Tiban', location: 'Tiban', distance: '5.5km', chargers: ['Fast'], power: '50 kW', status: 'Penuh', bookingTime: '2025-10-22 15:00', duration: '30 menit', price: 80000, serviceFee: 10000, isBookable: false, bookingNumber: 'BK1009', lat: 1.0956, lng: 104.0103 },
    { id: 10, name: 'SPKLU Sekupang', location: 'Sekupang', distance: '7.2km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 08:00', duration: '60 menit', price: 35000, serviceFee: 7000, isBookable: true, bookingNumber: 'BK1010', lat: 1.0552, lng: 103.9824 },
    { id: 11, name: 'SPKLU Batu Ampar', location: 'Batu Ampar', distance: '6.0km', chargers: ['Regular'], power: '11 kW', status: 'Tersedia', bookingTime: '2025-10-22 10:30', duration: '90 menit', price: 30000, serviceFee: 7000, isBookable: true, bookingNumber: 'BK1011', lat: 1.0873, lng: 104.0128 },
    { id: 12, name: 'SPKLU Nagoya City', location: 'Nagoya City', distance: '2.8km', chargers: ['Fast'], power: '80 kW', status: 'Tersedia', bookingTime: '2025-10-22 12:45', duration: '60 menit', price: 100000, serviceFee: 12000, isBookable: true, bookingNumber: 'BK1012', lat: 1.1290, lng: 104.0405 },
    { id: 13, name: 'SPKLU Batam Harbor', location: 'Batam Harbor', distance: '4.5km', chargers: ['Fast'], power: '100 kW', status: 'Penuh', bookingTime: '2025-10-22 14:30', duration: '30 menit', price: 150000, serviceFee: 15000, isBookable: false, bookingNumber: 'BK1013', lat: 1.1067, lng: 104.0622 },
    { id: 14, name: 'SPKLU Gajah Mada', location: 'Gajah Mada, Batam', distance: '1.5km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 09:30', duration: '60 menit', price: 40000, serviceFee: 8000, isBookable: true, bookingNumber: 'BK1014', lat: 1.1255, lng: 104.0280 },
    { id: 15, name: 'SPKLU Waterfront City', location: 'Waterfront City', distance: '3.2km', chargers: ['Regular'], power: '22 kW', status: 'Tersedia', bookingTime: '2025-10-22 11:15', duration: '60 menit', price: 45000, serviceFee: 9000, isBookable: true, bookingNumber: 'BK1015', lat: 1.1312, lng: 104.0588 },
]);

const availableStations = computed(() => stations.value.filter(s => s.status === 'Tersedia'));

const nearestStations = computed(() => {
    return stations.value.filter(s => s.status === 'Tersedia').slice().sort((a, b) => {
        const distA = parseFloat(a.distance.replace('km', ''));
        const distB = parseFloat(b.distance.replace('km', ''));
        return distA - distB;
    });
});

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

// Date & Time picker helpers (copied from LandingPage for consistent UX)
const today = new Date();
today.setHours(0,0,0,0);
const monthNames = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
const dayNames = ["Min","Sen","Sel","Rab","Kam","Jum","Sab"];

const isDateDropdownOpen = ref(false);
const selectedDate = ref(new Date(today));
const calendarDisplayDate = ref(new Date(today));

const isTimeDropdownOpen = ref(false);
const selectedTime = ref(formState.value.time || '');

const formatDate = (date) => `${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
const datePickerText = computed(() => selectedDate.value ? formatDate(selectedDate.value) : 'Pilih Tanggal');

const calendarDays = computed(() => {
    const year = calendarDisplayDate.value.getFullYear();
    const month = calendarDisplayDate.value.getMonth();
    const firstDayIndex = new Date(year, month, 1).getDay();
    const lastDate = new Date(year, month+1, 0).getDate();
    let days = [];
    for (let i=0;i<firstDayIndex;i++) days.push({ number: null, classes: 'text-gray-300' });
    for (let i=1;i<=lastDate;i++){
        const currentDate = new Date(year, month, i);
        const isSelected = selectedDate.value && currentDate.toDateString() === selectedDate.value.toDateString();
        const isDisabled = currentDate < today;
        let classes = 'day-number w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium transition duration-100';
        if (isDisabled) classes += ' text-gray-400 cursor-not-allowed';
        else if (isSelected) classes += ' bg-lime-600 text-white shadow-md';
        else classes += ' text-gray-800 hover:bg-lime-100 cursor-pointer';
        days.push({ number: i, date: currentDate, classes, isDisabled });
    }
    return days;
});

const prevMonth = () => { const d = new Date(calendarDisplayDate.value); d.setMonth(d.getMonth()-1); calendarDisplayDate.value = d; };
const nextMonth = () => { const d = new Date(calendarDisplayDate.value); d.setMonth(d.getMonth()+1); calendarDisplayDate.value = d; };

const selectDay = (day) => {
    if (!day.isDisabled){
        selectedDate.value = day.date;
        formState.value.date = day.date.toISOString().split('T')[0];
        selectedTime.value = '';
        formState.value.time = '';
        isDateDropdownOpen.value = false;
    }
};

const timeSlots = computed(() => {
    const slots = [];
    const now = new Date();
    const isToday = selectedDate.value.toDateString() === new Date(now).toDateString();
    for (let h=0; h<24; h++){
        for (let m=0;m<60;m+=30){
            const timeString = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
            let isDisabled = false;
            if (isToday){
                if (h < now.getHours() || (h === now.getHours() && m < now.getMinutes())) isDisabled = true;
            }
            slots.push({ time: timeString, isDisabled });
        }
    }
    return slots;
});

const selectTime = (slot) => {
    if (!slot.isDisabled){
        selectedTime.value = slot.time;
        formState.value.time = slot.time;
        isTimeDropdownOpen.value = false;
    }
};

// Close pickers when clicking outside (improves mobile behavior)
const closePickersOnOutsideClick = (event) => {
    if (isDateDropdownOpen.value && !event.target.closest('#date-picker-trigger') && !event.target.closest('.date-picker-content')) {
        isDateDropdownOpen.value = false;
    }
    if (isTimeDropdownOpen.value && !event.target.closest('#time-picker-trigger') && !event.target.closest('.time-picker-content')) {
        isTimeDropdownOpen.value = false;
    }
    if (isBrandOpen.value && !event.target.closest('#brand-trigger') && !event.target.closest('.brand-dropdown-content')) {
        isBrandOpen.value = false;
    }
    if (isTypeOpen.value && !event.target.closest('#type-trigger') && !event.target.closest('.type-dropdown-content')) {
        isTypeOpen.value = false;
    }
    if (isDomicileOpen.value && !event.target.closest('#domicile-trigger') && !event.target.closest('.domicile-dropdown-content')) {
        isDomicileOpen.value = false;
    }
    if (isStationOpen.value && !event.target.closest('#station-trigger') && !event.target.closest('.station-dropdown-content')) {
        isStationOpen.value = false;
    }
    if (isPortOpen.value && !event.target.closest('#port-trigger') && !event.target.closest('.port-dropdown-content')) {
        isPortOpen.value = false;
    }
    if (isDurationOpen.value && !event.target.closest('#duration-trigger') && !event.target.closest('.duration-dropdown-content')) {
        isDurationOpen.value = false;
    }
};

onMounted(async () => {
    document.body.addEventListener('click', closePickersOnOutsideClick);

    // Handle back button on mobile to close modals instead of navigating away
    window.addEventListener('popstate', handleBackButton);

    try {
        const L = await loadLeaflet();

        // initialize map centered on Batam
        map = L.map('map').setView([1.126, 104.030], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // create a Google-like SVG pin (green)
        const pinSvg = encodeURIComponent(`
            <svg width="32" height="48" viewBox="0 0 24 36" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 0C7 0 3.5 3.5 3.5 8.5 3.5 15.5 12 25.5 12 25.5s8.5-10 8.5-17C20.5 3.5 17 0 12 0z" fill="#00C853"/>
              <circle cx="12" cy="8.5" r="3.5" fill="white"/>
            </svg>
        `);
        const iconUrl = `data:image/svg+xml;charset=UTF-8,${pinSvg}`;

        const customIcon = L.icon({
            iconUrl,
            iconSize: [28, 42],
            iconAnchor: [14, 42],
            popupAnchor: [0, -38]
        });

        // add markers for available stations with coordinates using dynamic colored icons
        stations.value.filter(s => s.status === 'Tersedia' && s.lat && s.lng).forEach(s => {
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
            marker.bindPopup(`
                <div class="font-medium">${s.name}</div>
                <div class="text-sm text-gray-600">${s.location}</div>
                <div class="text-sm text-gray-500">Status: ${s.status}</div>
                <div class="text-sm" style="color: ${getMarkerColor(s.chargers)};">Charger: ${s.chargers.join(', ')}</div>
                <button onclick="openMapsLocation(${s.lat}, ${s.lng})" class="mt-2 w-full py-2 px-4 bg-[#00C853] text-white font-medium rounded-lg hover:bg-[#00A142] transition duration-200 shadow-md flex items-center justify-center space-x-2 text-sm">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Lihat Lokasi</span>
                </button>
            `);
        });
    } catch (err) {
        console.error('Failed to load Leaflet:', err);
    }
});

const handleBackButton = (event) => {
    if (showReceiptModal.value) {
        closeReceiptModal();
        event.preventDefault();
    } else if (showQrisPaymentModal.value) {
        cancelProcess();
        event.preventDefault();
    } else if (showConfirmationModal.value) {
        cancelProcess();
        event.preventDefault();
    } else if (showSearchModal.value) {
        closeModal();
        event.preventDefault();
    } else if (hasStartedBooking.value) {
        // Jika sudah mulai booking tapi tidak ada modal terbuka, tetap di halaman map
        event.preventDefault();
    }
    // Jika belum mulai booking, biarkan navigasi normal (kembali ke halaman sebelumnya)
};

onBeforeUnmount(() => {
    document.body.removeEventListener('click', closePickersOnOutsideClick);
    // Reset body styles to prevent stuck scrolling on mobile when navigating away
    document.body.classList.remove('modal-open');
    document.body.style.top = '';
});

// Watch for modal state to prevent background scrolling on mobile
watch(anyModalOpen, (isOpen) => {
    if (window.innerWidth <= 768) { // Only on mobile
        if (isOpen) {
            const scrollY = window.scrollY;
            document.body.style.top = `-${scrollY}px`;
            document.body.classList.add('modal-open');
        } else {
            document.body.classList.remove('modal-open');
            const scrollY = parseInt(document.body.style.top || '0');
            document.body.style.top = '';
            window.scrollTo(0, -scrollY);
        }
    }
});

// Buat fungsi global untuk digunakan di popup
window.openMapsLocation = (lat, lng) => {
    const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    if (isMobile) {
        // Untuk mobile, gunakan Google Maps URL scheme yang lebih kompatibel
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    // Gunakan Google Maps URL scheme untuk Android/iOS dengan directions
                    const url = `https://www.google.com/maps/dir/${userLat},${userLng}/${lat},${lng}`;
                    window.open(url, '_blank');
                },
                (error) => {
                    console.warn('Geolocation error:', error);
                    // Fallback: buka Google Maps dengan tujuan saja
                    const url = `https://www.google.com/maps?q=${lat},${lng}`;
                    window.open(url, '_blank');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 menit
                }
            );
        } else {
            // Fallback jika geolocation tidak didukung
            const url = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(url, '_blank');
        }
    } else {
        // Untuk desktop, coba dapatkan lokasi user dan buat rute otomatis
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    // Buat URL dengan rute dari lokasi user ke stasiun
                    const url = `https://www.google.com/maps/dir/${userLat},${userLng}/${lat},${lng}`;
                    window.open(url, '_blank');
                },
                (error) => {
                    console.warn('Geolocation error:', error);
                    // Fallback: buka Google Maps dengan tujuan saja
                    const url = `https://www.google.com/maps?q=${lat},${lng}`;
                    window.open(url, '_blank');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 menit
                }
            );
        } else {
            // Fallback jika geolocation tidak didukung
            const url = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(url, '_blank');
        }
    }
};



const formatRupiah = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0, }).format(amount);
};
const calculateTotal = (price, fee) => {
    return price + fee;
};
const calculateTotalFormatted = computed(() => {
    if (selectedStation.value) {
        const durationMultiplier = durationOptions.find(d => d.value === selectedDuration.value)?.multiplier || 1;
        const adjustedPrice = selectedStation.value.price * durationMultiplier;
        const adjustedFee = selectedStation.value.serviceFee * durationMultiplier;
        return formatRupiah(calculateTotal(adjustedPrice, adjustedFee));
    }
    return '';
});

// Fungsi untuk menampilkan modal konfirmasi
const reserveStation = (stationId) => {
    const station = stations.value.find(s => s.id === stationId);
    if (station && station.isBookable) {
        selectedStation.value = station;
        showConfirmationModal.value = true;
        history.pushState({modal: 'confirmation'}, '', window.location.href);
    }
};

// Fungsi untuk membatalkan proses (dari modal konfirmasi atau pembayaran)
const cancelProcess = () => {
    showConfirmationModal.value = false;
    showQrisPaymentModal.value = false;
    selectedStation.value = null;
    selectedPort.value = '';
    selectedDuration.value = '60'; // Reset to default
};

// Fungsi setelah user mengklik "Ya, Pesan" di modal konfirmasi
const proceedToPayment = () => {
    showConfirmationModal.value = false; // Tutup modal konfirmasi
    showQrisPaymentModal.value = true;  // Buka modal pembayaran QRIS
    history.pushState({modal: 'payment'}, '', window.location.href);
};

// Fungsi setelah user mengklik "Konfirmasi Pembayaran" di modal QRIS
const confirmPayment = () => {
    // Di sini akan ada logika sebenarnya untuk memverifikasi pembayaran.
    // Untuk demo, kita langsung asumsikan berhasil.
    console.log(`Pembayaran dikonfirmasi untuk booking: ${selectedStation.value.bookingNumber}`);
    showQrisPaymentModal.value = false; // Tutup modal QRIS
    showReceiptModal.value = true;      // Buka modal struk
};

// Fungsi untuk menutup modal struk akhir
const closeReceiptModal = () => {
    showReceiptModal.value = false;
    selectedStation.value = null; // Reset data stasiun
    if (history.state && history.state.modal) {
        history.back(); // Kembali ke state sebelumnya jika ada modal
    }
};

// buka tampilan PrintStrukPembayaran dari tombol "Unduh Struk"
const openPrintStruk = () => {
    // tutup modal struk jika masih terbuka, lalu navigasi ke halaman print struk
    showReceiptModal.value = false;
    // navigasi ke halaman PrintStrukPembayaran dengan data station dan total
    window.location.href = `/print-struk?station=${encodeURIComponent(JSON.stringify(selectedStation.value))}&total=${encodeURIComponent(calculateTotalFormatted.value)}`;
};

// Fungsi untuk menutup modal print struk
const closePrintStruk = () => {
    showPrintModal.value = false;
    selectedStation.value = null;
};

// Fungsi untuk membuat QR code di struk (placeholder)
const generateQrisQrCode = () => {
    // Dalam implementasi nyata, ini akan menjadi library QR code dengan data QRIS
    return 'QRIS_PAYMENT_DATA_FOR_' + selectedStation.value.bookingNumber;
};

// Fungsi untuk membuat QR code untuk charging (placeholder)
const generateChargingQrCode = () => {
    // Ini adalah QR code yang berbeda untuk digunakan di stasiun charging.
    return 'CHARGING_SESSION_DATA_FOR_' + selectedStation.value.bookingNumber;
};

// Utility untuk memformat tanggal bookingTime
const formatBookingDate = (dateTime) => {
    const [date, time] = dateTime.split(' ');
    return `${date} ${time}`;
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

// Helper function untuk mendapatkan URL Maps berdasarkan device
const getMapsUrl = (lat, lng) => {
    const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobile) {
        // Gunakan geo scheme untuk membuka aplikasi maps default (biasanya Google Maps)
        return `geo:${lat},${lng}`;
    } else {
        // Untuk desktop, buka Google Maps di browser
        return `https://www.google.com/maps?q=${lat},${lng}`;
    }
};
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
                            value="Cari Stasiun Lainnya..."
                        >
                        <i class="fas fa-search absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div class="relative w-full mb-8 rounded-xl shadow-lg overflow-hidden border border-gray-200" style="height: 450px; background-color: #e9f5ff;">
                    <!-- Dummy map background -->
                    <div class="absolute inset-0 bg-[url('/images/map-grid.png')] bg-cover bg-center opacity-40"></div>

                    <!-- Map content -->
                    <div class="absolute inset-0">
                        <div class="w-full h-full relative">
                            <!-- Center label -->
                            <div class="absolute left-1/2 top-4 transform -translate-x-1/2 text-center">
                                <h3 class="text-lg font-medium text-gray-800">Peta Sementara (Dummy)</h3>
                                <p class="text-sm text-gray-600">Markers are sample locations — interaksi akan dilakukan di implementasi nyata.</p>
                            </div>

                            <!-- Leaflet map will render here -->
                            <div id="map" class="w-full h-full"></div>
                        </div>
                    </div>
                </div>

                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Stasiun Charging Terdekat</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="station in nearestStations" :key="station.id"
                         class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 transition duration-300 hover:shadow-xl relative"
                         :class="{ 'border-[#00C853] ring-1 ring-[#00C853]': selectedStation && selectedStation.id === station.id && (showQrisPaymentModal || showReceiptModal) }">



                        <h2 class="text-xl font-semibold text-gray-900 mb-1">{{ station.name }}</h2>
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    {{ station.location }}
                                </div>
                                <span>{{ station.distance }}</span>
                            </div>

                        <div class="space-y-2 mb-6 text-sm">
                            <!-- Use a two-column grid so labels align flush-left with the pricing section -->
                            <div class="grid grid-cols-2 gap-4 items-center text-gray-700">
                                <div class="flex items-center font-medium text-gray-900">
                                    <i class="fas fa-bolt mr-2 text-yellow-600"></i>
                                    <span>Jenis Charger:</span>
                                </div>
                                <div class="text-left font-medium">{{ station.chargers.join(', ') }} &bull; {{ station.power }}</div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 space-y-2 mb-4 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Harga Charging:</span>
                                <span class="font-medium">{{ formatRupiah(station.price) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Biaya Layanan:</span>
                                <span class="font-medium">{{ formatRupiah(station.serviceFee) }}</span>
                            </div>
                            <div class="flex justify-between items-center font-semibold text-lg p-3 rounded-lg shadow-sm" style="background: linear-gradient(180deg,#f7ffe6,#e6ffb3);">
                                <span class="text-gray-900">Total Harga:</span>
                                <span class="text-gray-900">{{ formatRupiah(calculateTotal(station.price, station.serviceFee)) }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button
                                @click="reserveStation(station.id)"
                                :disabled="!station.isBookable"
                                :class="[
                                    'w-full py-3 rounded-xl text-white font-semibold transition duration-200 shadow-md flex items-center justify-center space-x-2',
                                    station.isBookable ? 'bg-[#00C853] hover:bg-[#00A142]' : 'bg-gray-300 text-gray-600 cursor-not-allowed'
                                ]"
                            >
                                <i class="fas fa-ticket-alt mr-2"></i>
                                <span>Pesan Tiket</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <Footer />
        
        <Transition name="fade">
            <div v-if="showSearchModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[99999] p-4 overflow-y-auto" @click.self="closeModal">
                <div class="bg-white rounded-xl p-8 shadow-2xl w-full max-w-2xl transform transition-all duration-300">
                    <h3 class="text-2xl font-medium text-gray-900 mb-6">Cari Jadwal Pengecasan</h3>
                    
                    <form @submit.prevent="submitSearch" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- BRAND: custom dropdown -->
                            <div class="relative">
                                <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Merk Mobil</label>
                                <div id="brand-trigger"
                                     @click.stop="openOnly('brand')"
                                     class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isBrandOpen}"
                                >
                                    <span class="text-gray-800">{{ formState.brand || 'Pilih Merk Mobil' }}</span>
                                    <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isBrandOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>

                                <div v-if="isBrandOpen" @click.stop class="brand-dropdown-content absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
                                    <div class="py-2">
                                        <div v-for="opt in brandOptions" :key="opt"
                                             @click="selectOption('brand', opt)"
                                             class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                                             :class="{'bg-lime-50 font-semibold text-lime-800': formState.brand === opt}">
                                            {{ opt }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TYPE: custom dropdown -->
                            <div class="relative">
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Mobil</label>
                                <div id="type-trigger"
                                     @click.stop="openOnly('type')"
                                     class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isTypeOpen}"
                                >
                                    <span class="text-gray-800">{{ formState.type || 'Pilih Tipe Mobil' }}</span>
                                    <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isTypeOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>

                                <div v-if="isTypeOpen" @click.stop class="type-dropdown-content absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
                                    <div class="py-2">
                                        <div v-for="opt in typeOptions" :key="opt"
                                             @click="selectOption('type', opt)"
                                             class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                                             :class="{'bg-lime-50 font-semibold text-lime-800': formState.type === opt}">
                                            {{ opt }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DOMICILE: custom dropdown -->
                            <div class="relative">
                                <label for="domicile" class="block text-sm font-medium text-gray-700 mb-1">Domisili</label>
                                <div id="domicile-trigger"
                                     @click.stop="openOnly('domicile')"
                                     class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isDomicileOpen}"
                                >
                                    <span class="text-gray-800">{{ formState.domicile || 'Pilih Domisili' }}</span>
                                    <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isDomicileOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>

                                <div v-if="isDomicileOpen" @click.stop class="domicile-dropdown-content absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
                                    <div class="py-2">
                                        <div v-for="opt in domicileOptions" :key="opt"
                                             @click="selectOption('domicile', opt)"
                                             class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                                             :class="{'bg-lime-50 font-semibold text-lime-800': formState.domicile === opt}">
                                            {{ opt }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- STATION: custom dropdown -->
                            <div class="relative">
                                <label for="station" class="block text-sm font-medium text-gray-700 mb-1">Stasiun Charger</label>
                                <div id="station-trigger"
                                     @click.stop="openOnly('station')"
                                     class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isStationOpen}"
                                >
                                    <span class="text-gray-800">{{ formState.station || 'Pilih Stasiun' }}</span>
                                    <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isStationOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>

                                <div v-if="isStationOpen" @click.stop class="station-dropdown-content absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
                                    <div class="py-2">
                                        <div v-for="opt in stationOptions" :key="opt"
                                             @click="selectOption('station', opt)"
                                             class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                                             :class="{'bg-lime-50 font-semibold text-lime-800': formState.station === opt}">
                                            {{ opt }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-3 flex justify-end space-x-3">
                            <button type="button" @click="closeModal" class="px-6 py-3 text-sm text-gray-600 hover:text-gray-900 transition duration-300 rounded-lg">
                                Batal
                            </button>
                            <button type="submit" 
                                class="bg-[#00C853] text-white font-medium px-8 py-3 rounded-lg hover:bg-[#00A142] transition duration-300 shadow-md">
                                Cari Stasiun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="showConfirmationModal && selectedStation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[99999] p-4 overflow-y-auto" @click.self="cancelProcess">
                <div class="bg-white rounded-lg p-6 shadow-2xl w-full max-w-lg transform transition-all duration-300">
                    <h3 class="text-xl font-medium text-gray-900 mb-6">Anda yakin ingin memesan tiket ini?</h3>

                    <!-- Port Selection -->
                    <div class="relative mb-4">
                        <label for="port" class="block text-sm font-medium text-gray-700 mb-2">Pilih Port Charging</label>
                        <div id="port-trigger"
                             @click.stop="openOnly('port')"
                             class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                             :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isPortOpen}"
                        >
                            <span class="text-gray-800">{{ selectedPort ? portOptions.find(p => p.value === selectedPort)?.label : 'Pilih Port' }}</span>
                            <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isPortOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>

                        <div v-if="isPortOpen" @click.stop class="port-dropdown-content absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto" :class="{ 'show': isPortOpen }">
                            <div class="py-2">
                                <div v-for="port in portOptions" :key="port.value"
                                     @click="selectOption('port', port.value)"
                                     class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                                     :class="{'bg-lime-50 font-semibold text-lime-800': selectedPort === port.value}">
                                    {{ port.label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Duration Selection -->
                    <div class="relative mb-4">
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Pilih Durasi Charging</label>
                        <div id="duration-trigger"
                             @click.stop="openOnly('duration')"
                             class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                             :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': isDurationOpen}"
                        >
                            <span class="text-gray-800">{{ durationOptions.find(d => d.value === selectedDuration)?.label || 'Pilih Durasi' }}</span>
                            <svg class="w-5 h-5 text-gray-500 transform" :class="{'rotate-180': isDurationOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>

                        <div v-if="isDurationOpen" @click.stop class="duration-dropdown-content absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto" :class="{ 'show': isDurationOpen }">
                            <div class="py-2">
                                <div v-for="duration in durationOptions" :key="duration.value"
                                     @click="selectOption('duration', duration.value)"
                                     class="px-4 py-2 hover:bg-lime-50 cursor-pointer transition-colors duration-150"
                                     :class="{'bg-lime-50 font-semibold text-lime-800': selectedDuration === duration.value}">
                                    {{ duration.label }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 text-gray-700">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-gray-400 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium">{{ selectedStation.name }}</p>
                                <p class="text-sm">{{ selectedStation.location }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-gray-400 mr-3"></i>
                            <p class="text-sm">{{ formatBookingDate(selectedStation.bookingTime) }} ({{ selectedDuration }} menit)</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-bolt text-yellow-600 mr-3"></i>
                            <p class="text-sm">{{ selectedPort ? `Port ${selectedPort.split('-')[1]}` : 'Port belum dipilih' }}</p>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                            <div class="flex items-center">
                                <i class="fas fa-dollar-sign text-xl text-gray-500 mr-2"></i>
                                <span class="text-lg font-medium text-gray-900">Total Harga:</span>
                            </div>
                            <span class="text-xl font-medium text-[#00C853]">{{ calculateTotalFormatted }}</span>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end space-x-3">
                        <button type="button" @click="cancelProcess" class="px-6 py-3 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-300">
                            Batal
                        </button>
                        <button type="button" @click="proceedToPayment" :disabled="!selectedPort"
                            class="bg-[#00C853] text-white font-medium px-8 py-3 rounded-lg hover:bg-[#00A142] transition duration-300 shadow-md disabled:bg-gray-300 disabled:cursor-not-allowed">
                            Ya, Pesan
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="showQrisPaymentModal && selectedStation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[99999] p-4 overflow-y-auto" @click.self="cancelProcess">
                <div class="bg-white rounded-lg p-4 shadow-2xl w-full max-w-2xl transform transition-all duration-300">
                    <div class="flex justify-between items-center pb-2">
                        <div class="flex items-center bg-[#FFFBEB] text-[#9A6A01] px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-clock mr-2"></i> <span>Menunggu Pembayaran</span>
                        </div>
                        <button @click="cancelProcess" class="text-gray-400 hover:text-gray-600 text-2xl font-light">
                            &times;
                        </button>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Pembayaran QRIS</h3>
                    
                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="w-full md:w-1/2 p-4 rounded-xl flex flex-col items-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=192x192&data=QRIS%20Payment%20Dummy" alt="QRIS Payment Code" class="w-48 h-48 rounded-lg shadow-inner">
                            <p class="text-center text-sm text-gray-600 mt-3">Scan QR code dengan aplikasi pembayaran Anda</p>
                        </div>
                        
                        <div class="w-full md:w-1/2 space-y-3">
                            <div>
                                <div class="text-sm font-medium text-gray-700">Nomor Booking</div>
                                <div class="text-lg font-semibold text-[#00C853]">{{ selectedStation.bookingNumber }}</div>
                            </div>
                            <div class="space-y-2 pt-2 border-t border-gray-100">
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-500 mt-1 mr-3"></i>
                                    <div>
                                        <p class="font-medium text-gray-900">Lokasi Charging</p>
                                        <p class="text-sm text-gray-600">{{ selectedStation.name }}</p>
                                        <p class="text-sm text-gray-600">{{ selectedStation.location }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-gray-500 mr-3"></i>
                                    <p class="text-sm text-gray-600">{{ formatBookingDate(selectedStation.bookingTime) }} ({{ selectedDuration }} menit)</p>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-bolt text-yellow-600 mr-3"></i>
                                    <p class="text-sm text-gray-600">Port: {{ selectedPort ? `Port ${selectedPort.split('-')[1]}` : 'Port belum dipilih' }} &bull; {{ selectedStation.chargers.join(', ') }} &bull; {{ selectedStation.power }}</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center bg-[#E6FFB3] p-3 rounded-lg mt-4">
                                <div class="flex items-center text-lg text-gray-900">
                                    <i class="fas fa-dollar-sign mr-2"></i> <span class="font-semibold">Total Pembayaran :</span>
                                </div>
                                <span class="text-xl font-semibold text-gray-900">{{ calculateTotalFormatted }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button @click="confirmPayment" 
                            class="w-full py-3 bg-[#00C853] text-white font-semibold rounded-xl hover:bg-[#00A142] transition duration-300 shadow-md flex items-center justify-center space-x-2">
                            <i class="fas fa-check-circle"></i> <span>Konfirmasi Pembayaran</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="showReceiptModal && selectedStation" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[99999] p-4 overflow-y-auto" @click.self="closeReceiptModal">
                <div class="bg-white rounded-xl p-4 shadow-2xl w-full max-w-xl transform transition-all duration-300">
                    
                    <div class="flex justify-between items-center pb-2">
                        <div class="flex items-center bg-[#D4EDDA] text-[#008000] px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-check-circle mr-2"></i> <span>Pembayaran Berhasil</span>
                        </div>
                        <button @click="closeReceiptModal" class="text-gray-400 hover:text-gray-600 text-2xl font-light">
                            &times;
                        </button>
                    </div>

                    <h3 class="text-xl font-medium text-gray-900 mb-4 text-center">Struk Pembayaran</h3>
                    
                    <div class="flex flex-col md:flex-row gap-4 items-center">
                        <div class="w-full md:w-1/2 p-4 rounded-xl flex flex-col items-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=192x192&data=QR%20Charging%20Dummy" alt="QR Charging Code" class="w-48 h-48 rounded-lg shadow-inner">
                            <p class="text-center text-sm text-gray-600 mt-3">Scan QR code ini saat tiba di lokasi charging</p>
                        </div>

                        <div class="w-full md:w-1/2 space-y-3">
                            <div>
                                <div class="text-sm font-medium text-gray-700">Nomor Booking</div>
                                <div class="text-lg font-medium text-[#00C853]">{{ selectedStation.bookingNumber }}</div>
                            </div>
                            <div class="space-y-2 pt-2 border-t border-gray-100">
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-500 mt-1 mr-3"></i>
                                    <div>
                                        <p class="font-medium text-gray-900">Lokasi</p>
                                        <p class="text-sm text-gray-600">{{ selectedStation.name }}</p>
                                        <p class="text-sm text-gray-600">{{ selectedStation.location }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-gray-500 mr-3"></i>
                                    <p class="text-sm text-gray-600">{{ formatBookingDate(selectedStation.bookingTime) }} ({{ selectedDuration }} menit)</p>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-bolt text-yellow-600 mr-3"></i>
                                    <p class="text-sm text-gray-600">Port: {{ selectedPort ? `Port ${selectedPort.split('-')[1]}` : 'Port belum dipilih' }} &bull; {{ selectedStation.chargers.join(', ') }} &bull; {{ selectedStation.power }}</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center bg-[#E6FFB3] p-3 rounded-lg mt-4">
                                <div class="flex items-center text-lg text-gray-900">
                                    <i class="fas fa-dollar-sign mr-2"></i> <span class="font-medium">Total Harga</span>
                                </div>
                                <span class="text-xl font-medium text-gray-900">{{ calculateTotalFormatted }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button @click="openPrintStruk" class="w-full py-3 bg-[#00C853] text-white font-medium rounded-xl hover:bg-[#00A142] transition duration-300 shadow-md flex items-center justify-center space-x-2">
                            <i class="fas fa-download"></i> <span>Unduh Struk</span>
                        </button>
                        <div class="bg-[#FFFBEB] text-[#9A6A01] p-3 rounded-lg text-sm text-center mt-3 font-medium">
                            Terima kasih telah menggunakan layanan kami!
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Ikon menggunakan warna yang sesuai dengan desain E-VOLT */
.fa-bolt { color: #f59e0b; /* kuning */ }
.fa-clock { color: #3b82f6; /* biru */ } /* Digunakan juga untuk "Menunggu Pembayaran" */
.fa-hourglass-half { color: #8b5cf6; /* ungu */ }
.fa-check-circle { color: #008000; } /* hijau untuk sukses */
.fa-dollar-sign { color: #00C853; } /* hijau E-VOLT untuk icon total harga */

/* Smooth dropdown animations */
.brand-dropdown-content,
.type-dropdown-content,
.domicile-dropdown-content,
.station-dropdown-content,
.port-dropdown-content,
.duration-dropdown-content {
    transform: translateY(-10px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}

.brand-dropdown-content.show,
.type-dropdown-content.show,
.domicile-dropdown-content.show,
.station-dropdown-content.show,
.port-dropdown-content.show,
.duration-dropdown-content.show {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
}

/* Enhanced dropdown item animations */
.brand-dropdown-content .px-4,
.type-dropdown-content .px-4,
.domicile-dropdown-content .px-4,
.station-dropdown-content .px-4,
.port-dropdown-content .px-4,
.duration-dropdown-content .px-4 {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateX(0);
}

.brand-dropdown-content .px-4:hover,
.type-dropdown-content .px-4:hover,
.domicile-dropdown-content .px-4:hover,
.station-dropdown-content .px-4:hover,
.port-dropdown-content .px-4:hover,
.duration-dropdown-content .px-4:hover {
    transform: translateX(4px);
    background-color: #ecfdf5;
}

/* Smooth icon rotation */
.transform {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Animasi untuk Modal */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>

<style>
/* Prevent background scrolling when modal is open on mobile only */
@media (max-width: 768px) {
    .modal-open {
        overflow: hidden;
        position: fixed;
        width: 100%;
    }
}
</style>
