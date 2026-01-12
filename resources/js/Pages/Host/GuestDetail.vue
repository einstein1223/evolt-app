<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    guest: Object,
    history: Array,
    stats: Object
});

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

// --- FUNGSI AMAN UNTUK INISIAL NAMA ---
// Jika nama kosong, kembalikan '?' agar tidak error screen putih
const getInitial = (name) => {
    return name ? name.charAt(0).toUpperCase() : '?';
};
</script>

<template>
    <Head :title="`Detail - ${guest.name || 'Tamu'}`" />
    
    <div class="min-h-screen flex flex-col bg-slate-50 font-poppins text-slate-800">
        <header class="bg-white/80 backdrop-blur-md px-6 py-4 sticky top-0 z-50 border-b border-slate-200">
            <div class="max-w-4xl mx-auto flex items-center gap-4">
                <Link :href="route('host.dashboard')" class="p-2 rounded-full hover:bg-slate-100 transition text-slate-500 hover:text-slate-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </Link>
                <h1 class="text-lg font-bold">Detail Pelanggan</h1>
            </div>
        </header>

        <main class="flex-grow py-8 px-4 sm:px-6">
            <div class="max-w-4xl mx-auto space-y-6">
                
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-200 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-lime-100/50 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                    
                    <div class="relative z-10 flex flex-col sm:flex-row items-start gap-6">
                        <div class="w-20 h-20 bg-slate-900 text-[#CCFF00] rounded-2xl flex items-center justify-center text-3xl font-black shadow-lg shadow-slate-200 flex-shrink-0">
                            {{ getInitial(guest.name) }}
                        </div>

                        <div class="flex-grow">
                            <h2 class="text-2xl font-bold text-slate-900">{{ guest.name || 'Nama Tidak Tersedia' }}</h2>
                            <div class="flex flex-wrap gap-4 mt-2 text-sm text-slate-500">
                                <span class="flex items-center gap-1"><i class="fas fa-envelope"></i> {{ guest.email }}</span>
                                <span class="flex items-center gap-1"><i class="fas fa-phone"></i> {{ guest.phone }}</span>
                            </div>
                            
                            <div class="mt-4 inline-flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                                <i class="fas fa-car text-slate-400"></i>
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kendaraan</p>
                                    <p class="text-sm font-bold text-slate-900">{{ guest.car }} <span class="text-slate-300">|</span> {{ guest.plat }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-6 sm:flex-col sm:gap-2 sm:text-right w-full sm:w-auto mt-4 sm:mt-0 pt-4 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Total Belanja</p>
                                <p class="text-xl font-black text-lime-600">{{ formatRupiah(stats.total_spent) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Total Kunjungan</p>
                                <p class="text-xl font-black text-slate-900">{{ stats.total_visits }}x</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-900">Riwayat Transaksi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Durasi</th>
                                    <th class="px-6 py-4">Biaya</th>
                                    <th class="px-6 py-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in history" :key="item.id" class="hover:bg-lime-50/30 transition">
                                    <td class="px-6 py-4 font-medium">{{ item.date }}</td>
                                    <td class="px-6 py-4">{{ item.duration }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-900">{{ formatRupiah(item.amount) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span v-if="item.status === 'Selesai'" class="text-green-600 bg-green-100 px-2 py-1 rounded-full text-xs font-bold">Sukses</span>
                                        <span v-else class="text-orange-600 bg-orange-100 px-2 py-1 rounded-full text-xs font-bold">{{ item.status }}</span>
                                    </td>
                                </tr>
                                <tr v-if="history.length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                        Belum ada riwayat transaksi.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
        <Footer />
    </div>
</template>