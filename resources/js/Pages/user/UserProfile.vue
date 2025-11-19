<script setup>
import { ref, reactive, computed } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
// Import komponen yang diperlukan (asumsi sudah tersedia)
import AppNavbar from "@/Components/AppNavbar.vue";
import AppFooter from "@/Components/AppFooter.vue";

// --- PROPS ---
// Anda perlu menambahkan `defineProps` jika komponen ini menerima props dari controller
const props = defineProps({
    user: {
        type: Object,
        default: () => null,
    },
});

// --- DATA INITIATION ---
const page = usePage();
const authUser = page.props.auth?.user || {};

// Data akun utama yang ditampilkan di sidebar dan form awal.
// Ambil dari props.user (jika ada) atau authUser (jika login).
const initialUserData = reactive({
    name: props.user?.name || authUser.name || "",
    email: props.user?.email || authUser.email || "",
    phone: props.user?.phone || authUser.phone || "", // Menambahkan phone
    address: props.user?.address || authUser.address || "", // Menambahkan address
    gender: props.user?.gender || authUser.gender || "Laki-laki",
    birthDate: props.user?.birthDate || authUser.birthDate || "",
    idType: props.user?.idType || authUser.idType || "",
    idNumber: props.user?.idNumber || authUser.idNumber || "",
    city: props.user?.city || authUser.city || "Kota Batam",
});


// --- INERTIA FORM HANDLER ---
// Menggunakan satu form Inertia untuk data profil utama
const form = useForm({
    // Account Information Fields
    name: initialUserData.name,
    gender: initialUserData.gender,
    birthDate: initialUserData.birthDate,
    idType: initialUserData.idType,
    idNumber: initialUserData.idNumber,
    city: initialUserData.city,
    // Personal Information Fields (yang bisa diubah)
    phone: initialUserData.phone,
    // Tambahkan field lain yang mungkin ada di backend (misal address, dll)
});

// Form terpisah untuk perubahan kata sandi
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});


// === UI STATE ===
const activeMenu = ref("account");
const activeTab = ref("informasi_akun"); // Hanya relevan saat activeMenu === 'account'


// --- FUNGSI UTAMA ---

const submitProfile = () => {
    // Tentukan data yang akan dikirim berdasarkan tab aktif
    let dataToSend = {};

    if (activeTab.value === 'informasi_akun') {
        dataToSend = {
            name: form.name,
            gender: form.gender,
            birthDate: form.birthDate,
            idType: form.idType,
            idNumber: form.idNumber,
            city: form.city,
        };
    } else if (activeTab.value === 'informasi_personal') {
        dataToSend = {
            phone: form.phone,
            // Email tidak bisa diubah karena disabled di template
        };
    }

    form.transform(data => ({
        ...dataToSend, // Kirim hanya data yang relevan
    })).put(route("profile.update"), {
        preserveScroll: true,
        onSuccess: () => {
            alert("Profil berhasil diperbarui!");
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};

const changePassword = () => {
    if (!passwordForm.current_password || !passwordForm.password || !passwordForm.password_confirmation) {
        alert("Mohon isi semua field kata sandi!");
        return;
    }
    if (passwordForm.password !== passwordForm.password_confirmation) {
        alert("Konfirmasi kata sandi tidak cocok!");
        // Reset password fields on error
        passwordForm.password = "";
        passwordForm.password_confirmation = "";
        return;
    }

    passwordForm.put(route("password.update"), { // Asumsi ada route untuk password update
        preserveScroll: true,
        onSuccess: () => {
            alert("Kata sandi berhasil diubah!");
            // Reset form
            passwordForm.reset();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};


const handleLogout = () => {
    router.post(route("logout"));
};


// === HELPER ===
const getColorClass = (type) => {
    switch (type) {
        case "accent":
            // Mengubah warna aksen agar lebih terlihat di latar putih
            return "bg-[#00C853] hover:bg-[#00A142] text-white";
        default:
            return "";
    }
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
    { id: 1, title: "Pesanan Station A", date: "2025-10-10", status: "Selesai" },
    { id: 2, title: "Pesanan Station B", date: "2025-10-12", status: "Proses" },
]);

const newsList = ref([
    { id: 1, title: "Rilis Fitur Baru", excerpt: "Reservasi stasiun kini tersedia!" },
    { id: 2, title: "Promo Musim Panas", excerpt: "Diskon 30% jam non-peak." },
]);

const promos = ref([{ id: 1, code: "HALOEV", desc: "Diskon 10% pengguna baru" }]);

const cancellations = ref([
    { id: 1, order: "Pesanan #2", reason: "Perubahan jadwal", date: "2025-09-20" },
]);
</script>

<template>
    <AppNavbar />

    <main
      class="flex-1 max-w-7xl mx-auto w-full p-6 flex flex-col md:flex-row md:space-x-8 space-y-6 md:space-y-0"
    >
      <aside
        class="w-full md:w-64 bg-white shadow-lg rounded-xl p-5 flex flex-col h-fit md:sticky md:top-6"
      >
        <div class="mb-6">
          <div class="text-lg font-bold">{{ initialUserData.name }}</div>
          <div class="text-sm text-gray-500">{{ initialUserData.email }}</div>
        </div>

        <nav class="flex flex-col space-y-1">
          <template v-for="item in menuItems" :key="item.id">
            <button
              @click="
                item.id === 'logout'
                  ? handleLogout()
                  : (activeMenu = item.id)
              "
              :class="[
                'flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-all duration-150',
                item.id === activeMenu
                  ? 'bg-[#DAE200] text-dark font-semibold' // Warna aksen sidebar
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
                activeTab === 'informasi_akun'
                  ? getColorClass('accent')
                  : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              Informasi Akun
            </button>
            <button
              @click="activeTab = 'informasi_personal'"
              :class="[
                'px-4 py-2 rounded-lg font-semibold transition',
                activeTab === 'informasi_personal'
                  ? getColorClass('accent')
                  : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              Informasi Personal
            </button>
          </div>

          <form @submit.prevent="submitProfile" class="space-y-6">
            <div v-if="activeTab === 'informasi_akun'">
              <div class="grid sm:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium">Nama Lengkap</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="w-full border rounded-lg p-3"
                  />
                  <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                </div>
                <div>
                  <label class="text-sm font-medium">Jenis Kelamin</label>
                  <select
                    v-model="form.gender"
                    class="w-full border rounded-lg p-3"
                  >
                    <option>Laki-laki</option>
                    <option>Perempuan</option>
                  </select>
                  <div v-if="form.errors.gender" class="text-red-600 text-sm mt-1">{{ form.errors.gender }}</div>
                </div>
                <div>
                  <label class="text-sm font-medium">Tanggal Lahir</label>
                  <input
                    v-model="form.birthDate"
                    type="date"
                    placeholder="DD/MM/YYYY"
                    class="w-full border rounded-lg p-3"
                  />
                  <div v-if="form.errors.birthDate" class="text-red-600 text-sm mt-1">{{ form.errors.birthDate }}</div>
                </div>
                <div>
                  <label class="text-sm font-medium">Jenis ID</label>
                  <select
                    v-model="form.idType"
                    class="w-full border rounded-lg p-3"
                  >
                    <option>KTP</option>
                    <option>SIM</option>
                    <option>Paspor</option>
                  </select>
                  <div v-if="form.errors.idType" class="text-red-600 text-sm mt-1">{{ form.errors.idType }}</div>
                </div>
                <div>
                  <label class="text-sm font-medium">Nomor ID</label>
                  <input
                    v-model="form.idNumber"
                    type="text"
                    class="w-full border rounded-lg p-3"
                  />
                  <div v-if="form.errors.idNumber" class="text-red-600 text-sm mt-1">{{ form.errors.idNumber }}</div>
                </div>
                <div>
                  <label class="text-sm font-medium">Kota Asal</label>
                  <select
                    v-model="form.city"
                    class="w-full border rounded-lg p-3"
                  >
                    <option>Kota Batam</option>
                    <option>Jakarta</option>
                    <option>Surabaya</option>
                  </select>
                  <div v-if="form.errors.city" class="text-red-600 text-sm mt-1">{{ form.errors.city }}</div>
                </div>
              </div>

              <button
                type="submit"
                :disabled="form.processing"
                :class="[
                  'mt-6 px-6 py-2.5 font-bold rounded-lg shadow-md disabled:opacity-50',
                  getColorClass('accent'),
                ]"
              >
                Simpan Perubahan
              </button>
            </div>

            <div v-else-if="activeTab === 'informasi_personal'" class="space-y-4">
              <div>
                <label class="text-sm font-medium">Email</label>
                <input
                  :value="initialUserData.email"
                  type="email"
                  class="w-full border rounded-lg p-3 bg-gray-100 text-gray-500"
                  disabled
                />
                <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah di sini.</p>
              </div>
              <div>
                <label class="text-sm font-medium">Nomor HP</label>
                <input
                  v-model="form.phone"
                  type="text"
                  class="w-full border rounded-lg p-3"
                />
                <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}</div>
              </div>
              
              <div class="flex gap-4">
                  <span class="text-sm" :class="{'text-green-600': initialUserData.isEmailVerified}">
                      Email: {{ initialUserData.isEmailVerified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                  </span>
                  <span class="text-sm" :class="{'text-green-600': initialUserData.isPhoneVerified}">
                      HP: {{ initialUserData.isPhoneVerified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                  </span>
              </div>
              
              <button
                type="submit"
                :disabled="form.processing"
                :class="[
                  'mt-4 px-6 py-2.5 font-bold rounded-lg shadow-md disabled:opacity-50',
                  getColorClass('accent'),
                ]"
              >
                Simpan Perubahan
              </button>
            </div>
          </form>
        </div>

        <div v-else-if="activeMenu === 'orders'">
          <h2 class="text-2xl font-bold mb-4">Pesanan Saya</h2>
          <div v-if="orders.length">
            <div v-for="o in orders" :key="o.id" class="border rounded-lg p-4 mb-3 shadow-sm">
              <div class="font-semibold">{{ o.title }}</div>
              <div class="text-sm text-gray-500">{{ o.date }}</div>
              <span class="text-sm font-medium" :class="{'text-green-600': o.status === 'Selesai', 'text-yellow-600': o.status === 'Proses'}">
                  Status: {{ o.status }}
              </span>
            </div>
          </div>
          <div v-else class="text-gray-500">Tidak ada pesanan saat ini.</div>
        </div>

        <div v-else-if="activeMenu === 'news'">
          <h2 class="text-2xl font-bold mb-4">Berita & Update</h2>
          <div v-if="newsList.length">
            <div v-for="n in newsList" :key="n.id" class="border rounded-lg p-4 mb-3 shadow-sm">
              <div class="font-semibold text-lg mb-1">{{ n.title }}</div>
              <p class="text-sm text-gray-600">{{ n.excerpt }}</p>
            </div>
          </div>
          <div v-else class="text-gray-500">Tidak ada berita saat ini.</div>
        </div>

        <div v-else-if="activeMenu === 'promo'">
          <h2 class="text-2xl font-bold mb-4">Promo</h2>
          <div v-if="promos.length">
            <div v-for="p in promos" :key="p.id" class="border border-dashed rounded-lg p-4 mb-3 bg-yellow-50">
              <div class="font-bold text-lg text-red-600">{{ p.code }}</div>
              <p class="text-sm text-gray-800">{{ p.desc }}</p>
            </div>
          </div>
          <div v-else class="text-gray-500">Saat ini tidak ada promo yang tersedia.</div>
        </div>

        <div v-else-if="activeMenu === 'cancellation'">
          <h2 class="text-2xl font-bold mb-4">Daftar Pembatalan Tiket</h2>
          <div v-if="cancellations.length">
            <div v-for="c in cancellations" :key="c.id" class="border rounded-lg p-4 mb-3 shadow-sm bg-red-50">
              <div class="font-semibold">{{ c.order }} ({{ c.date }})</div>
              <p class="text-sm text-gray-700">Alasan: {{ c.reason }}</p>
            </div>
          </div>
          <div v-else class="text-gray-500">Tidak ada pembatalan tiket.</div>
        </div>

        <div v-else-if="activeMenu === 'password'">
            <h2 class="text-2xl font-bold mb-4">Ubah Kata Sandi</h2>

            <form @submit.prevent="changePassword" class="space-y-4 max-w-lg">
                <div>
                    <label class="text-sm font-medium">Kata Sandi Saat Ini</label>
                    <input
                        v-model="passwordForm.current_password"
                        type="password"
                        class="w-full border rounded-lg p-3"
                        autocomplete="current-password"
                    />
                    <div v-if="passwordForm.errors.current_password" class="text-red-600 text-sm mt-1">{{ passwordForm.errors.current_password }}</div>
                </div>
                <div>
                    <label class="text-sm font-medium">Kata Sandi Baru</label>
                    <input
                        v-model="passwordForm.password"
                        type="password"
                        class="w-full border rounded-lg p-3"
                        autocomplete="new-password"
                    />
                    <div v-if="passwordForm.errors.password" class="text-red-600 text-sm mt-1">{{ passwordForm.errors.password }}</div>
                </div>
                <div>
                    <label class="text-sm font-medium">Konfirmasi Kata Sandi Baru</label>
                    <input
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        class="w-full border rounded-lg p-3"
                        autocomplete="new-password"
                    />
                    <div v-if="passwordForm.errors.password_confirmation" class="text-red-600 text-sm mt-1">{{ passwordForm.errors.password_confirmation }}</div>
                </div>
                
                <button
                    type="submit"
                    :disabled="passwordForm.processing"
                    :class="[
                        'mt-4 px-6 py-2.5 font-bold rounded-lg shadow-md disabled:opacity-50',
                        getColorClass('accent'),
                    ]"
                >
                    Ubah Kata Sandi
                </button>
            </form>
        </div>

      </section>
    </main>

    <AppFooter />
</template>

</style>
<style>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap");
.font-inter {
  font-family: "Inter", sans-serif;
}
</style>
