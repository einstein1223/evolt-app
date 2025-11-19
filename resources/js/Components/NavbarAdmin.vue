<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <header class="w-full bg-white shadow-md relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
      <!-- Logo -->
      <Link href="/admin-dashboard" class="text-2xl font-semibold text-[#00C853]">
        E-<span class="text-gray-900 font-bsemiold">VOLT</span>
      </Link>

      <!-- Ikon Profil (selalu tampil, responsif) -->
      <Link
        href="/admin/profile"
        class="flex items-center space-x-2 p-2 rounded-full hover:bg-gray-100 transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#00C853] group"
        title="Pengaturan Profil"
      >
        <div class="w-9 h-9 bg-lime-500 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-inner group-hover:bg-lime-600 transition duration-150">
          U
        </div>
        <span class="text-gray-800 font-medium hidden sm:inline group-hover:text-lime-700 transition duration-150">Profil</span>
      </Link>
    </div>


  </header>
</template>
