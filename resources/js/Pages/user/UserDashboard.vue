<script setup>

import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

import { Link } from '@inertiajs/vue3';

import Navbar from '@/Components/NavbarUser.vue';

import Footer from '@/Components/Footer.vue';



// --- STATE ---

const formState = ref({

    brand: '',

    domicile: '',

    type: '',

    station: '',

    date: '',

    time: ''

});



// --- DATA ---

const brandOptions = ['Hyundai', 'Wuling', 'Tesla', 'BYD', 'Kia', 'Toyota', 'MG', 'Chery', 'BMW'];

const domicileOptions = [

    'Batam Center', 'Nagoya', 'Harbour Bay', 'Lubuk Baja',

    'Batu Aji', 'Tiban', 'Kabil', 'Batu Ampar'

];



// --- DROPDOWN LOGIC ---

const isBrandOpen = ref(false);

const isDomicileOpen = ref(false);



const closeAllCustomDropdowns = () => {

    isBrandOpen.value = false;

    isDomicileOpen.value = false;

};



const openOnly = (which) => {

    if (which === 'brand') {

        isBrandOpen.value = !isBrandOpen.value;

        isDomicileOpen.value = false;

    } else if (which === 'domicile') {

        isDomicileOpen.value = !isDomicileOpen.value;

        isBrandOpen.value = false;

    }

    // Tutup popup dropdown jika ada

    isPopupBrandOpen.value = false;

    isPopupSeriesOpen.value = false;

    isPopupVariantOpen.value = false;

};



const selectOption = (field, value) => {

    formState.value[field] = value;

    closeAllCustomDropdowns();

};



const searchStations = () => {

    console.log('Search:', formState.value);

    // Logic redirect

};



// ==========================================

// === POPUP FIRST TIME LOGIN LOGIC START ===

// ==========================================

const showFirstTimePopup = ref(true);

const popupForm = ref({ brand: '', series: '', variant: '', plateNumber: '' });

const isPopupBrandOpen = ref(false);

const isPopupSeriesOpen = ref(false);

const isPopupVariantOpen = ref(false);



const carData = {

    'Hyundai': { 'Ioniq 5': ['Prime', 'Signature'], 'Kona': ['Signature'] },

    'Wuling': { 'Air EV': ['Long Range'], 'Binguo': ['Premium'] },

    'Tesla': { 'Model 3': ['Performance'], 'Model Y': ['Long Range'] },

    'BYD': { 'Seal': ['Performance'], 'Atto 3': ['Superior'] },

    // ... (Data lainnya disederhanakan untuk brevity kode, logika tetap sama)

};

// Fallback data dummy agar dropdown popup jalan jika carData di atas belum lengkap

const getSeries = (brand) => carData[brand] ? Object.keys(carData[brand]) : ['Standard Model', 'Long Range'];

const getVariants = (brand, series) => (carData[brand] && carData[brand][series]) ? carData[brand][series] : ['Base', 'Premium'];



const popupBrandOptions = computed(() => brandOptions); // Pakai brandOptions utama

const popupSeriesOptions = computed(() => popupForm.value.brand ? getSeries(popupForm.value.brand) : []);

const popupVariantOptions = computed(() => (popupForm.value.brand && popupForm.value.series) ? getVariants(popupForm.value.brand, popupForm.value.series) : []);



const togglePopupBrand = () => { isPopupBrandOpen.value = !isPopupBrandOpen.value; isPopupSeriesOpen.value = false; isPopupVariantOpen.value = false; };

const togglePopupSeries = () => { if (!popupForm.value.brand) return; isPopupSeriesOpen.value = !isPopupSeriesOpen.value; isPopupBrandOpen.value = false; isPopupVariantOpen.value = false; };

const togglePopupVariant = () => { if (!popupForm.value.series) return; isPopupVariantOpen.value = !isPopupVariantOpen.value; isPopupBrandOpen.value = false; isPopupSeriesOpen.value = false; };



const selectPopupBrand = (val) => { popupForm.value.brand = val; popupForm.value.series = ''; popupForm.value.variant = ''; isPopupBrandOpen.value = false; };

const selectPopupSeries = (val) => { popupForm.value.series = val; popupForm.value.variant = ''; isPopupSeriesOpen.value = false; };

const selectPopupVariant = (val) => { popupForm.value.variant = val; isPopupVariantOpen.value = false; };



const saveVehicleData = () => {

    formState.value.brand = popupForm.value.brand; // Sync ke dashboard

    showFirstTimePopup.value = false;

};



// --- OUTSIDE CLICK ---

const closePickersOnOutsideClick = (event) => {

    if (!event.target.closest('.custom-select-trigger')) {

        closeAllCustomDropdowns();

    }

    // Logic tutup popup dropdown

    if (showFirstTimePopup.value && !event.target.closest('.popup-trigger')) {

        isPopupBrandOpen.value = false; isPopupSeriesOpen.value = false; isPopupVariantOpen.value = false;

    }

};



onMounted(() => document.body.addEventListener('click', closePickersOnOutsideClick));

onBeforeUnmount(() => document.body.removeEventListener('click', closePickersOnOutsideClick));

</script>



<template>

    <div class="min-h-screen flex flex-col bg-gray-50 font-sans">

        <Navbar />



        <main class="flex-grow">

            <!-- HERO SECTION -->

            <!-- Background Full Color #CCFF00 sesuai request -->

            <section

                class="bg-[#CCFF00] pt-28 pb-32 lg:pb-48 rounded-b-[40px] lg:rounded-b-[80px] shadow-sm relative overflow-visible z-10">

                <div class="max-w-7xl mx-auto px-6 lg:px-8">

                    <div class="flex flex-col lg:flex-row items-center justify-between">



                        <!-- TEXT HERO -->

                        <div class="lg:w-1/2 text-center lg:text-left mb-10 lg:mb-0 relative z-10">

                            <!--<span class="inline-block py-1 px-3 rounded-full bg-black/10 text-black/70 text-sm font-bold tracking-wide mb-4 backdrop-blur-sm">

                                ⚡ EV Charging Batam

                            </span>-->

                            <h1

                                class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-[#333] leading-tight mb-4 lg:mb-6 tracking-tight">

                                Cari Charger <br>

                                <span>

                                    Tanpa Ribet.

                                </span>

                            </h1>

                            <p

                                class="text-lg sm:text-xl font-medium text-black/70 max-w-lg mx-auto lg:mx-0 leading-relaxed">

                                Hemat waktu perjalanan keluarga Anda. Temukan lokasi SPKLU terdekat dan tersedia hanya

                                dalam hitungan detik.

                            </p>

                        </div>



                        <!-- IMAGE HERO -->

                        <div class="lg:w-1/2 flex justify-center lg:justify-end relative z-0">

                            <!-- Visual Blob -->

                            <div

                                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] bg-white/40 rounded-full blur-3xl">

                            </div>

                            <img src="images/mobil.png" alt="EV Car Illustration"

                                class="relative w-full max-w-sm lg:max-w-xl drop-shadow-2xl hover:scale-105 transition-transform duration-500 ease-in-out">

                        </div>

                    </div>

                </div>



                <!-- FLOATING SEARCH COMPONENT -->

                <!-- Container ini dibuat 'absolute' agar menimpa perbatasan antara warna hijau dan background putih bawah -->

                <div class="absolute bottom-0 left-0 w-full transform translate-y-1/2 px-4 sm:px-6 z-30">

                    <div class="max-w-5xl mx-auto">



                        <!-- FORM BOX -->

                        <form @submit.prevent="searchStations"

                            class="bg-white rounded-[2rem] lg:rounded-full shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] p-3 lg:p-2 relative border-4 border-white/50 backdrop-blur-xl">



                            <!-- FLEX CONTAINER: Column di HP, Row di Desktop -->

                            <div class="flex flex-col lg:flex-row gap-3 lg:gap-0">



                                <!-- INPUT 1: MERK MOBIL -->

                                <div class="flex-1 relative group custom-select-trigger">

                                    <!-- Tombol Trigger -->

                                    <div @click.stop="openOnly('brand')"

                                        class="h-20 lg:h-20 flex items-center px-6 cursor-pointer bg-gray-50 hover:bg-lime-50 lg:bg-transparent lg:hover:bg-gray-50 rounded-[1.5rem] lg:rounded-l-full lg:rounded-r-none transition-all duration-300 border border-transparent hover:border-lime-200 lg:border-none">

                                        <!-- Icon Circle -->

                                        <div

                                            class="w-12 h-12 rounded-full bg-lime-100 flex items-center justify-center text-lime-700 mr-4 shrink-0 transition-transform group-hover:scale-110">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24"

                                                fill="currentColor">

                                                <path

                                                    d="M19 7h-3V6a4 4 0 00-8 0v1H5a3 3 0 00-3 3v7a3 3 0 003 3h14a3 3 0 003-3V10a3 3 0 00-3-3zm-4-1V6a2 2 0 00-4 0v1h4zm5 10a1 1 0 01-1 1H5a1 1 0 01-1-1v-7a1 1 0 011-1h14a1 1 0 011 1v7z" />

                                            </svg>

                                        </div>

                                        <!-- Text -->

                                        <div class="flex flex-col">

                                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Merk

                                                Mobil</span>

                                            <span class="text-lg font-medium text-gray-800 truncate block w-full">

                                                {{ formState.brand || 'Pilih Mobil' }}

                                            </span>

                                        </div>

                                        <!-- Arrow Icon -->

                                        <svg class="ml-auto w-5 h-5 text-gray-400 transform transition-transform duration-300"

                                            :class="{ 'rotate-180': isBrandOpen }" fill="none" stroke="currentColor"

                                            viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                                                d="M19 9l-7 7-7-7"></path>

                                        </svg>

                                    </div>



                                    <!-- Dropdown Menu -->

                                    <div v-if="isBrandOpen"

                                        class="absolute top-full left-0 mt-3 w-full bg-white rounded-3xl shadow-2xl border border-gray-100 z-50 overflow-hidden animate-pop-in">

                                        <div class="max-h-60 overflow-y-auto p-2">

                                            <div v-for="opt in brandOptions" :key="opt"

                                                @click="selectOption('brand', opt)"

                                                class="px-4 py-3 rounded-xl hover:bg-lime-50 cursor-pointer text-gray-700 font-semibold transition-colors flex justify-between items-center mb-1 last:mb-0"

                                                :class="{ 'bg-lime-50 text-lime-700': formState.brand === opt }">

                                                {{ opt }}

                                                <span v-if="formState.brand === opt" class="text-lime-600">●</span>

                                            </div>

                                        </div>

                                    </div>

                                </div>



                                <!-- DIVIDER (Desktop Only) -->

                                <div class="hidden lg:block w-[1px] h-12 bg-gray-200 self-center mx-2"></div>



                                <!-- INPUT 2: DOMISILI -->

                                <div class="flex-1 relative group custom-select-trigger">

                                    <div @click.stop="openOnly('domicile')"

                                        class="h-20 lg:h-20 flex items-center px-6 cursor-pointer bg-gray-50 hover:bg-blue-50 lg:bg-transparent lg:hover:bg-gray-50 rounded-[1.5rem] lg:rounded-none transition-all duration-300 border border-transparent hover:border-blue-200 lg:border-none">

                                        <!-- Icon Circle -->

                                        <div

                                            class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-4 shrink-0 transition-transform group-hover:scale-110">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24"

                                                fill="currentColor">

                                                <path fill-rule="evenodd"

                                                    d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z"

                                                    clip-rule="evenodd" />

                                            </svg>

                                        </div>

                                        <div class="flex flex-col">

                                            <span

                                                class="text-xs font-bold text-gray-400 uppercase tracking-wider">Lokasi

                                                / Area</span>

                                            <span class="text-lg font-medium text-gray-800 truncate block w-full">

                                                {{ formState.domicile || 'Semua Area' }}

                                            </span>

                                        </div>

                                        <svg class="ml-auto w-5 h-5 text-gray-400 transform transition-transform duration-300"

                                            :class="{ 'rotate-180': isDomicileOpen }" fill="none" stroke="currentColor"

                                            viewBox="0 0 24 24">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                                                d="M19 9l-7 7-7-7"></path>

                                        </svg>

                                    </div>



                                    <!-- Dropdown Menu -->

                                    <div v-if="isDomicileOpen"

                                        class="absolute top-full left-0 lg:left-auto lg:right-0 mt-3 w-full lg:w-[120%] bg-white rounded-3xl shadow-2xl border border-gray-100 z-50 overflow-hidden animate-pop-in">

                                        <div class="max-h-60 overflow-y-auto p-2">

                                            <div v-for="opt in domicileOptions" :key="opt"

                                                @click="selectOption('domicile', opt)"

                                                class="px-4 py-3 rounded-xl hover:bg-blue-50 cursor-pointer text-gray-700 font-semibold transition-colors flex justify-between items-center mb-1 last:mb-0"

                                                :class="{ 'bg-blue-50 text-blue-700': formState.domicile === opt }">

                                                {{ opt }}

                                                <span v-if="formState.domicile === opt" class="text-blue-600">●</span>

                                            </div>

                                        </div>

                                    </div>

                                </div>



                                <!-- BUTTON SEARCH -->

                                <div class="lg:p-2">

                                    <!-- UPDATED: Button Color #00c853, Text White -->

                                    <Link href="/map-results"

                                        class="flex items-center justify-center w-full lg:w-auto h-20 lg:h-full bg-[#00c853] hover:bg-[#009624] text-white font-bold text-lg px-10 rounded-[1.5rem] lg:rounded-full shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 active:scale-95 group">

                                    <span class="mr-2">Cari</span>

                                    <svg xmlns="http://www.w3.org/2000/svg"

                                        class="h-6 w-6 transform group-hover:translate-x-1 transition-transform"

                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">

                                        <path stroke-linecap="round" stroke-linejoin="round"

                                            d="M17 8l4 4m0 0l-4 4m4-4H3" />

                                    </svg>

                                    </Link>

                                </div>



                            </div>

                        </form>

                    </div>

                </div>

            </section>



            <!-- EMPTY SPACE (Buffer for floating element) -->

            <!-- UPDATED: Increased height to create gap between dashboard and footer -->

            <section class="h-64 lg:h-96"></section>

        </main>



        <!-- ============================================== -->

        <!-- === POPUP MODAL FIRST TIME LOGIN (MODERN) === -->

        <!-- ============================================== -->

        <div v-if="showFirstTimePopup" class="fixed inset-0 z-[100] flex items-center justify-center p-4">

            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>



            <div

                class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-6 sm:p-8 transform transition-all border-8 border-[#CCFF00]/20">

                <button @click="showFirstTimePopup = false"

                    class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition"><svg class="w-6 h-6"

                        fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">

                        </path>

                    </svg></button>



                <div class="text-center mb-8">

                    <div

                        class="w-20 h-20 bg-[#CCFF00] rounded-3xl mx-auto flex items-center justify-center mb-4 shadow-lg shadow-lime-200">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-black" fill="none"

                            viewBox="0 0 24 24" stroke="currentColor">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                                d="M13 10V3L4 14h7v7l9-11h-7z" />

                        </svg>

                    </div>

                    <h2 class="text-2xl font-bold text-gray-800">Setup Kilat</h2>

                    <p class="text-gray-500 font-medium">Isi data mobil agar kami bisa mencarikan charger yang cocok.

                    </p>

                </div>



                <div class="space-y-4">

                    <!-- Popup Fields (Styled Modern) -->

                    <div class="popup-trigger relative">

                        <div @click.stop="togglePopupBrand"

                            class="p-4 bg-gray-50 rounded-2xl flex justify-between items-center cursor-pointer hover:bg-lime-50 border-2 border-transparent hover:border-lime-200 transition-colors">

                            <span class="text-gray-800">{{ popupForm.brand || 'Pilih Merk Mobil' }}</span>

                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                                    d="M19 9l-7 7-7-7"></path>

                            </svg>

                        </div>

                        <div v-if="isPopupBrandOpen"

                            class="absolute z-50 top-full mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto">

                            <div v-for="b in popupBrandOptions" :key="b" @click="selectPopupBrand(b)"

                                class="p-3 hover:bg-lime-50 cursor-pointer font-medium text-gray-600">{{ b }}</div>

                        </div>

                    </div>



                    <div class="popup-trigger relative">

                        <div @click.stop="togglePopupSeries"

                            class="p-4 bg-gray-50 rounded-2xl flex justify-between items-center transition-colors border-2 border-transparent"

                            :class="popupForm.brand ? 'cursor-pointer hover:bg-lime-50 hover:border-lime-200' : 'opacity-50 cursor-not-allowed'">

                            <span class="text-gray-800">{{ popupForm.series || 'Pilih Model' }}</span>

                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                                    d="M19 9l-7 7-7-7"></path>

                            </svg>

                        </div>

                        <div v-if="isPopupSeriesOpen"

                            class="absolute z-50 top-full mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto">

                            <div v-for="s in popupSeriesOptions" :key="s" @click="selectPopupSeries(s)"

                                class="p-3 hover:bg-lime-50 cursor-pointer font-medium text-gray-600">{{ s }}</div>

                        </div>

                    </div>



                    <div class="popup-trigger relative">

                        <div @click.stop="togglePopupVariant"

                            class="p-4 bg-gray-50 rounded-2xl flex justify-between items-center transition-colors border-2 border-transparent"

                            :class="popupForm.series ? 'cursor-pointer hover:bg-lime-50 hover:border-lime-200' : 'opacity-50 cursor-not-allowed'">

                            <span class="text-gray-800">{{ popupForm.variant || 'Pilih Varian' }}</span>

                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                                    d="M19 9l-7 7-7-7"></path>

                            </svg>

                        </div>

                        <div v-if="isPopupVariantOpen"

                            class="absolute z-50 top-full mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 max-h-48 overflow-y-auto">

                            <div v-for="v in popupVariantOptions" :key="v" @click="selectPopupVariant(v)"

                                class="p-3 hover:bg-lime-50 cursor-pointer font-medium text-gray-600">{{ v }}</div>

                        </div>

                    </div>



                    <div>

                        <input type="text" v-model="popupForm.plateNumber" placeholder="PLAT NOMOR (Cth: BP 1234 XY)"

                            class="w-full p-4 bg-gray-50 rounded-2xl text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CCFF00] uppercase border-2 border-transparent">

                    </div>



                    <!-- UPDATED: Button Color #00c853, Text White -->

                    <button @click="saveVehicleData"

                        class="w-full bg-[#00c853] hover:bg-[#009624] text-white font-bold py-4 rounded-2xl shadow-lg transform transition active:scale-95 text-lg mt-2">

                        Simpan & Mulai

                    </button>

                </div>

            </div>

        </div>



        <Footer />

    </div>

</template>



<style scoped>

@keyframes popIn {

    0% {

        opacity: 0;

        transform: scale(0.95) translateY(-10px);

    }



    100% {

        opacity: 1;

        transform: scale(1) translateY(0);

    }

}



.animate-pop-in {

    animation: popIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;

}



/* Custom Scrollbar for Dropdowns */

::-webkit-scrollbar {

    width: 6px;

}



::-webkit-scrollbar-track {

    background: #f1f1f1;

    border-radius: 4px;

}



::-webkit-scrollbar-thumb {

    background: #d1d5db;

    border-radius: 4px;

}



::-webkit-scrollbar-thumb:hover {

    background: #9ca3af;

}

</style>

