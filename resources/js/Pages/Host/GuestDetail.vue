<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { defineProps, computed } from 'vue';

// Menerima data dari Controller
const props = defineProps({
    guest: {
        type: Object,
        default: () => ({ name: 'Tamu', email: '-' }) // Default agar tidak error jika null
    },
    history: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({ total_spent: 0, total_visits: 0, last_visit: '-' })
    }
});

// COMPUTED: Ambil Nama yang Aman (Cek name, kalau kosong cek username, kalau kosong pakai 'Tamu')
const guestNameDisplay = computed(() => {
    if (props.guest && props.guest.name) return props.guest.name;
    if (props.guest && props.guest.username) return props.guest.username;
    return 'Tamu';
});

// COMPUTED: Ambil Inisial untuk Avatar
const guestInitial = computed(() => {
    return guestNameDisplay.value.charAt(0).toUpperCase();
});

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
</script>

<template>
    <Head :title="`Detail Tamu - ${guestNameDisplay}`" />

    <div class="min-h-screen bg-slate-50 font-poppins text-slate-800 p-6">
        <div class="max-w-4xl mx-auto space-y-6">
            
            <Link :href="route('host.dashboard')" class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-slate-900 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Dashboard
            </Link>

            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-200 flex flex-col md:flex-row items-center md:items-start gap-6">
                <div class="w-20 h-20 bg-gradient-to-tr from-lime-400 to-emerald-500 rounded-full flex items-center justify-center text-white text-3xl font-black shadow-lg flex-shrink-0">
                    {{ guestInitial }}
                </div>
                
                <div class="text-center md:text-left flex-grow">
                    <h1 class="text-2xl font-black text-slate-900">{{ guestNameDisplay }}</h1>
                    <p class="text-slate-500 text-sm">{{ guest.email }}</p>
                    
                    <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-3">
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-xs font-bold text-slate-600 border border-slate-200">
                            Member
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pengeluaran</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ formatRupiah(stats?.total_spent) }}</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Kunjungan</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ stats?.total_visits || 0 }} Kali</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Terakhir Berkunjung</p>
                    <p class="text-lg font-bold text-slate-900 mt-1">{{ stats?.last_visit || '-' }}</p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-bold text-lg text-slate-900">Riwayat Transaksi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-900 font-bold uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Kode</th>
                                <th class="px-6 py-4">Durasi</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in history" :key="item.id" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">{{ item.date }}</td>
                                <td class="px-6 py-4 font-mono text-xs">{{ item.booking_code ?? '-' }}</td>
                                <td class="px-6 py-4">{{ item.duration }}</td>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ formatRupiah(item.amount) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold"
                                        :class="{
                                            'bg-green-100 text-green-700': item.status === 'Selesai',
                                            'bg-yellow-100 text-yellow-700': item.status === 'Menunggu',
                                            'bg-red-100 text-red-700': item.status === 'Batal'
                                        }">
                                        {{ item.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="history.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                    Belum ada riwayat transaksi.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap");
.font-poppins { font-family: 'Plus Jakarta Sans', sans-serif; }
</style>