<script setup>
import { ref, reactive, computed } from "vue";
import { usePage, useForm, router, Link } from "@inertiajs/vue3";
import Navbar from '@/Components/NavbarUser.vue';
import Footer from '@/Components/Footer.vue';

// 1. AMBIL DATA DARI PROPS & AUTH
const page = usePage();
const authUser = page.props.auth?.user || {};

const props = defineProps({
  orders: { type: Array, default: () => [] }
});

// 2. SETUP FORM PROFILE
const form = useForm({
  username: authUser.username || authUser.name || "",
  email: authUser.email || "",
  nomor_telepon: authUser.nomor_telepon || "",

  // Data Kendaraan (Biasanya Read-Only karena diupdate di Dashboard, tapi bisa diedit jika mau)
  nomor_plat: authUser.nomor_plat || "",
  car_brand: authUser.car_brand || "",
  car_series: authUser.car_series || "",
  car_type: authUser.car_type || "",

  // Data Personal Lainnya
  gender: authUser.gender || "Laki-laki",
  birthDate: authUser.birthDate || "",
  idType: authUser.idType || "KTP",
  idNumber: authUser.idNumber || "",
  city: authUser.city || "Kota Batam",
});

const passwordForm = useForm({
  current_password: "",
  password: "",
  password_confirmation: "",
});

// --- 3. UI STATE ---
const activeMenu = ref("account");
const activeTab = ref('informasi_akun');

const menuItems = computed(() => [
  { id: "account", label: "Akun", icon: "👤" },
  { id: "orders", label: "Pesanan Saya", icon: "🎟️" },
  { id: "news", label: "Berita", icon: "📰" },
  { id: "password", label: "Ubah Kata Sandi", icon: "🔑" },
  { id: "logout", label: "Keluar", icon: "🚪" },
]);

// --- 4. ACTION HANDLERS ---

const switchMenu = (id) => {
  if (id === 'logout') {
    if (confirm('Yakin ingin keluar?')) {
      router.post(route("logout"));
    }
  } else {
    activeMenu.value = id;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const submitProfile = () => {
  form.patch(route("profile.update"), {
    preserveScroll: true,
    onSuccess: () => alert("Profil berhasil diperbarui!"),
    onError: () => alert("Gagal update. Cek inputan Anda."),
  });
};

const changePassword = () => {
  passwordForm.put(route("password.update"), {
    preserveScroll: true,
    onSuccess: () => {
      alert("Kata sandi berhasil diubah!");
      passwordForm.reset();
    },
    onError: () => {
      if (passwordForm.errors.current_password) alert("Password lama salah!");
    }
  });
};

// Helper
const getColorClass = (type) => type === "accent" ? "bg-[#00C853] hover:bg-[#00A142] text-white" : "";
const formatRupiah = (val) => new Intl.NumberFormat('id-ID').format(val);
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-poppins flex flex-col text-gray-800">

    <Navbar />

    <main class="flex-1 max-w-7xl mx-auto w-full pt-24 p-4 sm:p-6 flex flex-col md:flex-row gap-8">

      <!-- SIDEBAR -->
      <aside class="w-full md:w-72 flex-shrink-0">
        <div class="bg-white shadow-lg rounded-2xl p-6 sticky top-24 border border-gray-100">
          <div class="mb-8 text-center">
            <div
              class="w-20 h-20 bg-gray-100 rounded-full mx-auto mb-3 flex items-center justify-center text-3xl border-2 border-green-100">
              👤
            </div>
            <h3 class="text-xl font-bold text-gray-900 truncate">{{ form.username }}</h3>
            <p class="text-sm text-gray-500 truncate">{{ form.email }}</p>

            <div v-if="form.nomor_plat"
              class="mt-3 inline-block px-3 py-1 bg-[#f0fdf4] text-[#16a34a] rounded-full text-xs font-bold border border-green-200">
              {{ form.nomor_plat }}
            </div>
          </div>

          <nav class="flex flex-col space-y-2">
            <template v-for="item in menuItems" :key="item.id">
              <button type="button" @click="switchMenu(item.id)" :class="[
                'flex items-center gap-4 px-4 py-3.5 rounded-xl text-left transition-all duration-200 font-medium w-full',
                item.id === activeMenu
                  ? 'bg-[#00C853] text-white shadow-md shadow-green-200'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-[#00C853]',
                item.id === 'logout' ? 'text-red-500 hover:bg-red-50 hover:text-red-600 mt-6 border-t border-gray-100 pt-4' : '',
              ]">
                <span class="text-xl">{{ item.icon }}</span>
                <span>{{ item.label }}</span>
              </button>
            </template>
          </nav>
        </div>
      </aside>

      <!-- MAIN CONTENT -->
      <section
        class="flex-1 bg-white shadow-xl rounded-2xl p-6 sm:p-10 min-h-[600px] border border-gray-100 relative overflow-hidden">

        <!-- 1. MENU AKUN -->
        <div v-if="activeMenu === 'account'" class="animate-fade-in">
          <h1 class="text-2xl font-bold mb-1 text-gray-900">Detail Akun</h1>
          <p class="text-gray-500 text-sm mb-6">Kelola informasi profil dan kendaraan Anda.</p>

          <!-- Tabs -->
          <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200 pb-1">
            <button @click="activeTab = 'informasi_akun'"
              :class="['px-6 py-2.5 rounded-t-lg font-semibold transition-all text-sm border-b-2', activeTab === 'informasi_akun' ? 'border-[#00C853] text-[#00C853] bg-green-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50']">
              Informasi Akun
            </button>
            <button @click="activeTab = 'informasi_personal'"
              :class="['px-6 py-2.5 rounded-t-lg font-semibold transition-all text-sm border-b-2', activeTab === 'informasi_personal' ? 'border-[#00C853] text-[#00C853] bg-green-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50']">
              Data Kendaraan & Kontak
            </button>
          </div>

          <form @submit.prevent="submitProfile" class="space-y-6">
            <!-- Tab Informasi Akun -->
            <div v-if="activeTab === 'informasi_akun'" class="grid sm:grid-cols-2 gap-6 animate-slide-up">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                <input v-model="form.username" type="text"
                  class="w-full border border-gray-300 rounded-xl p-3.5 focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition" />
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
                  Simpan Perubahan
                </button>
              </div>
            </div>

            <!-- Tab Data Kendaraan -->
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

              <!-- CARD KENDARAAN -->
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
                    <input v-model="form.car_brand"
                      class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-gray-700 font-medium text-sm"
                      readonly>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Model</label>
                    <input v-model="form.car_series"
                      class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-gray-700 font-medium text-sm"
                      readonly>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tipe</label>
                    <input v-model="form.car_type"
                      class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-gray-700 font-medium text-sm"
                      readonly>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Plat Nomor</label>
                    <input v-model="form.nomor_plat"
                      class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-gray-900 font-bold text-sm uppercase focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853]">
                  </div>
                </div>
                <p class="text-xs text-gray-400 mt-3 italic">*Hanya Plat Nomor yang dapat diubah langsung. Hubungi admin
                  untuk
                  ubah jenis mobil.</p>
              </div>

              <div class="pt-2 flex justify-end">
                <button type="submit" :disabled="form.processing"
                  class="px-8 py-3.5 font-bold rounded-xl shadow-lg bg-[#00C853] text-white hover:bg-[#008e3b] transition transform active:scale-95 disabled:opacity-70">
                  Simpan Data
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- 2. MENU PESANAN SAYA -->
        <div v-else-if="activeMenu === 'orders'" class="animate-fade-in">
          <h2 class="text-2xl font-bold mb-6 text-gray-900">Riwayat Pesanan</h2>

          <div v-if="props.orders && props.orders.length > 0" class="space-y-4">
            <div v-for="order in props.orders" :key="order.id"
              class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col md:flex-row justify-between md:items-center gap-4 group">
              <div>
                <div class="font-bold text-gray-800 text-lg group-hover:text-[#00C853] transition-colors">{{
                  order.station_name }}</div>
                <div class="text-sm text-gray-500 flex flex-wrap items-center gap-3 mt-2">
                  <span
                    class="bg-gray-100 px-3 py-1 rounded-md font-mono text-gray-700 font-medium border border-gray-200">{{
                      order.booking_number }}</span>
                  <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{
                    order.duration }}</span>
                  <span class="flex items-center gap-1" v-if="order.location && order.location !== '-'"><span
                      class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> {{ order.location }}</span>
                </div>
                <div class="text-xs text-gray-400 mt-2 font-medium">
                  {{ new Date(order.created_at).toLocaleDateString('id-ID', {
                    weekday: 'long', day: 'numeric', month:
                      'long',
                    year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                </div>
              </div>

              <div
                class="text-right flex flex-row md:flex-col justify-between items-center md:items-end gap-2 border-t md:border-t-0 border-gray-100 pt-3 md:pt-0">
                <div class="font-extrabold text-[#00C853] text-xl">Rp {{ formatRupiah(order.total_price) }}</div>
                <span
                  class="px-4 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200 uppercase tracking-wide">
                  {{ order.status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else
            class="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-gray-200 rounded-3xl bg-gray-50">
            <div class="text-6xl mb-4 opacity-30">🎫</div>
            <p class="text-lg font-medium text-gray-600 mb-2">Belum ada riwayat pesanan.</p>
            <p class="text-sm text-gray-400 mb-6">Mulai pesanan pertama Anda sekarang.</p>
            <Link :href="route('dashboard')"
              class="px-6 py-3 bg-[#00C853] text-white font-bold rounded-xl hover:bg-[#008e3b] transition shadow-lg active:scale-95">
              Cari Stasiun
            </Link>
          </div>
        </div>

        <!-- 3. MENU LAINNYA -->
        <div v-else-if="activeMenu === 'news'" class="animate-fade-in">
          <h2 class="text-2xl font-bold mb-4 text-gray-900">Berita & Update</h2>
          <div class="p-6 bg-blue-50 border border-blue-100 rounded-2xl mb-4 flex items-start gap-4">
            <div class="text-3xl">📢</div>
            <div>
              <div class="font-bold text-blue-900 text-lg">Rilis Fitur Baru</div>
              <p class="text-sm text-blue-700 mt-1 leading-relaxed">Kini Anda bisa melihat estimasi harga sebelum
                melakukan
                pembayaran! Nikmati kemudahan booking tanpa antri.</p>
            </div>
          </div>
        </div>

        <div v-else-if="activeMenu === 'password'" class="animate-fade-in">
          <h2 class="text-2xl font-bold mb-6 text-gray-900">Ubah Kata Sandi</h2>
          <form @submit.prevent="changePassword" class="space-y-5 max-w-lg">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi Saat Ini</label>
              <input type="password" v-model="passwordForm.current_password"
                class="w-full border border-gray-300 p-3.5 rounded-xl focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition" />
              <div v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1">{{
                passwordForm.errors.current_password }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi Baru</label>
              <input type="password" v-model="passwordForm.password"
                class="w-full border border-gray-300 p-3.5 rounded-xl focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition" />
              <div v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1">{{ passwordForm.errors.password
                }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Kata Sandi</label>
              <input type="password" v-model="passwordForm.password_confirmation"
                class="w-full border border-gray-300 p-3.5 rounded-xl focus:ring-2 focus:ring-[#00C853] focus:border-[#00C853] transition" />
            </div>
            <div class="pt-2">
              <button type="submit" :disabled="passwordForm.processing"
                class="px-8 py-3.5 rounded-xl bg-[#00C853] text-white font-bold shadow-lg hover:bg-[#008e3b] transition transform active:scale-95 disabled:opacity-70">
                Simpan Password Baru
              </button>
            </div>
          </form>
        </div>

      </section>
    </main>

    <Footer />
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

.font-poppins {
  font-family: 'Poppins', sans-serif;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

.animate-slide-up {
  animation: slideUp 0.4s ease-out forwards;
}
</style>