<script setup>
import { ref, computed, onMounted, onBeforeUnmount, reactive, watch, nextTick } from 'vue';
import { Link, usePage, router, Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';

// --- 1. GLOBAL STATE & USER DATA ---
const page = usePage();
const user = computed(() => page.props.auth?.user || {});

// Wajib menambahkan default () => [] agar tidak error jika backend terlambat mengirim data
const props = defineProps({
    stations: {
        type: Array,
        default: () => []
    }
});

// --- 2. DATABASE SPESIFIKASI MOBIL (EV SPECS) ---
const evSpecs = {
    'Hyundai': {
        'Ioniq 5': { 'Prime Standard': { battery: 58.0, range: 384 }, 'Signature Long Range': { battery: 72.6, range: 451 } },
        'Ioniq 6': { 'Signature': { battery: 77.4, range: 519 } },
        'Kona Electric': { 'Signature': { battery: 65.4, range: 505 } }
    },
    'Wuling': {
        'Air EV': { 'Lite': { battery: 17.3, range: 200 }, 'Long Range': { battery: 26.7, range: 300 } },
        'BinguoEV': { 'Premium': { battery: 37.9, range: 333 } },
        'Cloud EV': { '460km': { battery: 50.6, range: 460 } }
    },
    'BYD': {
        'Dolphin': { 'Premium': { battery: 60.4, range: 490 } },
        'Atto 3': { 'Superior': { battery: 60.48, range: 480 } },
        'Seal': { 'Premium': { battery: 82.56, range: 650 } },
        'M6': { 'Superior': { battery: 71.8, range: 530 } }
    },
    'Tesla': {
        'Model 3': { 'Performance': { battery: 78.0, range: 500 } },
        'Model Y': { 'Long Range': { battery: 80.0, range: 530 } }
    },
    'Jaecoo': {
        'J5 EV': { 'Standard': { battery: 60.9, range: 461 }, 'Premium': { battery: 60.9, range: 461 } },
        'J7 PHEV': { 'Premium': { battery: 18.3, range: 90 } },
        'J8 PHEV': { 'Premium': { battery: 34.5, range: 100 } }
    },
    'MG': {
        '4 EV': { 'Magnify': { battery: 64.0, range: 425 } },
        'S5': { 'Long Range': { battery: 62.2, range: 525 } }
    }
};

const brandOptions = Object.keys(evSpecs);
const domicileOptions = ['Batam Center', 'Nagoya', 'Harbour Bay', 'Lubuk Baja', 'Batu Aji'];

// --- 3. BATTERY & RANGE LOGIC (TERSINKRONISASI LOKAL) ---
const batteryLevel = ref(65);
// FIX #1: Baterai ini adalah INPUT MANUAL dari user (bukan data live dari kendaraan/IoT).
// Diberi label & ikon info yang jelas di UI agar user tidak mengira ini data otomatis.
const isBatteryManual = true;

// Menyimpan nilai baterai ke memory browser setiap ada perubahan
watch(batteryLevel, (newVal) => {
    localStorage.setItem('evolt_user_battery', newVal);
});

const maxRangeKm = computed(() => {
    if (user.value?.max_range) return user.value.max_range;

    if (user.value?.car_brand && user.value?.car_series) {
        const seriesData = evSpecs[user.value.car_brand]?.[user.value.car_series];
        if (seriesData) {
            const firstVariant = Object.values(seriesData)[0];
            if (firstVariant && firstVariant.range) return firstVariant.range;
        }
    }
    return 380; // Default
});

const estimatedRange = computed(() => Math.round((batteryLevel.value / 100) * maxRangeKm.value));
const neededToFull = computed(() => 100 - batteryLevel.value);

const batteryColor = computed(() => {
    if (batteryLevel.value <= 20) return 'text-red-600';
    if (batteryLevel.value <= 50) return 'text-orange-600';
    return 'text-slate-900';
});

// --- 4. GPS & LOGIKA JARAK ---
const userLocation = ref(null);
const domicileCoordinates = { 'Batam Center': { lat: 1.1301, lng: 104.0529 } };

const calculateDistance = (lat1, lon1, lat2, lon2) => {
    if (!lat1 || !lon1 || !lat2 || !lon2) return 999.9;
    const R = 6371; const dLat = (lat2 - lat1) * (Math.PI / 180); const dLon = (lon2 - lon1) * (Math.PI / 180);
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return parseFloat((R * c).toFixed(1));
};

const safeParseArray = (data) => {
    if (!data) return [];
    if (Array.isArray(data)) return data;
    if (typeof data === 'string') { try { return JSON.parse(data); } catch (e) { return [data]; } }
    return [];
};

// FIX #2: Badge status sekarang dinamis mengikuti nilai status stasiun, bukan selalu hijau.
const STATUS_STYLE_MAP = {
    'tersedia':  { classes: 'bg-green-100 text-green-700',  label: 'Tersedia' },
    'available': { classes: 'bg-green-100 text-green-700',  label: 'Tersedia' },
    'ramai':     { classes: 'bg-orange-100 text-orange-700', label: 'Ramai' },
    'penuh':     { classes: 'bg-orange-100 text-orange-700', label: 'Penuh' },
    'perbaikan': { classes: 'bg-red-100 text-red-700',       label: 'Perbaikan' },
    'maintenance': { classes: 'bg-red-100 text-red-700',     label: 'Perbaikan' },
    'tutup':     { classes: 'bg-slate-200 text-slate-600',   label: 'Tutup' },
};

const getStatusStyle = (status) => {
    const key = String(status || 'tersedia').toLowerCase().trim();
    return STATUS_STYLE_MAP[key] || { classes: 'bg-blue-100 text-blue-700', label: status || 'Tersedia' };
};

// FIX #5: Rekomendasi kini juga menghormati filter "Area Lokasi" dari search bar (jika dipilih),
// bukan murni jarak GPS yang independen dari search form. Beri penanda visual saat filter aktif.
const recommendedStations = computed(() => {
    const rawStations = Array.isArray(props.stations) ? props.stations : [];

    // Filter stasiun yang tidak tutup
    let activeStations = rawStations.filter(s => {
        const status = String(s.status || '').toLowerCase();
        return status !== 'tutup';
    });

    // Jika user sudah memilih Area Lokasi di search bar, ikutkan sebagai filter
    if (searchForm.domicile) {
        activeStations = activeStations.filter(s =>
            String(s.address || s.location || '').toLowerCase().includes(searchForm.domicile.toLowerCase())
        );
    }

    // Tentukan titik pusat (GPS User atau default)
    let center = userLocation.value || domicileCoordinates['Batam Center'];

    let mapped = activeStations.map(station => {
        let distStr = 'N/A';
        let distNum = 999.9;

        if (station.lat && station.lng) {
            distNum = calculateDistance(center.lat, center.lng, parseFloat(station.lat), parseFloat(station.lng));
            distStr = distNum + ' km';
        }

        const type = station.type || (safeParseArray(station.chargers_detail)[0]?.tipe) || 'Fast Charging';
        const formattedPrice = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(station.price || 50000);
        const statusStyle = getStatusStyle(station.status);

        return {
            ...station,
            realDistance: distNum,
            displayDistance: distStr,
            displayType: type,
            displayPrice: formattedPrice,
            statusClasses: statusStyle.classes,
            statusLabel: statusStyle.label,
        };
    });

    // Urutkan dari yang terdekat, lalu ambil 3
    mapped.sort((a, b) => a.realDistance - b.realDistance);
    return mapped.slice(0, 3);
});

// --- 5. SEARCH LOGIC ---
const searchForm = reactive({ brand: user.value?.car_brand || '', domicile: '' });
const isSearchDropdownOpen = reactive({ brand: false, domicile: false });
const isSearching = ref(false);

// FIX #6: refs untuk fokus & navigasi keyboard pada dropdown custom
const searchBrandListRef = ref(null);
const searchDomicileListRef = ref(null);
const searchBrandActiveIndex = ref(-1);
const searchDomicileActiveIndex = ref(-1);

const toggleSearchDropdown = (type) => {
    if (type === 'brand') {
        isSearchDropdownOpen.brand = !isSearchDropdownOpen.brand;
        isSearchDropdownOpen.domicile = false;
        if (isSearchDropdownOpen.brand) {
            searchBrandActiveIndex.value = brandOptions.indexOf(searchForm.brand);
            nextTick(() => focusDropdownItem(searchBrandListRef, searchBrandActiveIndex.value));
        }
    } else {
        isSearchDropdownOpen.domicile = !isSearchDropdownOpen.domicile;
        isSearchDropdownOpen.brand = false;
        if (isSearchDropdownOpen.domicile) {
            searchDomicileActiveIndex.value = domicileOptions.indexOf(searchForm.domicile);
            nextTick(() => focusDropdownItem(searchDomicileListRef, searchDomicileActiveIndex.value));
        }
    }
};

const focusDropdownItem = (listRef, index) => {
    const el = listRef.value?.children?.[Math.max(index, 0)];
    if (el && el.focus) el.focus();
};

const selectSearchOption = (type, value) => {
    searchForm[type] = value;
    isSearchDropdownOpen[type] = false;
};

// FIX #6: Navigasi keyboard (ArrowUp/ArrowDown/Enter/Escape) untuk dropdown pencarian
const handleDropdownKeydown = (e, type, options) => {
    const isBrand = type === 'brand';
    const activeIndex = isBrand ? searchBrandActiveIndex : searchDomicileActiveIndex;
    const listRef = isBrand ? searchBrandListRef : searchDomicileListRef;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = Math.min(activeIndex.value + 1, options.length - 1);
        focusDropdownItem(listRef, activeIndex.value);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
        focusDropdownItem(listRef, activeIndex.value);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (options[activeIndex.value]) selectSearchOption(type, options[activeIndex.value]);
    } else if (e.key === 'Escape') {
        isSearchDropdownOpen[type] = false;
    }
};

const handleSearch = () => {
    isSearching.value = true;
    router.get('/map-results', { brand: searchForm.brand, domicile: searchForm.domicile }, { onFinish: () => isSearching.value = false });
};

// Mengarahkan ke peta dengan stasiun yang dipilih
const selectRecommendedStation = (station) => {
    router.get('/map-results', { station: station.name, auto_book_id: station.id });
};

// --- 6. SETUP POPUP LOGIC ---
// FIX #4: Popup kini bisa ditutup sementara ("Nanti saja") tanpa mengisi data,
// disimpan di sessionStorage supaya tidak muncul berulang kali dalam sesi yang sama,
// namun tetap akan muncul lagi di sesi berikutnya selama profil belum lengkap.
const setupSkippedThisSession = sessionStorage.getItem('evolt_setup_skipped') === '1';
const showSetupPopup = ref((!user.value?.car_brand || !user.value?.nomor_plat) && !setupSkippedThisSession);
const setupForm = useForm({ brand: '', series: '', variant: '', plateNumber: '' });
const popupState = reactive({ openBrand: false, openSeries: false });

const setupSeriesOptions = computed(() => setupForm.brand && evSpecs[setupForm.brand] ? Object.keys(evSpecs[setupForm.brand]) : []);

watch(() => setupForm.brand, () => { setupForm.series = ''; setupForm.variant = ''; });
watch(() => setupForm.series, () => { setupForm.variant = ''; });

const selectSetupBrand = (val) => { setupForm.brand = val; popupState.openBrand = false; };
const selectSetupSeries = (val) => { setupForm.series = val; popupState.openSeries = false; };

const skipSetup = () => {
    sessionStorage.setItem('evolt_setup_skipped', '1');
    showSetupPopup.value = false;
};

const submitSetup = () => {
    if (!setupForm.brand || !setupForm.series || !setupForm.plateNumber) { alert("Mohon lengkapi semua data mobil."); return; }

    let specs = { battery: 50, range: 350 };
    const carData = evSpecs[setupForm.brand]?.[setupForm.series];
    if (carData) {
        const variantKey = setupForm.variant || Object.keys(carData)[0];
        if (carData[variantKey]) specs = carData[variantKey];
    }

    setupForm.transform((data) => ({
        ...data,
        nomor_plat: data.plateNumber.toUpperCase(),
        battery_capacity: specs.battery,
        max_range: specs.range
    }))
    .patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            sessionStorage.removeItem('evolt_setup_skipped');
            showSetupPopup.value = false;
            searchForm.brand = setupForm.brand;
        }
    });
};

const closeAllDropdowns = (e) => {
    if (!e.target.closest('.search-dropdown-trigger')) { isSearchDropdownOpen.brand = false; isSearchDropdownOpen.domicile = false; }
    if (showSetupPopup.value && !e.target.closest('.popup-dropdown-trigger')) { popupState.openBrand = false; popupState.openSeries = false; }
};

// --- LIFECYCLE HOOKS ---
onMounted(() => {
    document.addEventListener('click', closeAllDropdowns);

    // Sinkronisasi baterai saat halaman dimuat
    const savedBattery = localStorage.getItem('evolt_user_battery');
    if (savedBattery) {
        batteryLevel.value = Number(savedBattery);
    }

    // Ambil GPS User
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => { userLocation.value = { lat: pos.coords.latitude, lng: pos.coords.longitude }; },
            (err) => { console.log("GPS Ditolak, menggunakan lokasi Batam Center sebagai default."); }
        );
    }
});

onBeforeUnmount(() => document.removeEventListener('click', closeAllDropdowns));
</script>

<template>
    <Head title="Dashboard Pengguna" />

    <div class="min-h-screen flex flex-col bg-gray-50 font-sans text-slate-800">
        <Navbar />

        <main class="flex-grow">
            <!-- HERO SECTION -->
            <section class="bg-[#CCFF00] pt-28 pb-32 lg:pt-36 lg:pb-48 rounded-b-[3rem] lg:rounded-b-[5rem] relative overflow-hidden z-10 shadow-sm">
                <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-white rounded-full blur-[100px]"></div>
                    <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-lime-400 rounded-full blur-[80px]"></div>
                </div>

                <div class="max-w-4xl mx-auto px-6 lg:px-8 relative z-20 text-center">
                    <div v-if="user.car_brand" class="mb-8 inline-flex items-center justify-center bg-slate-900/10 backdrop-blur-md px-5 py-2 rounded-full border border-slate-900/10 shadow-sm animate-fade-in-down">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-900 mr-3 animate-pulse"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-900">
                            {{ user.car_brand }} {{ user.car_series || '' }} • {{ user.nomor_plat }}
                        </span>
                    </div>

                    <h1 class="text-4xl sm:text-6xl font-black text-slate-900 leading-tight mb-4 tracking-tight">
                        Siap Menjelajah?
                    </h1>
                    <!-- FIX #3: Kontras teks dinaikkan dari text-slate-800/70 -> text-slate-900/85 + font-semibold agar lebih terbaca di atas latar lime, terutama di luar ruangan -->
                    <p class="text-lg sm:text-xl font-semibold text-slate-900/85 max-w-2xl mx-auto mb-10">
                        Pantau kondisi baterai dan temukan stasiun pengisian terdekat.
                    </p>

                    <!-- BATERAI WIDGET -->
                    <div class="bg-white/60 backdrop-blur-xl border border-white/50 p-8 rounded-[2.5rem] shadow-xl max-w-md mx-auto hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex justify-between items-end mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-slate-900 rounded-xl text-[#CCFF00] shadow-md">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 12V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-6zM18 9h1a1 1 0 011 1v4a1 1 0 01-1 1h-1V9z"/></svg>
                                </div>
                                <div class="text-left">
                                    <span class="flex items-center gap-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        Sisa Baterai
                                        <!-- FIX #1: penanda bahwa nilai ini input manual, bukan data live -->
                                        <span
                                            v-if="isBatteryManual"
                                            class="group relative inline-flex items-center"
                                            tabindex="0"
                                            role="note"
                                            aria-label="Nilai baterai ini adalah input manual, bukan data langsung dari kendaraan"
                                        >
                                            <svg class="w-3 h-3 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                            <span class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 normal-case tracking-normal text-[11px] font-medium bg-slate-900 text-white rounded-lg px-3 py-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity z-20">
                                                Ini input manual, geser sesuai kondisi baterai mobil Anda saat ini.
                                            </span>
                                        </span>
                                    </span>
                                    <span class="block text-sm font-bold text-slate-800">
                                        Normal
                                        <span class="text-[10px] font-semibold text-slate-500 normal-case">· Estimasi manual</span>
                                    </span>
                                </div>
                            </div>
                            <span class="text-5xl font-black transition-colors duration-300 tracking-tighter" :class="batteryColor">{{ batteryLevel }}<span class="text-2xl align-top">%</span></span>
                        </div>

                        <div class="relative w-full h-4 bg-slate-200 rounded-full mb-4 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full bg-slate-900 rounded-full transition-all duration-300" :style="`width: ${batteryLevel}%`"></div>
                            <input
                                type="range"
                                v-model="batteryLevel"
                                min="0"
                                max="100"
                                aria-label="Atur estimasi sisa baterai secara manual"
                                class="absolute top-0 left-0 w-full h-full opacity-0 cursor-pointer z-10"
                            >
                        </div>

                        <div class="flex justify-between items-center mb-4 px-1">
                            <span class="text-xs font-bold text-slate-500">Butuh untuk Penuh:</span>
                            <span class="text-xs font-bold text-red-500">{{ neededToFull }}% Kapasitas</span>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-slate-900/10">
                            <span class="text-slate-600 font-medium text-sm">Estimasi Jarak</span>
                            <div class="text-right">
                                <span class="text-2xl font-black text-slate-900">{{ estimatedRange }}</span>
                                <span class="text-sm font-bold text-slate-500 ml-1">km</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SEARCH BAR -->
            <div class="relative z-30 px-4 -mt-12 sm:-mt-16 mb-8">
                <div class="max-w-4xl mx-auto">
                    <form @submit.prevent="handleSearch" class="bg-white rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] p-3 border border-gray-100 flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-gray-100 ring-4 ring-white/50">

                        <div class="relative flex-1 search-dropdown-trigger">
                            <div
                                @click.stop="toggleSearchDropdown('brand')"
                                @keydown.enter.prevent="toggleSearchDropdown('brand')"
                                @keydown.space.prevent="toggleSearchDropdown('brand')"
                                @keydown.esc="isSearchDropdownOpen.brand = false"
                                tabindex="0"
                                role="combobox"
                                :aria-expanded="isSearchDropdownOpen.brand"
                                aria-haspopup="listbox"
                                aria-label="Pilih merk mobil"
                                class="h-16 flex items-center px-6 cursor-pointer hover:bg-lime-50/50 rounded-[1.5rem] lg:rounded-l-[1.5rem] lg:rounded-r-none transition-colors group focus:outline-none focus:ring-2 focus:ring-lime-400 focus:ring-inset"
                            >
                                <div class="w-10 h-10 rounded-full bg-lime-100 text-lime-700 flex items-center justify-center mr-4 transition-transform group-hover:scale-110">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 7h-3V6a4 4 0 00-8 0v1H5a3 3 0 00-3 3v7a3 3 0 003 3h14a3 3 0 003-3V10a3 3 0 00-3-3zm-4-1V6a2 2 0 00-4 0v1h4zm5 10a1 1 0 01-1 1H5a1 1 0 01-1-1v-7a1 1 0 011-1h14a1 1 0 011 1v7z"/></svg>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Merk Mobil</span>
                                    <span class="text-base font-bold text-slate-800 truncate">{{ searchForm.brand || 'Pilih Mobil' }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="isSearchDropdownOpen.brand ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div
                                v-if="isSearchDropdownOpen.brand"
                                ref="searchBrandListRef"
                                role="listbox"
                                aria-label="Daftar merk mobil"
                                class="absolute top-full left-0 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 max-h-64 overflow-y-auto z-50"
                            >
                                <div
                                    v-for="(opt, idx) in brandOptions"
                                    :key="opt"
                                    role="option"
                                    :aria-selected="searchForm.brand === opt"
                                    tabindex="0"
                                    @click="selectSearchOption('brand', opt)"
                                    @keydown="handleDropdownKeydown($event, 'brand', brandOptions)"
                                    class="px-5 py-3 hover:bg-lime-50 focus:bg-lime-50 focus:outline-none cursor-pointer font-medium text-slate-600 flex justify-between"
                                >{{ opt }} <span v-if="searchForm.brand === opt" class="text-lime-600">✓</span></div>
                            </div>
                        </div>

                        <div class="relative flex-1 search-dropdown-trigger">
                            <div
                                @click.stop="toggleSearchDropdown('domicile')"
                                @keydown.enter.prevent="toggleSearchDropdown('domicile')"
                                @keydown.space.prevent="toggleSearchDropdown('domicile')"
                                @keydown.esc="isSearchDropdownOpen.domicile = false"
                                tabindex="0"
                                role="combobox"
                                :aria-expanded="isSearchDropdownOpen.domicile"
                                aria-haspopup="listbox"
                                aria-label="Pilih area lokasi"
                                class="h-16 flex items-center px-6 cursor-pointer hover:bg-blue-50/50 transition-colors group focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-inset"
                            >
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4 transition-transform group-hover:scale-110">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Area Lokasi</span>
                                    <span class="text-base font-bold text-slate-800 truncate">{{ searchForm.domicile || 'Semua Area' }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="isSearchDropdownOpen.domicile ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div
                                v-if="isSearchDropdownOpen.domicile"
                                ref="searchDomicileListRef"
                                role="listbox"
                                aria-label="Daftar area lokasi"
                                class="absolute top-full left-0 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 max-h-64 overflow-y-auto z-50"
                            >
                                <div
                                    v-for="(opt, idx) in domicileOptions"
                                    :key="opt"
                                    role="option"
                                    :aria-selected="searchForm.domicile === opt"
                                    tabindex="0"
                                    @click="selectSearchOption('domicile', opt)"
                                    @keydown="handleDropdownKeydown($event, 'domicile', domicileOptions)"
                                    class="px-5 py-3 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none cursor-pointer font-medium text-slate-600 flex justify-between"
                                >{{ opt }} <span v-if="searchForm.domicile === opt" class="text-blue-600">✓</span></div>
                            </div>
                        </div>

                        <div class="p-2">
                            <button type="submit" :disabled="isSearching" class="w-full lg:w-auto h-12 lg:h-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-base px-8 rounded-[1.2rem] shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 min-w-[120px]">
                                <span v-if="!isSearching">Cari</span>
                                <span v-else>...</span>
                                <svg v-if="!isSearching" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- QUICK LINKS -->
            <section class="max-w-4xl mx-auto px-6 mb-16 relative z-20">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <Link href="/scan-qr" class="group bg-white p-4 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md hover:border-lime-300 transition-all cursor-pointer">
                        <div class="w-12 h-12 rounded-2xl bg-lime-50 text-lime-600 flex items-center justify-center group-hover:bg-[#CCFF00] group-hover:text-black transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM6 8V4h4m4 8h4v4h-4v-4zM6 20h4m-4-4v4m4-4h4M6 16v-4h4v4H6z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Scan QR</h3>
                            <p class="text-xs text-slate-500">Charging</p>
                        </div>
                    </Link>

                    <Link href="/history" class="group bg-white p-4 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md hover:border-purple-300 transition-all cursor-pointer">
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-500 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Riwayat</h3>
                            <p class="text-xs text-slate-500">Aktivitas</p>
                        </div>
                    </Link>

                    <Link href="/support" class="group bg-white p-4 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md hover:border-orange-300 transition-all cursor-pointer">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center group-hover:bg-orange-500 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Bantuan</h3>
                            <p class="text-xs text-slate-500">Support</p>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- REKOMENDASI TERDEKAT -->
            <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-32">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-slate-900">Rekomendasi Terdekat</h2>
                        <!-- FIX #5: penjelasan singkat kapan hasil ini berdasarkan GPS vs filter area yang dipilih -->
                        <p class="text-slate-500 mt-1 text-sm">
                            <span v-if="searchForm.domicile">Difilter untuk area <span class="font-semibold text-slate-700">{{ searchForm.domicile }}</span>, diurutkan dari jarak terdekat</span>
                            <span v-else>Berdasarkan lokasi Anda saat ini</span>
                        </p>
                    </div>
                    <Link href="/map-results" class="text-lime-700 font-bold text-sm hover:text-lime-800 hover:underline">Lihat Semua</Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-if="recommendedStations.length === 0" class="col-span-full text-center py-10 text-gray-500">
                        Belum ada stasiun pengisian terdaftar di area ini.
                        <br><span class="text-xs">(Pastikan Controller Laravel telah mengirim data 'stations')</span>
                    </div>

                    <div v-else v-for="station in recommendedStations" :key="station.id"
                        @click="selectRecommendedStation(station)"
                        class="group bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer relative overflow-hidden">

                        <div class="absolute inset-0 bg-lime-50 opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>

                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <div class="p-3 bg-lime-50 rounded-2xl text-lime-700 group-hover:bg-[#CCFF00] group-hover:text-black transition-colors shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <!-- FIX #2: warna badge kini mengikuti status sebenarnya, bukan statis hijau -->
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="station.statusClasses">
                                {{ station.statusLabel }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 mb-1 relative z-10 truncate">{{ station.name }}</h3>
                        <p class="text-slate-500 text-xs mb-4 relative z-10 truncate">{{ station.address || station.location }}</p>

                        <div class="flex items-center gap-4 text-xs font-medium text-slate-600 mb-6 relative z-10">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ station.displayDistance }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ station.displayType }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 relative z-10">
                            <span class="font-bold text-slate-900 text-sm">{{ station.displayPrice }}</span>
                            <button class="bg-slate-900 text-white p-2 rounded-xl group-hover:bg-lime-500 transition-colors shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <Footer />

        <!-- SETUP POPUP -->
        <Transition name="modal-fade">
            <div v-if="showSetupPopup" class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="setup-popup-title">
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="skipSetup"></div>
                <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-8 transform transition-all">
                    <!-- FIX #4: tombol tutup / "Nanti saja" agar popup tidak wajib diisi langsung -->
                    <button
                        @click="skipSetup"
                        type="button"
                        aria-label="Tutup dan isi nanti"
                        class="absolute top-5 right-5 w-9 h-9 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-[#CCFF00] rounded-2xl mx-auto flex items-center justify-center mb-4 shadow-[0_10px_20px_rgba(204,255,0,0.4)]">
                            <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 01 1 1v5m-4 0h4"/></svg>
                        </div>
                        <h2 id="setup-popup-title" class="text-2xl font-bold text-slate-900">Setup Profil</h2>
                        <p class="text-slate-500 mt-2 text-sm">Lengkapi data kendaraan Anda agar estimasi jarak lebih akurat.</p>
                    </div>
                    <form @submit.prevent="submitSetup" class="space-y-4">
                        <div class="relative popup-dropdown-trigger">
                            <div
                                @click.stop="popupState.openBrand = !popupState.openBrand; popupState.openSeries = false"
                                @keydown.enter.prevent="popupState.openBrand = !popupState.openBrand"
                                tabindex="0"
                                role="combobox"
                                :aria-expanded="popupState.openBrand"
                                aria-haspopup="listbox"
                                aria-label="Pilih merk mobil"
                                class="p-4 bg-gray-50 rounded-xl flex justify-between items-center cursor-pointer hover:bg-lime-50 border border-transparent hover:border-lime-200 transition-colors focus:outline-none focus:ring-2 focus:ring-lime-400"
                            >
                                <span :class="setupForm.brand ? 'text-slate-900 font-semibold' : 'text-gray-400'">{{ setupForm.brand || 'Pilih Merk Mobil' }}</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div v-if="popupState.openBrand" role="listbox" aria-label="Daftar merk mobil" class="absolute z-50 top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto">
                                <div v-for="b in brandOptions" :key="b" role="option" :aria-selected="setupForm.brand === b" tabindex="0" @click="selectSetupBrand(b)" @keydown.enter="selectSetupBrand(b)" class="p-3 hover:bg-lime-50 focus:bg-lime-50 focus:outline-none cursor-pointer text-slate-600 font-medium">{{ b }}</div>
                            </div>
                        </div>
                        <div v-if="setupForm.brand" class="relative popup-dropdown-trigger">
                             <div
                                @click.stop="popupState.openSeries = !popupState.openSeries; popupState.openBrand = false"
                                @keydown.enter.prevent="popupState.openSeries = !popupState.openSeries"
                                tabindex="0"
                                role="combobox"
                                :aria-expanded="popupState.openSeries"
                                aria-haspopup="listbox"
                                aria-label="Pilih tipe mobil"
                                class="p-4 bg-gray-50 rounded-xl flex justify-between items-center cursor-pointer hover:bg-lime-50 border border-transparent hover:border-lime-200 transition-colors focus:outline-none focus:ring-2 focus:ring-lime-400"
                            >
                                <span :class="setupForm.series ? 'text-slate-900 font-semibold' : 'text-gray-400'">{{ setupForm.series || 'Pilih Tipe Mobil' }}</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                             <div v-if="popupState.openSeries" role="listbox" aria-label="Daftar tipe mobil" class="absolute z-50 top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto">
                                <div v-for="s in setupSeriesOptions" :key="s" role="option" :aria-selected="setupForm.series === s" tabindex="0" @click="selectSetupSeries(s)" @keydown.enter="selectSetupSeries(s)" class="p-3 hover:bg-lime-50 focus:bg-lime-50 focus:outline-none cursor-pointer text-slate-600 font-medium">{{ s }}</div>
                            </div>
                        </div>
                        <input type="text" v-model="setupForm.plateNumber" placeholder="PLAT NOMOR" aria-label="Plat nomor kendaraan" class="w-full p-4 bg-gray-50 rounded-xl text-slate-900 placeholder-gray-400 font-bold focus:outline-none focus:ring-2 focus:ring-[#CCFF00] uppercase border border-transparent tracking-widest text-center">
                        <button type="submit" :disabled="setupForm.processing" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-slate-800 transition">
                            {{ setupForm.processing ? 'Menyimpan...' : 'Simpan & Lanjutkan' }}
                        </button>
                        <button type="button" @click="skipSetup" class="w-full text-center text-sm font-semibold text-slate-400 hover:text-slate-600 py-2 transition-colors">
                            Nanti saja
                        </button>
                    </form>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.animate-fade-in-down { animation: fadeInDown 0.8s ease-out; }
@keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

/* Slider Styling */
input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 24px;
  width: 24px;
  border-radius: 50%;
  background: #0f172a;
  cursor: pointer;
  margin-top: -8px;
  box-shadow: 0 0 0 4px white, 0 4px 10px rgba(0,0,0,0.3);
}
input[type=range]::-webkit-slider-runnable-track {
  width: 100%;
  height: 8px;
  cursor: pointer;
  background: transparent;
  border-radius: 99px;
}
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
</style>