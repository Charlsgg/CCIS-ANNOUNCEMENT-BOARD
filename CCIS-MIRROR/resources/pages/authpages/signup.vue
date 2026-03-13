<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { 
  User, Mail, Lock, Eye, EyeOff, BadgeCheck, 
  ChevronDown, UserPlus, HelpCircle, ShieldCheck, Languages 
} from 'lucide-vue-next'

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const position = ref('')

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const errors = ref<{ name?: string; email?: string; password?: string; position?: string; general?: string }>({})
const isLoading = ref(false)

// 1. Pre-configure Axios for Laravel
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const submitForm = async () => {
  errors.value = {}
  isLoading.value = true

  try {

    const response = await axios.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
      position: position.value,
    }, {
      headers: {
        'Accept': 'application/json',
        // Note: Sanctum handles the CSRF token automatically once the cookie is set, 
        // so you technically don't even need the manual meta tag pull anymore, 
        // but leaving it doesn't hurt!
      }
    });

    if (response.status === 201 || response.status === 200) {
      // 3. Redirect to dashboard
      window.location.href = response.data.redirect || '/dashboard';
    }

    if (response.status === 201 || response.status === 200) {
      // 3. Redirect to dashboard or wherever Laravel points us
      window.location.href = response.data.redirect || '/dashboard';
    }

  } catch (err: any) {
    if (err.response) {
      const status = err.response.status;
      const data = err.response.data;

      if (status === 422) {
        errors.value.name = data.errors?.name?.[0];
        errors.value.email = data.errors?.email?.[0];
        errors.value.password = data.errors?.password?.[0];
        errors.value.position = data.errors?.position?.[0];
      } else {
        errors.value.general = 'Something went wrong on the server. Please try again later.';
      }
    } else {
      errors.value.general = 'Cannot connect to the server. Check your internet connection.';
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="font-display relative min-h-screen w-full flex items-center justify-center bg-cover bg-center bg-no-repeat py-12" 
       style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBxqSjfb2I-D_a0KE0Ksc13U5bPf6IXKbMzsL8UCrToLft6wvX-5oECO-rzq4EHA_YER_SXeabI9aU_sEPgjG0SVQeVdrTg-GD4LV8T2A0_C6zXekuha1sybBCquxfxpEK9-wZrIlf6n-buFaa07zTE0PnrvKUkGJ86IruHm3xdV_3e1p2TVfZIhIxWuP4dfe33wC8NBvRrjsx0zkHTDYAg5UPbBXEW988V2VLPl7qICBb2CqSRzG1h-kgI1ArI8Lto3JNxCrZ5W88');">
    
    <div class="absolute inset-0 bg-[#221610]/80 backdrop-blur-sm"></div>

    <div class="relative z-10 w-full max-w-md px-6">
      <div class="flex flex-col items-center mb-8">
        <div class="w-20 h-20 mb-4 rounded-full bg-[#ec5b13]/10 flex items-center justify-center p-2 border border-[#ec5b13]/20">
          <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDG_dO6l3Iv-6JX_UNhD5aLwqWaE82yinzBCISMfNIl8I8DmZoLOyLjAy8JmHVZCLnY5xIqN2X0zkFKlAk1UAvyzRAo8cMY0RLFezoTrDPAZxbE1KMjq3U3rrUBNFXjxgO5DnzwCIhPSVMiK9LUSa1qYqv6NuIpYRSLc1X0lhFMr8tzCEdXuyQ9-63qunLm_YODLh2fyjUiAhwzXpsMJ7z472_-IgLN5jTSCF1jCSOQcK-k6L6MhhGmXdTU2KnYO3s0IGj6EqRZFDQ" alt="CSUCCIS Logo" class="w-full h-full object-contain" />
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-100 text-center uppercase">CSUCCIS signup</h1>
        <p class="text-slate-400 mt-2 text-center font-medium">Create your portal account</p>
      </div>

      <div class="bg-[#221610]/40 border border-[#ec5b13]/10 backdrop-blur-md rounded-xl p-8 shadow-2xl">
        
        <transition name="fade">
            <div v-if="errors.general" class="mb-5 p-3 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center font-medium">
              {{ errors.general }}
            </div>
        </transition>

        <form @submit.prevent="submitForm" class="space-y-4">
          
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5 ml-1">Full Name</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#ec5b13] transition-colors">
                <User :size="20" />
              </div>
              <input 
                v-model="name"
                type="text" 
                placeholder="Juan Dela Cruz"
                required
                :class="['block w-full pl-11 pr-4 py-2.5 bg-[#221610]/50 border rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all', 
                        errors.name ? 'border-red-500' : 'border-slate-700']"
              />
            </div>
            <p v-if="errors.name" class="text-red-500 text-xs mt-1 ml-1">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5 ml-1">Email Address</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#ec5b13] transition-colors">
                <Mail :size="20" />
              </div>
              <input 
                v-model="email"
                type="email" 
                placeholder="name@university.edu"
                required
                :class="['block w-full pl-11 pr-4 py-2.5 bg-[#221610]/50 border rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all', 
                        errors.email ? 'border-red-500' : 'border-slate-700']"
              />
            </div>
            <p v-if="errors.email" class="text-red-500 text-xs mt-1 ml-1">{{ errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5 ml-1">Position</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#ec5b13] transition-colors">
                <BadgeCheck :size="20" />
              </div>
              <select 
                v-model="position"
                required
                :class="['block w-full pl-11 pr-10 py-2.5 bg-[#221610]/50 border rounded-lg text-slate-100 appearance-none focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all cursor-pointer',
                        errors.position ? 'border-red-500' : 'border-slate-700']"
              >
                <option disabled value="">Select your position</option>
                <option value="it_instructor">IT INSTRUCTOR</option>
                <option value="is_instructor">IS INSTRUCTOR</option>
                <option value="cs_instructor">CS INSTRUCTOR</option>
                <option value="lsg_officer">CCISLG OFFICER</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-500">
                <ChevronDown :size="20" />
              </div>
            </div>
            <p v-if="errors.position" class="text-red-500 text-xs mt-1 ml-1">{{ errors.position }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5 ml-1">Password</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#ec5b13] transition-colors">
                <Lock :size="20" />
              </div>
              <input 
                v-model="password"
                :type="showPassword ? 'text' : 'password'" 
                placeholder="••••••••"
                required
                :class="['block w-full pl-11 pr-12 py-2.5 bg-[#221610]/50 border rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all',
                        errors.password ? 'border-red-500' : 'border-slate-700']"
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300"
              >
                <EyeOff v-if="showPassword" :size="20" />
                <Eye v-else :size="20" />
              </button>
            </div>
            <p v-if="errors.password" class="text-red-500 text-xs mt-1 ml-1">{{ errors.password }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5 ml-1">Confirm Password</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#ec5b13] transition-colors">
                <Lock :size="20" />
              </div>
              <input 
                v-model="password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'" 
                placeholder="••••••••"
                required
                :class="['block w-full pl-11 pr-12 py-2.5 bg-[#221610]/50 border rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all',
                        errors.password ? 'border-red-500' : 'border-slate-700']"
              />
              <button 
                type="button" 
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300"
              >
                <EyeOff v-if="showConfirmPassword" :size="20" />
                <Eye v-else :size="20" />
              </button>
            </div>
          </div>

          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full bg-[#ec5b13] hover:bg-[#ec5b13]/90 text-white font-bold py-3 px-4 rounded-lg shadow-lg shadow-[#ec5b13]/20 transform transition-all active:scale-[0.98] mt-6 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <span v-if="!isLoading">Create Account</span>
            <span v-else>Processing...</span>
            <UserPlus v-if="!isLoading" :size="20" />
          </button>

          <div class="text-center mt-6">
            <p class="text-slate-400 text-sm">
              Already have an account? 
              <a href="/login" class="text-[#ec5b13] font-semibold hover:underline decoration-2 underline-offset-4 ml-1">Log In</a>
            </p>
          </div>
        </form>
      </div>

      <footer class="mt-8 text-center">
        <p class="text-slate-500 text-xs uppercase tracking-widest font-medium">
          © 2026 CSU College of Computing and Information Sciences. All rights reserved.
        </p>
        <div class="flex justify-center gap-4 mt-4">
          <a href="#" class="text-slate-500 hover:text-[#ec5b13] transition-colors" title="Help"><HelpCircle :size="18" /></a>
          <a href="#" class="text-slate-500 hover:text-[#ec5b13] transition-colors" title="Policy"><ShieldCheck :size="18" /></a>
          <a href="#" class="text-slate-500 hover:text-[#ec5b13] transition-colors" title="Language"><Languages :size="18" /></a>
        </div>
      </footer>
    </div>
  </div>
</template>

<style scoped>
.font-display {
  font-family: 'Space Grotesk', sans-serif;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>