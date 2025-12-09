<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const page = usePage();

const getStartRoute = () => {
  const user = page.props.auth.user;
  if (!user) return route('register');

  if (user.role === 'admin') return route('admin.dashboard');
  if (user.role === 'operator') return route('operator.dashboard');
  return route('dashboard'); // default to user dashboard
};

const isModalOpen = ref(false);

const openModal = () => {
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const closePickersOnOutsideClick = (event) => {
  if (isModalOpen.value && !event.target.closest('.modal-content')) {
    closeModal();
  }
};

const updateViewport = () => {
  // Logic viewport tambahan jika diperlukan
};

onMounted(() => {
  document.body.addEventListener('click', closePickersOnOutsideClick);
  window.addEventListener('resize', updateViewport);
});

onBeforeUnmount(() => {
  document.body.removeEventListener('click', closePickersOnOutsideClick);
  window.removeEventListener('resize', updateViewport);
});
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white font-sans">
    <Navbar />

    <main class="flex-grow relative z-10">

      <!-- HERO SECTION -->
      <section
        class="bg-[#CCFF00] pt-24 pb-12 sm:pt-28 sm:pb-16 lg:pt-32 lg:pb-20 relative overflow-hidden rounded-b-[2.5rem] lg:rounded-none shadow-sm lg:shadow-none z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div class="flex flex-col lg:flex-row items-center justify-between gap-8 md:gap-10 lg:gap-12">

            <div class="w-full lg:w-1/2 text-center lg:text-left order-1 lg:order-1">
              <h2
                class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-[#333] leading-tight mb-4 lg:mb-6 tracking-tight">
                Menghubungkan Pengendara Dengan <span class="text-lime-700 lg:text-[#333]">Stasiun Cerdas</span>
              </h2>
              <p
                class="text-base sm:text-lg lg:text-xl text-gray-800 font-medium mb-8 lg:mb-8 max-w-md mx-auto lg:mx-0 leading-relaxed opacity-90">
                Temukan, reservasi, dan isi daya dengan mudah di mana saja tanpa ribet.
              </p>

              <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start w-full">
                <Link :href="route('register')"
                  class="w-full sm:w-auto bg-[#00C853] text-white font-bold px-6 py-3.5 rounded-xl shadow-lg hover:bg-[#00A142] active:scale-95 transition duration-200 focus:outline-none focus:ring-4 focus:ring-lime-300/50 text-base text-center">
                  Mulai Sekarang
                </Link>

                <Link href="#"
                  class="w-full sm:w-auto bg-white/80 backdrop-blur-sm border-2 border-[#00C853] text-[#00C853] font-bold px-6 py-3.5 rounded-xl hover:bg-white active:scale-95 transition duration-200 focus:outline-none focus:ring-4 focus:ring-lime-300/50 text-base text-center">
                  Pelajari Lebih Lanjut
                </Link>
              </div>
            </div>

            <!-- Gambar Hero -->
            <div class="w-full lg:w-1/2 flex justify-center order-2 lg:order-2 mt-2 lg:mt-0">
              <div class="relative">
                <div class="absolute inset-0 bg-white/30 blur-2xl rounded-full transform scale-90 lg:hidden"></div>
                <img src="images/mobil.png" alt="EV Car"
                  class="relative z-10 w-full max-w-[280px] sm:max-w-sm md:max-w-md lg:max-w-full drop-shadow-xl lg:drop-shadow-none transform hover:scale-105 transition duration-500">
              </div>
            </div>
          </div>
        </div>
      </section>


      <!-- STEP 1: Pencarian Stasiun -->
      <section class="py-12 lg:py-20 bg-white lg:bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12">
            <!-- Text Content -->
            <div class="w-full lg:w-1/2 text-center lg:text-left">
              <span
                class="inline-block px-3 py-1 bg-lime-100 text-lime-700 rounded-full text-xs font-bold uppercase tracking-wider mb-3 lg:mb-3">Langkah
                1</span>
              <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-4 text-balance leading-tight">
                Pencarian Stasiun <span class="text-lime-600">Terdekat</span>
              </h2>
              <p class="text-gray-600 text-base sm:text-lg leading-relaxed max-w-lg mx-auto lg:mx-0">
                Aplikasi menampilkan peta geolokasi dengan semua SPKLU terdaftar. Data <span
                  class="font-semibold text-gray-800">Real-Time</span> memastikan Anda hanya melihat slot yang siap
                pakai.
              </p>
            </div>
            <!-- Image -->
            <div class="w-full lg:w-1/2 flex justify-center mt-4 lg:mt-0">
              <div class="bg-lime-50 p-4 rounded-[2rem] lg:bg-transparent lg:p-0 w-full flex justify-center">
                <img src="https://i.pinimg.com/736x/28/8b/44/288b44c8e76a00feed1853b351057876.jpg"
                  alt="Aesthetic Map Navigation UI"
                  class="w-full max-w-[260px] sm:max-w-xs md:max-w-sm rounded-2xl shadow-lg lg:shadow-2xl transform rotate-0 lg:rotate-2 transition hover:rotate-0 duration-300 object-cover"
                  loading="lazy">
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- STEP 2: Reservasi Slot -->
      <section class="py-12 lg:py-20 bg-gray-50 lg:bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row-reverse items-center gap-8 lg:gap-12">

            <div class="w-full lg:w-1/2 text-center lg:text-left">
              <span
                class="inline-block px-3 py-1 bg-lime-100 text-lime-700 rounded-full text-xs font-bold uppercase tracking-wider mb-3 lg:mb-3">Langkah
                2</span>
              <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-4 text-balance leading-tight">
                Reservasi Slot <span class="text-lime-600">Terjamin</span>
              </h2>
              <p class="text-gray-600 text-base sm:text-lg leading-relaxed max-w-lg mx-auto lg:mx-0">
                Kunci slot pengisian spesifik sebelum Anda tiba. Sistem OCPP kami menjamin slot Anda aman. Dapatkan QR
                Booking dan lewati antrian.
              </p>
            </div>

            <!-- Image -->
            <div class="w-full lg:w-1/2 flex justify-center mt-4 lg:mt-0">
              <div
                class="bg-white p-4 rounded-[2rem] shadow-sm lg:bg-transparent lg:shadow-none lg:p-0 w-full flex justify-center">
                <img src="images/uibooking.png" alt="Clean Booking Slot UI"
                  class="w-full max-w-xs sm:max-w-sm md:max-w-md rounded-2xl shadow-lg lg:shadow-xl object-cover"
                  loading="lazy">
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- STEP 3: Otomasi & Audit -->
      <section class="py-12 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12">

            <div class="w-full lg:w-1/2 text-center lg:text-left">
              <span
                class="inline-block px-3 py-1 bg-lime-100 text-lime-700 rounded-full text-xs font-bold uppercase tracking-wider mb-3 lg:mb-3">Langkah
                3</span>
              <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-4 text-balance leading-tight">
                <span class="text-lime-600">Otomasi</span> & Data Audit
              </h2>
              <p class="text-gray-600 text-base sm:text-lg leading-relaxed max-w-lg mx-auto lg:mx-0">
                Komunikasi dua arah antara Server dan SPKLU memungkinkan audit transaksi otomatis. Efisiensi tinggi
                dengan model <span class="italic">Zero Operator Cost</span>.
              </p>
            </div>

            <!-- Image -->
            <div class="w-full lg:w-1/2 flex justify-center mt-4 lg:mt-0">
              <div class="bg-gray-50 p-4 rounded-[2rem] lg:bg-transparent lg:p-0 w-full flex justify-center">
                <img src="images/admin.png" alt="Automation Dashboard Isometric"
                  class="w-full max-w-xs sm:max-w-sm md:max-w-md rounded-2xl shadow-lg lg:shadow-xl object-contain"
                  loading="lazy">
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SPECIAL FEATURE: Pengecasan Darurat -->
      <section class="py-12 lg:py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row-reverse items-center gap-8 lg:gap-12">

            <div class="w-full lg:w-1/2 text-center lg:text-left">
              <span
                class="inline-block px-3 py-1 bg-orange-100 text-orange-600 rounded-full text-xs font-bold uppercase tracking-wider mb-3 lg:mb-3">Fitur
                Spesial</span>
              <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-4 text-balance leading-tight">
                Pengecasan <span class="text-orange-600">Darurat Tetangga</span>
              </h2>
              <p class="text-gray-600 text-base sm:text-lg leading-relaxed max-w-lg mx-auto lg:mx-0">
                Kehabisan daya di tengah jalan? Gunakan fitur ini untuk menemukan tetangga sesama pengguna Evolt yang
                membuka akses charger rumah mereka. Solusi darurat berbasis komunitas.
              </p>
            </div>

            <!-- Image -->
            <div class="w-full lg:w-1/2 flex justify-center mt-4 lg:mt-0">
              <div
                class="bg-white p-4 rounded-[2rem] shadow-sm lg:bg-transparent lg:shadow-none lg:p-0 w-full flex justify-center">
                <img src="images/charging-illustration.png" alt="Emergency Charging"
                  class="w-full max-w-xs sm:max-w-sm md:max-w-md rounded-2xl shadow-lg lg:shadow-xl object-contain"
                  loading="lazy">
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA & MITRA HOST SECTION (UPDATED) -->
      <section class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

          <!-- Dual Card CTA -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- CARD 1: UNTUK DRIVER -->
            <div
              class="bg-[#00C853] p-8 sm:p-10 rounded-3xl shadow-xl text-white relative overflow-hidden flex flex-col justify-center group transition hover:shadow-2xl hover:-translate-y-1">
              <div
                class="absolute top-0 right-0 -mr-8 -mt-8 w-40 h-40 bg-white opacity-10 rounded-full group-hover:scale-110 transition-transform duration-500">
              </div>
              <div class="relative z-10">
                <div class="flex items-center gap-3 mb-4">
                  <span class="bg-white/20 p-2 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg"
                      class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg></span>
                  <span class="uppercase text-sm font-bold tracking-wider text-lime-100">Untuk Pengendara</span>
                </div>
                <h3 class="text-3xl font-bold mb-4 leading-tight">Cari Charger? Gabung Sekarang.</h3>
                <p class="mb-8 text-lime-50 text-base leading-relaxed">
                  Akses ribuan stasiun pengisian daya dengan satu aplikasi. Hemat waktu, biaya transparan, dan bebas
                  antri.
                </p>
                <Link :href="route('register')"
                  class="inline-block bg-white text-[#00C853] font-bold px-8 py-4 rounded-xl shadow-md hover:bg-lime-50 active:scale-95 transition duration-300 w-full sm:w-auto text-center">
                  Daftar Gratis
                </Link>
              </div>
            </div>

            <!-- CARD 2: UNTUK MITRA HOST (BARU) -->
            <div
              class="bg-gray-900 p-8 sm:p-10 rounded-3xl shadow-xl text-white relative overflow-hidden flex flex-col justify-center group transition hover:shadow-2xl hover:-translate-y-1">
              <div
                class="absolute bottom-0 left-0 -ml-8 -mb-8 w-40 h-40 bg-[#CCFF00] opacity-10 rounded-full group-hover:scale-110 transition-transform duration-500">
              </div>
              <div class="relative z-10">
                <div class="flex items-center gap-3 mb-4">
                  <span class="bg-white/10 p-2 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg"
                      class="h-6 w-6 text-[#CCFF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg></span>
                  <span class="uppercase text-sm font-bold tracking-wider text-gray-400">Untuk Pemilik Rumah</span>
                </div>
                <h3 class="text-3xl font-bold mb-4 leading-tight">Punya Charger Nganggur? <span
                    class="text-[#CCFF00]">Jadikan Cuan.</span></h3>
                <p class="mb-8 text-gray-400 text-base leading-relaxed">
                  Sewakan listrik rumah Anda ke sesama pengguna EV. Jadilah bagian dari jaringan energi komunitas
                  terbesar.
                </p>
                <Link :href="route('become.host')"
                  class="inline-block bg-[#CCFF00] text-black font-bold px-8 py-4 rounded-xl shadow-md hover:bg-[#b3e600] active:scale-95 transition duration-300 w-full sm:w-auto text-center">
                  Jadi Mitra Host
                </Link>
              </div>
            </div>

          </div>
        </div>
      </section>

      <!-- TRUSTED BY -->
      <section class="py-10 lg:py-20 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h3 class="text-sm sm:text-base md:text-lg font-semibold text-gray-400 uppercase tracking-widest mb-8">
            Didukung Oleh Teknologi
          </h3>
          <div
            class="grid grid-cols-3 sm:flex sm:flex-wrap justify-center items-center gap-8 sm:gap-12 grayscale opacity-70">
            <img src="https://cdn.worldvectorlogo.com/logos/tesla-9.svg" alt="Tesla"
              class="h-6 sm:h-8 md:h-10 mx-auto transition hover:grayscale-0 hover:opacity-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/e/e2/BYD_Auto_2022_logo.svg" alt="BYD"
              class="h-6 sm:h-8 md:h-10 mx-auto transition hover:grayscale-0 hover:opacity-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Hyundai_Motor_Company_logo.svg" alt="Hyundai"
              class="h-6 sm:h-8 md:h-10 mx-auto transition hover:grayscale-0 hover:opacity-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b6/KIA_logo3.svg" alt="Kia"
              class="h-6 sm:h-8 md:h-10 mx-auto transition hover:grayscale-0 hover:opacity-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/1/16/Wuling-logo.svg" alt="Wuling"
              class="h-6 sm:h-8 md:h-10 col-span-3 sm:col-span-1 mx-auto transition hover:grayscale-0 hover:opacity-100">
          </div>
        </div>
      </section>

    </main>

    <!-- MODAL INFO (MOBILE) -->
    <Transition name="fade">
      <div v-if="isModalOpen" @click="closeModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-80 backdrop-blur-sm z-[99] flex items-center justify-center p-4">
        <div @click.stop
          class="modal-content bg-white p-6 rounded-3xl shadow-2xl max-w-md w-full transform transition-all">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-xl font-bold text-gray-900">Informasi</h4>
            <button @click="closeModal"
              class="bg-gray-100 p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-200 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <p class="text-gray-600 mb-6">
            Fitur pencarian sedang dioptimalkan untuk pengalaman mobile yang lebih baik.
          </p>
          <div class="flex justify-end">
            <button @click="closeModal"
              class="w-full sm:w-auto bg-[#00C853] text-white font-bold px-6 py-3 rounded-xl hover:bg-[#00A142] transition duration-300">Mengerti</button>
          </div>
        </div>
      </div>
    </Transition>

    <Footer />
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>