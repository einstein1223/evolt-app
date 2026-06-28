<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';
import ContactForm from '@/Components/ContactForm.vue';

// --- STATE ---
const isModalOpen = ref(false);
const isScrolled = ref(false);
const isMenuOpen = ref(false);

// --- AUTH (sama persis seperti Navbar.vue asli) ---
const page = usePage();
const user = computed(() => page.props.auth.user);

const getDashboardRoute = () => {
    if (!user.value) return route('welcome');
    if (user.value.role === 'admin') return route('admin.dashboard');
    if (user.value.role === 'host') return route('host.dashboard');
    return route('dashboard');
};

const handleScroll = () => { isScrolled.value = window.scrollY > 50; };

// Scroll Reveal
const observeElements = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => { if (entry.isIntersecting) entry.target.classList.add('active'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    nextTick(() => observeElements());
});
onUnmounted(() => { window.removeEventListener('scroll', handleScroll); });

const openModal = () => { isModalOpen.value = true; document.body.style.overflow = 'hidden'; };
const closeModal = () => { isModalOpen.value = false; document.body.style.overflow = ''; };

const scrollToSection = (id) => {
    isMenuOpen.value = false;
    const element = document.getElementById(id);
    if (element) {
        const offsetPosition = element.getBoundingClientRect().top + window.pageYOffset - 80;
        window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
    }
};

const waLink = (message) => `https://wa.me/6281234567890?text=${encodeURIComponent(message)}`;

const services = [
    { title: 'Aplikasi Terintegrasi', desc: 'Satu aplikasi untuk cari, booking, dan bayar.', iconClass: 'bg-lime-50 text-lime-600' },
    { title: 'Manajemen IoT', desc: 'Monitoring stasiun secara real-time 24/7.', iconClass: 'bg-slate-100 text-slate-600' },
    { title: 'Sistem Pembayaran', desc: 'Dukungan QRIS dan E-Wallet otomatis.', iconClass: 'bg-lime-50 text-lime-600' },
    { title: 'Laporan Keuangan', desc: 'Transparansi pendapatan bagi mitra host.', iconClass: 'bg-slate-100 text-slate-600' },
];

const contactData = {
    phone: '+62 812 3456 7890',
    email: 'hello@evolt.id',
    address: 'Jl. Ahmad Yani Batam Kota,\nKota Batam, Kepulauan Riau,\nIndonesia',
};
</script>

<template>
    <div class="min-h-screen flex flex-col bg-slate-50/50 font-sans text-slate-800 overflow-x-hidden selection:bg-[#CCFF00] selection:text-slate-900 scroll-smooth relative">

        <!-- Ambient BG -->
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-[600px] h-[600px] rounded-full bg-[#CCFF00] opacity-25 blur-[150px]"></div>
            <div class="absolute top-1/2 left-0 -translate-y-1/2 -translate-x-1/4 w-[500px] h-[500px] rounded-full bg-[#CCFF00] opacity-20 blur-[150px]"></div>
            <div class="absolute bottom-0 right-0 translate-y-1/4 translate-x-1/4 w-[600px] h-[600px] rounded-full bg-white opacity-60 blur-[150px]"></div>
        </div>

        <!-- ─── NAVBAR (Terintegrasi) ─────────────────────── -->
        <header :class="['fixed top-0 left-0 right-0 w-full z-50 transition-all duration-300', isScrolled ? 'bg-white/90 backdrop-blur-md shadow-sm py-2' : 'bg-transparent py-4']">
            <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">

                <!-- Logo -->
                <Link href="/" class="text-2xl font-bold tracking-tight">
                    <span class="text-[#00C853]">E-</span><span class="text-slate-900">VOLT</span>
                </Link>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex items-center gap-10 text-slate-700 font-medium text-sm">
                    <Link :href="route('welcome')" class="hover:text-[#00C853] transition">Beranda</Link>
                    <button @click="scrollToSection('cara-kerja')" class="hover:text-[#00C853] transition">Cara Kerja</button>
                    <button @click="scrollToSection('paket')" class="hover:text-[#00C853] transition">Paket</button>
                    <button @click="scrollToSection('kontak')" class="hover:text-[#00C853] transition">Kontak</button>
                </nav>

                <!-- Desktop CTA -->
                <div class="hidden md:flex items-center gap-3">
                    <template v-if="user">
                        <Link :href="getDashboardRoute()" class="flex items-center gap-3 group">
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Halo,</p>
                                <p class="font-bold text-gray-900 group-hover:text-[#00C853] transition-colors text-sm">{{ user.username }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-[#00C853] text-white flex items-center justify-center font-bold shadow-sm">
                                {{ user.username.charAt(0).toUpperCase() }}
                            </div>
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-semibold text-slate-700 hover:text-[#00C853] transition px-4 py-2">Masuk</Link>
                        <Link :href="route('register')" class="text-sm font-bold bg-slate-900 text-white px-5 py-2.5 rounded-xl hover:bg-slate-800 transition shadow-sm">Daftar Gratis</Link>
                    </template>
                </div>

                <!-- Hamburger -->
                <button @click="isMenuOpen = !isMenuOpen" class="md:hidden text-slate-700 hover:text-[#00C853] transition ml-4" aria-label="Menu">
                    <svg v-if="!isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <Transition name="mobile-menu"
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2">
                <div v-if="isMenuOpen" class="md:hidden absolute top-full left-0 right-0 z-40">
                    <div class="bg-white shadow-lg border-t border-gray-100 rounded-b-2xl mx-4 p-4">
                        <nav class="flex flex-col gap-1 text-slate-700 font-medium text-sm">
                            <Link :href="route('welcome')" @click="isMenuOpen = false" class="px-3 py-2.5 rounded-lg hover:bg-lime-50 hover:text-[#00C853] transition">Beranda</Link>
                            <button @click="scrollToSection('cara-kerja')" class="text-left px-3 py-2.5 rounded-lg hover:bg-lime-50 hover:text-[#00C853] transition">Cara Kerja</button>
                            <button @click="scrollToSection('paket')" class="text-left px-3 py-2.5 rounded-lg hover:bg-lime-50 hover:text-[#00C853] transition">Paket</button>
                            <button @click="scrollToSection('kontak')" class="text-left px-3 py-2.5 rounded-lg hover:bg-lime-50 hover:text-[#00C853] transition">Kontak</button>
                            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col gap-2">
                                <template v-if="user">
                                    <Link :href="getDashboardRoute()" @click="isMenuOpen = false" class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg hover:bg-lime-50 transition">
                                        <div class="w-8 h-8 rounded-full bg-[#00C853] text-white flex items-center justify-center font-bold text-sm">
                                            {{ user.username.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Masuk sebagai</p>
                                            <p class="font-bold text-gray-900 text-sm">{{ user.username }}</p>
                                        </div>
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link :href="route('login')" @click="isMenuOpen = false" class="text-center py-2.5 font-semibold border-2 border-[#00C853] text-[#00C853] rounded-xl hover:bg-[#00C853] hover:text-white transition">Masuk</Link>
                                    <Link :href="route('register')" @click="isMenuOpen = false" class="text-center py-2.5 font-bold bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition">Daftar Gratis</Link>
                                </template>
                            </div>
                        </nav>
                    </div>
                </div>
            </Transition>
        </header>

        <main class="flex-grow relative z-10">

            <!-- ─── HERO ──────────────────────────────────── -->
            <section class="relative z-20 bg-[#CCFF00] rounded-b-[3rem] lg:rounded-b-[5rem] overflow-hidden shadow-sm">
                <div class="absolute inset-0 opacity-15 pointer-events-none bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-multiply"></div>
                <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 pt-36 pb-24 lg:pt-48 lg:pb-40 relative">
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-20">
                        <div class="w-full lg:w-1/2 text-center lg:text-left z-20 reveal">
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-900/5 border border-slate-900/10 mb-8 animate-fade-in-up backdrop-blur-sm">
                                <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-slate-900 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-slate-900"></span></span>
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-900/80">Solusi Energi Masa Depan</span>
                            </div>
                            <h1 class="flex flex-col font-black text-slate-900 tracking-tight mb-8">
                                <span class="text-5xl sm:text-6xl lg:text-[5rem] leading-[1.1]">Pengendara</span>
                                <span class="text-5xl sm:text-6xl lg:text-[5rem] leading-[1.1] mt-2 lg:mt-3">Cerdas,</span>
                                <span class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-800/70 mt-4 lg:mt-6">Stasiun Pintar.</span>
                            </h1>
                            <p class="text-lg sm:text-xl text-slate-800/80 font-medium mb-10 max-w-lg mx-auto lg:mx-0 leading-relaxed">Ekosistem pengisian daya kendaraan listrik terintegrasi. Temukan, reservasi, dan isi daya dalam satu genggaman.</p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                <Link href="/register" class="group relative inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white transition-all duration-300 bg-slate-900 rounded-2xl hover:bg-slate-800 hover:shadow-xl hover:-translate-y-1 focus:ring-4 focus:ring-slate-900/20">
                                    Mulai Sekarang
                                    <svg class="w-5 h-5 ml-2 -mr-1 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </Link>
                                <button @click="scrollToSection('paket')" class="inline-flex items-center justify-center px-8 py-4 text-base font-bold text-slate-900 transition-all duration-300 bg-white/40 border-2 border-slate-900/10 rounded-2xl hover:bg-white hover:border-white hover:shadow-xl focus:ring-4 focus:ring-white/40 backdrop-blur-sm">
                                    Lihat Paket
                                </button>
                            </div>
                        </div>
                        <div class="w-full lg:w-1/2 flex justify-center lg:justify-end relative z-10 mt-10 lg:mt-0 reveal delay-200">
                            <div class="relative w-full max-w-[600px]">
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[120%] bg-white/50 blur-[80px] rounded-full mix-blend-overlay"></div>
                                <img src="/images/mobil.png" alt="EV Car" class="relative z-10 w-full drop-shadow-2xl transform hover:scale-[1.02] transition duration-700 ease-out" style="filter: drop-shadow(0 25px 50px rgba(0,0,0,0.15));">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ─── SERVICES ──────────────────────────────── -->
            <section class="py-24 bg-white/80 backdrop-blur-sm">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="max-w-3xl mx-auto text-center mb-16 reveal">
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 mb-6">Misi Kami</h2>
                        <p class="text-lg text-slate-500 leading-relaxed"><span class="font-bold text-lime-600 bg-lime-50 px-2 py-0.5 rounded">E-VOLT</span> percaya teknologi tidak harus rumit. Misi kami adalah menyederhanakan transisi energi melalui aplikasi yang intuitif, cepat, dan menguntungkan semua pihak.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                        <div v-for="(service, index) in services" :key="index"
                            class="bg-white/90 p-8 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 group hover:-translate-y-1 hover:border-lime-200 reveal"
                            :style="`transition-delay: ${index * 100}ms`">
                            <div :class="`w-14 h-14 rounded-2xl flex items-center justify-center mb-6 ${service.iconClass} transition-transform group-hover:scale-110 duration-300`">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">{{ service.title }}</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">{{ service.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ─── CARA KERJA ─────────────────────────────── -->
            <div id="cara-kerja" class="py-24 bg-slate-50/90 backdrop-blur-sm rounded-[3rem] border-y border-slate-100/50">
                <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 space-y-32">

                    <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-24 reveal">
                        <div class="w-full lg:w-1/2 group">
                            <div class="relative">
                                <div class="absolute inset-0 bg-lime-200 rounded-[2.5rem] rotate-3 group-hover:rotate-6 transition-transform duration-500 ease-out"></div>
                                <img src="https://i.pinimg.com/736x/28/8b/44/288b44c8e76a00feed1853b351057876.jpg" alt="Map Navigation" class="relative z-10 w-full rounded-[2.5rem] shadow-2xl shadow-lime-900/10 border-4 border-white object-cover aspect-[4/3] transform transition duration-500 group-hover:-translate-y-2">
                            </div>
                        </div>
                        <div class="w-full lg:w-1/2 text-center lg:text-left">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-lime-100 text-lime-800 rounded-2xl mb-8 font-black text-xl shadow-sm">01</div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-slate-900 mb-6 leading-tight">Pencarian <br><span class="text-lime-600 decoration-lime-300 underline decoration-4 underline-offset-4">Presisi</span> & Cepat</h2>
                            <p class="text-lg text-slate-500 leading-relaxed max-w-lg mx-auto lg:mx-0">Peta geolokasi <b>Real-Time</b>. Filter stasiun berdasarkan tipe konektor (AC/DC), daya charging, dan fasilitas sekitar.</p>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row-reverse items-center gap-16 lg:gap-24 reveal">
                        <div class="w-full lg:w-1/2 group">
                            <div class="relative">
                                <div class="absolute inset-0 bg-lime-200 rounded-[2.5rem] -rotate-3 group-hover:-rotate-6 transition-transform duration-500 ease-out"></div>
                                <img src="/images/uibooking.png" alt="Booking Interface" class="relative z-10 w-full rounded-[2.5rem] shadow-2xl shadow-lime-900/10 border-4 border-white object-cover bg-lime-50 aspect-[4/3] transform transition duration-500 group-hover:-translate-y-2">
                            </div>
                        </div>
                        <div class="w-full lg:w-1/2 text-center lg:text-left">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-lime-100 text-lime-800 rounded-2xl mb-8 font-black text-xl shadow-sm">02</div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-slate-900 mb-6 leading-tight">Booking Slot, <br><span class="text-lime-600 decoration-lime-300 underline decoration-4 underline-offset-4">Bebas Antre</span></h2>
                            <p class="text-lg text-slate-500 leading-relaxed max-w-lg mx-auto lg:mx-0">Kunci slot pengisian spesifik sebelum Anda tiba. Dapatkan <b>QR Access</b> digital dan nikmati pengalaman pengisian daya prioritas.</p>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-24 reveal">
                        <div class="w-full lg:w-1/2 group">
                            <div class="relative">
                                <div class="absolute inset-0 bg-lime-200 rounded-[2.5rem] rotate-3 group-hover:rotate-6 transition-transform duration-500 ease-out"></div>
                                <img src="/images/admin.png" alt="Automation Dashboard" class="relative z-10 w-full rounded-[2.5rem] shadow-2xl shadow-lime-900/10 border-4 border-white object-contain bg-white aspect-[4/3] transform transition duration-500 group-hover:-translate-y-2">
                            </div>
                        </div>
                        <div class="w-full lg:w-1/2 text-center lg:text-left">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-lime-100 text-lime-800 rounded-2xl mb-8 font-black text-xl shadow-sm">03</div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-slate-900 mb-6 leading-tight">Otomasi & <br><span class="text-lime-600 decoration-lime-300 underline decoration-4 underline-offset-4">Audit Transparan</span></h2>
                            <p class="text-lg text-slate-500 leading-relaxed max-w-lg mx-auto lg:mx-0">Komunikasi dua arah (OCPP) yang canggih. Transparansi biaya total pendapatan Host <i>Zero Operator Cost</i>.</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ─── EV PEDIA ───────────────────────────────── -->
            <section class="py-24 bg-slate-900 relative overflow-hidden z-20">
                <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-[#CCFF00] rounded-full blur-[150px] opacity-10"></div>
                <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 relative z-10">
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-16 reveal">
                        <div class="w-full lg:w-1/2 text-center lg:text-left text-white">
                            <span class="text-[#CCFF00] font-bold tracking-[0.2em] uppercase text-xs bg-[#CCFF00]/10 px-4 py-2 rounded-full mb-8 inline-block border border-[#CCFF00]/20">Pusat Pengetahuan</span>
                            <h2 class="text-4xl lg:text-6xl font-black mb-6 leading-tight">Baru Punya <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-[#CCFF00] to-lime-500">Mobil Listrik?</span></h2>
                            <p class="text-slate-400 text-lg mb-10 max-w-lg mx-auto lg:mx-0 leading-relaxed">Bingung bedanya CCS2 dan CHAdeMO? E-VOLT Pedia hadir sebagai panduan lengkap Anda.</p>
                            <Link href="/ev-pedia" class="inline-flex items-center bg-[#CCFF00] text-slate-900 font-bold px-8 py-4 rounded-2xl hover:bg-white transition-all duration-300 hover:-translate-y-1 shadow-[0_0_25px_rgba(204,255,0,0.3)]">
                                Jelajahi Pedia
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </Link>
                        </div>
                        <div class="w-full lg:w-1/2 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <Link href="/ev-pedia" class="bg-white/5 p-8 rounded-[2rem] border border-white/5 hover:bg-white/10 hover:border-[#CCFF00]/30 transition-all duration-300 group hover:-translate-y-2">
                                <div class="text-4xl mb-6">🚗</div>
                                <h3 class="text-white font-bold text-xl mb-2 group-hover:text-[#CCFF00] transition">Katalog Mobil</h3>
                                <p class="text-slate-400 text-sm">Cek spek baterai & jarak tempuh.</p>
                            </Link>
                            <Link href="/ev-pedia" class="bg-white/5 p-8 rounded-[2rem] border border-white/5 hover:bg-white/10 hover:border-[#CCFF00]/30 transition-all duration-300 group mt-0 sm:mt-12 hover:-translate-y-2">
                                <div class="text-4xl mb-6">🔌</div>
                                <h3 class="text-white font-bold text-xl mb-2 group-hover:text-[#CCFF00] transition">Tipe Charger</h3>
                                <p class="text-slate-400 text-sm">Pahami bedanya AC Type 2, CCS2.</p>
                            </Link>
                            <Link href="/ev-pedia" class="bg-white/5 p-8 rounded-[2rem] border border-white/5 hover:bg-white/10 hover:border-[#CCFF00]/30 transition-all duration-300 group hover:-translate-y-2 sm:col-span-2 lg:col-span-1">
                                <div class="text-4xl mb-6">💡</div>
                                <h3 class="text-white font-bold text-xl mb-2 group-hover:text-[#CCFF00] transition">Tips & Trik</h3>
                                <p class="text-slate-400 text-sm">Cara agar baterai awet & hemat.</p>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ─── DARURAT CHARGING ───────────────────────── -->
            <section class="py-24 bg-white/80 backdrop-blur-sm border-t border-slate-200/50">
                <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">
                    <div class="bg-slate-50/90 rounded-[3rem] p-8 lg:p-20 shadow-xl overflow-hidden relative border border-slate-100/50 reveal">
                        <div class="absolute top-0 right-0 w-80 h-80 bg-orange-100 rounded-full blur-[100px] -mr-20 -mt-20 pointer-events-none opacity-60"></div>
                        <div class="flex flex-col lg:flex-row items-center gap-16">
                            <div class="w-full lg:w-1/2 relative z-10">
                                <span class="inline-block px-4 py-2 bg-orange-50 text-orange-600 rounded-full text-sm font-bold uppercase tracking-wider mb-8 border border-orange-100">Fitur Eksklusif</span>
                                <h2 class="text-3xl lg:text-5xl font-black text-slate-900 mb-6 leading-tight">Pengecasan Darurat <br><span class="text-orange-500">Tetangga</span></h2>
                                <p class="text-lg text-slate-500 mb-10 leading-relaxed">Kehabisan daya di tengah jalan? Fitur komunitas ini menghubungkan Anda dengan pemilik charger rumah terdekat yang bersedia berbagi daya.</p>
                                <button @click="openModal" class="group inline-flex items-center text-orange-600 font-bold hover:text-orange-700 text-lg transition">
                                    <span class="border-b-2 border-orange-200 group-hover:border-orange-500 pb-0.5 transition-all">Lihat cara kerjanya</span>
                                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </button>
                            </div>
                            <div class="w-full lg:w-1/2 flex justify-center relative z-10">
                                <img src="/images/charging-illustration.png" alt="Emergency Charging" class="w-full max-w-md transform hover:scale-105 transition duration-700 drop-shadow-2xl">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ─── PAKET ──────────────────────────────────── -->
            <section id="paket" class="py-24 bg-transparent">
                <div class="max-w-7xl mx-auto px-6">

                    <div class="text-center mb-16 reveal">
                        <span class="bg-[#CCFF00] text-slate-900 px-4 py-1.5 rounded-full text-sm font-bold tracking-wider uppercase mb-6 inline-block">Peluang Emas</span>
                        <h2 class="text-4xl md:text-5xl font-black text-slate-900 mb-4 leading-tight">Paket <span class="text-lime-600">Bundle Mitra</span></h2>
                        <p class="text-slate-500 text-xl max-w-xl mx-auto">Pilih paket sesuai kebutuhan — hunian atau komersial.</p>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 text-center divide-x divide-gray-200 bg-white/90 backdrop-blur-sm rounded-[2rem] p-10 border border-gray-100 shadow-xl mb-20 reveal delay-100">
                        <div><div class="text-5xl font-black text-lime-500 mb-2">+45%</div><p class="text-lg font-bold text-slate-900">Pertumbuhan EV</p></div>
                        <div><div class="text-5xl font-black text-lime-500 mb-2">24/7</div><p class="text-lg font-bold text-slate-900">Passive Income</p></div>
                        <div><div class="text-5xl font-black text-lime-500 mb-2">0%</div><p class="text-lg font-bold text-slate-900">Biaya Franchise</p></div>
                    </div>

                    <!-- 2 Package Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto reveal delay-200">

                        <!-- HOME -->
                        <div class="bg-white/90 backdrop-blur-sm rounded-[2rem] p-8 border border-gray-200 hover:border-[#00C853] hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-lime-600 bg-lime-50 px-3 py-1 rounded-full">Hunian</span>
                                    <h3 class="text-2xl font-black text-slate-900 mt-3">Home Bundle</h3>
                                    <p class="text-slate-500 text-sm mt-1">AC Wallbox untuk garasi pribadi.</p>
                                </div>
                                <div class="w-14 h-14 bg-lime-50 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0">🏠</div>
                            </div>
                            <div class="bg-gray-50/80 p-6 rounded-2xl mb-6 flex-grow">
                                <h5 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Spesifikasi</h5>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-500">Unit</span><span class="font-bold text-slate-900">AC Wallbox</span></div>
                                    <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-500">Daya</span><span class="font-bold text-slate-900">20 kW (Standard Fast)</span></div>
                                    <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-500">Konektor</span><span class="font-bold text-slate-900">Type 2 / CCS2</span></div>
                                    <div class="flex justify-between pb-2"><span class="text-gray-500">Cocok untuk</span><span class="font-bold text-slate-900">Garasi & Vila</span></div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 mb-6">
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="w-4 h-4 text-lime-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Monitoring real-time via app
                                </div>
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="w-4 h-4 text-lime-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Laporan pendapatan otomatis
                                </div>
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="w-4 h-4 text-lime-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Garansi unit 2 tahun
                                </div>
                            </div>
                            <a :href="waLink('Saya tertarik paket Home Bundle 20kW')" class="block w-full py-4 rounded-xl border-2 border-slate-900 text-slate-900 font-bold text-center hover:bg-slate-900 hover:text-white transition-all duration-300">
                                Tanya Paket Ini
                            </a>
                        </div>

                        <!-- COMMERCIAL -->
                        <div class="bg-slate-900 rounded-[2rem] p-8 border border-slate-800 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col relative overflow-hidden">
                            <div class="absolute top-0 right-0 bg-[#CCFF00] text-slate-900 text-xs font-bold px-4 py-2 rounded-bl-2xl tracking-wide">FAST CHARGING</div>
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-[#CCFF00] bg-[#CCFF00]/10 border border-[#CCFF00]/20 px-3 py-1 rounded-full">Komersial</span>
                                    <h3 class="text-2xl font-black text-white mt-3">Commercial Bundle</h3>
                                    <p class="text-gray-400 text-sm mt-1">DC Fast Charger untuk area publik.</p>
                                </div>
                                <div class="w-14 h-14 bg-[#CCFF00]/10 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0">🏢</div>
                            </div>
                            <div class="bg-slate-800 p-6 rounded-2xl mb-6 flex-grow border border-slate-700">
                                <h5 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Spesifikasi</h5>
                                <div class="space-y-3 text-sm text-gray-300">
                                    <div class="flex justify-between border-b border-slate-700 pb-2"><span>Unit</span><span class="font-bold text-white">DC Fast Charger</span></div>
                                    <div class="flex justify-between border-b border-slate-700 pb-2"><span>Daya</span><span class="font-bold text-white">30 kW – 60 kW</span></div>
                                    <div class="flex justify-between border-b border-slate-700 pb-2"><span>Konektor</span><span class="font-bold text-white">CCS2 / CHAdeMO</span></div>
                                    <div class="flex justify-between pb-2"><span>Cocok untuk</span><span class="font-bold text-white">SPKLU & Rest Area</span></div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 mb-6">
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <svg class="w-4 h-4 text-[#CCFF00] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Dashboard OCPP terintegrasi
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <svg class="w-4 h-4 text-[#CCFF00] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    QRIS & E-Wallet otomatis
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <svg class="w-4 h-4 text-[#CCFF00] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Garansi unit 3 tahun + support
                                </div>
                            </div>
                            <a :href="waLink('Saya tertarik paket Commercial Bundle DC Fast Charger')" class="block w-full py-4 rounded-xl bg-[#00C853] text-white font-bold text-center hover:bg-[#00a844] transition-all duration-300 shadow-lg shadow-green-900/20">
                                Ambil Paket Ini
                            </a>
                        </div>

                    </div>
                </div>
            </section>

            <!-- ─── CONTACT ────────────────────────────────── -->
            <section id="kontak" class="py-24 bg-slate-900 text-white relative overflow-hidden rounded-t-[4rem] z-20">
                <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-[#CCFF00] rounded-full blur-[150px] opacity-10 pointer-events-none"></div>
                <div class="max-w-7xl mx-auto px-6 relative z-10">
                    <div class="text-center mb-16 reveal">
                        <h2 class="text-4xl md:text-5xl font-black mb-6">Mari Berkolaborasi</h2>
                        <p class="text-gray-400 text-lg max-w-2xl mx-auto">Kami mengundang pemilik stasiun, operator energi, dan brand kendaraan listrik untuk bergabung.</p>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-20 reveal">
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-8 rounded-3xl text-center hover:bg-white/10 transition group">
                            <div class="w-16 h-16 bg-[#00C853]/20 text-[#00C853] rounded-2xl flex items-center justify-center text-3xl mx-auto mb-6 group-hover:scale-110 transition">📞</div>
                            <h3 class="font-bold text-xl mb-2">Telepon</h3><p class="text-gray-400">{{ contactData.phone }}</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-8 rounded-3xl text-center hover:bg-white/10 transition group">
                            <div class="w-16 h-16 bg-[#00C853]/20 text-[#00C853] rounded-2xl flex items-center justify-center text-3xl mx-auto mb-6 group-hover:scale-110 transition">📧</div>
                            <h3 class="font-bold text-xl mb-2">Email</h3><p class="text-gray-400">{{ contactData.email }}</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-8 rounded-3xl text-center hover:bg-white/10 transition group">
                            <div class="w-16 h-16 bg-[#00C853]/20 text-[#00C853] rounded-2xl flex items-center justify-center text-3xl mx-auto mb-6 group-hover:scale-110 transition">📍</div>
                            <h3 class="font-bold text-xl mb-2">Kantor</h3><p class="text-gray-400 whitespace-pre-line text-sm">{{ contactData.address }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-[3rem] p-8 lg:p-12 shadow-2xl text-slate-900 reveal">
                        <div class="grid lg:grid-cols-2 gap-12 items-center">
                            <div>
                                <h3 class="text-3xl font-bold mb-4">Kirim Pesan 👋</h3>
                                <p class="text-gray-600 mb-8 leading-relaxed">Punya pertanyaan soal proses booking? Tim support kami biasanya membalas dalam <span class="font-bold">1x24 jam</span>.</p>
                                <div class="bg-lime-50 p-6 rounded-2xl border border-lime-100">
                                    <p class="text-lime-800 text-sm font-medium">💡 Tips: Jelaskan kebutuhan Anda sedetail mungkin.</p>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-2"><ContactForm /></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Partner Logos -->
            <section class="py-16 bg-white/80 backdrop-blur-sm relative z-20">
                <div class="max-w-7xl mx-auto px-6 text-center reveal">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.3em] mb-12">Didukung Ekosistem Global</p>
                    <div class="flex flex-wrap justify-center items-center gap-8 md:gap-20 opacity-40 grayscale hover:grayscale-0 transition-all duration-700">
                        <img src="https://cdn.worldvectorlogo.com/logos/tesla-9.svg" alt="Tesla" class="h-6 md:h-8 hover:scale-110 transition-transform cursor-pointer">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/e2/BYD_Auto_2022_logo.svg" alt="BYD" class="h-6 md:h-8 hover:scale-110 transition-transform cursor-pointer">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/4/44/Hyundai_Motor_Company_logo.svg" alt="Hyundai" class="h-5 md:h-7 hover:scale-110 transition-transform cursor-pointer">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b6/KIA_logo3.svg" alt="Kia" class="h-5 md:h-7 hover:scale-110 transition-transform cursor-pointer">

                    </div>
                </div>
            </section>

        </main>

        <Footer class="relative z-50" />

        <!-- ─── MODAL ──────────────────────────────────────── -->
        <Transition name="modal-fade">
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4" @click="handleOutsideClick">
                <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md"></div>
                <div class="modal-content relative bg-white p-10 rounded-[2.5rem] shadow-2xl max-w-md w-full text-center z-[110]">
                    <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-orange-100 shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-3xl font-black text-slate-900 mb-2">Segera Hadir!</h4>
                    <p class="text-slate-500 mb-8 leading-relaxed">Fitur <span class="font-bold text-orange-600 bg-orange-50 px-1 rounded">Pengecasan Darurat</span> sedang dalam tahap uji coba final.</p>
                    <button @click="closeModal" class="w-full bg-slate-900 text-white font-bold px-6 py-4 rounded-2xl hover:bg-slate-800 transition duration-200 shadow-xl hover:shadow-2xl hover:-translate-y-0.5">Mengerti, Terima Kasih</button>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
.reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
}
.reveal.active { opacity: 1; transform: translateY(0); }
.delay-100 { transition-delay: 100ms; }
.delay-200 { transition-delay: 200ms; }

/* Modal */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.modal-fade-enter-active .modal-content { transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-fade-leave-active .modal-content { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.modal-fade-enter-from .modal-content, .modal-fade-leave-to .modal-content { opacity: 0; transform: scale(0.7) translateY(30px); }

/* Mobile menu */
.mobile-menu-enter-active, .mobile-menu-leave-active { transition: all 0.2s ease; }
.mobile-menu-enter-from, .mobile-menu-leave-to { opacity: 0; transform: translateY(-8px); }

@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
</style>