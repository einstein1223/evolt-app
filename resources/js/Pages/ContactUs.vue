<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import ContactForm from '@/Components/ContactForm.vue';

// Data statis
const contactData = {
  phone: '+62 812 3456 7890', // Contoh format Indo
  email: 'hello@evolt.id',
  address: 'Jl. Ahmad Yani Batam Kota,\nKota Batam, Kepulauan Riau,\nIndonesia',
};
</script>

<template>

  <Head title="Hubungi Kami" />

  <div class="min-h-screen flex flex-col bg-white font-sans text-gray-600">
    <Navbar />

    <main class="flex-grow">

      <!-- SECTION 1: HERO & MAP -->
      <div class="max-w-7xl mx-auto pt-24 pb-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-12">

          <!-- Text Content -->
          <div class="lg:w-5/12 space-y-6 text-center lg:text-left order-2 lg:order-1">
            <div
              class="inline-block px-4 py-1.5 rounded-full bg-lime-100 text-lime-700 text-sm font-bold tracking-wide uppercase mb-2">
              👋 Hubungi Kami
            </div>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 leading-tight">
              Mari Berkolaborasi <br />
              <span class="text-[#00C853]">Bersama EVolt</span>
            </h1>
            <p class="text-lg text-gray-600 leading-relaxed">
              Kami mengundang pemilik stasiun, operator energi, dan brand kendaraan listrik untuk bergabung dalam
              ekosistem Zero Operator Cost kami.
            </p>
            <p class="text-gray-500">
              Dapatkan dashboard audit terpusat dan jangkauan pengguna yang lebih luas.
            </p>
          </div>

          <!-- Map Image (Kept as requested) -->
          <div class="lg:w-6/12 w-full order-1 lg:order-2">
            <div class="relative rounded-[2.5rem] bg-gray-50 p-8 border border-gray-100 shadow-inner">
              <!-- Decorative Pulse -->
              <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-lime-200 rounded-full blur-3xl opacity-40 pointer-events-none">
              </div>

              <img src="/images/mapdot.png" alt="Peta Persebaran Global"
                class="relative z-10 w-full h-auto object-contain hover:scale-105 transition-transform duration-700" />
            </div>
          </div>

        </div>
      </div>

      <!-- SECTION 2: INFO CARDS (Phone, Address, Socials) -->
      <!-- Menggunakan Grid Card agar responsif dan rapi -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <!-- Card 1: Social Media -->
          <div
            class="bg-white border border-gray-100 p-8 rounded-[2rem] shadow-lg shadow-gray-100/50 hover:shadow-xl transition duration-300 flex flex-col items-center text-center group">
            <div
              class="w-14 h-14 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mb-4 text-2xl group-hover:scale-110 transition duration-300">
              <i class="fas fa-share-alt"></i>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-4">Ikuti Kami</h3>
            <div class="flex space-x-3">
              <a href="#" class="social-btn bg-[#1877F2] text-white"><i class="fab fa-facebook-f"></i></a>
              <a href="#" class="social-btn bg-[#E1306C] text-white"><i class="fab fa-instagram"></i></a>
              <a href="#" class="social-btn bg-[#1DA1F2] text-white"><i class="fab fa-twitter"></i></a>
              <a href="#" class="social-btn bg-[#0077B5] text-white"><i class="fab fa-linkedin-in"></i></a>
            </div>
          </div>

          <!-- Card 2: Phone & Email -->
          <div
            class="bg-white border border-gray-100 p-8 rounded-[2rem] shadow-lg shadow-gray-100/50 hover:shadow-xl transition duration-300 flex flex-col items-center text-center group">
            <div
              class="w-14 h-14 bg-lime-100 text-lime-600 rounded-2xl flex items-center justify-center mb-4 text-2xl group-hover:scale-110 transition duration-300">
              <i class="fas fa-headset"></i>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-2">Layanan Pelanggan</h3>
            <p class="text-gray-600 mb-1 font-medium">{{ contactData.phone }}</p>
            <p class="text-gray-500 text-sm">{{ contactData.email }}</p>
          </div>

          <!-- Card 3: Address -->
          <div
            class="bg-white border border-gray-100 p-8 rounded-[2rem] shadow-lg shadow-gray-100/50 hover:shadow-xl transition duration-300 flex flex-col items-center text-center group">
            <div
              class="w-14 h-14 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mb-4 text-2xl group-hover:scale-110 transition duration-300">
              <i class="fas fa-map-pin"></i>
            </div>
            <h3 class="font-bold text-gray-900 text-lg mb-2">Kantor Pusat</h3>
            <p class="text-gray-600 whitespace-pre-line leading-relaxed text-sm">
              {{ contactData.address }}
            </p>
          </div>

        </div>
      </div>

      <!-- SECTION 3: CONTACT FORM AREA -->
      <!-- Dibuat rounded container agar lebih 'friendly' -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="bg-[#CCFF00] rounded-[3rem] px-6 py-16 sm:px-12 lg:px-20 relative overflow-hidden">

          <!-- Background Decoration -->
          <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white opacity-20 rounded-full blur-2xl"></div>
          <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 bg-lime-600 opacity-10 rounded-full blur-2xl">
          </div>

          <div class="relative z-10 grid lg:grid-cols-3 gap-12 items-start">

            <!-- Form Header Text -->
            <div class="lg:col-span-1 text-center lg:text-left">
              <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Sapa Kami 👋</h2>
              <p class="text-gray-800 font-medium text-lg mb-6 leading-relaxed opacity-90">
                Punya pertanyaan soal proses booking? Butuh bantuan teknis? Atau sekadar ingin menyapa?
              </p>
              <p class="text-sm text-gray-700 bg-white/40 p-4 rounded-2xl inline-block backdrop-blur-sm">
                Tim support kami biasanya membalas dalam <span class="font-bold">1x24 jam</span> di hari kerja.
              </p>
            </div>

            <!-- The Form Component -->
            <!-- Kita bungkus form dalam card putih agar kontras dan bersih -->
            <div class="lg:col-span-2 bg-white rounded-[2rem] shadow-2xl p-2 sm:p-4">
              <ContactForm />
            </div>

          </div>
        </div>
      </div>

    </main>

    <Footer />
  </div>
</template>

<style scoped>
/* Custom Social Buttons */
.social-btn {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  font-size: 18px;
}

.social-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.15);
}

/* Memastikan fontawesome icons ter-load jika belum ada di index.html */
/* Jika menggunakan CDN, pastikan <link> ada di <head> file utama project */
</style>