<script setup>
import { ref } from 'vue'; 
import { Link } from '@inertiajs/vue3'; 

const isMenuOpen = ref(false); 
</script>

<template>
    <header class="w-full bg-white shadow-md relative z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <Link :href="route('dashboard')" class="text-2xl font-semibold text-[#00C853]">
          E-<span class="text-gray-900 font-bsemiold">VOLT</span>
        </Link>
        
        <nav class="hidden md:flex ml-auto items-center gap-12 text-gray-700 font-medium">
            <Link :href="route('welcome')" class="hover:text-[#00C853] transition duration-150">Beranda</Link>
            <Link :href="route('about')" class="hover:text-[#00C853] transition duration-150">Tentang Kami</Link>
            <Link :href="route('contact')" class="hover:text-[#00C853] transition duration-150">Hubungi Kami</Link>
        </nav>

        <div class="hidden md:flex items-center ml-6">
          <Link :href="route('login')" class="px-6 py-2 border-2 border-[#00C853] text-[#00C853] font-semibold rounded-lg hover:bg-[#00C853] hover:text-white transition duration-300 shadow-md">
            Masuk
          </Link>
        </div>

        <button @click="isMenuOpen = !isMenuOpen" class="md:hidden text-gray-700 hover:text-[#00C853] transition duration-150 ml-4">
          <svg v-if="isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
      </div>
      
      <transition 
          name="mobile-menu" 
          enter-active-class="transition ease-out duration-200" 
          enter-from-class="transform opacity-0 -translate-y-2" 
          enter-to-class="transform opacity-100 translate-y-0" 
          leave-active-class="transition ease-in duration-150" 
          leave-from-class="transform opacity-100 translate-y-0" 
          leave-to-class="transform opacity-0 -translate-y-2"
      >
          <div v-if="isMenuOpen" class="md:hidden absolute top-full left-0 right-0 z-40">
            <div class="bg-white shadow-lg border-t border-gray-100 rounded-b-xl mx-4 p-4">
              <nav class="flex flex-col p-2 space-y-2">
                <Link @click="isMenuOpen = false" :href="route('welcome')" class="py-2 px-3 block hover:bg-lime-50 rounded-lg text-gray-700 font-medium transition duration-150">Beranda</Link>
                <Link @click="isMenuOpen = false" :href="route('about')" class="py-2 px-3 block hover:bg-lime-50 rounded-lg text-gray-700 font-medium transition duration-150">Tentang Kami</Link>
                <Link @click="isMenuOpen = false" :href="route('contact')" class="py-2 px-3 block hover:bg-lime-50 rounded-lg text-gray-700 font-medium transition duration-150">Hubungi Kami</Link>
               
                <Link @click="isMenuOpen = false" :href="route('login')" class="mt-4 px-6 py-2 border-2 border-[#00C853] text-[#00C853] font-semibold rounded-lg hover:bg-[#00C853] hover:text-white transition duration-300 shadow-md text-center">
                    Masuk
                </Link>
              </nav>
            </div>
          </div>
      </transition>
    </header>
</template>