<script setup>
import { ref, reactive, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route } from "ziggy-js"; // ✅ pastikan ziggy-js sudah terinstall
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";

// === USER DATA ===
const page = usePage();
const authUser = page.props.auth?.user || {};

const user = reactive({
  name: authUser.name || "",
  email: authUser.email || "",
  phone: authUser.phone || "",
  address: authUser.address || "",
});

// === FORM DATA ===
const accountForm = reactive({
  name: user.name,
  gender: "Laki-laki",
  birthDate: "",
  idType: "",
  idNumber: "",
  city: "",
});

const personalForm = reactive({
  email: user.email,
  phone: user.phone,
  isEmailVerified: true,
  isPhoneVerified: true,
});

const passwordForm = reactive({
  current: "",
  password: "",
  confirm: "",
});

// === ACTIONS ===
const submitProfile = () => {
  router.put(
    route("profile.update"),
    {
      ...accountForm,
      phone: personalForm.phone,
    },
    {
      onSuccess: () => alert("Profil berhasil diperbarui!"),
      onError: (errors) => console.error(errors),
    }
  );
};

const changePassword = () => {
  if (!passwordForm.current || !passwordForm.password) {
    alert("Mohon isi semua field kata sandi!");
    return;
  }
  if (passwordForm.password !== passwordForm.confirm) {
    alert("Konfirmasi kata sandi tidak cocok!");
    return;
  }
  alert("Kata sandi berhasil diubah (simulasi)");
  Object.assign(passwordForm, { current: "", password: "", confirm: "" });
};

const handleLogout = () => {
  router.post(route("logout"));
};

// === UI STATE ===
const activeMenu = ref("account");
const activeTab = ref("informasi_akun");

// === HELPERS ===
const getColorClass = (type) => {
  switch (type) {
    case "accent":
      return "bg-[#00C853] hover:bg-[#00A142] text-white";
    default:
      return "";
  }
};

// === MENU ITEMS ===
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
