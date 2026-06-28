<script setup>
import { ref, reactive, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";

// === PROPS DARI CONTROLLER ===
const props = defineProps({
  bookings: { 
    type: Array, 
    default: () => [] // Menerima data booking dari backend
  }
});

// === USER DATA ===
const page = usePage();
const authUser = page.props.auth?.user || {};

const user = reactive({
  name: authUser.name || "einstein",
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

// === UI STATE ===
const activeMenu = ref("account");
const activeTab = ref("informasi_akun");

// === ACTIONS ===
const submitProfile = () => {
  router.put(route("profile.update"), {
      ...accountForm,
      phone: personalForm.phone,
    }, {
      onSuccess: () => alert("Profil berhasil diperbarui!"),
      onError: (errors) => console.error(errors),
    }
  );
};

const changePassword = () => {
  if (!passwordForm.current || !passwordForm.password) return alert("Mohon isi semua field kata sandi!");
  if (passwordForm.password !== passwordForm.confirm) return alert("Konfirmasi kata sandi tidak cocok!");
  
  alert("Kata sandi berhasil diubah");
  Object.assign(passwordForm, { current: "", password: "", confirm: "" });
};

const handleLogout = () => {
  router.post(route("logout"));
};

// === MENU ITEMS ===
const menuItems = computed(() => [
  { id: "account", label: "Akun", icon: "👤" },
  { id: "orders", label: "Pesanan Saya", icon: "🎟️" }, // Tab untuk Booking
  { id: "cancellation", label: "Pembatalan Tiket", icon: "⏰" },
  { id: "password", label: "Ubah Kata Sandi", icon: "🔑" },
  { id: "logout", label: "Keluar", icon: "🚪" },
]);
</script>