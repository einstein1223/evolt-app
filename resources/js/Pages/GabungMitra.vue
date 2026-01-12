<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

const waLink = (message) => `https://wa.me/6281234567890?text=${encodeURIComponent(message)}`;
</script>

<template>
    <Head title="Investasi E-VOLT" />

    <div class="min-h-screen flex flex-col bg-white font-sans text-gray-800">
        <Navbar />

        <!-- Tambahkan padding-top (pt-24) agar konten tidak tertutup navbar -->
        <main class="flex-grow pt-24">
            
            <!-- 1. WHY INVEST (Langsung Data Pasar) -->
            <section class="py-12 md:py-20">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="text-center mb-12">
                        <h1 class="text-3xl md:text-5xl font-extrabold text-[#1f2937] leading-tight mb-4">
                            Peluang Investasi <span class="text-[#00C853]">Energi Masa Depan</span>
                        </h1>
                        <p class="text-gray-500 max-w-2xl mx-auto text-lg">
                            Bergabunglah dengan ekosistem kendaraan listrik yang sedang berkembang pesat di Indonesia.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center divide-y md:divide-y-0 md:divide-x divide-gray-100 bg-gray-50 rounded-3xl p-8 border border-gray-100">
                        <div class="p-4">
                            <div class="text-5xl font-black text-[#00C853] mb-2">+45%</div>
                            <p class="text-lg font-bold text-gray-900">Pertumbuhan EV</p>
                            <p class="text-sm text-gray-500 mt-2">Lonjakan adopsi kendaraan listrik di Indonesia tiap tahunnya.</p>
                        </div>
                        <div class="p-4">
                            <div class="text-5xl font-black text-[#00C853] mb-2">24/7</div>
                            <p class="text-lg font-bold text-gray-900">Passive Income</p>
                            <p class="text-sm text-gray-500 mt-2">Mesin bekerja otomatis, uang masuk ke dashboard Anda kapan saja.</p>
                        </div>
                        <div class="p-4">
                            <div class="text-5xl font-black text-[#00C853] mb-2">0%</div>
                            <p class="text-lg font-bold text-gray-900">Biaya Franchise</p>
                            <p class="text-sm text-gray-500 mt-2">Model kemitraan bagi hasil yang adil tanpa biaya royalti di muka.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. PILIHAN JALUR KEMITRAAN -->
            <section id="paket" class="py-20 bg-white">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Pilih Model Investasi</h2>
                        <p class="text-gray-500 mt-4 text-lg">Sesuaikan dengan jenis properti dan target pasar Anda.</p>
                    </div>

                    <!-- KUBU 1: MITRA TETANGGA -->
                    <div class="mb-20">
                        <div class="flex items-center gap-4 mb-8 border-b border-gray-200 pb-4">
                            <div class="p-4 bg-gray-100 rounded-2xl text-gray-600 shadow-sm">
                                <i class="fas fa-home text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Mitra Tetangga (Residential)</h3>
                                <p class="text-gray-500 text-sm">Monetisasi listrik rumah atau ruko kecil untuk komunitas sekitar.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Paket A: Starter Home -->
                            <div class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl transition border border-gray-100 flex flex-col group relative overflow-hidden">
                                <div class="mb-4 flex justify-between items-start">
                                    <div>
                                        <h4 class="text-xl font-bold text-gray-900">Starter Home</h4>
                                        <p class="text-sm text-gray-500">Entry level untuk garasi pribadi.</p>
                                    </div>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold uppercase">Hemat</span>
                                </div>

                                <!-- Detail Alat -->
                                <div class="bg-gray-50 p-5 rounded-2xl mb-6">
                                    <h5 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Spesifikasi Alat</h5>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-600">Unit</span> <span class="font-bold text-gray-900">Smart Socket / Portable</span></div>
                                        <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-600">Daya</span> <span class="font-bold text-gray-900">3.5 kW (Slow)</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Target</span> <span class="font-bold text-gray-900">Motor Listrik / City Car</span></div>
                                    </div>
                                </div>

                                <ul class="space-y-3 mb-8 text-sm text-gray-600 flex-grow">
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Dashboard Host Mobile</li>
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Manajemen Jadwal Buka/Tutup</li>
                                </ul>

                                <a :href="waLink('Saya tertarik paket Starter Home (Mitra Tetangga)')" class="w-full py-3 rounded-xl border border-gray-300 text-gray-700 font-bold text-center hover:bg-gray-800 hover:text-white transition">
                                    Tanya Paket Ini
                                </a>
                            </div>

                            <!-- Paket B: Pro Home -->
                            <div class="bg-white rounded-3xl p-8 shadow-md hover:shadow-2xl transition border border-gray-100 flex flex-col group relative overflow-hidden">
                                <div class="absolute top-0 right-0 bg-green-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">REKOMENDASI</div>
                                <div class="mb-4">
                                    <h4 class="text-xl font-bold text-gray-900">Pro Home</h4>
                                    <p class="text-sm text-gray-500">Standar pengisian mobil listrik rumahan.</p>
                                </div>

                                <!-- Detail Alat -->
                                <div class="bg-gray-50 p-5 rounded-2xl mb-6">
                                    <h5 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Spesifikasi Alat</h5>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-600">Unit</span> <span class="font-bold text-gray-900">AC Wallbox (Fixed)</span></div>
                                        <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-600">Daya</span> <span class="font-bold text-gray-900">7 kW (Standard)</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Target</span> <span class="font-bold text-gray-900">Semua Mobil EV</span></div>
                                    </div>
                                </div>

                                <ul class="space-y-3 mb-8 text-sm text-gray-600 flex-grow">
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> RFID Access Control</li>
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Weatherproof (IP55)</li>
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Support Full Dashboard</li>
                                </ul>

                                <a :href="waLink('Saya tertarik paket Pro Home (Mitra Tetangga)')" class="w-full py-3 rounded-xl bg-gray-900 text-white font-bold text-center hover:bg-gray-800 transition shadow-lg">
                                    Ambil Paket Ini
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- KUBU 2: MITRA OPERATOR -->
                    <div>
                        <div class="flex items-center gap-4 mb-8 border-b border-gray-200 pb-4">
                            <div class="p-4 bg-gray-100 rounded-2xl text-gray-600 shadow-sm">
                                <i class="fas fa-building text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Mitra Operator (Commercial)</h3>
                                <p class="text-gray-500 text-sm">Investasi skala bisnis untuk lokasi strategis & publik.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Paket C: Commercial Hub -->
                            <div class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl transition border border-gray-100 flex flex-col group relative overflow-hidden">
                                <div class="mb-4">
                                    <h4 class="text-xl font-bold text-gray-900">Commercial Hub</h4>
                                    <p class="text-sm text-gray-500 mt-1">Untuk Cafe, Hotel, Kantor, atau Apartemen.</p>
                                </div>

                                <!-- Detail Alat -->
                                <div class="bg-gray-50 p-5 rounded-2xl mb-6">
                                    <h5 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Spesifikasi Alat</h5>
                                    <div class="space-y-2 text-sm text-gray-600">
                                        <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-600">Unit</span> <span class="font-bold text-gray-900">AC Floor Stand</span></div>
                                        <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-600">Daya</span> <span class="font-bold text-gray-900">22 kW (Dual Gun)</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Fitur</span> <span class="font-bold text-gray-900">OCPP 1.6J, 4G/WiFi</span></div>
                                    </div>
                                </div>

                                <ul class="space-y-3 mb-8 text-sm text-gray-600 flex-grow">
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Dashboard Operator Pro</li>
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Manajemen Tarif Fleksibel</li>
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Bisa Melayani 2 Mobil Sekaligus</li>
                                </ul>

                                <a :href="waLink('Saya tertarik paket Commercial Hub (Operator)')" class="w-full py-3 rounded-xl bg-gray-900 text-white font-bold text-center hover:bg-gray-800 transition shadow-lg">
                                    Hitung ROI Bisnis
                                </a>
                            </div>

                            <!-- Paket D: Power Station -->
                            <div class="bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition border border-gray-100 flex flex-col group relative overflow-hidden">
                                <div class="mb-4">
                                    <h4 class="text-xl font-bold text-gray-900">Power Station</h4>
                                    <p class="text-sm text-gray-500 mt-1">Infrastruktur pengisian cepat (Fast Charging).</p>
                                </div>

                                <!-- Detail Alat -->
                                <div class="bg-gray-50 p-5 rounded-2xl mb-6">
                                    <h5 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Spesifikasi Alat</h5>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-600">Unit</span> <span class="font-bold text-gray-900">DC Fast Charger</span></div>
                                        <div class="flex justify-between border-b border-gray-200 pb-2"><span class="text-gray-600">Daya</span> <span class="font-bold text-gray-900">50 - 150 kW</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Konektor</span> <span class="font-bold text-gray-900">CCS2 / CHAdeMO</span></div>
                                    </div>
                                </div>

                                <ul class="space-y-3 mb-6 text-sm text-gray-600 flex-grow">
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Revenue Share Premium</li>
                                    <li class="flex gap-3"><i class="fas fa-check text-green-500"></i> Full Maintenance & Support</li>
                                </ul>

                                <a :href="waLink('Info Paket Fast Charging')" class="mt-auto block w-full py-3.5 rounded-xl border-2 border-gray-900 text-gray-900 font-bold text-center hover:border-gray-600 hover:bg-gray-800 hover:text-white transition">
                                    Konsultasi Project
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- 4. SIMPLE PROCESS -->
            <section class="py-20 bg-gray-50 border-t border-gray-100">
                <div class="max-w-5xl mx-auto px-6 text-center">
                    <h2 class="text-3xl font-bold text-gray-900 mb-12">Langkah Mudah Menjadi Mitra</h2>
                    <div class="flex flex-col md:flex-row justify-between items-center gap-8 relative">
                        <!-- Line -->
                        <div class="hidden md:block absolute top-8 left-20 right-20 h-0.5 bg-gray-200 -z-10"></div>

                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm w-full md:w-1/3">
                            <div class="w-12 h-12 bg-lime-100 text-lime-700 rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">1</div>
                            <h4 class="font-bold text-gray-900">Konsultasi</h4>
                            <p class="text-sm text-gray-500 mt-2">Diskusikan lokasi dan kebutuhan daya listrik Anda dengan tim kami.</p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm w-full md:w-1/3">
                            <div class="w-12 h-12 bg-lime-100 text-lime-700 rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">2</div>
                            <h4 class="font-bold text-gray-900">Instalasi</h4>
                            <p class="text-sm text-gray-500 mt-2">Tim teknis bersertifikat melakukan pemasangan dan integrasi sistem.</p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm w-full md:w-1/3">
                            <div class="w-12 h-12 bg-lime-100 text-lime-700 rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">3</div>
                            <h4 class="font-bold text-gray-900">Mulai Cuan</h4>
                            <p class="text-sm text-gray-500 mt-2">Stasiun aktif di aplikasi E-VOLT, pantau pendapatan secara real-time.</p>
                        </div>
                    </div>
                </div>
            </section>

        </main>

        <Footer />
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap");
.font-sans { font-family: 'Poppins', sans-serif; }
</style>