<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { 
  Mail, ArrowLeft, Send, HelpCircle, ShieldCheck, Languages 
} from 'lucide-vue-next'

const email = ref('')
const status = ref('')
const errors = ref<{ email?: string; general?: string }>({})
const isLoading = ref(false)

// 1. Configure Axios
axios.defaults.baseURL = 'http://localhost:8000'; 
// withCredentials is not needed for monolith web routes, but fine to leave
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// 2. Grab the CSRF token on mount and attach it to Axios
onMounted(() => {
  const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
  if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
  } else {
    console.error('CSRF token not found: Ensure <meta name="csrf-token" content="{{ csrf_token() }}"> is in your Blade layout head.');
  }
})

const submitForm = async () => {
  errors.value = {}
  status.value = ''
  isLoading.value = true

  try {
    // 3. Removed the await axios.get('/sanctum/csrf-cookie') call
    // Just perform the direct POST request
    const response = await axios.post('/forgot-password', {
      email: email.value,
    });

    if (response.status === 200) {
      status.value = response.data.status || 'We have emailed your password reset link.';
      email.value = ''; 
    }

  } catch (err: any) {
    if (err.response) {
      const code = err.response.status;
      const data = err.response.data;

      if (code === 422) {
        errors.value.email = data.errors?.email?.[0];
      } else {
        errors.value.general = data.message || 'Something went wrong. Please try again later.';
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
  <div class="font-display relative min-h-screen w-full flex items-center justify-center bg-cover bg-center bg-no-repeat" 
       style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBxqSjfb2I-D_a0KE0Ksc13U5bPf6IXKbMzsL8UCrToLft6wvX-5oECO-rzq4EHA_YER_SXeabI9aU_sEPgjG0SVQeVdrTg-GD4LV8T2A0_C6zXekuha1sybBCquxfxpEK9-wZrIlf6n-buFaa07zTE0PnrvKUkGJ86IruHm3xdV_3e1p2TVfZIhIxWuP4dfe33wC8NBvRrjsx0zkHTDYAg5UPbBXEW988V2VLPl7qICBb2CqSRzG1h-kgI1ArI8Lto3JNxCrZ5W88');">
    
    <div class="absolute inset-0 bg-[#221610]/80 backdrop-blur-sm"></div>

    <div class="relative z-10 w-full max-w-md px-6 py-12">
      <div class="flex flex-col items-center mb-10">
        <div class="w-24 h-24 mb-6 rounded-full bg-[#ec5b13]/10 flex items-center justify-center p-2 border border-[#ec5b13]/20">
          <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDG_dO6l3Iv-6JX_UNhD5aLwqWaE82yinzBCISMfNIl8I8DmZoLOyLjAy8JmHVZCLnY5xIqN2X0zkFKlAk1UAvyzRAo8cMY0RLFezoTrDPAZxbE1KMjq3U3rrUBNFXjxgO5DnzwCIhPSVMiK9LUSa1qYqv6NuIpYRSLc1X0lhFMr8tzCEdXuyQ9-63qunLm_YODLh2fyjUiAhwzXpsMJ7z472_-IgLN5jTSCF1jCSOQcK-k6L6MhhGmXdTU2KnYO3s0IGj6EqRZFDQ" alt="CSUCCIS Logo" class="w-full h-full object-contain" />
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-100 text-center uppercase">Reset Password</h1>
        <p class="text-slate-400 mt-2 text-center font-medium px-4">
          Enter your email address and we'll send you a link to reset your password.
        </p>
      </div>

      <div class="bg-[#221610]/40 border border-[#ec5b13]/10 backdrop-blur-md rounded-xl p-8 shadow-2xl">
        
        <transition name="fade">
            <div v-if="status" class="mb-5 p-4 bg-emerald-500/10 border border-emerald-500/50 rounded-lg text-emerald-400 text-sm text-center font-medium">
              {{ status }}
            </div>
        </transition>

        <transition name="fade">
            <div v-if="errors.general" class="mb-5 p-3 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center font-medium">
              {{ errors.general }}
            </div>
        </transition>

        <form @submit.prevent="submitForm" class="space-y-5">
          
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
                :disabled="isLoading"
                :class="['block w-full pl-11 pr-4 py-3 bg-[#221610]/50 border rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all disabled:opacity-50 disabled:cursor-not-allowed', 
                        errors.email ? 'border-red-500' : 'border-slate-700']"
              />
            </div>
            <p v-if="errors.email" class="text-red-500 text-xs mt-1 ml-1">{{ errors.email }}</p>
          </div>

          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full bg-[#ec5b13] hover:bg-[#ec5b13]/90 text-white font-bold py-3.5 px-4 rounded-lg shadow-lg shadow-[#ec5b13]/20 transform transition-all active:scale-[0.98] mt-2 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <span v-if="!isLoading">Send Reset Link</span>
            <span v-else>Sending...</span>
            <Send v-if="!isLoading" :size="18" class="ml-1" />
          </button>

          <div class="text-center mt-6">
            <a href="/login" class="inline-flex items-center justify-center text-slate-400 text-sm hover:text-[#ec5b13] transition-colors font-medium group">
              <ArrowLeft :size="16" class="mr-1.5 transform group-hover:-translate-x-1 transition-transform" />
              Back to Login
            </a>
          </div>
        </form>
      </div>

      <footer class="mt-12 text-center">
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