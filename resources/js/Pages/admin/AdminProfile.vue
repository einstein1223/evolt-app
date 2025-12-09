<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';

import Navbar from '@/Components/NavbarAdmin.vue';
import Footer from '@/Components/Footer.vue';
import LineChart from '@/Components/LineChart.vue';

// showPasswordChange, togglePasswordChange dihilangkan, karena logic pindah ke activeTab

// --- MOCK COMPONENTS (Untuk memastikan file dapat berjalan secara mandiri) ---
const IconUser = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' }
const IconTicket = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="10" width="18" height="12" rx="2"/><path d="M7 10v2a2 2 0 0 1-2 2H3"/><path d="M17 10v2a2 2 0 0 0 2 2h2"/><path d="M12 2v4"/><path d="M12 6h2"/><path d="M12 6H10"/></svg>' }
const IconNews = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>' }
const IconTag = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.5" cy="7.5" r="5.5"/><path d="M13.5 19l4.5 4.5 3-3L16.5 16l-3 3z"/></svg>' }
const IconClock = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' }
const IconKey = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12h-3a4 4 0 0 0-4 4v3a4 4 0 0 0 4 4h3a4 4 0 0 0 4-4v-3a4 4 0 0 0-4-4z"/><circle cx="16" cy="16" r="2"/></svg>' }
const IconLogout = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>' }
const IconUserSmall = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' }
const IconCalendar = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>' }
const IconLockOpen = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 11c0-.667.333-2 2-2s2 1.333 2 2v3m-4 4h4m-2-7h.01M5 13V7a4 4 0 118 0v6m-4 4h4" /></svg>' }
const IconMail = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>' }
const IconPhone = { template: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2 2c-3.23 0-6.4-.9-9.25-2.61-2.92-1.74-5.26-4.08-7-7C2.9 10.42 2 7.25 2 4a2 2 0 0 1 2-2h3c.6 0 1.25.2 1.7.7l.6 1.4c.15.4.15.8 0 1.2l-1.3 2.7c-.2.4.1.9.5 1.2l5 5c.4.4.8.7 1.2.5l2.7-1.3c.4-.2.8-.2 1.2 0l1.4.6c.5.4.7 1 .7 1.6v3z"/></svg>' }

// --- HELPER FUNCTION ---
const getColorClass = (type) => {
    switch (type) {
        case 'accent': return 'bg-[#00C853] hover:bg-[#00A142] text-white';
        case 'light-accent-bg': return 'bg-[#00C853] border-l-4 border-l-green-400 text-purple-800';
        case 'tab-active-bg': return 'bg-[#00C853] text-dark';
        case 'icon-active': return 'text-dark';
        case 'icon-inactive': return 'text-gray-500';
        default: return '';
    }
};

// --- DUMMY DATA ---
const user = reactive({
    name: 'Admin',
    email: 'admin@evcharge.com',
});

// Data untuk 'Informasi Akun' (Demografis/Fisik)
const accountForm = reactive({
    name: user.name,
    gender: 'Laki-laki',
    birthDate: '23/09/1997',
    idType: 'KTP',
    idNumber: '8098765421216789',
    city: 'Kota Batam',
    phone: '628217012451', // Dipindahkan dari personalForm agar bisa di-edit di 'Informasi Akun'
});

// Data untuk 'Informasi Personal' (Kontak/Verifikasi)
const personalForm = reactive({
    email: user.email,
    isEmailVerified: true,
    isPhoneVerified: true,
});

// Default sidebar menu aktif
const activeMenu = ref('account');

// Default inner tab aktif (hanya berlaku saat activeMenu === 'account')
// Nilai 'informasi_personal' diubah menjadi 'ubah_kata_sandi'
const activeTab = ref('informasi_akun');

// Menu Items untuk Sidebar Navigation (Updated for Admin)
const menuItems = computed(() => [
    { id: 'account', label: 'Akun', icon: IconUser, route: '#account' },
    { id: 'dashboard', label: 'Dashboard', icon: IconNews, route: '#dashboard' },
    { id: 'orders', label: 'Daftar Pesanan', icon: IconTicket, route: '/admin/orders' },
    { id: 'partners', label: 'List Partner', icon: IconTag, route: '/admin/partners' },
    { id: 'logout', label: 'Keluar', icon: IconLogout, route: '/logout' },
]);

// --- FUNGSI UTAMA ---

const submitProfile = () => {
    // Fungsi ini dipanggil saat tombol SIMPAN PERUBAHAN di-klik (Tab Informasi Akun aktif)
    console.log('Mengirim data Informasi Akun:', accountForm, personalForm);
    alert('Perubahan Informasi Akun berhasil disimpan (Simulasi)');
};

const handleLogout = () => {
    console.log('User logging out...');
    alert('Anda telah keluar (Simulasi)');
    // router.visit('/logout');
};

// Simple password change state (simulasi)
const passwordForm = reactive({ current: '', password: '', confirm: '' });
const changePassword = () => {
    // Fungsi ini dipanggil saat tombol SIMPAN KATA SANDI di-klik (Tab Ubah Kata Sandi aktif)
    if (!passwordForm.current || !passwordForm.password) {
        alert('Mohon isi semua field kata sandi (Simulasi)');
        return;
    }
    if (passwordForm.password !== passwordForm.confirm) {
        alert('Konfirmasi kata sandi tidak cocok');
        return;
    }
    console.log('Menyimpan kata sandi baru (simulasi)', passwordForm.password);
    alert('Kata sandi berhasil diubah (Simulasi)');
    passwordForm.current = '';
    passwordForm.password = '';
    passwordForm.confirm = '';
};

// ... (Data dummy lainnya untuk orders, partners, reports, dan chartData/chartOptions)

// Placeholder data for admin menus
const orders = ref([
    { id: 1, title: 'Pesanan - Pengisian di Station A', date: '2025-10-10', status: 'Selesai', user: 'User1' },
    { id: 2, title: 'Pesanan - Pengisian di Station B', date: '2025-10-12', status: 'Dalam Proses', user: 'User2' },
]);

const partners = ref([
    { id: 1, name: 'Partner A', location: 'Batam', status: 'Active' },
    { id: 2, name: 'Partner B', location: 'Jakarta', status: 'Inactive' },
]);

// Dummy data for operator reports
const operatorReports = ref([
  {
    id: 1,
    stationName: 'EV Station Batam Centre',
    week: 'Minggu 1 (Januari)',
    totalSessions: 150,
    revenue: 'Rp 2,500,000',
    status: 'Dikirim',
  },
  {
    id: 2,
    stationName: 'EV Station Nagoya',
    week: 'Minggu 1 (Januari)',
    totalSessions: 120,
    revenue: 'Rp 1,800,000',
    status: 'Dikirim',
  },
  {
    id: 3,
    stationName: 'EV Station Sekupang',
    week: 'Minggu 1 (Januari)',
    totalSessions: 80,
    revenue: 'Rp 1,200,000',
    status: 'Pending',
  },
  {
    id: 4,
    stationName: 'EV Station Tanjung Uncang',
    week: 'Minggu 1 (Januari)',
    totalSessions: 95,
    revenue: 'Rp 1,500,000',
    status: 'Dikirim',
  },
  {
    id: 5,
    stationName: 'EV Station Batu Aji',
    week: 'Minggu 1 (Januari)',
    totalSessions: 110,
    revenue: 'Rp 1,700,000',
    status: 'Dikirim',
  },
]);

// Data untuk line chart penghasilan mingguan operator
const lineChartData = reactive({
    labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
    datasets: [{
        label: 'Penghasilan Operator (Rp)',
        data: [2500000, 1800000, 1200000, 1500000], 
        borderColor: '#4CAF50',
        backgroundColor: 'rgba(76, 175, 80, 0.2)',
        tension: 0.1,
        fill: true
    }]
});

const chartOptions = reactive({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                font: {
                    size: 14
                }
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    const label = context.dataset.label || '';
                    const value = context.parsed.y;
                    return `${label}: Rp ${value.toLocaleString('id-ID')}`;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return 'Rp ' + value.toLocaleString('id-ID');
                }
            }
        }
    },
    animation: {
        animateScale: true,
        animateRotate: true
    }
});
</script>

<template>
<div class="min-h-screen bg-gray-50 font-inter flex flex-col text-gray-700">

    <Navbar />

    <main class="flex-1 max-w-7xl mx-auto w-full pt-24 p-6 flex flex-col md:flex-row md:space-x-8 space-y-6 md:space-y-0">

        <aside class="w-full md:w-64 bg-white shadow-lg rounded-xl p-5 flex flex-col h-fit md:sticky md:top-6">
            <div class="mb-6 leading-tight">
                <div class="text-lg font-bold text-gray-900">{{ user.name }}</div>
                <div class="text-sm text-gray-500">{{ user.email }}</div>
            </div>

            <nav class="flex flex-col w-full space-y-1">
                <template v-for="item in menuItems" :key="item.id">
                    <button
                        @click="item.id === 'logout' ? handleLogout() : activeMenu = item.id"
                        class="flex items-center w-full px-3 py-2 rounded-lg text-left transition-all duration-150"
                        :class="[
                            item.id === activeMenu
                                ? 'bg-[#DAE200] text-dark font-semibold'
                                : 'text-gray-700 hover:bg-gray-100',
                            // Menangani tombol keluar secara visual
                            item.id === 'logout' ? 'text-red-500 hover:bg-red-50' : ''
                        ]"
                    >
                        <component :is="item.icon" class="w-5 h-5 mr-3"
                            :class="[item.id === activeMenu ? 'text-purple-600' : 'text-gray-500',
                                    item.id === 'logout' ? 'text-red-500' : '']" />
                        <span class="truncate">{{ item.label }}</span>
                    </button>
                </template>
            </nav>
        </aside>

        <section class="flex-1 bg-white shadow-xl rounded-xl p-8">

            <div v-if="activeMenu === 'account'">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">{{ activeTab === 'informasi_akun' ? 'Detail Akun' : 'Ubah Kata Sandi' }}</h1>
                    <p class="text-gray-500 text-sm mt-1">
                        {{ activeTab === 'informasi_akun' ? 'Kelola informasi akun dan data personal Anda.' : 'Perbarui kata sandi Anda untuk menjaga keamanan akun.' }}
                    </p>
                </div>

                <div class="flex flex-col md:flex-row md:justify-between md:items-center pb-4 mb-6 border-b border-gray-200">
                    <div class="flex flex-wrap gap-4 items-center">
                        <button
                            @click="activeTab = 'informasi_akun'"
                            :class="['flex items-center justify-center w-full md:w-auto gap-2 py-2 px-4 font-semibold rounded-lg transition duration-150',
                                    activeTab === 'informasi_akun' ? getColorClass('accent') : 'text-gray-500 hover:bg-gray-100']">
                            Informasi Akun
                        </button>
                        <button
                            @click="activeTab = 'ubah_kata_sandi'"
                            :class="['flex items-center justify-center w-full md:w-auto gap-2 py-2 px-4 font-semibold rounded-lg transition duration-150',
                                    activeTab === 'ubah_kata_sandi' ? getColorClass('accent') : 'text-gray-500 hover:bg-gray-100']">
                            Ubah Kata Sandi
                        </button>
                    </div>

                    <button 
                        type="button" 
                        @click.prevent="activeTab === 'informasi_akun' ? submitProfile() : changePassword()"
                        :class="['mt-4 md:mt-0 w-full md:w-auto px-6 py-2.5 text-sm font-bold rounded-lg transition duration-150 ease-in-out shadow-md', getColorClass('accent')]">
                        {{ activeTab === 'informasi_akun' ? 'SIMPAN PERUBAHAN' : 'SIMPAN KATA SANDI' }}
                    </button>
                </div>

                <form @submit.prevent="activeTab === 'informasi_akun' ? submitProfile() : changePassword()" id="profile-form" class="space-y-6">

                    <div v-if="activeTab === 'informasi_akun'">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="col-span-1">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                    <IconUserSmall class="w-4 h-4 mr-1 text-green-600" /> Nama Lengkap
                                </label>
                                <input type="text" v-model="accountForm.name" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-1  focus:ring-lime-500 focus:border-lime-500 transition duration-150" />
                            </div>

                            <div class="col-span-1">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                    <IconCalendar class="w-4 h-4 mr-1 text-purple-600" /> Tanggal Lahir
                                </label>
                                <input type="text" v-model="accountForm.birthDate" placeholder="DD/MM/YYYY" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-1  focus:ring-lime-500 focus:border-lime-500  transition duration-150" />
                            </div>

                            <div class="col-span-1">
                                <label class="flex items-center text-sm font-bold text-gray-700 mb-2">
                                    <IconMail class="w-4 h-4 mr-1 text-green-600" /> Alamat Email
                                </label>
                                <input type="email" v-model="personalForm.email" disabled class="w-full border border-gray-300 rounded-lg p-3 bg-gray-50 focus:outline-none focus:ring-1  focus:ring-lime-500 focus:border-lime-500  transition duration-150" />
                            </div>

                            <div class="col-span-1">
                                <label class="flex items-center text-sm font-bold text-gray-700 mb-2">
                                    <IconPhone class="w-4 h-4 mr-1 text-green-600" /> Nomor Handphone
                                </label>
                                <input type="text" v-model="accountForm.phone" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-1  focus:ring-lime-500 focus:border-lime-500  transition duration-150" />
                            </div>
                        </div>

                        <div :class="['mt-8 p-6 rounded-lg shadow-inner', 'bg-yellow-50 border border-yellow-200 text-yellow-800']">
                            <div class="flex items-start gap-3">
                                <IconLockOpen class="w-6 h-6 text-yellow-700 flex-shrink-0 mt-1"/>
                                <div>
                                    <h4 class="font-bold text-yellow-800">Privasi & Keamanan Data</h4>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Data personal Anda dienkripsi dan hanya digunakan untuk verifikasi identitas serta keperluan transaksi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="activeTab === 'ubah_kata_sandi'">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                            <div class="col-span-1">
                                <label for="current_password" class="text-sm font-bold text-gray-700 mb-1 block">Kata Sandi Saat Ini</label>
                                <input type="password" id="current_password" v-model="passwordForm.current" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-1 focus:ring-lime-500 focus:border-lime-500 transition duration-150" />
                            </div>

                            <div class="col-span-1">
                                <label for="new_password" class="text-sm font-bold text-gray-700 mb-1 block">Kata Sandi Baru</label>
                                <input type="password" id="new_password" v-model="passwordForm.password" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-1 focus:ring-lime-500 focus:border-lime-500 transition duration-150" />
                            </div>

                            <div class="col-span-full"> 
                                <label for="new_password_confirmation" class="text-sm font-bold text-gray-700 mb-1 block">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" id="new_password_confirmation" v-model="passwordForm.confirm" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-1 focus:ring-lime-500 focus:border-lime-500 transition duration-150" />
                            </div>
                            
                        </div>

                        <p class="text-sm text-gray-500 mt-4">
                            Tips: Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol.
                        </p>

                        <div class="mt-6 bg-green-50 border border-green-200 rounded-xl p-5 flex items-start gap-4">
                            <IconLockOpen class="w-6 h-6 text-green-700 flex-shrink-0 mt-1"/>
                            <div>
                                <h3 class="font-bold text-green-800">Akun Anda Terlindungi</h3>
                                <p class="text-sm text-green-700 mt-1">
                                    Kedua metode verifikasi aktif. Kami merekomendasikan untuk mengubah kata sandi secara berkala
                                    dan mengaktifkan autentikasi dua faktor untuk keamanan maksimal.
                                </p>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            
            <div v-else>
                <div v-if="activeMenu === 'dashboard'">
                    <h2 class="text-xl font-bold mb-6">Chart</h2>
                    <div class="bg-white p-6 rounded-lg shadow-md border">
                        <h4 class="text-lg font-semibold mb-4 text-center">Penghasilan Operator Mingguan</h4>
                        <LineChart :chartData="lineChartData" :chartOptions="chartOptions" />
                    </div>
                </div>

                <div v-else-if="activeMenu === 'orders'">
                    <h2 class="text-xl font-bold mb-6">Daftar Pesanan</h2>
                    <div class="space-y-3">
                        <div v-for="o in orders" :key="o.id" class="p-4 border rounded-lg flex justify-between items-center">
                            <div>
                                <div class="font-semibold">{{ o.title }}</div>
                                <div class="text-xs text-gray-500">{{ o.date }} - {{ o.user }}</div>
                            </div>
                            <div class="text-sm font-semibold" :class="o.status === 'Selesai' ? 'text-green-600' : 'text-yellow-600'">{{ o.status }}</div>
                        </div>
                    </div>
                </div>

                <div v-else-if="activeMenu === 'partners'">
                    <h2 class="text-xl font-bold mb-4">List Partner</h2>
                    <div class="space-y-3">
                        <div v-for="p in partners" :key="p.id" class="p-4 border rounded-lg flex justify-between items-center">
                            <div>
                                <div class="font-semibold">{{ p.name }}</div>
                                <div class="text-sm text-gray-600">{{ p.location }}</div>
                            </div>
                            <div class="text-sm font-semibold" :class="p.status === 'Active' ? 'text-green-600' : 'text-red-600'">{{ p.status }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <Footer />
</div>
</template>

<style>
/* Memuat font Inter untuk estetika yang lebih baik */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
.font-inter {
    font-family: 'Inter', sans-serif;
}
input[disabled] {
    background-color: #f9fafb !important;
    color: #4b5563 !important;
    cursor: default;
}
</style>