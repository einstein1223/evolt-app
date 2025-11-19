<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue'; 
import ContactForm from '@/Components/ContactForm.vue'; 
// Asumsi gambar mapdot.png diakses melalui jalur publik

// Data statis untuk bagian "Mari Berkolaborasi"
const contactData = {
  phone: '+94 4444 5555 6',
  address: 'Jl. Ahmad Yani Batam Kota. Kota Batam. kepulauan Riau. Indonesia', 
};
</script>

<template>
  <Head title="Hubungi Kami" />

  <Navbar />

  <div class="min-h-screen">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      
      <div class="flex flex-col lg:flex-row items-start justify-between mb-16">
        
        <div class="lg:w-2/5 space-y-4">
          <div class="flex items-center space-x-3">
            <h1 class="text-4xl font-bold text-gray-800">Mari Berkolaborasi</h1>
          </div>
          <p class="text-gray-600 max-w-sm">EVolt siap menjadi platform utama Anda untuk efisiensi SPKLU. Kami mengundang pemilik stasiun, operator energi, dan brand kendaraan listrik untuk bergabung dalam ekosistem Zero Operator Cost kami. Dapatkan dashboard audit terpusat, jangkauan pengguna yang lebih luas, dan bagi hasil yang transparan.</p>
          </div>

        <div class="lg:w-3/5 mt-8 lg:mt-0 lg:flex lg:justify-end">
          <div class="w-full h-64 lg:h-80 rounded-lg">
            <img src="/images/mapdot.png" alt="Peta Dunia Titik-titik" class="w-full h-full object-contain" />
          </div>
        </div>
      </div>
      
      <div class="flex flex-col sm:flex-row justify-start pt-8 pb-12 border-t border-gray-100 space-y-6 sm:space-y-0">
          
          <div class="w-full sm:w-auto sm:pr-16">
            <p class="text-sm font-semibold mb-2 text-gray-700">Ikuti Kami</p>
            <div class="flex space-x-4 text-gray-800">
              <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
              <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
              <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
              <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
            </div>
          </div>

          <div class="sm:px-16 sm:border-l border-gray-300 pt-6 sm:pt-0">
            <p class="text-sm font-semibold mb-2 text-gray-700 sm:hidden">Telepon</p>
            <div class="flex items-start">
              <span class="text-gray-500 mr-2 mt-0.5"><i class="fas fa-phone"></i></span>
              <p class="font-medium text-gray-800">{{ contactData.phone }}</p>
            </div>
          </div>

          <div class="sm:pl-16 sm:border-l border-gray-300 pt-6 sm:pt-0">
            <p class="text-sm font-semibold mb-2 text-gray-700 sm:hidden">Alamat</p>
            <div class="flex items-start">
              <span class="text-gray-500 mr-2 mt-0.5"><i class="fas fa-map-marker-alt"></i></span>
              <p class="text-gray-800 whitespace-pre-line max-w-xs">{{ contactData.address.replace('tetapi', 'tetapi\njuga') }}</p>
            </div>
          </div>
        </div>
      
    </div> 
    
    <div class="bg-[#dae20083] py-16 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
          <h2 class="text-xl font-bold text-gray-800">Sapa Kami</h2>
          <p class="text-sm text-gray-600 mt-1">Apakah Anda memiliki pertanyaan mengenai proses booking, memerlukan bantuan selama sesi pengisian, atau ingin memberikan masukan?</p>
        </div>
        
        <ContactForm />

      </div>
    </div>
  </div>

  <Footer />
</template>

<style scoped>
/* Gaya kustom untuk ikon media sosial agar sesuai gambar (tetap sama) */
.social-icon {
    width: 40px; 
    height: 40px;
    border-radius: 50%;
    background-color: white;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); 
    display: flex;
    justify-content: center;
    align-items: center;
    color: #1f2937;
    transition: transform 0.2s, box-shadow 0.2s;
}

.social-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}

.social-icon i {
    font-size: 16px; 
}

/* Penyesuaian untuk memecah teks alamat menjadi dua baris */
.whitespace-pre-line {
    white-space: pre-line;
}
</style>