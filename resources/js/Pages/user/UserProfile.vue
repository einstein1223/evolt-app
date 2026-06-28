<script setup>
import { ref, computed } from "vue";
import { usePage, useForm, router, Link } from "@inertiajs/vue3";
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';

// ─── 1. AUTH & PROPS ─────────────────────────────────────────────────────────
const page = usePage();
const authUser = page.props.auth?.user || {};

const props = defineProps({
    bookings: { type: Array, default: () => [] }
});

// ─── 2. FORMAT DATA BOOKING ───────────────────────────────────────────────────
const userOrders = computed(() => {
    return props.bookings.map(booking => ({
        id:             booking.id,
        station_name:   booking.station_name  || 'Stasiun Pengisian',
        booking_number: booking.booking_code  || `EV-${booking.id}`,
        duration:       booking.duration      ? `${booking.duration} Menit` : 'N/A',
        location:       booking.location      || '-',
        created_at:     booking.booking_date  || booking.created_at,
        total_price:    booking.total_price   || 0,
        status:         booking.status        || 'Booked',
        port_type:      booking.port_type     || '-',
        booking_slot:   booking.booking_slot  || null,
        plate_number:   booking.plate_number  || '-',
        end_time:       booking.end_time      || null,
    }));
});

// ─── 3. FORM PROFIL ──────────────────────────────────────────────────────────
const form = useForm({
    username:      authUser.username      || authUser.name || "",
    email:         authUser.email         || "",
    nomor_telepon: authUser.nomor_telepon || "",
    nomor_plat:    authUser.nomor_plat    || "",
    car_brand:     authUser.car_brand     || "",
    car_series:    authUser.car_series    || "",
    car_type:      authUser.car_type      || "",
    gender:        authUser.gender        || "Laki-laki",
    birthDate:     authUser.birthDate     || "",
    city:          authUser.city          || "Kota Batam",
});

const passwordForm = useForm({
    current_password:      "",
    password:              "",
    password_confirmation: "",
});

// ─── 4. UI STATE ─────────────────────────────────────────────────────────────
const urlParams   = new URLSearchParams(window.location.search);
const defaultMenu = urlParams.get('tab') === 'orders' ? 'orders' : 'account';

const activeMenu = ref(defaultMenu);
const activeTab  = ref('informasi_akun');
const flashMsg   = ref('');

if (defaultMenu === 'orders') {
    flashMsg.value = '✅ Booking berhasil! Cek detail pesanan Anda di bawah.';
    setTimeout(() => flashMsg.value = '', 4000);
}

// ─── 5. DETAIL ORDER MODAL ───────────────────────────────────────────────────
const showDetailModal  = ref(false);
const selectedOrder    = ref(null);

const openDetail = (order) => {
    selectedOrder.value   = order;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    selectedOrder.value   = null;
};

// ─── 6. STATUS HELPERS ───────────────────────────────────────────────────────
/**
 * Kembalikan { bg, text, border, icon, label } berdasarkan status booking
 */
const statusInfo = (status) => {
    const s = (status || '').toLowerCase();

    // ✅ Sudah Lunas / Selesai
    if (['lunas', 'selesai', 'done', 'paid'].includes(s))
        return { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-300', icon: '✅', label: 'Sudah Dibayar' };

    // 🕐 Menunggu pembayaran
    if (['menunggu pembayaran', 'pending', 'booked'].includes(s))
        return { bg: 'bg-amber-100', text: 'text-amber-700', border: 'border-amber-300', icon: '🕐', label: 'Menunggu Pembayaran' };

    // ❌ Dibatalkan oleh host
    if (s === 'dibatalkan host')
        return { bg: 'bg-rose-100', text: 'text-rose-700', border: 'border-rose-300', icon: '🚫', label: 'Dibatalkan Host' };

    // ❌ Dibatalkan user
    if (['batal', 'dibatalkan', 'cancelled'].includes(s))
        return { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-300', icon: '❌', label: 'Dibatalkan' };

    // ⏰ Kadaluarsa
    if (['kadaluarsa', 'expired'].includes(s))
        return { bg: 'bg-gray-100', text: 'text-gray-500', border: 'border-gray-300', icon: '⏰', label: 'Kadaluarsa' };

    // 💸 Gagal bayar
    if (['gagal bayar', 'failed'].includes(s))
        return { bg: 'bg-red-100', text: 'text-red-600', border: 'border-red-300', icon: '💸', label: 'Gagal Bayar' };

    // 🔄 Refund
    if (s === 'refund' || s === 'refunded')
        return { bg: 'bg-purple-100', text: 'text-purple-700', border: 'border-purple-300', icon: '🔄', label: 'Refund' };

    return { bg: 'bg-gray-100', text: 'text-gray-600', border: 'border-gray-300', icon: '📋', label: status || 'Tidak Diketahui' };
};

const isPaid = (status) => {
    const s = (status || '').toLowerCase();
    return ['lunas', 'selesai', 'done', 'paid'].includes(s);
};

const menuItems = [
    { id: "account",  label: "Akun",           icon: "👤" },
    { id: "orders",   label: "Pesanan Saya",    icon: "🎟️" },
    { id: "news",     label: "Berita",           icon: "📰" },
    { id: "password", label: "Ubah Kata Sandi", icon: "🔑" },
    { id: "logout",   label: "Keluar",           icon: "🚪" },
];

// ─── 7. HANDLERS ─────────────────────────────────────────────────────────────
const switchMenu = (id) => {
    if (id === 'logout') {
        if (confirm('Yakin ingin keluar?')) router.post(route("logout"));
    } else {
        activeMenu.value = id;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const submitProfile = () => {
    form.patch(route("profile.update"), {
        preserveScroll: true,
        onSuccess: () => { flashMsg.value = "Profil berhasil diperbarui!"; setTimeout(() => flashMsg.value = '', 3000); },
        onError:   () => { flashMsg.value = "Gagal update. Periksa kembali inputan Anda."; },
    });
};

const changePassword = () => {
    passwordForm.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => { flashMsg.value = "Kata sandi berhasil diubah!"; passwordForm.reset(); },
        onError:   () => {
            if (passwordForm.errors.current_password) flashMsg.value = "Password lama salah!";
        }
    });
};

// ─── 8. HELPERS ──────────────────────────────────────────────────────────────
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
}).format(val);

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long',
        year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const formatDateShort = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

const qrUrl = (code) =>
    `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(code)}`;
</script>

<template>
    <div class="min-h-screen bg-gray-50 font-poppins flex flex-col text-gray-800">

        <Navbar />

        <!-- Flash Message -->
        <Transition name="fade">
            <div v-if="flashMsg"
                class="fixed top-24 left-1/2 -translate-x-1/2 z-50 bg-green-600 text-white px-6 py-3 rounded-2xl shadow-xl font-semibold text-sm whitespace-nowrap">
                {{ flashMsg }}
            </div>
        </Transition>

        <main class="flex-1 max-w-7xl mx-auto w-full pt-32 pb-16 px-4 sm:px-6 flex flex-col md:flex-row gap-8">

            <!-- ── SIDEBAR ── -->
            <aside class="w-full md:w-72 flex-shrink-0">
                <div class="bg-white shadow-lg rounded-2xl p-6 sticky top-32 border border-gray-100">
                    <div class="mb-8 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full mx-auto mb-3 flex items-center justify-center text-3xl border-2 border-green-100">
                            👤
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 truncate">{{ form.username || 'User E-VOLT' }}</h3>
                        <p class="text-sm text-gray-500 truncate">{{ form.email }}</p>
                        <div v-if="form.nomor_plat"
                            class="mt-3 inline-block px-3 py-1 bg-[#f0fdf4] text-[#16a34a] rounded-full text-xs font-bold border border-green-200 uppercase tracking-widest">
                            {{ form.nomor_plat }}
                        </div>
                    </div>

                    <nav class="flex flex-col space-y-2">
                        <button v-for="item in menuItems" :key="item.id"
                            type="button" @click="switchMenu(item.id)"
                            :class="[
                                'flex items-center gap-4 px-4 py-3.5 rounded-xl text-left transition-all duration-200 font-medium w-full',
                                item.id === activeMenu
                                    ? 'bg-[#00C853] text-white shadow-md shadow-green-200'
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-[#00C853]',
                                item.id === 'logout'
                                    ? 'text-red-500 hover:bg-red-50 hover:text-red-600 mt-6 border-t border-gray-100 pt-4'
                                    : '',
                            ]">
                            <span class="text-xl">{{ item.icon }}</span>
                            <span>{{ item.label }}</span>
                            <span v-if="item.id === 'orders' && userOrders.length > 0"
                                class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full"
                                :class="item.id === activeMenu ? 'bg-white/30 text-white' : 'bg-green-100 text-green-700'">
                                {{ userOrders.length }}
                            </span>
                        </button>
                    </nav>
                </div>
            </aside>

            <!-- ── KONTEN UTAMA ── -->
            <section class="flex-1 bg-white shadow-xl rounded-2xl p-6 sm:p-10 min-h-[600px] border border-gray-100 relative overflow-hidden">

                <!-- AKUN -->
                <div v-if="activeMenu === 'account'" class="animate-fade-in">
                    <h1 class="text-2xl font-bold mb-1 text-gray-900">Detail Akun</h1>
                    <p class="text-gray-500 text-sm mb-6">Kelola informasi profil dan kendaraan Anda.</p>

                    <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200 pb-1">
                        <button @click="activeTab = 'informasi_akun'"
                            :class="['px-6 py-2.5 rounded-t-lg font-semibold transition-all text-sm border-b-2',
                                activeTab === 'informasi_akun'
                                    ? 'border-[#00C853] text-[#00C853] bg-green-50/50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700']">
                            Informasi Akun
                        </button>
                        <button @click="activeTab = 'informasi_personal'"
                            :class="['px-6 py-2.5 rounded-t-lg font-semibold transition-all text-sm border-b-2',
                                activeTab === 'informasi_personal'
                                    ? 'border-[#00C853] text-[#00C853] bg-green-50/50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700']">
                            Data Kendaraan & Kontak
                        </button>
                    </div>

                    <form @submit.prevent="submitProfile" class="space-y-6">
                        <div v-if="activeTab === 'informasi_akun'" class="grid sm:grid-cols-2 gap-6 animate-slide-up">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                                <input v-model="form.username" type="text"
                                    class="w-full border border-gray-300 rounded-xl p-3.5 focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition" />
                                <p v-if="form.errors.username" class="text-red-500 text-xs mt-1">{{ form.errors.username }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kelamin</label>
                                <select v-model="form.gender"
                                    class="w-full border border-gray-300 rounded-xl p-3.5 focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] bg-white">
                                    <option>Laki-laki</option>
                                    <option>Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kota Asal</label>
                                <select v-model="form.city"
                                    class="w-full border border-gray-300 rounded-xl p-3.5 focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] bg-white">
                                    <option>Kota Batam</option>
                                    <option>Jakarta</option>
                                    <option>Surabaya</option>
                                </select>
                            </div>
                            <div class="col-span-2 pt-4 flex justify-end">
                                <button type="submit" :disabled="form.processing"
                                    class="px-8 py-3.5 font-bold rounded-xl shadow-lg bg-[#00C853] text-white hover:bg-[#008e3b] transition transform active:scale-95 disabled:opacity-70">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'informasi_personal'" class="space-y-6 animate-slide-up">
                            <div class="grid sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                                    <input :value="form.email" disabled
                                        class="w-full border border-gray-200 rounded-xl p-3.5 bg-gray-100 text-gray-500 cursor-not-allowed" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor HP / WhatsApp</label>
                                    <input v-model="form.nomor_telepon" type="text"
                                        class="w-full border border-gray-300 rounded-xl p-3.5 focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853]" />
                                </div>
                            </div>

                            <div class="bg-[#fcfdfa] border border-[#e3fbd8] rounded-2xl p-6 relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4 opacity-10">
                                    <span class="text-8xl">🚗</span>
                                </div>
                                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <span class="w-1 h-5 bg-[#00C853] rounded-full"></span> Data Mobil Anda
                                </h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Merk</label>
                                        <input v-model="form.car_brand" readonly
                                            class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-gray-700 font-medium text-sm cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Model / Seri</label>
                                        <input v-model="form.car_series" readonly
                                            class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-gray-700 font-medium text-sm cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipe / Varian</label>
                                        <input v-model="form.car_type" readonly
                                            class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-gray-700 font-medium text-sm cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Plat Nomor</label>
                                        <input v-model="form.nomor_plat"
                                            class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-gray-900 font-bold text-sm uppercase focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853]">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-4 italic">*Hanya Plat Nomor yang dapat diubah di sini.</p>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" :disabled="form.processing"
                                    class="px-8 py-3.5 font-bold rounded-xl shadow-lg bg-[#00C853] text-white hover:bg-[#008e3b] transition transform active:scale-95 disabled:opacity-70">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ══════════════════════════════════════
                     PESANAN SAYA
                ══════════════════════════════════════ -->
                <div v-else-if="activeMenu === 'orders'" class="animate-fade-in">
                    <h2 class="text-2xl font-bold mb-1 text-gray-900">Riwayat Pesanan</h2>
                    <p class="text-gray-500 text-sm mb-6">Total {{ userOrders.length }} booking tercatat.</p>

                    <div v-if="userOrders.length > 0" class="space-y-4">
                        <div v-for="order in userOrders" :key="order.id"
                            class="bg-white border rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group"
                            :class="isPaid(order.status) ? 'border-emerald-200 bg-emerald-50/20' : 'border-gray-100'">

                            <!-- Baris atas: nama stasiun + status badge -->
                            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                <div>
                                    <div class="font-bold text-gray-800 text-lg group-hover:text-[#00C853] transition-colors leading-tight">
                                        {{ order.station_name }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        🗓 {{ formatDate(order.created_at) }}
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <span :class="[
                                    'flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border',
                                    statusInfo(order.status).bg,
                                    statusInfo(order.status).text,
                                    statusInfo(order.status).border
                                ]">
                                    {{ statusInfo(order.status).icon }}
                                    {{ statusInfo(order.status).label }}
                                </span>
                            </div>

                            <!-- Chips info -->
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                <span class="bg-gray-100 px-3 py-1 rounded-md font-mono text-gray-700 font-medium border border-gray-200 text-xs">
                                    {{ order.booking_number }}
                                </span>
                                <span v-if="order.booking_slot"
                                    class="flex items-center gap-1 bg-green-50 text-green-700 border border-green-200 px-2.5 py-1 rounded-md text-xs font-bold">
                                    🕐 {{ order.booking_slot }} WIB
                                </span>
                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                    <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                    ⏱ {{ order.duration }}
                                </span>
                                <span v-if="order.port_type && order.port_type !== '-'" class="text-xs text-gray-500 flex items-center gap-1">
                                    <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                    ⚡ {{ order.port_type }}
                                </span>
                                <span v-if="order.location && order.location !== '-'" class="text-xs text-gray-500 flex items-center gap-1">
                                    <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                    📍 {{ order.location }}
                                </span>
                            </div>

                            <!-- Baris bawah: harga + tombol detail -->
                            <div class="flex items-center justify-between border-t border-gray-100 pt-3 gap-3">
                                <div class="font-extrabold text-[#00C853] text-xl">
                                    {{ formatRupiah(order.total_price) }}
                                </div>
                                <button @click="openDetail(order)"
                                    class="flex items-center gap-1.5 px-4 py-2 bg-[#00C853] text-white text-xs font-bold rounded-xl hover:bg-[#00A142] transition active:scale-95 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-gray-200 rounded-3xl bg-gray-50">
                        <div class="text-6xl mb-4 opacity-30">🎫</div>
                        <p class="text-lg font-medium text-gray-600 mb-2">Belum ada riwayat pesanan.</p>
                        <p class="text-sm text-gray-400 mb-6">Mulai pengisian pertama Anda sekarang!</p>
                        <Link :href="route('dashboard')"
                            class="px-6 py-3 bg-[#00C853] text-white font-bold rounded-xl hover:bg-[#008e3b] transition shadow-lg active:scale-95">
                            Cari Stasiun EV
                        </Link>
                    </div>
                </div>

                <!-- BERITA -->
                <div v-else-if="activeMenu === 'news'" class="animate-fade-in">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Berita & Update</h2>
                    <div class="p-6 bg-blue-50 border border-blue-100 rounded-2xl flex items-start gap-4">
                        <div class="text-3xl">📢</div>
                        <div>
                            <div class="font-bold text-blue-900 text-lg">E-VOLT Kini Tersedia di Batam!</div>
                            <p class="text-sm text-blue-700 mt-1 leading-relaxed">
                                Sistem reservasi pintar E-VOLT resmi beroperasi. Nikmati kemudahan mencari stasiun tanpa antre panjang.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- UBAH PASSWORD -->
                <div v-else-if="activeMenu === 'password'" class="animate-fade-in">
                    <h2 class="text-2xl font-bold mb-6 text-gray-900">Ubah Kata Sandi</h2>
                    <form @submit.prevent="changePassword" class="space-y-5 max-w-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi Saat Ini</label>
                            <input type="password" v-model="passwordForm.current_password"
                                class="w-full border border-gray-300 p-3.5 rounded-xl focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition" />
                            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.current_password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi Baru</label>
                            <input type="password" v-model="passwordForm.password"
                                class="w-full border border-gray-300 p-3.5 rounded-xl focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition" />
                            <p v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Kata Sandi</label>
                            <input type="password" v-model="passwordForm.password_confirmation"
                                class="w-full border border-gray-300 p-3.5 rounded-xl focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition" />
                            <p v-if="passwordForm.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password_confirmation }}</p>
                        </div>
                        <div class="pt-2">
                            <button type="submit" :disabled="passwordForm.processing"
                                class="px-8 py-3.5 rounded-xl bg-[#00C853] text-white font-bold shadow-lg hover:bg-[#008e3b] transition transform active:scale-95 disabled:opacity-70">
                                {{ passwordForm.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                            </button>
                        </div>
                    </form>
                </div>

            </section>
        </main>

        <Footer />

        <!-- ══════════════════════════════════════════════════════
             MODAL DETAIL PESANAN
        ══════════════════════════════════════════════════════ -->
        <Transition name="modal-fade">
            <div v-if="showDetailModal && selectedOrder"
                class="fixed inset-0 z-[9000] flex items-end sm:items-center justify-center p-0 sm:p-4"
                @click.self="closeDetail">

                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDetail"></div>

                <!-- Sheet -->
                <div class="relative z-10 bg-white w-full max-h-[92vh] flex flex-col rounded-t-[2rem] sm:rounded-3xl shadow-2xl sm:max-w-lg overflow-hidden">

                    <!-- Header warna sesuai status -->
                    <div class="px-6 py-5 flex-shrink-0"
                        :class="isPaid(selectedOrder.status) ? 'bg-gradient-to-br from-emerald-500 to-green-600' : 'bg-gradient-to-br from-gray-700 to-gray-900'">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 bg-white/20 rounded-2xl flex items-center justify-center text-2xl">
                                    {{ isPaid(selectedOrder.status) ? '✅' : '🎫' }}
                                </div>
                                <div>
                                    <p class="text-white/70 text-xs font-medium">Detail Pesanan</p>
                                    <h3 class="text-white font-black text-lg leading-tight">{{ selectedOrder.station_name }}</h3>
                                </div>
                            </div>
                            <button @click="closeDetail"
                                class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Status pill -->
                        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                            <span class="text-base">{{ statusInfo(selectedOrder.status).icon }}</span>
                            <span class="text-white font-bold text-sm">{{ statusInfo(selectedOrder.status).label }}</span>
                        </div>
                    </div>

                    <!-- Body scrollable -->
                    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                        <!-- QR Code kode booking -->
                        <div class="flex flex-col items-center bg-gray-50 rounded-2xl p-5 border border-gray-100">
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-3">Kode Booking</p>
                            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-200 mb-3">
                                <img :src="qrUrl(selectedOrder.booking_number)"
                                    :alt="selectedOrder.booking_number"
                                    class="w-36 h-36 object-contain" />
                            </div>
                            <span class="font-mono font-black text-xl text-gray-800 tracking-widest">
                                {{ selectedOrder.booking_number }}
                            </span>
                            <p class="text-xs text-gray-400 mt-1">Tunjukkan QR ini kepada operator stasiun</p>
                        </div>

                        <!-- Info grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Jam Booking</p>
                                <p class="font-bold text-gray-800 text-sm flex items-center gap-1">
                                    <span class="text-green-500">🕐</span>
                                    {{ selectedOrder.booking_slot ? selectedOrder.booking_slot + ' WIB' : '-' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Durasi</p>
                                <p class="font-bold text-gray-800 text-sm flex items-center gap-1">
                                    <span>⏱</span> {{ selectedOrder.duration }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Port Charging</p>
                                <p class="font-bold text-gray-800 text-sm flex items-center gap-1">
                                    <span class="text-yellow-500">⚡</span>
                                    {{ selectedOrder.port_type !== '-' ? selectedOrder.port_type : 'Regular' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Plat Nomor</p>
                                <p class="font-bold text-gray-800 text-sm uppercase">{{ selectedOrder.plate_number }}</p>
                            </div>
                        </div>

                        <!-- Lokasi -->
                        <div v-if="selectedOrder.location && selectedOrder.location !== '-'"
                            class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100 flex items-start gap-3">
                            <span class="text-lg mt-0.5">📍</span>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-0.5">Lokasi Stasiun</p>
                                <p class="font-semibold text-gray-800 text-sm leading-snug">{{ selectedOrder.location }}</p>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100 flex items-start gap-3">
                            <span class="text-lg mt-0.5">🗓</span>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-0.5">Tanggal Booking</p>
                                <p class="font-semibold text-gray-800 text-sm">{{ formatDate(selectedOrder.created_at) }}</p>
                            </div>
                        </div>

                        <!-- Total harga — highlight jika sudah bayar -->
                        <div class="rounded-2xl px-5 py-4 flex items-center justify-between border-2"
                            :class="isPaid(selectedOrder.status)
                                ? 'bg-emerald-50 border-emerald-200'
                                : 'bg-gray-50 border-gray-200'">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide"
                                    :class="isPaid(selectedOrder.status) ? 'text-emerald-600' : 'text-gray-400'">
                                    Total Pembayaran
                                </p>
                                <p class="font-black text-2xl"
                                    :class="isPaid(selectedOrder.status) ? 'text-emerald-600' : 'text-gray-800'">
                                    {{ formatRupiah(selectedOrder.total_price) }}
                                </p>
                            </div>
                            <div v-if="isPaid(selectedOrder.status)"
                                class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center text-2xl">
                                ✅
                            </div>
                            <div v-else class="text-right">
                                <span :class="[
                                    'px-3 py-1.5 rounded-full text-xs font-bold border',
                                    statusInfo(selectedOrder.status).bg,
                                    statusInfo(selectedOrder.status).text,
                                    statusInfo(selectedOrder.status).border
                                ]">
                                    {{ statusInfo(selectedOrder.status).label }}
                                </span>
                            </div>
                        </div>

                        <!-- Catatan untuk status tertentu -->
                        <div v-if="!isPaid(selectedOrder.status) && ['menunggu pembayaran','pending','booked'].includes(selectedOrder.status.toLowerCase())"
                            class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-3">
                            <span class="text-amber-500 text-lg mt-0.5">⚠️</span>
                            <p class="text-xs text-amber-700 font-medium leading-relaxed">
                                Pembayaran belum dikonfirmasi. Jika sudah bayar via QRIS, status akan diperbarui otomatis dalam beberapa menit.
                            </p>
                        </div>

                        <div v-if="['dibatalkan host'].includes(selectedOrder.status.toLowerCase())"
                            class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 flex items-start gap-3">
                            <span class="text-rose-500 text-lg mt-0.5">🚫</span>
                            <p class="text-xs text-rose-700 font-medium leading-relaxed">
                                Pesanan ini dibatalkan oleh host stasiun. Hubungi customer service untuk informasi lebih lanjut.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex-shrink-0 px-6 py-4 border-t border-gray-100 bg-white">
                        <button @click="closeDetail"
                            class="w-full py-3.5 rounded-xl font-bold text-sm transition active:scale-95 border-2 border-gray-200 text-gray-600 hover:bg-gray-50 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

.font-poppins { font-family: 'Poppins', sans-serif; }

@keyframes fadeIn  { from { opacity: 0; }                              to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.animate-fade-in  { animation: fadeIn 0.4s ease-out forwards; }
.animate-slide-up { animation: slideUp 0.4s ease-out forwards; }

/* Flash fade */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from,   .fade-leave-to    { opacity: 0; transform: translateY(-8px); }

/* Modal */
.modal-fade-enter-active { transition: opacity .25s ease; }
.modal-fade-leave-active { transition: opacity .2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.modal-fade-enter-active .relative { animation: slide-up-sheet .35s cubic-bezier(.34,1.56,.64,1) both; }
.modal-fade-leave-active  .relative { animation: slide-down-sheet .2s ease both; }

@keyframes slide-up-sheet   { from { transform: translateY(60px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes slide-down-sheet { from { transform: translateY(0); opacity: 1; }   to { transform: translateY(60px); opacity: 0; } }
</style>