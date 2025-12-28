<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
</script>

<template>

    <Head title="Gabung Mitra" />

    <div class="min-h-screen flex flex-col bg-white font-sans">
        <Navbar />

        <main class="flex-grow pt-24 pb-12 lg:pt-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <!-- <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900">Gabung Mitra</h1>
                    <p class="text-lg text-gray-600 mt-2">Pilih solusi yang tepat untuk Anda</p>
                </div> -->

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- LEFT COLUMN: Brand Solution -->
                    <div>
                        <div class="mb-6 border-b border-lime-200 pb-2">
                            <h2 class="text-2xl font-bold flex items-center gap-2">
                                <span class="text-yellow-500 text-3xl">⚡</span>
                                <span class="text-gray-900">Brand Solution</span>
                                <span class="text-sm font-normal text-lime-600 ml-2 bg-lime-50 px-2 py-1 rounded">|
                                    Beli & Operasikan Sendiri</span>
                            </h2>
                        </div>

                        <div class="space-y-6">
                            <!-- Card 1: Hybrid -->
                            <div class="bg-white border rounded-2xl shadow-sm p-6 relative overflow-hidden">
                                <div class="border-l-4 border-lime-500 pl-4 mb-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900">Hybrid (AC & DC)</h3>
                                            <span
                                                class="inline-block bg-lime-100 text-lime-700 text-xs px-2 py-0.5 rounded mt-1 font-semibold">AC
                                                + DC Power</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-lime-600 font-bold text-2xl">Rp</span>
                                            <span class="text-gray-400 text-sm block">/ set</span>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-gray-600 text-sm mb-6">
                                    Paket kombinasi charger AC dan DC berdaya tinggi untuk kecepatan dan layanan
                                    maksimal di lokasi premium.
                                </p>

                                <ul class="space-y-2 mb-6 text-sm text-gray-600">
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-lime-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Unit DC Fast Charger (50kW - 150kW)
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-lime-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Unit AC Charger (7.4kW - 22kW)
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-lime-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Daya Input: 3 Phase Industrial Grade
                                    </li>
                                </ul>

                                <button
                                    class="w-full bg-[#84cc16] hover:bg-[#65a30d] text-white font-bold py-3 px-4 rounded-xl transition duration-200 flex justify-center items-center gap-2">
                                    Lihat Detail & Foto Lengkap <span>→</span>
                                </button>
                            </div>

                            <!-- Card 2: AC Standard -->
                            <div class="bg-white border rounded-2xl shadow-sm p-6 relative overflow-hidden">
                                <div class="border-l-4 border-blue-500 pl-4 mb-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900">AC Standard</h3>
                                            <span
                                                class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded mt-1 font-semibold">AC
                                                Only Solution</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-lime-600 font-bold text-2xl">Rp</span>
                                            <span class="text-gray-400 text-sm block">/ set</span>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-gray-600 text-sm mb-6">
                                    Solusi efisien dan hemat biaya untuk lokasi dengan kebutuhan pengisian daya reguler
                                    dan instalasi sederhana.
                                </p>

                                <ul class="space-y-2 mb-6 text-sm text-gray-600">
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-lime-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Unit AC Charger (7.4kW atau 11kW)
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-lime-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Daya Input: 1 Phase atau 3 Phase
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-lime-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Konektor: Type 2
                                    </li>
                                </ul>

                                <button
                                    class="w-full bg-[#3b82f6] hover:bg-[#2563eb] text-white font-bold py-3 px-4 rounded-xl transition duration-200 flex justify-center items-center gap-2">
                                    Lihat Detail & Foto Lengkap <span>→</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Mitra Operator -->
                    <div>
                        <div class="mb-6 border-b border-red-200 pb-2">
                            <h2 class="text-2xl font-bold flex items-center gap-2">
                                <span class="text-orange-500 text-3xl">🤝</span>
                                <span class="text-gray-900">Mitra Operator</span>
                                <span class="text-sm font-normal text-red-500 ml-2 bg-red-50 px-2 py-1 rounded">|
                                    Kemitraan Revenue Share</span>
                            </h2>
                        </div>

                        <div class="space-y-4">
                            <!-- Card 1: Pengelola Lahan -->
                            <div
                                class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="p-2 bg-red-50 rounded-full text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">Pengelola Lahan Strategis</h3>
                                        <p class="text-xs text-gray-500 mt-1">Ideal untuk pemilik properti komersial
                                            seperti mal, hotel, dan area parkir.</p>
                                    </div>
                                </div>
                                <div class="mt-2 bg-red-50/50 p-3 rounded-lg">
                                    <p class="text-xs font-bold text-red-500 mb-1">Manfaat Utama:</p>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span> Bagi
                                            hasil pendapatan dari setiap sesi charging</li>
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span>
                                            Investasi Unit 0% dari sisi mitra</li>
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span>
                                            Peningkatan nilai properti dan traffic pengunjung</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Card 2: Operator Teknis -->
                            <div
                                class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="p-2 bg-red-50 rounded-full text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">Operator Teknis Lokal</h3>
                                        <p class="text-xs text-gray-500 mt-1">Kesempatan bagi perusahaan instalasi
                                            listrik dan teknis untuk menjalankan O&M di area tertentu.</p>
                                    </div>
                                </div>
                                <div class="mt-2 bg-red-50/50 p-3 rounded-lg">
                                    <p class="text-xs font-bold text-red-500 mb-1">Manfaat Utama:</p>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span> Proyek
                                            instalasi dan pemeliharaan berkesinambungan</li>
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span>
                                            Pelatihan bersertifikat Brand EV</li>
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span> Akses ke
                                            sistem O&M canggih untuk efisiensi operasional</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Card 3: Investor Charging -->
                            <div
                                class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="p-2 bg-red-50 rounded-full text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-lg">Investor Charging</h3>
                                        <p class="text-xs text-gray-500 mt-1">Program investasi yang aman dan transparan
                                            di sektor EV charging dengan potensi ROI tinggi.</p>
                                    </div>
                                </div>
                                <div class="mt-2 bg-red-50/50 p-3 rounded-lg">
                                    <p class="text-xs font-bold text-red-500 mb-1">Manfaat Utama:</p>
                                    <ul class="text-xs text-gray-600 space-y-1">
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span> Model
                                            bisnis bagi hasil (Revenue Share) yang kompetitif</li>
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span> Laporan
                                            keuangan terintegrasi dan transparan</li>
                                        <li class="flex items-center gap-1"><span class="text-red-400">⊙</span> Proyeksi
                                            ROI yang jelas didukung data utilisasi riil</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button
                                class="w-full bg-[#dc2626] hover:bg-[#b91c1c] text-white font-bold py-4 px-6 rounded-xl shadow-lg transition duration-200 transform hover:-translate-y-1 flex justify-center items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                Daftar Jadi Mitra Operator Sekarang!
                            </button>
                            <p class="text-center text-xs text-gray-400 mt-2">Proses cepat, siap beroperasi dalam 4-6
                                minggu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
