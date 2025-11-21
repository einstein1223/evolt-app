<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-gray-50 bg-ev-pattern">
    <div class="w-full max-w-lg bg-white rounded-xl shadow-2xl p-8 z-10">
      
      <!-- LOGO -->
      <div class="text-center mb-6">
        <h1 class="text-2xl font-extrabold text-green-600">E-VOLT</h1> 
      </div>
      
      <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">
        Join & Connect the Fastest Growing Online Community
      </h2>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Username -->
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <input
            id="username"
            type="text"
            v-model="form.username"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
          />
          <p v-if="form.errors.username" class="text-sm text-red-600 mt-1">{{ form.errors.username }}</p>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
          />
          <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
        </div>

        <!-- Nomor Plat -->
        <div>
          <label for="nomor_plat" class="block text-sm font-medium text-gray-700">Nomor Plat</label>
          <input
            id="nomor_plat"
            type="text"
            v-model="form.nomor_plat"
            required
            placeholder="Contoh: BP 1234 XY"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
          />
          <p v-if="form.errors.nomor_plat" class="text-sm text-red-600 mt-1">{{ form.errors.nomor_plat }}</p>
        </div>

        <!-- Nomor Telepon -->
        <div>
          <label for="nomor_telepon" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
          <input
            id="nomor_telepon"
            type="tel"
            v-model="form.nomor_telepon"
            required
            placeholder="Contoh: 08123456789"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
          />
          <p v-if="form.errors.nomor_telepon" class="text-sm text-red-600 mt-1">{{ form.errors.nomor_telepon }}</p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="relative">
            <input
              id="password"
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              required
              autocomplete="new-password"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm pr-10"
            />
            <button
              type="button"
              @click="togglePasswordVisibility"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5"
            >
              <span class="text-gray-500">{{ showPassword ? '🙈' : '👁️' }}</span>
            </button>
          </div>
          <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
        </div>

        <!-- Password Confirmation -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
          <input
            id="password_confirmation"
            type="password"
            v-model="form.password_confirmation"
            required
            autocomplete="new-password"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
          />
        </div>

        <!-- Terms -->
        <div class="flex items-center">
          <input
            id="terms"
            name="terms"
            type="checkbox"
            v-model="form.terms"
            required
            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
          />
          <label for="terms" class="ml-2 block text-sm text-gray-900">
            I accept the <span class="text-green-600 hover:text-green-500 cursor-pointer">terms & condition</span>
          </label>
        </div>

        <!-- Button -->
        <div>
          <button
            type="submit"
            :disabled="form.processing || !form.terms"
            class="w-full flex justify-center py-2 px-4 rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 disabled:opacity-50 transition"
          >
            SIGN UP
          </button>
        </div>
      </form>

      <!-- Link ke login -->
      <div class="mt-6 text-center text-sm">
        <p>
          Own an account?
          <Link :href="route('login')" class="font-medium text-green-600 hover:text-green-500">
            JUMP RIGHT IN
          </Link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'

const form = useForm({
  username: '',
  email: '',
  nomor_plat: '',
  nomor_telepon: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const showPassword = ref(false)

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const submit = () => {
  if (!form.terms) {
    alert('Harap setujui Syarat & Ketentuan')
    return
  }

  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<style scoped>
.bg-ev-pattern {
  background-image: url('/images/image5.png'), url('/images/image4.png');
  background-position: left center, right center;
  background-repeat: no-repeat, no-repeat;
  background-size: contain;
}
</style>
