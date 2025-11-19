<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

// Hanya menyisakan logic dasar untuk modal (jika tombol "Mulai Sekarang" masih ingin berfungsi)
const isModalOpen = ref(false); 

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const closePickersOnOutsideClick = (event) => {
    // Menutup modal jika diklik di luar
    if (isModalOpen.value && !event.target.closest('.modal-content')) {
        closeModal();
    }
};

const updateViewport = () => {
  // Hanya jika perlu logic mobile/desktop lainnya
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
  <div class="min-h-screen flex flex-col bg-white">
    <Navbar />

    <main class="flex-grow relative z-10"> 
      
      <section class="bg-[#B6F500] pt-8 sm:pt-12 md:pt-16 lg:pt-20 pb-12 sm:pb-16 md:pb-20 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 relative z-10">
          <div class="flex flex-col lg:flex-row items-center justify-between gap-6 md:gap-8 lg:gap-12">
            
            <div class="w-full lg:w-1/2 text-center lg:text-left">
              <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-extrabold text-[#333] leading-tight mb-3 sm:mb-4 md:mb-6 text-balance">
                Menghubungkan Pengendara Dengan Stasiun Pengecasan Cerdas
              </h2>
              <p class="text-sm sm:text-base md:text-lg lg:text-xl text-gray-800 mb-6 sm:mb-8 max-w-lg mx-auto lg:mx-0">
                Temukan, reservasi, dan isi daya dengan mudah di mana saja.
              </p>
              
              <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center lg:justify-start">
  <Link
    :href="route('register')"
    class="bg-[#00C853] text-white font-semibold px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg shadow-lg hover:bg-[#00A142] transition duration-300 focus:outline-none focus:ring-4 focus:ring-lime-300 text-sm sm:text-base text-center"
  >
    Mulai Sekarang
  </Link>

  <Link
    href="#"
    class="bg-white border-2 border-[#00C853] text-[#00C853] font-semibold px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg hover:bg-gray-50 transition duration-300 focus:outline-none focus:ring-4 focus:ring-lime-300 text-sm sm:text-base text-center"
  >
    Pelajari Lebih Lanjut
  </Link>
</div>

            </div>

            <div class="w-full lg:w-1/2 flex justify-center">
              <img 
                src="images/mobil.png" 
                alt="EV Car" 
                class="w-3/4 sm:w-2/3 md:w-3/5 lg:w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg"
              >
            </div>
          </div>
        </div>
        
        </section>
      

      <section class="py-12 sm:py-16 md:py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row items-center gap-8 md:gap-12 text-center lg:text-left">
            <div class="lg:w-1/2"> 
              <span class="text-xs sm:text-sm font-semibold text-lime-600 uppercase tracking-widest block mb-2 sm:mb-3">Langkah 1</span>
              <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-900 mb-3 sm:mb-4 md:mb-6 text-balance">
                Pencarian Stasiun <span class="text-lime-600">Terdekat</span>
              </h2>
              <p class="text-sm sm:text-base md:text-lg text-gray-600 leading-relaxed max-w-xl mx-auto lg:mx-0">
                Aplikasi menampilkan peta geolokasi dengan semua SPKLU yang terdaftar. Kami menggunakan data Status Real-Time yang secara akurat menampilkan slot yang Available (siap pakai) ke pengguna.
              </p>
            </div>
            <div class="lg:w-1/2 flex justify-center w-full">
              <img src="https://placehold.co/400x500/f0f9ff/000?text=App+Screenshot" alt="Mobile App" class="w-full max-w-xs sm:max-w-sm md:max-w-md rounded-2xl sm:rounded-3xl shadow-2xl" loading="lazy">
            </div>
          </div>
        </div>
      </section>

      <section class="py-12 sm:py-16 md:py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row-reverse items-center gap-8 md:gap-12 text-center lg:text-left">
            <div class="lg:w-1/2">
              <span class="text-xs sm:text-sm font-semibold text-lime-600 uppercase tracking-widest block mb-2 sm:mb-3">Langkah 2</span>
              <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-900 mb-3 sm:mb-4 md:mb-6 text-balance">
                Reservasi Slot <span class="text-lime-600">Terjamin</span>
              </h2>
              <p class="text-sm sm:text-base md:text-lg text-gray-600 leading-relaxed max-w-xl mx-auto lg:mx-0">
                Pengguna dapat memilih stasiun dan mengunci slot pengisian spesifik. Sistem mengirimkan perintah OCPP Remote Reservation ke stasiun. Setelah pembayaran, slot terjamin dan pengguna menerima QR Booking untuk memulai sesi tanpa antrian.
              </p>
            </div>
            <div class="lg:w-1/2 flex justify-center w-full">
              <img src="https://placehold.co/500x350/ffffff/333?text=Stacked+UI+Cards" alt="Stacked Cards UI" class="w-full max-w-xs sm:max-w-sm md:max-w-md rounded-2xl sm:rounded-3xl shadow-xl" loading="lazy">
            </div>
          </div>
        </div>
      </section>

      <section class="py-12 sm:py-16 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row items-center gap-8 md:gap-12 text-center lg:text-left">
            <div class="lg:w-1/2">
              <span class="text-xs sm:text-sm font-semibold text-lime-600 uppercase tracking-widest block mb-2 sm:mb-3">Langkah 3</span>
              <h2 class="text-2xl sm:text-3xl md:text-4xl font-semibold text-gray-900 mb-3 sm:mb-4 md:mb-6 text-balance">
                <span class="text-lime-600"> Otomasi</span> Operasional & Data Audit
              </h2>
              <p class="text-sm sm:text-base md:text-lg text-gray-600 leading-relaxed max-w-xl mx-auto lg:mx-0">
                Platform dibangun di atas protokol OCPP yang memungkinkan Komunikasi Dua Arah antara Backend Server dan SPKLU. Integrasi ini menghasilkan Audit Data Transaksi Otomatis dan mendukung model Zero Operator Cost yang efisien.
              </p>
            </div>
            <div class="lg:w-1/2 flex justify-center w-full">
              <img src="https://placehold.co/500x350/a7f3d0/000?text=VR+Experience" alt="VR User" class="w-full max-w-xs sm:max-w-sm md:max-w-md rounded-2xl sm:rounded-3xl shadow-xl" loading="lazy">
            </div>
          </div>
        </div>
      </section>

      <section class="py-12 sm:py-16 md:py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 md:gap-8">
            <div class="bg-white p-4 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl shadow-lg border border-gray-100">
              <h3 class="text-xl sm:text-2xl md:text-3xl font-semibold text-gray-900 mb-3 sm:mb-4 md:mb-6">
                "Pengalaman Pengecasan Terbaik"
              </h3>
              <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6 leading-relaxed">
                Ini adalah kutipan testimonial dari pengguna kami. Lorem Ipsum is simply dummy text. 
              </p>
               <button class="bg-[#00C853] text-white font-semibold px-4 sm:px-6 py-2 sm:py-3 rounded-lg shadow-xl hover:bg-[#00A142] transition duration-300 focus:outline-none focus:ring-4 focus:ring-lime-300 w-full sm:w-auto text-sm sm:text-base">
                  Lihat Testimoni
                </button>
            </div>
            <div class="bg-[#00C853] p-4 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl shadow-lg text-white relative overflow-hidden">
                <h3 class="text-xl sm:text-2xl md:text-3xl font-semibold mb-3 sm:mb-4 md:mb-6">
                Siap Bergabung?
              </h3>
              <p class="mb-4 sm:mb-6 text-lime-100 text-sm sm:text-base leading-relaxed">
                Daftar sekarang dan nikmati kemudahan menemukan stasiun pengisian daya di seluruh Indonesia.
              </p>
              <button class="bg-white text-gray-900 font-semibold px-4 sm:px-6 py-2 sm:py-3 rounded-lg shadow-xl hover:bg-gray transition duration-300 focus:outline-none focus:ring-4 focus:ring-white w-full sm:w-auto text-sm sm:text-base">
                Daftar Gratis
              </button>
            </div>
          </div>
        </div>
      </section>

      <section class="py-12 sm:py-16 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 text-center">
          <h3 class="text-base sm:text-lg md:text-xl font-medium text-gray-500 mb-8 sm:mb-10 md:mb-12">
            Dipercaya oleh banyak perusahaan hebat
          </h3>
          <div class="flex flex-wrap justify-center items-center gap-4 sm:gap-8 md:gap-12 lg:gap-16 grayscale opacity-60">
            <img src="https://cdn.worldvectorlogo.com/logos/tesla-9.svg" alt="Tesla" class="h-8 sm:h-10 md:h-12 transition duration-300 hover:grayscale-0 hover:opacity-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/e/e2/BYD_Auto_2022_logo.svg" alt="BYD" class="h-8 sm:h-10 md:h-12 transition duration-300 hover:grayscale-0 hover:opacity-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Hyundai_Motor_Company_logo.svg" alt="Hyundai" class="h-8 sm:h-10 md:h-12 transition duration-300 hover:grayscale-0 hover:opacity-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b6/KIA_logo3.svg" alt="Kia" class="h-8 sm:h-10 md:h-12 transition duration-300 hover:grayscale-0 hover:opacity-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/1/16/Wuling-logo.svg" alt="Wuling" class="h-8 sm:h-10 md:h-12 transition duration-300 hover:grayscale-0 hover:opacity-100">
          </div>
        </div>
      </section>
      
    </main>
    
    <Transition name="fade">
      <div v-if="isModalOpen" @click="closeModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-[99] flex items-center justify-center p-3 sm:p-4">
        <div @click.stop class="modal-content bg-white p-4 sm:p-6 md:p-8 rounded-xl sm:rounded-2xl shadow-2xl max-w-lg w-full transform transition-all max-h-[90vh] overflow-y-auto">
          <div class="flex justify-between items-start mb-3 sm:mb-4 md:mb-6">
            <h4 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">Aksi Dipicu</h4>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
              <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>
          <p class="text-sm sm:text-base text-gray-600 mb-3 sm:mb-4">
            Ini adalah modal pemicu karena Search Box telah dihapus.
          </p>
          <div class="flex justify-end gap-3">
            <button @click="closeModal" class="bg-[#00C853] text-white font-semibold px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:bg-[#00A142] transition duration-300 text-sm sm:text-base">Tutup</button>
          </div>
        </div>
      </div>
    </Transition>
    
    <Footer />
  </div>
</template>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>