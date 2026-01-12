<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import ContactForm from '@/Components/ContactForm.vue';

// --- Data Dummy (Updated with Real Image Placeholders) ---
const teamMembers = [
    {
        id: 1,
        name: 'Einstein',
        title: 'Chief Executive Officer',
        image: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=800&auto=format&fit=crop'
    },
    {
        id: 2,
        name: 'Akbar Hafiz',
        title: 'Chief Technology Officer',
        image: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=800&auto=format&fit=crop'
    },
    {
        id: 3,
        name: 'Nurul Aini',
        title: 'Chief Marketing Officer',
        image: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=800&auto=format&fit=crop'
    },
];

const services = [
    { title: 'Web Application', desc: 'Membangun web modern yang cepat.', iconClass: 'bg-red-50 text-red-500' },
    { title: 'SEO Optimization', desc: 'Tingkatkan peringkat pencarian.', iconClass: 'bg-green-50 text-green-500' },
    { title: 'Mobile Apps', desc: 'Aplikasi Android & iOS native.', iconClass: 'bg-purple-50 text-purple-500' },
    { title: 'AR/VR Experience', desc: 'Teknologi masa depan imersif.', iconClass: 'bg-pink-50 text-pink-500' },
];

// Images for Hero Section
const heroImages = {
    main: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80&w=1200', // Team working
    sub1: 'https://images.unsplash.com/photo-1531498860502-7c67cf02f657?auto=format&fit=crop&q=80&w=600', // Meeting
    sub2: 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80&w=400', // Shake hands / close up
};
</script>

<template>

    <Head title="About Us - Siapa Kami" />

    <div class="min-h-screen flex flex-col font-sans text-gray-600 bg-white">
        <Navbar />

        <main class="flex-grow">

            <!-- SECTION 1: HERO & STORY -->
            <!-- Menggunakan pola asimetris yang lebih organik dan 'friendly' -->
            <section class="relative pt-24 pb-16 lg:pt-32 lg:pb-24 overflow-hidden">
                <!-- Dekorasi Background Blob -->
                <div
                    class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-green-50 rounded-full blur-3xl opacity-50 -z-10">
                </div>
                <div
                    class="absolute bottom-0 left-0 -ml-20 -mb-20 w-72 h-72 bg-blue-50 rounded-full blur-3xl opacity-50 -z-10">
                </div>

                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

                        <!-- Text Content (Order 1 on Mobile for Context) -->
                        <div class="order-1 lg:order-1 text-center lg:text-left">
                            <div
                                class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-600 text-sm font-bold tracking-wide uppercase mb-6">
                                👋 Tentang Kami
                            </div>
                            <h1
                                class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 leading-tight mb-6">
                                Membangun Solusi Digital dengan <span class="text-green-500">Hati & Teknologi</span>
                            </h1>
                            <p class="text-lg sm:text-xl text-gray-600 leading-relaxed mb-8 text-balance">
                                Kode Technology hadir bukan sekadar membuat aplikasi, tapi membantu bisnis Anda tumbuh
                                melalui inovasi yang manusiawi dan mudah digunakan.
                            </p>

                            <!-- Stats Row -->
                            <div
                                class="flex flex-wrap justify-center lg:justify-start gap-8 sm:gap-12 border-t border-gray-100 pt-8">
                                <div>
                                    <span class="block text-3xl font-bold text-gray-900">50+</span>
                                    <span class="text-sm text-gray-500 font-medium">Klien Happy</span>
                                </div>
                                <div>
                                    <span class="block text-3xl font-bold text-gray-900">120+</span>
                                    <span class="text-sm text-gray-500 font-medium">Proyek Selesai</span>
                                </div>
                                <div>
                                    <span class="block text-3xl font-bold text-gray-900">5th</span>
                                    <span class="text-sm text-gray-500 font-medium">Pengalaman</span>
                                </div>
                            </div>
                        </div>

                        <!-- Image Collage (Order 2) -->
                        <!-- Dibuat rounded-3xl agar lebih ramah/friendly -->
                        <div class="order-2 lg:order-2 relative">
                            <div class="grid grid-cols-2 gap-4">
                                <img :src="heroImages.main" alt="Tim Kolaborasi"
                                    class="col-span-2 w-full h-64 sm:h-80 object-cover rounded-[2.5rem] shadow-xl transform hover:scale-[1.02] transition duration-500">
                                <img :src="heroImages.sub1" alt="Rapat Santai"
                                    class="w-full h-40 sm:h-48 object-cover rounded-[2rem] shadow-lg transform hover:-translate-y-1 transition duration-300 delay-100">
                                <div class="relative overflow-hidden rounded-[2rem] shadow-lg h-40 sm:h-48">
                                    <img :src="heroImages.sub2" alt="Diskusi"
                                        class="w-full h-full object-cover transform hover:scale-110 transition duration-700">
                                    <div class="absolute inset-0 bg-green-500/10 mix-blend-multiply"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- SECTION 2: MISSION & VALUES -->
            <section class="py-16 lg:py-24 bg-gray-50 rounded-t-[3rem] sm:rounded-t-[4rem]">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl mx-auto text-center mb-16">
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6">Misi Kami</h2>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            <span class="font-bold text-green-600">KODE TECHNOLOGY</span> adalah tim yang berdedikasi
                            penuh. Kami percaya teknologi tidak harus rumit. Misi kami adalah menyederhanakan proses
                            bisnis Anda melalui aplikasi web dan mobile yang intuitif, cepat, dan menyenangkan untuk
                            digunakan.
                        </p>
                    </div>

                    <!-- Services Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                        <div v-for="(service, index) in services" :key="index"
                            class="bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl transition-shadow duration-300 border border-gray-100 group">
                            <div
                                :class="`w-14 h-14 rounded-2xl flex items-center justify-center mb-6 ${service.iconClass} transition-transform group-hover:scale-110 duration-300`">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ service.title }}</h3>
                            <p class="text-gray-500 text-sm leading-relaxed">{{ service.desc }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: TEAM -->
            <section class="py-16 lg:py-24 bg-white">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Bertemu Tim Hebat Kami</h2>
                        <p class="text-gray-500 max-w-xl mx-auto">Orang-orang berdedikasi di balik setiap baris kode dan
                            desain pixel-perfect.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
                        <div v-for="member in teamMembers" :key="member.id" class="group text-center">
                            <div class="relative inline-block mb-5">
                                <!-- Lingkaran dekoratif di belakang foto -->
                                <div
                                    class="absolute inset-0 bg-green-100 rounded-full transform translate-x-2 translate-y-2 group-hover:translate-x-0 group-hover:translate-y-0 transition duration-300">
                                </div>
                                <img :src="member.image" :alt="member.name"
                                    class="relative w-48 h-48 sm:w-56 sm:h-56 object-cover rounded-full border-4 border-white shadow-lg group-hover:shadow-xl transition duration-300 mx-auto">
                            </div>

                            <h3
                                class="text-xl font-bold text-gray-900 group-hover:text-green-600 transition duration-300">
                                {{ member.name }}</h3>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mt-1">{{ member.title }}
                            </p>

                            <!-- Social Icons (Optional Decoration) -->
                            <div
                                class="flex justify-center space-x-3 mt-4 opacity-0 group-hover:opacity-100 transition duration-300 translate-y-2 group-hover:translate-y-0">
                                <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Box -->
                    <div
                        class="mt-20 bg-green-500 rounded-[2.5rem] p-8 sm:p-12 lg:p-16 text-center text-white relative overflow-hidden shadow-2xl">
                        <!-- Pattern Background -->
                        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none"
                            style="background-image: radial-gradient(circle, #fff 2px, transparent 2.5px); background-size: 20px 20px;">
                        </div>

                        <div class="relative z-10 max-w-2xl mx-auto">
                            <h2 class="text-2xl sm:text-3xl font-bold mb-6">Punya Ide Brilian? Mari Wujudkan.</h2>
                            <p class="text-green-50 text-lg mb-8">Diskusikan kebutuhan proyek Anda bersama kami.
                                Konsultasi gratis!</p>
                            <Link href="/contact"
                                class="inline-block bg-white text-green-600 font-bold text-lg px-8 py-4 rounded-full shadow-lg hover:bg-gray-100 hover:scale-105 transition duration-300">
                                Hubungi Kami Sekarang
                            </Link>
                        </div>
                    </div>

                </div>
            </section>

        </main>

        <Footer />
    </div>
</template>

<style scoped>
/* Opsional: Jika ingin scroll bar lebih halus */
html {
    scroll-behavior: smooth;
}
</style>