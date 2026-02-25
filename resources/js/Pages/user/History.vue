<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    bookings: Array
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <Head title="Riwayat Charging" />
    
    <div class="min-h-screen flex flex-col bg-gray-50 font-sans text-slate-800">
        <Navbar />

        <main class="flex-grow pt-28 pb-10 px-4 sm:px-6">
            <div class="max-w-3xl mx-auto">
                
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-black text-slate-900">Riwayat Aktivitas</h1>
                    <Link href="/dashboard" class="text-sm font-bold text-gray-500 hover:text-slate-900">Kembali</Link>
                </div>

                <div v-if="bookings.length > 0" class="space-y-4">
                    <div v-for="item in bookings" :key="item.id" class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow flex flex-col sm:flex-row justify-between gap-4">
                        
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
                                :class="item.status === 'Selesai' ? 'bg-lime-100 text-lime-700' : 'bg-slate-100 text-slate-600'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-900">{{ item.station_name }}</h3>
                                <p class="text-xs text-slate-500">{{ formatDate(item.booking_date) }}</p>
                                <div class="mt-2 flex gap-2">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold uppercase rounded-md tracking-wide">
                                        {{ item.port_type }}
                                    </span>
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md tracking-wide"
                                        :class="{
                                            'bg-green-100 text-green-700': item.status === 'Selesai',
                                            'bg-yellow-100 text-yellow-700': item.status === 'Pending',
                                            'bg-red-100 text-red-700': item.status === 'Batal'
                                        }">
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row sm:flex-col justify-between sm:items-end text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-gray-50 mt-2 sm:mt-0">
                            <div>
                                <span class="block text-xs text-slate-400">Total Biaya</span>
                                <span class="font-black text-lg text-slate-900">{{ formatRupiah(item.total_price) }}</span>
                            </div>
                            <div class="mt-1">
                                <span class="text-xs font-medium text-slate-500">{{ item.duration }} Menit Charging</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-else class="text-center py-20 bg-white rounded-[2.5rem] border border-dashed border-gray-300">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900">Belum ada riwayat</h3>
                    <p class="text-slate-500 mb-6">Mulai pengisian daya pertama Anda sekarang.</p>
                    <Link href="/dashboard" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg hover:bg-slate-800 transition">Cari Stasiun</Link>
                </div>

            </div>
        </main>
    </div>
</template>