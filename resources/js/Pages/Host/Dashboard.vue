<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Head, Link } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';

// --- PROPS & DATA ---
// Data ini sekarang dikirim dari HostDashboardController
const props = defineProps({
    station: {
        type: Object,
        default: () => null
    },
    stats: {
        type: Object,
        default: () => ({ total_revenue: 0, month_revenue: 0, growth: 0 })
    },
    recent_guests: {
        type: Array,
        default: () => []
    },
    weekly_chart: { // Data grafik dinamis dari database
        type: Array,
        default: () => []
    }
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const userNameDisplay = computed(() => 'Pak Hasan');

// --- STATE ---
const showProfileMenu = ref(false);
// Cek apakah station ada sebelum mengakses statusnya
const isOpen = ref(props.station ? (props.station.status === 'Tersedia' || props.station.is_open) : false);
const isToggling = ref(false);

// --- METHODS ---
const toggleProfileMenu = () => showProfileMenu.value = !showProfileMenu.value;
const logout = () => router.post(route('logout'));

const toggleStore = () => {
    if (isToggling.value) return;
    if (!props.station) return alert("Anda belum mendaftarkan station.");

    isToggling.value = true;
    const previousState = isOpen.value;
    isOpen.value = !isOpen.value; // Optimistic UI update (update tampilan dulu biar cepat)

    router.post(route('host.toggle'), { is_open: isOpen.value }, {
        preserveScroll: true,
        onSuccess: () => { isToggling.value = false; },
        onError: () => {
            isOpen.value = previousState; // Kembalikan status jika server error
            isToggling.value = false;
            alert("Gagal mengubah status station. Silakan coba lagi.");
        }
    });
};

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
</script>

<template>
    <Head title="Host Dashboard" />

    <div class="min-h-screen flex flex-col bg-slate-50 font-poppins text-slate-800">

        <header class="bg-white/80 backdrop-blur-md px-6 py-4 sticky top-0 z-50 border-b border-slate-100">
            <div class="max-w-6xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-[#CCFF00] shadow-lg shadow-slate-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <span class="text-xl font-black tracking-tight block leading-none">
                            <span class="text-lime-500">E-</span><span class="text-slate-900">VOLT</span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Partner Center</span>
                    </div>
                </div>

                <div class="relative">
                    <button @click="toggleProfileMenu" class="flex items-center gap-3 hover:bg-slate-50 p-1.5 rounded-full pr-4 transition-all border border-transparent hover:border-slate-100">
                        <div class="w-9 h-9 bg-gradient-to-tr from-lime-400 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md">
                            {{ userNameDisplay.charAt(0).toUpperCase() }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-xs text-slate-500">Halo,</p>
                            <p class="text-sm font-bold text-slate-900 leading-none">{{ userNameDisplay }}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div v-if="showProfileMenu" class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden py-2 animate-fade-in-down origin-top-right z-50">
                        <div class="px-4 py-3 border-b border-slate-50 mb-2 bg-slate-50/50">
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Akun Saya</p>
                            <p class="text-sm font-bold text-slate-900 truncate">{{ user.email }}</p>
                        </div>
                        <Link :href="route('profile.edit')" class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-lime-50 hover:text-lime-700 transition">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Edit Profil
                        </Link>
                        <div class="h-px bg-slate-100 my-2 mx-4"></div>
                        <button @click="logout" class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Keluar
                        </button>
                    </div>
                    <div v-if="showProfileMenu" @click="showProfileMenu = false" class="fixed inset-0 z-40 bg-transparent"></div>
                </div>
            </div>
        </header>

        <main class="flex-grow py-10 px-4 sm:px-6">
            <div class="max-w-6xl mx-auto space-y-8">

                <div v-if="station" class="bg-white rounded-[2rem] p-1 shadow-sm border border-slate-200">
                    <div class="bg-slate-900 rounded-[1.8rem] p-6 sm:p-10 text-white relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-lime-500 rounded-full filter blur-[80px] opacity-20 -mr-16 -mt-16 pointer-events-none"></div>

                        <div class="relative z-10 text-center md:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 mb-4">
                                <span class="w-2 h-2 rounded-full" :class="isOpen ? 'bg-[#CCFF00] animate-pulse' : 'bg-red-500'"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">{{ isOpen ? 'Station Aktif' : 'Station Non-Aktif' }}</span>
                            </div>
                            <h1 class="text-3xl sm:text-4xl font-black mb-2">{{ station.name }}</h1>
                            <p class="text-slate-400 flex items-center justify-center md:justify-start gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                ID: {{ station.id }}
                            </p>
                        </div>

                        <div @click="toggleStore" class="relative z-10 group cursor-pointer">
                            <div class="w-64 h-16 bg-white/10 backdrop-blur-md rounded-full border border-white/20 p-1.5 flex items-center relative transition-all duration-300 hover:bg-white/20">
                                <div class="absolute w-full flex justify-between px-6 text-xs font-bold uppercase tracking-widest text-white/50 pointer-events-none">
                                    <span>Tutup</span>
                                    <span>Buka</span>
                                </div>
                                <div class="h-full w-1/2 bg-[#CCFF00] rounded-full shadow-lg shadow-lime-900/20 flex items-center justify-center transition-all duration-500 cubic-bezier(0.34, 1.56, 0.64, 1)"
                                     :class="isOpen ? 'translate-x-full' : 'translate-x-0 bg-red-500'">
                                    <i v-if="isToggling" class="fas fa-spinner fa-spin text-slate-900"></i>
                                    <i v-else class="fas" :class="isOpen ? 'fa-check text-slate-900' : 'fa-times text-white'"></i>
                                </div>
                            </div>
                            <p class="text-center text-xs text-slate-500 mt-3 opacity-0 group-hover:opacity-100 transition">Klik untuk mengubah status</p>
                        </div>
                    </div>
                </div>

                <div v-if="station" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <div class="lg:col-span-2 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="p-3 bg-lime-50 text-lime-600 rounded-2xl group-hover:bg-[#CCFF00] group-hover:text-slate-900 transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-1 rounded-lg"
                                        :class="props.stats.growth >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'">
                                        {{ props.stats.growth >= 0 ? '+' : '' }}{{ props.stats.growth }}%
                                    </span>
                                </div>
                                <p class="text-slate-500 text-sm font-medium">Total Pendapatan</p>
                                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ formatRupiah(props.stats.total_revenue) }}</h3>
                            </div>
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl group-hover:bg-blue-100 transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                </div>
                                <p class="text-slate-500 text-sm font-medium">Pendapatan Bulan Ini</p>
                                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ formatRupiah(props.stats.month_revenue) }}</h3>
                            </div>
                        </div>

                        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm">
                            <div class="flex justify-between items-center mb-8">
                                <div>
                                    <h3 class="font-bold text-lg text-slate-900">Performa Mingguan</h3>
                                    <p class="text-sm text-slate-500">Pendapatan 7 hari terakhir</p>
                                </div>
                            </div>

                            <div class="flex items-end justify-between h-48 gap-3 sm:gap-4">
                                <div v-for="(item, index) in props.weekly_chart" :key="index" class="flex flex-col items-center flex-1 h-full justify-end group cursor-pointer">
                                    <div class="mb-2 opacity-0 group-hover:opacity-100 transition-opacity transform translate-y-2 group-hover:translate-y-0 text-[10px] font-bold bg-slate-800 text-white py-1 px-2 rounded pointer-events-none z-10 whitespace-nowrap">
                                        {{ formatRupiah(item.raw_value) }}
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-t-xl relative overflow-hidden h-full flex items-end">
                                        <div class="w-full bg-lime-400 rounded-t-xl transition-all duration-1000 ease-out group-hover:bg-lime-500 relative"
                                             :style="{ height: item.value + '%' }">
                                            <div class="absolute top-0 left-0 w-full h-1 bg-white/30"></div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-slate-400 font-medium mt-3 group-hover:text-slate-800 transition">{{ item.day }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm h-full flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-lg text-slate-900">Tamu Terakhir</h3>
                                <Link href="#" class="text-xs font-bold text-lime-600 hover:underline">Lihat Semua</Link>
                            </div>

                            <div class="flex-grow space-y-4 overflow-y-auto max-h-[500px] pr-2 custom-scrollbar">
                                <div v-if="props.recent_guests.length > 0">
                                    <Link
                                        v-for="(guest, index) in props.recent_guests"
                                        :key="index"
                                        :href="guest.user_id ? route('host.guest.detail', guest.user_id) : '#'"
                                        class="group flex items-center gap-4 p-3 hover:bg-slate-50 rounded-2xl transition border border-transparent hover:border-lime-200 cursor-pointer"
                                    >
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 group-hover:bg-white group-hover:shadow-md transition-all">
                                            <i class="fas fa-car-side text-lg"></i>
                                        </div>

                                        <div class="flex-grow min-w-0">
                                            <p class="text-sm font-bold text-slate-900 truncate group-hover:text-lime-700 transition">{{ guest.guest_name }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ guest.car }} • <span class="font-mono text-slate-400">{{ guest.plat }}</span></p>
                                        </div>

                                        <div class="text-right flex-shrink-0">
                                            <p class="text-sm font-bold text-slate-900">{{ formatRupiah(guest.amount).replace(',00', '') }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ guest.date }}</p>
                                        </div>
                                    </Link>
                                </div>
                                <div v-else class="h-40 flex flex-col items-center justify-center text-slate-400">
                                    <i class="fas fa-inbox text-3xl mb-2 opacity-30"></i>
                                    <p class="text-xs">Belum ada transaksi</p>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-50">
                                <button class="w-full py-3 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition shadow-lg shadow-slate-200">
                                    Scan Tamu Baru
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <div v-else class="text-center py-20">
                    <h2 class="text-2xl font-bold text-slate-900">Belum Punya Station?</h2>
                    <p class="text-slate-500 mb-6">Daftarkan lokasi Anda sekarang dan mulai hasilkan uang.</p>
                    <button class="bg-slate-900 text-white px-6 py-3 rounded-xl font-bold">Daftar Jadi Mitra</button>
                </div>

            </div>
        </main>

        <Footer />
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap");
.font-poppins { font-family: 'Plus Jakarta Sans', sans-serif; }

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-fade-in-down {
    animation: fadeInDown 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 20px;
}
</style>