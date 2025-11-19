<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';

// Reactive state for the form
const formState = ref({
  brand: '',
  type: '',
  date: '',
  domicile: '',
  station: '',
  time: '',
});

// New: options + dropdown states for custom dropdowns (brand, type, domicile, station)
const brandOptions = ['Hyundai', 'Wuling', 'Tesla', 'BYD', 'Kia'];
const typeOptions = ['SUV', 'City Car', 'Hatchback', 'Sedan', 'MPV'];
const domicileOptions = [
  'Batam Center', 'Nagoya', 'Harbour Bay', 'Sekupang', 'Batu Aji',
  'Lubuk Baja', 'Tiban', 'Kabil', 'Batu Ampar', 'Galang', 'Bulang'
];
const stationOptions = [
  'SPKLU Mega Mall','SPKLU Grand Batam Mall','SPKLU Nagoya Hill','SPKLU Harbour Bay',
  'SPKLU Batam Center','SPKLU Batam City Square','SPKLU Kepri Mall','SPKLU Batam View','SPKLU Nagoya City'
];

const isBrandOpen = ref(false);
const isTypeOpen = ref(false);
const isDomicileOpen = ref(false);
const isStationOpen = ref(false);

// helper to close all custom dropdowns (keeps date/time logic separate)
const closeAllCustomDropdowns = () => {
  isBrandOpen.value = false;
  isTypeOpen.value = false;
  isDomicileOpen.value = false;
  isStationOpen.value = false;
};

const openOnly = (which) => {
  // Toggle the requested dropdown; close the others
  if (which === 'brand') {
    isBrandOpen.value = !isBrandOpen.value;
    isTypeOpen.value = false;
    isDomicileOpen.value = false;
    isStationOpen.value = false;
  } else if (which === 'type') {
    isTypeOpen.value = !isTypeOpen.value;
    isBrandOpen.value = false;
    isDomicileOpen.value = false;
    isStationOpen.value = false;
  } else if (which === 'domicile') {
    isDomicileOpen.value = !isDomicileOpen.value;
    isBrandOpen.value = false;
    isTypeOpen.value = false;
    isStationOpen.value = false;
  } else if (which === 'station') {
    isStationOpen.value = !isStationOpen.value;
    isBrandOpen.value = false;
    isTypeOpen.value = false;
    isDomicileOpen.value = false;
  }

  // keep date/time closed when toggling custom dropdowns
  isDateDropdownOpen.value = false;
  isTimeDropdownOpen.value = false;
};

const selectOption = (field, value) => {
  formState.value[field] = value;
  closeAllCustomDropdowns();
};

// --- DATE & TIME PICKER LOGIC ---
const today = new Date();
today.setHours(0, 0, 0, 0);

const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
const dayNames = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];

// DATE STATE
const isDateDropdownOpen = ref(false);
const selectedDate = ref(new Date(today)); // Tanggal yang dipilih
const calendarDisplayDate = ref(new Date(today)); // Bulan/Tahun yang ditampilkan di kalender

// TIME STATE
const isTimeDropdownOpen = ref(false);
const selectedTime = ref(''); // Jam yang dipilih (e.g., "10:30")

// Format tanggal untuk tampilan
const formatDate = (date) => `${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;

// Teks yang ditampilkan di input tanggal
const datePickerText = computed(() => {
    return selectedDate.value ? formatDate(selectedDate.value) : 'Pilih Tanggal';
});

// GENERATE CALENDAR GRID
const calendarDays = computed(() => {
    const year = calendarDisplayDate.value.getFullYear();
    const month = calendarDisplayDate.value.getMonth();
    const firstDayIndex = new Date(year, month, 1).getDay(); 
    const lastDate = new Date(year, month + 1, 0).getDate();
    
    let days = [];
    
    for(let i = 0; i < firstDayIndex; i++) {
        days.push({ number: null, classes: 'text-gray-300' });
    }

    for (let i = 1; i <= lastDate; i++) {
        const currentDate = new Date(year, month, i);
        const isSelected = selectedDate.value && currentDate.toDateString() === selectedDate.value.toDateString();
        const isDisabled = currentDate < today;
        
        let classes = 'day-number w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium transition duration-100';
        if (isDisabled) {
            classes += ' text-gray-400 cursor-not-allowed';
        } else if (isSelected) {
            classes += ' bg-lime-600 text-white shadow-md';
        } else {
            classes += ' text-gray-800 hover:bg-lime-100 cursor-pointer';
        }
        
        days.push({ number: i, date: currentDate, classes, isDisabled });
    }
    return days;
});

// NAVIGATION LOGIC
const prevMonth = () => {
    const newDate = new Date(calendarDisplayDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    calendarDisplayDate.value = newDate;
};

const nextMonth = () => {
    const newDate = new Date(calendarDisplayDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    calendarDisplayDate.value = newDate;
};

const selectDay = (day) => {
    if (!day.isDisabled) {
        selectedDate.value = day.date;
        formState.value.date = day.date.toISOString().split('T')[0];
        selectedTime.value = ''; 
        formState.value.time = ''; 
        isDateDropdownOpen.value = false;
    }
};

// GENERATE TIME SLOTS
const timeSlots = computed(() => {
    const slots = [];
    const now = new Date();
    const isToday = selectedDate.value.toDateString() === new Date(now).toDateString();
    
    for(let h = 0; h < 24; h++) {
        for(let m = 0; m < 60; m += 30) {
            const timeString = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
            let isDisabled = false;

            if (isToday) {
                if (h < now.getHours() || (h === now.getHours() && m < now.getMinutes())) {
                    isDisabled = true;
                }
            }
            
            slots.push({ time: timeString, isDisabled });
        }
    }
    return slots;
});

const selectTime = (slot) => {
    if (!slot.isDisabled) {
        selectedTime.value = slot.time;
        formState.value.time = slot.time;
        isTimeDropdownOpen.value = false;
    }
};

// --- FORM SUBMISSION ---
const searchStations = () => {
  console.log('Mencari stasiun dengan data:', formState.value);
  // Di sini Anda akan menambahkan logika untuk mengirim data ke backend
  // atau navigasi ke halaman hasil pencarian, misalnya menggunakan Inertia.get()
  // Contoh: Inertia.get('/map-results', formState.value, { preserveState: true });
};

// --- GLOBAL EVENT LISTENER ---
const closePickersOnOutsideClick = (event) => {
    if (isDateDropdownOpen.value && !event.target.closest('#date-picker-trigger') && !event.target.closest('.date-picker-content')) {
        isDateDropdownOpen.value = false;
    }
    if (isTimeDropdownOpen.value && !event.target.closest('#time-picker-trigger') && !event.target.closest('.time-picker-content')) {
        isTimeDropdownOpen.value = false;
    }

    // close custom dropdowns when clicking outside their triggers/contents
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
};

onMounted(() => {
    document.body.addEventListener('click', closePickersOnOutsideClick);
    formState.value.date = selectedDate.value.toISOString().split('T')[0];
});

onBeforeUnmount(() => {
    document.body.removeEventListener('click', closePickersOnOutsideClick);
});
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Navbar />

    <main class="flex-grow relative z-0"> 
      
      <section class="bg-[#CCFF00] pt-16 pb-64 relative overflow-visible">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div class="flex flex-col lg:flex-row items-center justify-between">
            
            <div class="lg:w-1/2 mb-12 lg:mb-0 lg:pr-10">
              <h2 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-[#333] leading-tight mb-4">
                Selamat Datang di Dashboard Anda
              </h2>
              <h3 class="text-2xl sm:text-3xl font-semibold text-[#333]">Gunakan form di bawah ini untuk mencari stasiun pengisian daya yang tersedia</h3>
            </div>

            <div class="lg:w-1/2 flex justify-center lg:justify-end">
              <img 
                src="images/mobil.png" 
                alt="Placeholder Mobil EV" 
                class="w-full max-w-xs sm:max-w-sm lg:max-w-full"
              >
            </div>
          </div>
        </div>

        <!-- Search Form -->
        <div class="static mt-8 lg:absolute lg:left-1/2 lg:bottom-0 lg:transform lg:-translate-x-1/2 lg:translate-y-1/2 w-full max-w-6xl px-4 sm:px-6 lg:px-8 z-20">
          <form @submit.prevent="searchStations" class="bg-white p-6 md:p-8 rounded-2xl shadow-2xl border border-gray-100">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">Cari EV Charge Station</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <!-- BRAND: custom dropdown -->
              <div class="relative">
                <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">Merk Mobil</label>
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
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Mobil</label>
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
              
              <!-- DATE picker (leave unchanged) -->
              

              <!-- DOMICILE: custom dropdown -->
              <div class="relative">
                <label for="domicile" class="block text-sm font-medium text-gray-700 mb-2">Domisili</label>
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

              <!-- STATION: custom dropdown -->
              <div class="relative">
                <label for="station" class="block text-sm font-medium text-gray-700 mb-2">Stasiun Charger</label>
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
            
            <div class="text-end mt-6">
                <Link href="/map-results" class="inline-block bg-[#00C853] text-white font-semibold px-8 py-3 rounded-lg shadow-lg hover:bg-[#00A142] transition duration-300 focus:outline-none focus:ring-4 focus:ring-lime-300">
                  Cari Jadwal
                </Link>
            </div>
          </form>
        </div>
      </section>
      
      <section class="pt-16 sm:pt-64 lg:pt-50 pb-24 lg:pb-32 bg-white"> 
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 text-center">
        </div>
      </section>

    </main>

    <Footer />
  </div>
</template>