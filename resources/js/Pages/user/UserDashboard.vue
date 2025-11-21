<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3'; // Gunakan router untuk navigasi
import Navbar from '@/Components/NavbarUser.vue'; // Pastikan path sesuai
import Footer from '@/Components/Footer.vue';

// --- 1. STATE FORM ---
const formState = ref({
  brand: '',
  type: '',
  domicile: '',
  station: ''
});

const activeDropdown = ref(null);

// --- 2. DATA OPSI DROPDOWN ---
const brandOptions = ['Nissan', 'Toyota', 'Wuling', 'Hyundai', 'Tesla', 'BYD', 'Kia'];
const typeOptions = ['SUV', 'City Car', 'Hatchback', 'Sedan', 'MPV'];

// Data Domisili (Nama saja untuk dropdown)
const domicileOptions = [
  'Batam Center', 'Nagoya', 'Harbour Bay', 'Sekupang', 'Batu Aji',
  'Lubuk Baja', 'Tiban', 'Kabil', 'Batu Ampar', 'Galang', 'Bulang'
];

// Data Stasiun (Nama saja untuk dropdown)
const stationOptions = [
  'SPKLU Nagoya Hill', 'SPKLU Mega Mall Batam', 'SPKLU Harbour Bay',
  'SPKLU Batam Center', 'SPKLU Batam City Square', 'SPKLU Kepri Mall',
  'SPKLU Batam View', 'SPKLU Tiban', 'SPKLU Sekupang',
  'SPKLU Batu Ampar', 'SPKLU Nagoya City', 'SPKLU Batam Harbor',
  'SPKLU Gajah Mada', 'SPKLU Waterfront City'
];

// --- 3. ACTION HANDLERS ---

const toggleDropdown = (name) => {
    activeDropdown.value = activeDropdown.value === name ? null : name;
};

const closeDropdowns = () => {
    activeDropdown.value = null;
};

const selectOption = (category, value) => {
    formState.value[category] = value;
    activeDropdown.value = null;
};

// --- 4. FUNGSI SUBMIT (REDIRECT) ---
const submitSearch = () => {
    // Mengarahkan ke halaman map-results sambil membawa data filter
    // Data ini akan menjadi query params: /map-results?brand=Hyundai&domicile=...
    router.get('/map-results', {
        brand: formState.value.brand,
        type: formState.value.type,
        domicile: formState.value.domicile,
        station: formState.value.station
    });
};
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-50" @click="closeDropdowns">
    <Navbar />

    <main class="flex-grow relative z-0"> 
      
      <section class="bg-[#CCFF00] pt-16 pb-64 relative overflow-visible">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div class="flex flex-col lg:flex-row items-center justify-between">
            
            <div class="lg:w-1/2 mb-12 lg:mb-0 lg:pr-10">
              <h2 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-[#333] leading-tight mb-4">
                Selamat Datang di Dashboard Anda
              </h2>
              <h3 class="text-2xl sm:text-3xl font-semibold text-[#333]">
                Gunakan form di bawah ini untuk mencari stasiun pengisian daya yang tersedia
              </h3>
            </div>

            <div class="lg:w-1/2 flex justify-center lg:justify-end">
              <img 
                src="/images/mobil.png" 
                alt="Placeholder Mobil EV" 
                class="w-full max-w-xs sm:max-w-sm lg:max-w-full"
              >
            </div>
          </div>
        </div>

        <div class="static mt-8 lg:absolute lg:left-1/2 lg:bottom-0 lg:transform lg:-translate-x-1/2 lg:translate-y-1/2 w-full max-w-6xl px-4 sm:px-6 lg:px-8 z-20">
          <form @click.stop @submit.prevent="submitSearch" class="bg-white p-6 md:p-8 rounded-2xl shadow-2xl border border-gray-100">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">Cari EV Charge Station</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-2">Merk Mobil</label>
                <div 
                     @click="toggleDropdown('brand')"
                     class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': activeDropdown === 'brand'}"
                >
                  <span :class="formState.brand ? 'text-gray-900' : 'text-gray-400'">
                      {{ formState.brand || 'Pilih Merk Mobil' }}
                  </span>
                  <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{'rotate-180': activeDropdown === 'brand'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>

                <div v-if="activeDropdown === 'brand'" class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
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
              
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Mobil</label>
                <div 
                     @click="toggleDropdown('type')"
                     class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': activeDropdown === 'type'}"
                >
                  <span :class="formState.type ? 'text-gray-900' : 'text-gray-400'">
                      {{ formState.type || 'Pilih Tipe Mobil' }}
                  </span>
                  <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{'rotate-180': activeDropdown === 'type'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>

                <div v-if="activeDropdown === 'type'" class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
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

              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-2">Domisili</label>
                <div 
                     @click="toggleDropdown('domicile')"
                     class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': activeDropdown === 'domicile'}"
                >
                  <span :class="formState.domicile ? 'text-gray-900' : 'text-gray-400'">
                      {{ formState.domicile || 'Pilih Domisili' }}
                  </span>
                  <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{'rotate-180': activeDropdown === 'domicile'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>

                <div v-if="activeDropdown === 'domicile'" class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
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

              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-2">Stasiun Charger</label>
                <div 
                     @click="toggleDropdown('station')"
                     class="w-full p-3 border border-gray-300 rounded-xl cursor-pointer flex justify-between items-center bg-white transition duration-150"
                     :class="{'ring-2 ring-lime-500 border-lime-500 shadow-md': activeDropdown === 'station'}"
                >
                  <span :class="formState.station ? 'text-gray-900' : 'text-gray-400'">
                      {{ formState.station || 'Pilih Stasiun' }}
                  </span>
                  <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{'rotate-180': activeDropdown === 'station'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>

                <div v-if="activeDropdown === 'station'" class="absolute top-full mt-2 w-full bg-white rounded-xl shadow-2xl border border-gray-100 z-30 left-0 max-h-48 overflow-y-auto">
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
            
            <div class="text-end mt-6">
                <button 
                    type="submit"
                    class="inline-block bg-[#00C853] text-white font-semibold px-8 py-3 rounded-lg shadow-lg hover:bg-[#00A142] transition duration-300 focus:outline-none focus:ring-4 focus:ring-lime-300"
                >
                  Cari Jadwal
                </button>
            </div>
          </form>
        </div>
      </section>
      
      <section class="pt-24 lg:pt-32 pb-24 bg-white"> 
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500">
           </div>
      </section>

    </main>

    <Footer />
  </div>
</template>