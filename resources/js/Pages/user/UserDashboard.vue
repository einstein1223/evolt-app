<script setup>
import { ref, computed, onMounted, onBeforeUnmount, reactive, watch } from 'vue';
import { Link, usePage, router, Head, useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';

// --- 1. GLOBAL STATE & USER DATA ---
const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    stations: Array 
});

// --- 2. BATTERY & RANGE LOGIC ---
const batteryLevel = ref(65); 
const maxRangeKm = 380; 

const estimatedRange = computed(() => Math.round((batteryLevel.value / 100) * maxRangeKm));

const batteryColor = computed(() => {
    if (batteryLevel.value <= 20) return 'text-red-600';
    if (batteryLevel.value <= 50) return 'text-orange-600';
    return 'text-slate-900';
});

// --- 3. DATA HANDLING (STASIUN) ---
const recommendedStations = computed(() => {
    if (props.stations && props.stations.length > 0) return props.stations;
    return [
        { id: 1, name: 'SPKLU Batam Centre', address: 'Jl. Engku Putri No.1', distance: '1.2 km', type: 'DC Fast', status: 'Tersedia', price: 'Rp 2.466/kWh' },
        { id: 2, name: 'Grand Batam Mall', address: 'Lubuk Baja Kota', distance: '3.5 km', type: 'AC Charging', status: 'Terisi', price: 'Gratis' },
        { id: 3, name: 'Harris Hotel Batam', address: 'Tering Bay, Nongsa', distance: '8.0 km', type: 'DC Fast', status: 'Tersedia', price: 'Rp 2.466/kWh' },
    ];
});

// --- 4. DATA SETUP MOBIL ---
const brandOptions = ['Hyundai', 'Wuling', 'Tesla', 'BYD', 'Kia', 'Toyota', 'MG', 'BMW'];
const domicileOptions = ['Batam Center', 'Nagoya', 'Harbour Bay', 'Lubuk Baja', 'Batu Aji'];

const carDatabase = {
    'Hyundai': { 'Ioniq 5': ['Prime', 'Signature'], 'Kona': ['Signature'] },
    'Wuling':  { 'Air EV': ['Long Range', 'Standard'], 'Binguo': ['Premium'] },
    'Tesla':   { 'Model 3': ['Performance'], 'Model Y': ['Long Range'] },
    'BYD':     { 'Seal': ['Performance'], 'Atto 3': ['Superior'] },
};

// --- 5. SEARCH LOGIC ---
const searchForm = reactive({ brand: user.value?.car_brand || '', domicile: '' });
const isSearchDropdownOpen = reactive({ brand: false, domicile: false });
const isSearching = ref(false);

const toggleSearchDropdown = (type) => {
    if (type === 'brand') {
        isSearchDropdownOpen.brand = !isSearchDropdownOpen.brand;
        isSearchDropdownOpen.domicile = false;
    } else {
        isSearchDropdownOpen.domicile = !isSearchDropdownOpen.domicile;
        isSearchDropdownOpen.brand = false;
    }
};

const selectSearchOption = (type, value) => { searchForm[type] = value; isSearchDropdownOpen[type] = false; };

const handleSearch = () => {
    isSearching.value = true;
    router.get('/map-results', { brand: searchForm.brand, location: searchForm.domicile }, { onFinish: () => isSearching.value = false });
};

const selectRecommendedStation = (station) => {
    router.get('/map-results', { station: station.name, domicile: 'Batam Center', auto_book_id: station.id });
};

// --- 6. SETUP POPUP LOGIC ---
const showSetupPopup = ref(!user.value.car_brand || !user.value.nomor_plat);
const setupForm = useForm({ brand: '', series: '', variant: '', plateNumber: '' });
const popupState = reactive({ openBrand: false, openSeries: false });

const setupSeriesOptions = computed(() => setupForm.brand && carDatabase[setupForm.brand] ? Object.keys(carDatabase[setupForm.brand]) : []);

watch(() => setupForm.brand, () => { setupForm.series = ''; setupForm.variant = ''; });

const selectSetupBrand = (val) => { setupForm.brand = val; popupState.openBrand = false; };
const selectSetupSeries = (val) => { setupForm.series = val; popupState.openSeries = false; };

const submitSetup = () => {
    if(!setupForm.brand || !setupForm.series || !setupForm.plateNumber) { alert("Mohon lengkapi semua data mobil."); return; }
    setupForm.transform((data) => ({ ...data, nomor_plat: data.plateNumber.toUpperCase() }))
        .patch(route('profile.update'), { preserveScroll: true, onSuccess: () => { showSetupPopup.value = false; searchForm.brand = setupForm.brand; } });
};

const closeAllDropdowns = (e) => {
    if (!e.target.closest('.search-dropdown-trigger')) { isSearchDropdownOpen.brand = false; isSearchDropdownOpen.domicile = false; }
    if (showSetupPopup.value && !e.target.closest('.popup-dropdown-trigger')) { popupState.openBrand = false; popupState.openSeries = false; }
};
onMounted(() => document.addEventListener('click', closeAllDropdowns));
onBeforeUnmount(() => document.removeEventListener('click', closeAllDropdowns));
</script>

<template>
    <Head title="Dashboard Pengguna" />

    <div class="min-h-screen flex flex-col bg-gray-50 font-sans text-slate-800">
        <Navbar />

        <main class="flex-grow">
            
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
                    <p class="text-lg sm:text-xl font-medium text-slate-800/70 max-w-2xl mx-auto mb-10">
                        Pantau kondisi baterai dan temukan stasiun pengisian terdekat.
                    </p>
                    
                    <div class="bg-white/60 backdrop-blur-xl border border-white/50 p-8 rounded-[2.5rem] shadow-xl max-w-md mx-auto hover:shadow-2xl transition-shadow duration-300">
                        <div class="flex justify-between items-end mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-slate-900 rounded-xl text-[#CCFF00] shadow-md">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 12V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-6zM18 9h1a1 1 0 011 1v4a1 1 0 01-1 1h-1V9z"/></svg>
                                </div>
                                <div class="text-left">
                                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sisa Baterai</span>
                                    <span class="block text-sm font-bold text-slate-800">Normal</span>
                                </div>
                            </div>
                            <span class="text-5xl font-black transition-colors duration-300 tracking-tighter" :class="batteryColor">{{ batteryLevel }}<span class="text-2xl align-top">%</span></span>
                        </div>
                        
                        <div class="relative w-full h-4 bg-slate-200 rounded-full mb-6 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full bg-slate-900 rounded-full transition-all duration-300" :style="`width: ${batteryLevel}%`"></div>
                            <input type="range" v-model="batteryLevel" min="0" max="100" class="absolute top-0 left-0 w-full h-full opacity-0 cursor-pointer">
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

            <div class="relative z-30 px-4 -mt-12 sm:-mt-16 mb-8">
                <div class="max-w-4xl mx-auto">
                    <form @submit.prevent="handleSearch" class="bg-white rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] p-3 border border-gray-100 flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-gray-100 ring-4 ring-white/50">
                        <div class="relative flex-1 search-dropdown-trigger">
                            <div @click.stop="toggleSearchDropdown('brand')" class="h-16 flex items-center px-6 cursor-pointer hover:bg-lime-50/50 rounded-[1.5rem] lg:rounded-l-[1.5rem] lg:rounded-r-none transition-colors group">
                                <div class="w-10 h-10 rounded-full bg-lime-100 text-lime-700 flex items-center justify-center mr-4 transition-transform group-hover:scale-110">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 7h-3V6a4 4 0 00-8 0v1H5a3 3 0 00-3 3v7a3 3 0 003 3h14a3 3 0 003-3V10a3 3 0 00-3-3zm-4-1V6a2 2 0 00-4 0v1h4zm5 10a1 1 0 01-1 1H5a1 1 0 01-1-1v-7a1 1 0 011-1h14a1 1 0 011 1v7z"/></svg>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Merk Mobil</span>
                                    <span class="text-base font-bold text-slate-800 truncate">{{ searchForm.brand || 'Pilih Mobil' }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="isSearchDropdownOpen.brand ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div v-if="isSearchDropdownOpen.brand" class="absolute top-full left-0 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 max-h-64 overflow-y-auto z-50">
                                <div v-for="opt in brandOptions" :key="opt" @click="selectSearchOption('brand', opt)" class="px-5 py-3 hover:bg-lime-50 cursor-pointer font-medium text-slate-600 flex justify-between">{{ opt }} <span v-if="searchForm.brand === opt" class="text-lime-600">✓</span></div>
                            </div>
                        </div>

                        <div class="relative flex-1 search-dropdown-trigger">
                            <div @click.stop="toggleSearchDropdown('domicile')" class="h-16 flex items-center px-6 cursor-pointer hover:bg-blue-50/50 transition-colors group">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-4 transition-transform group-hover:scale-110">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" /></svg>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Area Lokasi</span>
                                    <span class="text-base font-bold text-slate-800 truncate">{{ searchForm.domicile || 'Semua Area' }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="isSearchDropdownOpen.domicile ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div v-if="isSearchDropdownOpen.domicile" class="absolute top-full left-0 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 max-h-64 overflow-y-auto z-50">
                                <div v-for="opt in domicileOptions" :key="opt" @click="selectSearchOption('domicile', opt)" class="px-5 py-3 hover:bg-blue-50 cursor-pointer font-medium text-slate-600 flex justify-between">{{ opt }} <span v-if="searchForm.domicile === opt" class="text-blue-600">✓</span></div>
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

            <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-32">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold text-slate-900">Rekomendasi Terdekat</h2>
                        <p class="text-slate-500 mt-1 text-sm">Berdasarkan lokasi Anda saat ini</p>
                    </div>
                    <Link href="/map-results" class="text-lime-700 font-bold text-sm hover:text-lime-800 hover:underline">Lihat Semua</Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="station in recommendedStations" :key="station.id" 
                        @click="selectRecommendedStation(station)"
                        class="group bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer relative overflow-hidden">
                        
                        <div class="absolute inset-0 bg-lime-50 opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>

                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <div class="p-3 bg-lime-50 rounded-2xl text-lime-700 group-hover:bg-[#CCFF00] group-hover:text-black transition-colors shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" 
                                :class="station.status === 'Tersedia' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                {{ station.status }}
                            </span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-slate-900 mb-1 relative z-10 truncate">{{ station.name }}</h3>
                        <p class="text-slate-500 text-xs mb-4 relative z-10 truncate">{{ station.address }}</p>
                        
                        <div class="flex items-center gap-4 text-xs font-medium text-slate-600 mb-6 relative z-10">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ station.distance }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ station.type }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 relative z-10">
                            <span class="font-bold text-slate-900 text-sm">{{ station.price }}</span>
                            <button class="bg-slate-900 text-white p-2 rounded-xl group-hover:bg-lime-500 transition-colors shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <Footer />

        <Transition name="modal-fade">
            <div v-if="showSetupPopup" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>
                <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-8 transform transition-all">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-[#CCFF00] rounded-2xl mx-auto flex items-center justify-center mb-4 shadow-[0_10px_20px_rgba(204,255,0,0.4)]">
                            <svg class="w-8 h-8 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 01 1 1v5m-4 0h4"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900">Setup Profil</h2>
                        <p class="text-slate-500 mt-2 text-sm">Lengkapi data kendaraan Anda.</p>
                    </div>
                    <form @submit.prevent="submitSetup" class="space-y-4">
                        <div class="relative popup-dropdown-trigger">
                            <div @click.stop="popupState.openBrand = !popupState.openBrand; popupState.openSeries = false" class="p-4 bg-gray-50 rounded-xl flex justify-between items-center cursor-pointer hover:bg-lime-50 border border-transparent hover:border-lime-200 transition-colors">
                                <span :class="setupForm.brand ? 'text-slate-900 font-semibold' : 'text-gray-400'">{{ setupForm.brand || 'Pilih Merk Mobil' }}</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div v-if="popupState.openBrand" class="absolute z-50 top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto">
                                <div v-for="b in brandOptions" :key="b" @click="selectSetupBrand(b)" class="p-3 hover:bg-lime-50 cursor-pointer text-slate-600 font-medium">{{ b }}</div>
                            </div>
                        </div>
                        <div v-if="setupForm.brand" class="relative popup-dropdown-trigger">
                             <div @click.stop="popupState.openSeries = !popupState.openSeries; popupState.openBrand = false" class="p-4 bg-gray-50 rounded-xl flex justify-between items-center cursor-pointer hover:bg-lime-50 border border-transparent hover:border-lime-200 transition-colors">
                                <span :class="setupForm.series ? 'text-slate-900 font-semibold' : 'text-gray-400'">{{ setupForm.series || 'Pilih Tipe Mobil' }}</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                             <div v-if="popupState.openSeries" class="absolute z-50 top-full mt-2 w-full bg-white rounded-xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto">
                                <div v-for="s in setupSeriesOptions" :key="s" @click="selectSetupSeries(s)" class="p-3 hover:bg-lime-50 cursor-pointer text-slate-600 font-medium">{{ s }}</div>
                            </div>
                        </div>
                        <input type="text" v-model="setupForm.plateNumber" placeholder="PLAT NOMOR" class="w-full p-4 bg-gray-50 rounded-xl text-slate-900 placeholder-gray-400 font-bold focus:outline-none focus:ring-2 focus:ring-[#CCFF00] uppercase border border-transparent tracking-widest text-center">
                        <button type="submit" :disabled="setupForm.processing" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-slate-800 transition">
                            {{ setupForm.processing ? 'Menyimpan...' : 'Simpan & Lanjutkan' }}
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