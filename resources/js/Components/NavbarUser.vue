<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
  isTransparent: {
    type: Boolean,
    default: false
  }
});

const isScrolled = ref(false);

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <header :class="[
    'fixed top-0 left-0 right-0 w-full z-50 transition-all duration-300 ease-in-out',
    isScrolled ? 'bg-white/80 backdrop-blur-md shadow-md py-2' : 'bg-transparent py-4'
  ]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center transition-all duration-300">
      <Link href="/dashboard" class="text-2xl font-semibold text-[#00C853]">
        E-<span class="text-gray-900 font-bold">VOLT</span>
      </Link>

      <Link :href="route('profile.edit')"
        class="flex items-center space-x-2 p-2 rounded-full hover:bg-gray-100/50 transition duration-150 focus:outline-none focus:ring-2 focus:ring-[#00C853] group"
        title="Pengaturan Profil">
        <div
          class="w-9 h-9 bg-lime-500 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-inner group-hover:bg-lime-600 transition duration-150">
          U
        </div>
        <span
          class="text-gray-800 font-medium hidden sm:inline group-hover:text-lime-700 transition duration-150">Profil</span>
      </Link>
    </div>
  </header>
</template>