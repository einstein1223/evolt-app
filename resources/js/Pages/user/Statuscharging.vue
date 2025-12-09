<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Navbar from '@/Components/NavbarUser.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    stationName: String,
    location: String,
    duration: [Number, String],
    power: String,
    type: String,
});

const isSliding = ref(false);
const sliderOffset = ref(0);
const sliderContainer = ref(null);
const sliderHandle = ref(null);
const maxSlide = ref(0);

const updateMaxSlide = () => {
    if (sliderContainer.value && sliderHandle.value) {
        maxSlide.value = sliderContainer.value.clientWidth - sliderHandle.value.clientWidth - 8; // 8px padding total (4px left + 4px right)
    }
};

onMounted(() => {
    updateMaxSlide();
    window.addEventListener('resize', updateMaxSlide);
    window.addEventListener('mouseup', onMouseUp);
    window.addEventListener('touchend', onTouchEnd);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('touchmove', onTouchMove);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateMaxSlide);
    window.removeEventListener('mouseup', onMouseUp);
    window.removeEventListener('touchend', onTouchEnd);
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('touchmove', onTouchMove);
});

const startDrag = (clientX) => {
    isSliding.value = true;
};

const onTouchStart = (e) => startDrag(e.touches[0].clientX);
const onMouseDown = (e) => startDrag(e.clientX);

const moveDrag = (clientX) => {
    if (!isSliding.value || !sliderContainer.value) return;

    const rect = sliderContainer.value.getBoundingClientRect();
    // Calculate raw offset from left padding (4px)
    const rawX = clientX - rect.left - 4;

    if (rawX < 0) {
        sliderOffset.value = 0;
    } else if (rawX > maxSlide.value) {
        sliderOffset.value = maxSlide.value;
    } else {
        sliderOffset.value = rawX;
    }
};

const onTouchMove = (e) => moveDrag(e.touches[0].clientX);
const onMouseMove = (e) => moveDrag(e.clientX);

const endDrag = () => {
    if (!isSliding.value) return;
    isSliding.value = false;

    if (sliderOffset.value > maxSlide.value * 0.9) {
        // Success threshold
        sliderOffset.value = maxSlide.value;
        // Trigger action
        setTimeout(() => {
            alert('Pengisian dihentikan!');
            // Reset after action
            sliderOffset.value = 0;
        }, 200);
    } else {
        // Snap back
        sliderOffset.value = 0;
    }
};

const onTouchEnd = () => endDrag();
const onMouseUp = () => endDrag();

</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 font-sans text-gray-800 pt-16">
        <Navbar />

        <main class="flex-grow flex flex-col items-center px-4 py-6 pt-24">
            <div
                class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden relative min-h-[80vh] flex flex-col">

                <!-- Header -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
                    <Link href="/map-results" class="text-gray-800 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                    </Link>
                    <h1 class="text-lg font-bold text-[#1e1b4b]">Charging</h1>
                    <div class="w-6"></div> <!-- Spacer -->
                </div>

                <!-- Content -->
                <div class="flex-grow flex flex-col items-center px-6 pt-8 pb-6 text-center">

                    <h2 class="text-2xl font-bold text-[#1e1b4b] mb-2">Sedang mengisi</h2>
                    <p class="text-gray-500 text-sm mb-8 leading-relaxed">
                        Anda dapat menghentikan pengisian melalui aplikasi atau langsung dari kendaraan Anda.
                    </p>

                    <!-- Illustration Placeholder -->
                    <div class="w-full h-48 mb-8 flex items-center justify-center">
                        <!-- Replace with actual SVG/Image -->
                        <img src="/images/charging-illustration.png" alt="Charging Illustration"
                            class="h-full object-contain"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <div class="hidden flex-col items-center justify-center text-gray-300">
                            <i class="fas fa-car-battery text-6xl mb-2"></i>
                            <span class="text-xs">Illustration Here</span>
                        </div>
                    </div>

                    <!-- Duration Input/Display -->
                    <div class="w-full mb-8">
                        <div class="bg-gray-100 rounded-xl p-3 mb-3 text-left">
                            <span class="text-gray-500 text-sm">Durasi ({{ props.duration }})</span>
                        </div>
                        <div class="flex gap-2 justify-between">
                            <div v-for="i in 8" :key="i" class="h-8 w-full bg-gray-200 rounded-md"></div>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="w-full bg-gray-50 rounded-2xl p-4 border border-gray-100 text-left mb-auto">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="text-[#00C853] text-xl">
                                <i class="fas fa-charging-station"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-[#1e1b4b] text-base">{{ props.stationName }}</h3>
                                <p class="text-gray-500 text-xs">{{ props.location }}</p>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex items-center gap-2 text-xs text-gray-500">
                            <span class="flex items-center gap-1"><i class="fas fa-plug"></i> {{ props.power }}</span>
                            <span>•</span>
                            <span class="flex items-center gap-1"><i class="fas fa-bolt"></i> AC</span>
                            <span>•</span>
                            <span class="flex items-center gap-1"><i class="fas fa-circle-notch"></i> {{ props.type
                            }}</span>
                        </div>
                    </div>

                    <!-- Slider Button -->
                    <div ref="sliderContainer"
                        class="w-full h-16 bg-[#00C853] rounded-full relative overflow-hidden cursor-pointer select-none mt-6 shadow-lg shadow-[#00C853]/30">
                        <div class="absolute inset-0 flex items-center justify-center text-white font-bold text-sm z-10 pointer-events-none transition-opacity duration-200"
                            :style="{ opacity: isSliding ? 0.5 : 1 }">
                            Geser untuk stop pengisian
                        </div>

                        <!-- Slider Handle -->
                        <div ref="sliderHandle"
                            class="absolute top-1 bottom-1 left-1 bg-white rounded-full shadow-md flex items-center justify-center z-20"
                            :class="{ 'transition-transform duration-300 ease-out': !isSliding }"
                            :style="{ width: '56px', transform: `translateX(${sliderOffset}px)` }"
                            @touchstart.stop="onTouchStart" @mousedown.stop="onMouseDown">
                            <i class="fas fa-angle-double-right text-[#00C853] text-xl animate-pulse"></i>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Custom styles if needed */
</style>
