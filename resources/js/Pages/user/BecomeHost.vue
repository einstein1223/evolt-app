<script setup>
import { useForm } from '@inertiajs/vue3';
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';

// Form Data
const form = useForm({
    name: '',
    address: '',
    city: 'Batam Center', // Default
    charger_type: 'Wall Socket (Colokan Biasa)',
    power: '',
    price: '',
    photo: null,
    agreeTerms: false,
    // Koordinat dummy (nanti bisa dikembangkan pakai map picker)
    lat: 1.1301,
    lng: 104.0529
});

// Handle File Upload Preview
const handleFileUpload = (e) => {
    form.photo = e.target.files[0];
};

const submit = () => {
    if (!form.agreeTerms) {
        alert("Anda harus menyetujui Syarat & Ketentuan Mitra.");
        return;
    }

    // Mengirim data ke Backend (HostController@store)
    form.post(route('host.store'), {
        onSuccess: () => {
            // Backend akan otomatis redirect ke dashboard host
            console.log("Berhasil mendaftar jadi host");
        },
        onError: (errors) => {
            console.error(errors);
            alert("Gagal mendaftar. Mohon cek kembali inputan Anda.");
        }
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 font-poppins">
        <Navbar />

        <main class="flex-grow pt-24 pb-10 px-4">
            <div class="max-w-3xl mx-auto">

                <!-- Header Section -->
                <div class="text-center mb-10">
                    <span class="bg-orange-100 text-orange-600 px-4 py-1.5 rounded-full text-sm font-bold tracking-wide uppercase">
                        Program Mitra
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-4">
                        Jadikan Rumahmu <br><span class="text-[#00C853]">Stasiun Pengisian</span>
                    </h1>
                    <p class="text-gray-500 mt-3 text-lg">
                        Bantu komunitas EV dan dapatkan penghasilan tambahan dari charger nganggur di rumah Anda.
                    </p>
                </div>

                <!-- Form Container -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-[#CCFF00] h-2 w-full"></div> <!-- Aksen Atas -->

                    <form @submit.prevent="submit" class="p-8 md:p-10 space-y-8">

                        <!-- 1. Info Lokasi -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm">1</span>
                                Informasi Lokasi
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tempat</label>
                                    <input v-model="form.name" type="text"
                                        placeholder="Cth: Garasi Pak Budi / Cluster Mawar No. 5"
                                        class="w-full border-gray-300 rounded-xl p-3 focus:ring-[#00C853] focus:border-[#00C853]"
                                        required>
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kota / Area</label>
                                    <select v-model="form.city"
                                        class="w-full border-gray-300 rounded-xl p-3 focus:ring-[#00C853] focus:border-[#00C853]">
                                        <option>Batam Center</option>
                                        <option>Nagoya</option>
                                        <option>Sekupang</option>
                                        <option>Batu Aji</option>
                                        <option>Bengkong</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                                    <input v-model="form.address" type="text"
                                        placeholder="Nama Jalan, Blok, Nomor Rumah"
                                        class="w-full border-gray-300 rounded-xl p-3 focus:ring-[#00C853] focus:border-[#00C853]"
                                        required>
                                    <div v-if="form.errors.address" class="text-red-500 text-xs mt-1">{{ form.errors.address }}</div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <!-- 2. Spesifikasi Charger -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm">2</span>
                                Spesifikasi Alat
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Konektor</label>
                                    <select v-model="form.charger_type"
                                        class="w-full border-gray-300 rounded-xl p-3 focus:ring-[#00C853] focus:border-[#00C853]">
                                        <option value="Wall Socket">Colokan Rumahan (Schuko)</option>
                                        <option value="Type 2">AC Wallbox (Type 2)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Daya Listrik (Watt/kW)</label>
                                    <div class="relative">
                                        <input v-model="form.power" type="number" placeholder="Cth: 2200"
                                            class="w-full border-gray-300 rounded-xl p-3 focus:ring-[#00C853] focus:border-[#00C853]"
                                            required>
                                        <span class="absolute right-4 top-3 text-gray-400 text-sm">Watt</span>
                                    </div>
                                    <div v-if="form.errors.power" class="text-red-500 text-xs mt-1">{{ form.errors.power }}</div>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Sewa per Jam</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-3 text-gray-500 font-bold">Rp</span>
                                        <input v-model="form.price" type="number" placeholder="Cth: 5000"
                                            class="w-full border-gray-300 rounded-xl p-3 pl-10 focus:ring-[#00C853] focus:border-[#00C853]"
                                            required>
                                        <span class="absolute right-4 top-3 text-gray-400 text-sm font-bold">/ Jam</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">*Saran: Rp 2.500 - Rp 10.000 per jam tergantung lokasi.</p>
                                    <div v-if="form.errors.price" class="text-red-500 text-xs mt-1">{{ form.errors.price }}</div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <!-- 3. Upload Foto -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm">3</span>
                                Foto Lokasi
                            </h3>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-gray-50 transition cursor-pointer relative">
                                <input type="file" @change="handleFileUpload"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <div class="text-gray-500">
                                    <i class="fas fa-camera text-2xl mb-2 text-[#00C853]"></i>
                                    <p class="text-sm font-medium">Klik untuk upload foto garasi/charger</p>
                                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG (Max 2MB)</p>
                                </div>
                                <div v-if="form.photo" class="mt-2 text-green-600 text-sm font-bold">
                                    File dipilih: {{ form.photo.name }}
                                </div>
                            </div>
                        </div>

                        <!-- Legal Terms -->
                        <div class="bg-orange-50 p-4 rounded-xl border border-orange-100">
                            <div class="flex items-start">
                                <input id="terms" v-model="form.agreeTerms" type="checkbox"
                                    class="mt-1 w-4 h-4 text-[#00C853] border-gray-300 rounded focus:ring-[#00C853] cursor-pointer">
                                <label for="terms" class="ml-3 text-sm text-gray-600 cursor-pointer">
                                    Saya menyatakan bahwa instalasi listrik di rumah saya aman dan saya menyetujui
                                    <a href="#" class="text-orange-600 font-bold hover:underline">Perjanjian Kemitraan Host</a>.
                                    Biaya yang saya terima adalah biaya sewa fasilitas & ganti rugi listrik, bukan
                                    penjualan listrik komersial.
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" :disabled="form.processing"
                            class="w-full py-4 bg-[#00C853] text-white font-bold rounded-xl hover:bg-[#00A142] transition shadow-lg transform active:scale-[0.98] text-lg">
                            Daftarkan Sekarang
                        </button>

                    </form>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

.font-poppins {
    font-family: 'Poppins', sans-serif;
}
</style>