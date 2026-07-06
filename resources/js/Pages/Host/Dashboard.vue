<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage, Head, Link, useForm } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';
import axios from 'axios';

const props = defineProps({
    station:       { type: Object, default: () => null },
    stats:         { type: Object, default: () => ({ total_revenue: 0, month_revenue: 0, growth: 0 }) },
    recent_guests: { type: Array,  default: () => [] },
    weekly_chart:  { type: Array,  default: () => [] },
    all_bookings:  { type: Array,  default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const userNameDisplay = computed(() => user.value?.name || user.value?.username || '');

// ─── STATE ────────────────────────────────────────────────────────────────────
const showProfileMenu = ref(false);
const isOpen          = ref(props.station ? (props.station.status === 'Tersedia' || props.station.is_open) : false);
const isToggling      = ref(false);

const chartData     = ref([...props.weekly_chart]);
const liveStats     = ref({ ...props.stats });
const todayBookings = ref(0);
const lastUpdated   = ref(null);
const isRefreshing  = ref(false);

// ─── BOOKING TABLE STATE ──────────────────────────────────────────────────────
const bookings            = ref([...props.all_bookings]);
const showDeleteConfirm   = ref(false);
const deletingId          = ref(null);
const deletingCode        = ref('');
const isDeleting          = ref(false);
const deleteError         = ref('');
const bookingSearch       = ref('');
const bookingStatusFilter = ref('');

const filteredBookings = computed(() => {
    let list = bookings.value;
    if (bookingSearch.value) {
        const q = bookingSearch.value.toLowerCase();
        list = list.filter(b =>
            b.booking_code.toLowerCase().includes(q) ||
            b.guest_name.toLowerCase().includes(q)   ||
            b.plat.toLowerCase().includes(q)
        );
    }
    if (bookingStatusFilter.value) {
        list = list.filter(b => b.status === bookingStatusFilter.value);
    }
    return list;
});

const uniqueStatuses = computed(() => [...new Set(props.all_bookings.map(b => b.status))]);

let pollInterval = null;

// ─── STATE MODAL PENDAFTARAN STATION ─────────────────────────────────────────
const showRegisterModal = ref(false);

// Opsi pilihan untuk select
const stationTypes    = ['DC Fast Charging', 'AC Standard', 'AC Fast Charging', 'Ultra Fast Charging'];
const locationTypes   = ['Mall / Pusat Perbelanjaan', 'Perumahan', 'Perkantoran', 'Restoran / Kafe', 'Hotel', 'SPBU', 'Parkiran Umum', 'Lainnya'];

const registerForm = useForm({
    name:          '',
    address:       '',
    city:          '',
    phone:         '',
    type:          '',
    location_type: '',
    price:         '',
    service_fee:   '',
    lat:           '',
    lng:           '',
});

const submitStation = () => {
    registerForm.post(route('host.station.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showRegisterModal.value = false;
            registerForm.reset();
        },
    });
};

// ─── REALTIME POLLING ─────────────────────────────────────────────────────────
const fetchChartData = async () => {
    if (!props.station) return;
    isRefreshing.value = true;
    try {
        const res = await axios.get(route('host.chart'));
        chartData.value   = res.data.weekly_chart || chartData.value;
        liveStats.value   = {
            total_revenue: res.data.total_revenue ?? liveStats.value.total_revenue,
            month_revenue: res.data.month_revenue ?? liveStats.value.month_revenue,
            growth:        props.stats.growth,
        };
        todayBookings.value = res.data.today_bookings ?? 0;
        lastUpdated.value   = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        if (res.data.all_bookings) bookings.value = res.data.all_bookings;
    } catch (e) {
        console.warn('Chart refresh error:', e);
    }
    isRefreshing.value = false;
};

// ─── DELETE BOOKING ───────────────────────────────────────────────────────────
const confirmDelete = (booking) => {
    deletingId.value        = booking.id;
    deletingCode.value      = booking.booking_code;
    deleteError.value       = '';
    showDeleteConfirm.value = true;
};

const cancelDelete = () => {
    showDeleteConfirm.value = false;
    deletingId.value        = null;
    deletingCode.value      = '';
    deleteError.value       = '';
};

const executeDelete = async () => {
    if (!deletingId.value) return;
    isDeleting.value  = true;
    deleteError.value = '';
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        await axios.delete(route('host.booking.destroy', deletingId.value), {
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        });
        bookings.value          = bookings.value.filter(b => b.id !== deletingId.value);
        showDeleteConfirm.value = false;
        deletingId.value        = null;
        deletingCode.value      = '';
    } catch (e) {
        deleteError.value = e.response?.data?.message ?? 'Gagal menghapus booking. Coba lagi.';
    }
    isDeleting.value = false;
};

// ─── STATUS BADGE ─────────────────────────────────────────────────────────────
const statusBadge = (status) => {
    const map = {
        'Lunas':               'bg-green-100 text-green-700',
        'Booked':              'bg-blue-100 text-blue-700',
        'Selesai':             'bg-teal-100 text-teal-700',
        'Menunggu Pembayaran': 'bg-yellow-100 text-yellow-700',
        'Gagal Bayar':         'bg-red-100 text-red-700',
        'Dibatalkan':          'bg-gray-100 text-gray-500',
        'Dibatalkan Host':     'bg-orange-100 text-orange-600',
        'Kadaluarsa':          'bg-red-50 text-red-400',
        'Refund':              'bg-purple-100 text-purple-600',
    };
    return map[status] ?? 'bg-gray-100 text-gray-500';
};

const isDeletable = (status) => !['Dibatalkan', 'Dibatalkan Host', 'Kadaluarsa'].includes(status);

// ─── SVG CHART ────────────────────────────────────────────────────────────────
const CHART_W = 560;
const CHART_H = 180;
const BAR_GAP = 12;

const bars = computed(() => {
    const data  = chartData.value;
    if (!data.length) return [];
    const count = data.length;
    const barW  = (CHART_W - BAR_GAP * (count + 1)) / count;
    return data.map((item, i) => {
        const barH = Math.max((item.value / 100) * CHART_H, item.raw_value > 0 ? 6 : 0);
        const x    = BAR_GAP + i * (barW + BAR_GAP);
        const y    = CHART_H - barH;
        return { ...item, x, y, w: barW, h: barH };
    });
});

const linePath = computed(() => {
    if (!bars.value.length) return '';
    return 'M ' + bars.value.map(b => `${b.x + b.w / 2},${b.y}`).join(' L ');
});

const areaPath = computed(() => {
    if (!bars.value.length) return '';
    const pts   = bars.value.map(b => `${b.x + b.w / 2},${b.y}`).join(' L ');
    const last  = bars.value[bars.value.length - 1];
    const first = bars.value[0];
    return `M ${first.x + first.w / 2},${CHART_H} L ${pts} L ${last.x + last.w / 2},${CHART_H} Z`;
});

const tooltip = ref({ show: false, x: 0, y: 0, item: null });
const showTooltip = (e, bar) => { tooltip.value = { show: true, x: bar.x + bar.w / 2, y: Math.max(bar.y - 12, 10), item: bar }; };
const hideTooltip = () => { tooltip.value.show = false; };

// ─── METHODS ──────────────────────────────────────────────────────────────────
const toggleProfileMenu = () => showProfileMenu.value = !showProfileMenu.value;
const logout = () => router.post(route('logout'));

const toggleStore = () => {
    if (isToggling.value || !props.station) return;
    isToggling.value = true;
    const prev   = isOpen.value;
    isOpen.value = !isOpen.value;
    router.post(route('host.toggle'), { is_open: isOpen.value }, {
        preserveScroll: true,
        onSuccess: () => { isToggling.value = false; },
        onError:   () => { isOpen.value = prev; isToggling.value = false; alert('Gagal mengubah status.'); },
    });
};

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);

const formatShort = (val) => {
    if (val >= 1_000_000) return (val / 1_000_000).toFixed(1) + 'jt';
    if (val >= 1_000)     return (val / 1_000).toFixed(0) + 'rb';
    return val.toString();
};

onMounted(() => {
    fetchChartData();
    pollInterval = setInterval(fetchChartData, 30_000);
});

onBeforeUnmount(() => clearInterval(pollInterval));
</script>

<template>
    <Head title="Host Dashboard" />

    <div class="min-h-screen flex flex-col bg-slate-50 font-poppins text-slate-800">

        <!-- HEADER -->
        <header class="bg-white/80 backdrop-blur-md px-6 py-4 sticky top-0 z-50 border-b border-slate-100">
            <div class="max-w-6xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-lime-400 shadow-lg shadow-slate-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-black tracking-tight block leading-none">
                            <span class="text-lime-600">E-</span><span class="text-slate-900">VOLT</span>
                        </span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Host Panel</span>
                    </div>
                </div>

                <div class="relative">
                    <button @click="toggleProfileMenu"
                        class="flex items-center gap-3 hover:bg-slate-50 p-1.5 rounded-full pr-4 transition-all border border-transparent hover:border-slate-100">
                        <div class="w-9 h-9 bg-gradient-to-tr from-lime-400 to-lime-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md">
                            {{ userNameDisplay.charAt(0).toUpperCase() }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-xs text-slate-500">Halo,</p>
                            <p class="text-sm font-bold text-slate-900 leading-none">{{ userNameDisplay }}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <Transition name="dropdown">
                        <div v-if="showProfileMenu"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden py-2 z-50">
                            <div class="px-4 py-3 border-b border-slate-50 mb-2 bg-slate-50/50">
                                <p class="text-xs text-slate-500 uppercase tracking-wider font-bold">Akun Saya</p>
                                <p class="text-sm font-bold text-slate-900 truncate">{{ user?.email }}</p>
                            </div>
                            <Link :href="route('profile.edit')"
                                class="flex items-center px-4 py-2.5 text-sm text-slate-600 hover:bg-lime-50 hover:text-lime-700 transition">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Edit Profil
                            </Link>
                            <div class="h-px bg-slate-100 my-2 mx-4"></div>
                            <button @click="logout"
                                class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar
                            </button>
                        </div>
                    </Transition>
                    <div v-if="showProfileMenu" @click="showProfileMenu = false" class="fixed inset-0 z-40"></div>
                </div>
            </div>
        </header>

        <main class="flex-grow py-10 px-4 sm:px-6">
            <div class="max-w-6xl mx-auto space-y-8">

                <!-- STATION HERO CARD -->
                <div v-if="station" class="bg-white rounded-[2rem] p-1 shadow-sm border border-slate-200">
                    <div class="bg-slate-900 rounded-[1.8rem] p-6 sm:p-10 text-white relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-lime-500 rounded-full filter blur-[80px] opacity-20 -mr-16 -mt-16 pointer-events-none"></div>
                        <div class="relative z-10 text-center md:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 mb-4">
                                <span class="w-2 h-2 rounded-full" :class="isOpen ? 'bg-lime-500 animate-pulse' : 'bg-red-500'"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">{{ isOpen ? 'Station Aktif' : 'Station Non-Aktif' }}</span>
                            </div>
                            <h1 class="text-3xl sm:text-4xl font-black mb-2">{{ station.name }}</h1>
                            <p class="text-slate-400 flex items-center justify-center md:justify-start gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ station.city || station.address }}
                            </p>
                        </div>
                        <!-- TOGGLE SWITCH -->
                        <div @click="toggleStore" class="relative z-10 group cursor-pointer">
                            <div class="w-64 h-16 bg-white/10 backdrop-blur-md rounded-full border border-white/20 p-1.5 flex items-center relative transition-all duration-300 hover:bg-white/20">
                                <div class="absolute w-full flex justify-between px-6 text-xs font-bold uppercase tracking-widest text-white/50 pointer-events-none">
                                    <span>Tutup</span><span>Buka</span>
                                </div>
                                <div class="h-full w-1/2 rounded-full shadow-lg flex items-center justify-center transition-all duration-500"

                                    :class="isOpen ? 'translate-x-full bg-lime-500 shadow-lime-900/20' : 'translate-x-0 bg-red-500 shadow-red-900/20'">
                                    <i v-if="isToggling" class="fas fa-spinner fa-spin text-white"></i>
                                    <i v-else class="fas" :class="isOpen ? 'fa-check text-slate-900' : 'fa-times text-white'"></i>
>>>>>>> deploy-vps
                                </div>
                            </div>
                            <p class="text-center text-xs text-slate-500 mt-3 opacity-0 group-hover:opacity-100 transition">Klik untuk mengubah status</p>
                        </div>
                    </div>
                </div>

                <!-- STATS + CHART + GUESTS -->
                <div v-if="station" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <!-- STATS CARDS -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="p-3 bg-lime-50 text-lime-600 rounded-2xl group-hover:bg-lime-400 group-hover:text-slate-900 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-1 rounded-lg"
                                        :class="props.stats.growth >= 0 ? 'text-lime-600 bg-lime-50' : 'text-red-600 bg-red-50'">
                                        {{ props.stats.growth >= 0 ? '+' : '' }}{{ props.stats.growth }}%
                                    </span>
                                </div>
                                <p class="text-slate-500 text-xs font-medium">Total Pendapatan</p>
                                <h3 class="text-xl font-black text-slate-900 mt-1">{{ formatRupiah(liveStats.total_revenue) }}</h3>
                            </div>

                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                                <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl group-hover:bg-blue-100 transition w-fit mb-4">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-slate-500 text-xs font-medium">Bulan Ini</p>
                                <h3 class="text-xl font-black text-slate-900 mt-1">{{ formatRupiah(liveStats.month_revenue) }}</h3>
                            </div>

                            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition group">
                                <div class="p-3 bg-orange-50 text-orange-500 rounded-2xl group-hover:bg-orange-100 transition w-fit mb-4">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <p class="text-slate-500 text-xs font-medium">Booking Hari Ini</p>
                                <h3 class="text-xl font-black text-slate-900 mt-1">{{ todayBookings }} <span class="text-sm font-medium text-slate-400">transaksi</span></h3>
                            </div>
                        </div>

                        <!-- GRAFIK -->
                        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="font-bold text-lg text-slate-900">Performa Mingguan</h3>
                                    <p class="text-sm text-slate-400">Pendapatan 7 hari terakhir</p>
                                </div>
                                <div class="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full"
                                    :class="isRefreshing ? 'bg-amber-50 text-amber-600' : 'bg-lime-50 text-lime-600'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="isRefreshing ? 'bg-amber-400 animate-pulse' : 'bg-lime-400 animate-pulse'"></span>
                                    {{ isRefreshing ? 'Memperbarui...' : (lastUpdated ? 'Update: ' + lastUpdated : 'Live') }}
                                </div>
                            </div>

                            <div class="relative w-full overflow-x-auto">
                                <svg :viewBox="`0 0 ${CHART_W} ${CHART_H + 40}`" class="w-full" style="min-width:300px;" @mouseleave="hideTooltip">
                                    <defs>
                                        <linearGradient id="barGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%"   stop-color="#a3e635"/>
                                            <stop offset="100%" stop-color="#65a30d"/>
                                        </linearGradient>
                                        <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%"   stop-color="#a3e635" stop-opacity="0.25"/>
                                            <stop offset="100%" stop-color="#a3e635" stop-opacity="0"/>
                                        </linearGradient>
                                        <linearGradient id="todayGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%"   stop-color="#65a30d"/>
                                            <stop offset="100%" stop-color="#4d7c0f"/>
                                        </linearGradient>
                                    </defs>
                                    <line v-for="n in 4" :key="n" x1="0" :y1="(CHART_H/4)*n" :x2="CHART_W" :y2="(CHART_H/4)*n" stroke="#f1f5f9" stroke-width="1"/>
                                    <path v-if="bars.length" :d="areaPath" fill="url(#areaGrad)"/>
                                    <path v-if="bars.length" :d="linePath" fill="none" stroke="#84cc16" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <g v-for="bar in bars" :key="bar.day">
                                        <rect :x="bar.x" y="0" :width="bar.w" :height="CHART_H" rx="8" fill="#f8fafc"/>
                                        <rect :x="bar.x" :y="bar.y" :width="bar.w" :height="bar.h" rx="8"
                                            :fill="bar.is_today ? 'url(#todayGrad)' : 'url(#barGrad)'"
                                            class="transition-all duration-700 cursor-pointer"
                                            @mouseenter="showTooltip($event, bar)" @mouseleave="hideTooltip"/>
                                        <circle v-if="bar.raw_value > 0" :cx="bar.x + bar.w/2" :cy="bar.y" r="4"
                                            :fill="bar.is_today ? '#65a30d' : '#84cc16'" stroke="white" stroke-width="2"/>
                                        <text :x="bar.x + bar.w/2" :y="CHART_H+18" text-anchor="middle" font-size="11" font-weight="600"
                                            :fill="bar.is_today ? '#65a30d' : '#94a3b8'">{{ bar.day }}</text>
                                        <text v-if="bar.is_today" :x="bar.x + bar.w/2" :y="CHART_H+32" text-anchor="middle" font-size="9" font-weight="700" fill="#65a30d">Hari Ini</text>
                                        <text v-if="bar.raw_value > 0" :x="bar.x + bar.w/2" :y="bar.y - 8" text-anchor="middle" font-size="9" font-weight="700"
                                            :fill="bar.is_today ? '#65a30d' : '#64748b'">{{ formatShort(bar.raw_value) }}</text>
                                    </g>
                                    <g v-if="tooltip.show && tooltip.item" style="pointer-events:none">
                                        <rect :x="tooltip.x - 52" :y="tooltip.y - 36" width="104" height="32" rx="8" fill="#0f172a"/>
                                        <text :x="tooltip.x" :y="tooltip.y - 24" text-anchor="middle" font-size="10" font-weight="700" fill="#a3e635">{{ tooltip.item.date }}</text>
                                        <text :x="tooltip.x" :y="tooltip.y - 10" text-anchor="middle" font-size="10" fill="white">{{ formatRupiah(tooltip.item.raw_value) }}</text>
                                    </g>
                                </svg>
                                <div v-if="!bars.length || bars.every(b => b.raw_value === 0)"
                                    class="absolute inset-0 flex flex-col items-center justify-center text-slate-300">
                                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <p class="text-sm font-medium">Belum ada transaksi minggu ini</p>
                                </div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <button @click="fetchChartData" :disabled="isRefreshing"
                                    class="text-xs font-bold text-slate-400 hover:text-lime-600 transition flex items-center gap-1.5 disabled:opacity-50">
                                    <svg class="w-3.5 h-3.5" :class="isRefreshing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Refresh sekarang
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RECENT GUESTS -->
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm h-full flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-lg text-slate-900">Tamu Terakhir</h3>
                                <span class="text-xs font-bold text-lime-600 bg-lime-50 px-2 py-1 rounded-full">
                                    {{ props.recent_guests.length }} tamu
                                </span>
                            </div>
                            <div class="flex-grow space-y-3 overflow-y-auto max-h-[460px] pr-1 custom-scrollbar">
                                <template v-if="props.recent_guests.length > 0">
                                    <Link v-for="(guest, index) in props.recent_guests" :key="index"
                                        :href="guest.user_id ? route('host.guest.detail', guest.user_id) : '#'"
                                        class="group flex items-center gap-3 p-3 hover:bg-slate-50 rounded-2xl transition border border-transparent hover:border-lime-200 cursor-pointer block">
                                        <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-lime-100 to-lime-200 text-lime-700 flex items-center justify-center flex-shrink-0 font-black text-sm group-hover:from-lime-200 group-hover:to-lime-300 transition-all">
                                            {{ guest.guest_name?.charAt(0)?.toUpperCase() ?? '?' }}
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <p class="text-sm font-bold text-slate-900 truncate group-hover:text-lime-700 transition">{{ guest.guest_name }}</p>
                                            <p class="text-xs font-mono text-slate-400 bg-slate-100 inline-block px-1.5 py-0.5 rounded mt-0.5">{{ guest.plat }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ guest.car }}</p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <p class="text-sm font-bold text-slate-900">{{ formatRupiah(guest.amount) }}</p>
                                            <p v-if="guest.booking_slot" class="text-[10px] font-bold text-lime-600 mt-0.5">🕐 {{ guest.booking_slot }}</p>
                                            <p v-else class="text-[10px] text-slate-400 mt-0.5">{{ guest.date }}</p>
                                        </div>
                                    </Link>
                                </template>
                                <div v-else class="h-40 flex flex-col items-center justify-center text-slate-300">
                                    <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-xs font-medium">Belum ada transaksi</p>
                                </div>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-50">
                                <button class="w-full py-3 text-sm font-bold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition shadow-lg shadow-slate-200 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                    Scan Tamu Baru
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABEL MANAJEMEN BOOKING -->
                <div v-if="station" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900">Manajemen Booking</h3>
                            <p class="text-sm text-slate-400">Kelola semua booking di stasiun Anda</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input v-model="bookingSearch" type="text" placeholder="Cari kode / nama / plat..."
                                    class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-lime-400 w-full sm:w-52 transition"/>
                            </div>
                            <select v-model="bookingStatusFilter"
                                class="px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-lime-400 transition bg-white">
                                <option value="">Semua Status</option>
                                <option v-for="s in uniqueStatuses" :key="s" :value="s">{{ s }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Desktop table -->
                    <div class="overflow-x-auto hidden sm:block">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                    <th class="px-5 py-3 text-left font-semibold">Kode Booking</th>
                                    <th class="px-5 py-3 text-left font-semibold">Tamu</th>
                                    <th class="px-5 py-3 text-left font-semibold">Tanggal & Jam</th>
                                    <th class="px-5 py-3 text-left font-semibold">Port / Durasi</th>
                                    <th class="px-5 py-3 text-right font-semibold">Total</th>
                                    <th class="px-5 py-3 text-center font-semibold">Status</th>
                                    <th class="px-5 py-3 text-center font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="b in filteredBookings" :key="b.id" class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-4">
                                        <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded-lg">{{ b.booking_code }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-slate-800">{{ b.guest_name }}</p>
                                        <p class="text-xs text-slate-400 font-mono">{{ b.plat }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-700">{{ b.booking_date }}</p>
                                        <p class="text-xs text-lime-600 font-bold">🕐 {{ b.booking_slot }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-slate-600">{{ b.port_type }}</p>
                                        <p class="text-xs text-slate-400">{{ b.duration }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right font-bold text-slate-800">{{ formatRupiah(b.total_price) }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-full" :class="statusBadge(b.status)">{{ b.status }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <button v-if="isDeletable(b.status)" @click="confirmDelete(b)"
                                            class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition" title="Batalkan booking">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                        <span v-else class="text-xs text-slate-300">—</span>
                                    </td>
                                </tr>
                                <tr v-if="filteredBookings.length === 0">
                                    <td colspan="7" class="px-5 py-12 text-center text-slate-300">
                                        <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2-2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-sm font-medium">Tidak ada booking ditemukan</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile cards -->
                    <div class="block sm:hidden divide-y divide-slate-100">
                        <div v-for="b in filteredBookings" :key="b.id" class="p-4 hover:bg-slate-50 transition">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded-lg">{{ b.booking_code }}</span>
                                <span class="text-xs font-bold px-2 py-1 rounded-full" :class="statusBadge(b.status)">{{ b.status }}</span>
                            </div>
                            <p class="font-semibold text-slate-800 text-sm">{{ b.guest_name }}</p>
                            <p class="text-xs text-slate-400 font-mono mb-2">{{ b.plat }}</p>
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-slate-500">{{ b.booking_date }} · <span class="text-lime-600 font-bold">{{ b.booking_slot }}</span></p>
                                    <p class="text-sm font-bold text-slate-800">{{ formatRupiah(b.total_price) }}</p>
                                </div>
                                <button v-if="isDeletable(b.status)" @click="confirmDelete(b)"
                                    class="p-2.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition border border-red-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-if="filteredBookings.length === 0" class="py-12 text-center text-slate-300">
                            <p class="text-sm font-medium">Tidak ada booking ditemukan</p>
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-400 font-medium">
                        Menampilkan {{ filteredBookings.length }} dari {{ bookings.length }} booking
                    </div>
                </div>

                <!-- EMPTY STATE — belum punya station -->
                <div v-else class="text-center py-20">
                    <div class="text-6xl mb-4">⚡</div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Belum Punya Station?</h2>
                    <p class="text-slate-500 mb-6">Daftarkan lokasi Anda sekarang dan mulai hasilkan uang.</p>
                    <button @click="showRegisterModal = true"
                        class="bg-slate-900 text-white px-6 py-3 rounded-xl font-bold hover:bg-slate-800 transition shadow-lg shadow-slate-200">
                        Daftar Jadi Mitra
                    </button>
                </div>

            </div>
        </main>

        <Footer />

        <!-- ══════════════════════════════════════════
             MODAL KONFIRMASI HAPUS BOOKING
        ══════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showDeleteConfirm"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[9000] px-4"
                @click.self="cancelDelete">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8">
                    <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 text-center mb-2">Batalkan Booking?</h3>
                    <p class="text-slate-500 text-sm text-center mb-1">Booking berikut akan dibatalkan:</p>
                    <div class="bg-slate-50 rounded-2xl px-4 py-3 text-center mb-5 border border-slate-100">
                        <span class="font-mono font-black text-slate-800 text-base">{{ deletingCode }}</span>
                        <p class="text-xs text-slate-400 mt-1">Status akan berubah menjadi <span class="font-bold text-orange-500">Dibatalkan Host</span></p>
                    </div>
                    <div v-if="deleteError" class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-4 flex items-start gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ deleteError }}</span>
                    </div>
                    <div class="flex gap-3">
                        <button @click="cancelDelete"
                            class="flex-1 py-3 border-2 border-slate-200 rounded-xl font-bold text-slate-600 hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button @click="executeDelete" :disabled="isDeleting"
                            class="flex-[2] py-3 bg-red-500 text-white rounded-xl font-bold hover:bg-red-600 transition flex items-center justify-center gap-2 disabled:opacity-60">
                            <svg v-if="isDeleting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ isDeleting ? 'Membatalkan...' : 'Ya, Batalkan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ══════════════════════════════════════════
             MODAL DAFTAR STATION BARU (LENGKAP)
        ══════════════════════════════════════════ -->
        <Transition name="fade">
            <div v-if="showRegisterModal"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-start justify-center z-[9000] px-4 py-6 overflow-y-auto"
                @click.self="showRegisterModal = false">

                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8 relative my-auto">

                    <!-- Tombol Close -->
                    <button @click="showRegisterModal = false"
                        class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 transition bg-slate-50 hover:bg-slate-100 p-2 rounded-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Header Modal -->
                    <div class="mb-6 pr-10">
                        <div class="w-12 h-12 bg-lime-100 text-lime-600 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900">Daftar Station Baru</h3>
                        <p class="text-sm text-slate-500 mt-1">Lengkapi detail lokasi charging station EV Anda.</p>
                    </div>

                    <!-- Form -->
                    <div class="space-y-4">

                        <!-- Divider: Informasi Dasar -->
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Informasi Dasar</span>
                            <div class="h-px flex-grow bg-slate-100"></div>
                        </div>

                        <!-- Nama Station -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                                Nama Station <span class="text-red-500">*</span>
                            </label>
                            <input v-model="registerForm.name" type="text"
                                placeholder="Contoh: Batam Center EV Station" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm"
                                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': registerForm.errors.name }"/>
                            <p v-if="registerForm.errors.name" class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ registerForm.errors.name }}
                            </p>
                        </div>

                        <!-- Kota -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                                Kota <span class="text-red-500">*</span>
                            </label>
                            <input v-model="registerForm.city" type="text"
                                placeholder="Contoh: Batam" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm"
                                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': registerForm.errors.city }"/>
                            <p v-if="registerForm.errors.city" class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ registerForm.errors.city }}
                            </p>
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                                Alamat Lengkap <span class="text-red-500">*</span>
                            </label>
                            <textarea v-model="registerForm.address" rows="3"
                                placeholder="Masukkan alamat lengkap lokasi station..." required
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm resize-none"
                                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400': registerForm.errors.address }"></textarea>
                            <p v-if="registerForm.errors.address" class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ registerForm.errors.address }}
                            </p>
                        </div>

                        <!-- Nomor Telepon -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Nomor Telepon</label>
                            <input v-model="registerForm.phone" type="tel"
                                placeholder="Contoh: 08123456789"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm"/>
                        </div>

                        <!-- Divider: Tipe & Kategori -->
                        <div class="flex items-center gap-3 pt-2">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Tipe & Kategori</span>
                            <div class="h-px flex-grow bg-slate-100"></div>
                        </div>

                        <!-- Tipe Station + Tipe Lokasi (2 kolom) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Tipe Charger</label>
                                <select v-model="registerForm.type"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm bg-white text-slate-700">
                                    <option value="">Pilih tipe...</option>
                                    <option v-for="t in stationTypes" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Tipe Lokasi</label>
                                <select v-model="registerForm.location_type"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm bg-white text-slate-700">
                                    <option value="">Pilih lokasi...</option>
                                    <option v-for="l in locationTypes" :key="l" :value="l">{{ l }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Divider: Harga -->
                        <div class="flex items-center gap-3 pt-2">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Harga</span>
                            <div class="h-px flex-grow bg-slate-100"></div>
                        </div>

                        <!-- Harga per kWh + Biaya Layanan (2 kolom) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Harga per kWh (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">Rp</span>
                                    <input v-model="registerForm.price" type="number" min="0" step="500"
                                        placeholder="0"
                                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Biaya Layanan (Rp)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">Rp</span>
                                    <input v-model="registerForm.service_fee" type="number" min="0" step="500"
                                        placeholder="0"
                                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm"/>
                                </div>
                            </div>
                        </div>

                        <!-- Divider: Koordinat (opsional) -->
                        <div class="flex items-center gap-3 pt-2">
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Koordinat <span class="normal-case font-medium">(opsional)</span></span>
                            <div class="h-px flex-grow bg-slate-100"></div>
                        </div>
                        <p class="text-xs text-slate-400 -mt-2">Isi koordinat agar station muncul di peta. Bisa diisi nanti.</p>

                        <!-- Lat + Lng (2 kolom) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Latitude</label>
                                <input v-model="registerForm.lat" type="number" step="any"
                                    placeholder="Contoh: 1.1300"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm font-mono"/>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Longitude</label>
                                <input v-model="registerForm.lng" type="number" step="any"
                                    placeholder="Contoh: 104.0530"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:border-[#00C853] focus:ring-1 focus:ring-[#00C853] transition text-sm font-mono"/>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="showRegisterModal = false"
                                class="flex-1 py-3 border-2 border-slate-200 rounded-xl font-bold text-slate-600 hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="button" @click="submitStation" :disabled="registerForm.processing"
                                class="flex-[2] py-3 bg-[#00C853] text-white rounded-xl font-bold hover:bg-green-600 transition flex items-center justify-center gap-2 disabled:opacity-60 shadow-lg shadow-green-200">
                                <svg v-if="registerForm.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                {{ registerForm.processing ? 'Mendaftarkan...' : 'Daftarkan Station' }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap");
.font-poppins { font-family: 'Plus Jakarta Sans', sans-serif; }
.dropdown-enter-active, .dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to       { opacity: 0; transform: translateY(-8px) scale(0.96); }
.fade-enter-active, .fade-leave-active         { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to               { opacity: 0; }
.custom-scrollbar::-webkit-scrollbar       { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 20px; }
</style>