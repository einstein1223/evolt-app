<template>
  <!-- Komponen Footer menggunakan Tailwind CSS -->
  <footer class="bg-gray-900 text-white pt-12 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8 border-b border-gray-700 pb-8 mb-8">
        
        <!-- Logo & Description -->
        <div>
          <a href="/" class="text-2xl font-extrabold text-green-400 mb-4 block">E-<span class="text-white">Volt</span></a>
          <p class="text-gray-400 text-sm leading-relaxed">
            Platform koneksi cerdas untuk pengguna kendaraan listrik dan stasiun pengisian daya.
          </p>
        </div>

        <!-- Quick Links (Layanan) -->
        <div>
          <h5 class="text-lg font-semibold mb-4 text-lime-400">Layanan</h5>
          <ul class="space-y-2 text-sm text-gray-400">
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Cari Stasiun</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Reservasi</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Langganan</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Aplikasi Mobile</a></li>
          </ul>
        </div>
        
        <!-- Company (Perusahaan) -->
        <div>
          <h5 class="text-lg font-semibold mb-4 text-lime-400">Perusahaan</h5>
          <ul class="space-y-2 text-sm text-gray-400">
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Tentang Kami</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Karir</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Pers</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Blog</a></li>
          </ul>
        </div>
        
        <!-- Legal (Hukum) -->
        <div>
          <h5 class="text-lg font-semibold mb-4 text-lime-400">Hukum</h5>
          <ul class="space-y-2 text-sm text-gray-400">
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Kebijakan Privasi</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Syarat Penggunaan</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">FAQ</a></li>
            <li><a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400">Bantuan</a></li>
          </ul>
        </div>

      </div>

      <!-- Copyright & Social -->
      <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
        <!-- Mendapatkan tahun secara dinamis -->
        <p>&copy; {{ currentYear }} EV.Charge. All rights reserved.</p>
        <div class="flex space-x-4 mt-4 md:mt-0">
          <!-- Placeholder untuk ikon media sosial. Pastikan Anda sudah mengimpor Font Awesome atau library ikon lainnya. -->
          <a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400"><i class="fab fa-twitter"></i></a>
          <a href="#" class="hover:text-lime-400 transition focus:outline-none focus:text-lime-400"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue';

// Menggunakan ref untuk menyimpan tahun saat ini
const currentYear = ref(new Date().getFullYear());
</script>
