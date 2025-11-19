<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Definisikan form state menggunakan useForm dari Inertia
const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  message: '',
});

// Fungsi untuk menangani pengiriman formulir
const submit = () => {
  // form.post akan mengirim permintaan POST ke endpoint '/contact'
  // Ganti '/contact' dengan URL endpoint Anda
  form.post(route('contact.store'), {
    // Pengaturan tambahan opsional:
    onSuccess: () => {
      // Logika setelah berhasil, misalnya reset form dan tampilkan pesan sukses
      form.reset();
      alert('Pesan Anda telah terkirim!');
    },
    onError: () => {
      // Logika saat terjadi error validasi
      console.error('Ada kesalahan pada formulir.');
    },
  });
};

// Asumsi Anda juga memiliki route() helper jika menggunakan Laravel
// Jika tidak, ganti route('contact.store') dengan string URL statis, misalnya '/contact'
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label for="first-name" class="block text-sm font-medium text-gray-700">Nama Depan</label>
        <input
          id="first-name"
          type="text"
          v-model="form.first_name"
          class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-3 focus:ring-lime-500 focus:border-lime-500"
          :class="{'border-red-500': form.errors.first_name}"
        />
        <div v-if="form.errors.first_name" class="text-sm text-red-500 mt-1">{{ form.errors.first_name }}</div>
      </div>

      <div>
        <label for="last-name" class="block text-sm font-medium text-gray-700">Nama Belakang</label>
        <input
          id="last-name"
          type="text"
          v-model="form.last_name"
          class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-3 focus:ring-lime-500 focus:border-lime-500"
          :class="{'border-red-500': form.errors.last_name}"
        />
        <div v-if="form.errors.last_name" class="text-sm text-red-500 mt-1">{{ form.errors.last_name }}</div>
      </div>
    </div>

    <div>
      <label for="email-address" class="block text-sm font-medium text-gray-700">Alamat Email</label>
      <input
        id="email-address"
        type="email"
        v-model="form.email"
        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-3 focus:ring-lime-500 focus:border-lime-500"
        :class="{'border-red-500': form.errors.email}"
      />
      <div v-if="form.errors.email" class="text-sm text-red-500 mt-1">{{ form.errors.email }}</div>
    </div>

    <div>
      <label for="message" class="block text-sm font-medium text-gray-700">Pesan</label>
      <textarea
        id="message"
        v-model="form.message"
        rows="6"
        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm p-3 resize-none focus:ring-lime-500 focus:border-lime-500"
        :class="{'border-red-500': form.errors.message}"
      ></textarea>
      <div v-if="form.errors.message" class="text-sm text-red-500 mt-1">{{ form.errors.message }}</div>
    </div>

    <div class="text-center pt-2">
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-block bg-[#00C853] text-white font-semibold px-8 py-3 rounded-lg shadow-lg hover:bg-[#00A142] transition duration-300 focus:outline-none focus:ring-4 focus:ring-lime-300"
      >
        <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
        {{ form.processing ? 'Sending...' : 'Kirim' }}
      </button>
    </div>
  </form>
</template>