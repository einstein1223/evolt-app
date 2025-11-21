<script setup>
import { ref, reactive, computed } from "vue";
import { usePage, useForm, router, Link } from "@inertiajs/vue3";


// --- NOTE PENTING ---
// Import ini saya matikan dulu agar tidak LAYAR PUTIH karena file mungkin belum ada/salah path.
// Jika file AppNavbar.vue dan AppFooter.vue sudah ada di folder Components, silakan uncomment.
// import AppNavbar from "@/Components/AppNavbar.vue";
// import AppFooter from "@/Components/AppFooter.vue";
// TERIMA DATA DARI CONTROLLER
const props = defineProps({
    orders: Array // <-- Ini data riwayat booking
});

// --- DATA INITIATION ---
const page = usePage();
// Gunakan ?. (Optional Chaining) agar tidak error layar putih saat loading
const authUser = page.props.auth?.user || {};

// --- INERTIA FORM HANDLER ---
// Menggunakan field yang SESUAI dengan Database User.php kita
const form = useForm({
    // Account Info
    username: authUser.username || "", // GANTI name jadi username
    gender: authUser.gender || "Laki-laki",
    birthDate: authUser.birthDate || "",
    idType: authUser.idType || "KTP",
    idNumber: authUser.idNumber || "",
    city: authUser.city || "Kota Batam",
    
    // Personal Info
    email: authUser.email || "",
    nomor_telepon: authUser.nomor_telepon || "", // GANTI phone jadi nomor_telepon
    nomor_plat: authUser.nomor_plat || "",       // WAJIB ADA (Field baru)
});

// Form Password
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

// === UI STATE ===
const activeMenu = ref("account");
const activeTab = ref("informasi_akun"); 

// --- FUNGSI UTAMA ---
const submitProfile = () => {
    // Kirim semua data form ke backend
    form.patch(route("profile.update"), {
        preserveScroll: true,
        onSuccess: () => {
            alert("Profil berhasil diperbarui!");
        },
        onError: (errors) => {
            console.error(errors);
            alert("Gagal update profil. Cek inputan anda.");
        },
    });
};

const changePassword = () => {
    if (!passwordForm.current_password || !passwordForm.password || !passwordForm.password_confirmation) {
        alert("Mohon isi semua field kata sandi!");
        return;
    }
    passwordForm.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            alert("Kata sandi berhasil diubah!");
            passwordForm.reset();
        },
        onError: (errors) => {
            if(errors.current_password) alert("Password lama salah!");
        },
    });
};

const handleLogout = () => {
    router.post(route("logout"));
};

// === HELPER ===
const getColorClass = (type) => {
    // Menggunakan warna hijau E-VOLT atau warna aksen desainmu
    if (type === "accent") return "bg-[#00C853] hover:bg-[#00A142] text-white"; 
    return "";
};

// === SIDEBAR MENU ===
const menuItems = computed(() => [
    { id: "account", label: "Akun", icon: "👤" },
    { id: "orders", label: "Pesanan Saya", icon: "🎟️" },
    { id: "news", label: "Berita", icon: "📰" },
    { id: "promo", label: "Promo", icon: "🏷️" },
    { id: "cancellation", label: "Pembatalan Tiket", icon: "⏰" },
    { id: "password", label: "Ubah Kata Sandi", icon: "🔑" },
    { id: "logout", label: "Keluar", icon: "🚪" },
]);

// === DUMMY DATA ===
const orders = ref([
    { id: 1, title: "Charging Station A", date: "2025-10-10", status: "Selesai" },
    { id: 2, title: "Charging Station B", date: "2025-10-12", status: "Proses" },
]);
const newsList = ref([
    { id: 1, title: "Rilis Fitur Baru", excerpt: "Reservasi stasiun kini tersedia!" },
]);
const promos = ref([{ id: 1, code: "HALOEV", desc: "Diskon 10% pengguna baru" }]);
const cancellations = ref([]);

</script>

<template>
    <nav class="bg-white shadow p-4 mb-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="font-bold text-xl text-green-600">E-VOLT</div>
            <Link :href="route('dashboard')" class="text-gray-600 hover:text-green-600 text-sm font-medium">
                Kembali ke Dashboard
            </Link>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl mx-auto w-full p-6 flex flex-col md:flex-row md:space-x-8 space-y-6 md:space-y-0">
      
      <aside class="w-full md:w-64 bg-white shadow-lg rounded-xl p-5 flex flex-col h-fit md:sticky md:top-6">
        <div class="mb-6">
          <div class="text-lg font-bold break-words">{{ form.username || 'User' }}</div>
          <div class="text-sm text-gray-500 break-words">{{ form.email }}</div>
          <div class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded mt-2 inline-block">
              {{ form.nomor_plat || 'No Plat' }}
          </div>
        </div>

        <nav class="flex flex-col space-y-1">
          <template v-for="item in menuItems" :key="item.id">
            <button
              @click="item.id === 'logout' ? handleLogout() : (activeMenu = item.id)"
              :class="[
                'flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-all duration-150',
                item.id === activeMenu
                  ? 'bg-[#DAE200] text-gray-900 font-semibold' // Warna Desain Client
                  : 'text-gray-700 hover:bg-gray-100',
                item.id === 'logout' ? 'text-red-500 hover:bg-red-50' : '',
              ]"
            >
              <span>{{ item.icon }}</span>
              <span>{{ item.label }}</span>
            </button>
          </template>
        </nav>
      </aside>

      <section class="flex-1 bg-white shadow-xl rounded-xl p-8">
        
        <div v-if="activeMenu === 'account'">
          <h1 class="text-2xl font-bold mb-4">Detail Akun</h1>

          <div class="flex flex-wrap gap-3 mb-6 border-b pb-4">
            <button
              @click="activeTab = 'informasi_akun'"
              :class="[
                'px-4 py-2 rounded-lg font-semibold transition',
                activeTab === 'informasi_akun' ? getColorClass('accent') : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              Informasi Akun
            </button>
            <button
              @click="activeTab = 'informasi_personal'"
              :class="[
                'px-4 py-2 rounded-lg font-semibold transition',
                activeTab === 'informasi_personal' ? getColorClass('accent') : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              Informasi Personal
            </button>
          </div>

          <form @submit.prevent="submitProfile" class="space-y-6">
            
            <div v-if="activeTab === 'informasi_akun'">
              <div class="grid sm:grid-cols-2 gap-4">
                <div class="col-span-2">
                  <label class="text-sm font-medium">Username</label> <input
                    v-model="form.username" 
                    type="text"
                    class="w-full border rounded-lg p-3"
                  />
                  <div v-if="form.errors.username" class="text-red-600 text-sm mt-1">{{ form.errors.username }}</div>
                </div>
                <div>
                  <label class="text-sm font-medium">Jenis Kelamin</label>
                  <select v-model="form.gender" class="w-full border rounded-lg p-3">
                    <option>Laki-laki</option>
                    <option>Perempuan</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm font-medium">Tanggal Lahir</label>
                  <input v-model="form.birthDate" type="date" class="w-full border rounded-lg p-3" />
                </div>
                <div>
                  <label class="text-sm font-medium">Kota Asal</label>
                  <select v-model="form.city" class="w-full border rounded-lg p-3">
                    <option>Kota Batam</option>
                    <option>Jakarta</option>
                    <option>Surabaya</option>
                  </select>
                </div>
                <div>
                  <label class="text-sm font-medium">Jenis ID</label>
                  <select v-model="form.idType" class="w-full border rounded-lg p-3">
                    <option>KTP</option>
                    <option>SIM</option>
                  </select>
                </div>
                 <div>
                  <label class="text-sm font-medium">Nomor ID</label>
                  <input v-model="form.idNumber" type="text" class="w-full border rounded-lg p-3" />
                </div>
              </div>

              <button
                type="submit"
                :disabled="form.processing"
                :class="['mt-6 px-6 py-2.5 font-bold rounded-lg shadow-md disabled:opacity-50', getColorClass('accent')]"
              >
                Simpan Perubahan
              </button>
            </div>

            <div v-else-if="activeTab === 'informasi_personal'" class="space-y-4">
              <div>
                <label class="text-sm font-medium">Email</label>
                <input
                  :value="form.email"
                  type="email"
                  class="w-full border rounded-lg p-3 bg-gray-100 text-gray-500 cursor-not-allowed"
                  disabled
                />
                <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah disini.</p>
              </div>
              
              <div>
                <label class="text-sm font-medium">Nomor HP</label>
                <input
                  v-model="form.nomor_telepon"
                  type="text"
                  class="w-full border rounded-lg p-3"
                />
                <div v-if="form.errors.nomor_telepon" class="text-red-600 text-sm mt-1">{{ form.errors.nomor_telepon }}</div>
              </div>

              <div>
                <label class="text-sm font-medium">Nomor Plat Kendaraan</label>
                <input
                  v-model="form.nomor_plat"
                  type="text"
                  placeholder="Contoh: BP 1234 XY"
                  class="w-full border rounded-lg p-3 bg-yellow-50 focus:ring-2 focus:ring-yellow-400"
                />
                <div v-if="form.errors.nomor_plat" class="text-red-600 text-sm mt-1">{{ form.errors.nomor_plat }}</div>
              </div>
              
              <button
                type="submit"
                :disabled="form.processing"
                :class="['mt-4 px-6 py-2.5 font-bold rounded-lg shadow-md disabled:opacity-50', getColorClass('accent')]"
              >
                Simpan Perubahan
              </button>
            </div>
          </form>
        </div>

       <div v-else-if="activeMenu === 'orders'">
  <h2 class="text-2xl font-bold mb-4">Pesanan Saya</h2>
  
  <div v-if="props.orders && props.orders.length > 0">
    
    <div v-for="order in props.orders" :key="order.id" class="border rounded-lg p-4 mb-3 shadow-sm flex justify-between items-center bg-white hover:shadow-md transition">
      <div>
        <div class="font-bold text-gray-800 text-lg">{{ order.station_name }}</div>
        <div class="text-sm text-gray-600 flex items-center gap-2">
            <span class="bg-gray-100 px-2 py-0.5 rounded text-xs font-mono">{{ order.booking_number }}</span>
            <span>• {{ order.location }}</span>
        </div>
        <div class="text-xs text-gray-400 mt-1">
            {{ new Date(order.created_at).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
        </div>
      </div>
      
      <div class="text-right">
        <div class="font-bold text-[#00C853] text-lg">
            Rp {{ new Intl.NumberFormat('id-ID').format(order.total_price) }}
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 inline-block mt-1">
            {{ order.status }}
        </span>
      </div>
    </div>

  </div>
  
  <div v-else class="text-center py-12 text-gray-500">
      <p class="mb-2">Belum ada riwayat pemesanan.</p>
      <Link :href="route('dashboard')" class="text-[#00C853] font-semibold hover:underline">
          Mulai Booking Sekarang
      </Link>
  </div>
</div>

        <div v-else-if="activeMenu === 'password'">
            <h2 class="text-2xl font-bold mb-4">Ubah Kata Sandi</h2>
            <form @submit.prevent="changePassword" class="space-y-4 max-w-lg">
                <div>
                    <label class="text-sm font-medium">Kata Sandi Saat Ini</label>
                    <input v-model="passwordForm.current_password" type="password" class="w-full border rounded-lg p-3" autocomplete="current-password" />
                    <div v-if="passwordForm.errors.current_password" class="text-red-600 text-sm mt-1">{{ passwordForm.errors.current_password }}</div>
                </div>
                <div>
                    <label class="text-sm font-medium">Kata Sandi Baru</label>
                    <input v-model="passwordForm.password" type="password" class="w-full border rounded-lg p-3" autocomplete="new-password" />
                    <div v-if="passwordForm.errors.password" class="text-red-600 text-sm mt-1">{{ passwordForm.errors.password }}</div>
                </div>
                <div>
                    <label class="text-sm font-medium">Konfirmasi Kata Sandi Baru</label>
                    <input v-model="passwordForm.password_confirmation" type="password" class="w-full border rounded-lg p-3" autocomplete="new-password" />
                </div>
                <button type="submit" :disabled="passwordForm.processing" :class="['mt-4 px-6 py-2.5 font-bold rounded-lg shadow-md', getColorClass('accent')]">
                    Ubah Kata Sandi
                </button>
            </form>
        </div>

        <div v-else>
            <h2 class="text-2xl font-bold mb-4">{{ menuItems.find(i => i.id === activeMenu)?.label }}</h2>
            <p class="text-gray-500">Fitur ini belum tersedia.</p>
        </div>

      </section>
    </main>
    
    </template>

<style>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap");
.font-inter {
  font-family: "Inter", sans-serif;
}
</style>