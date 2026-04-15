<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { 
  Mail, Lock, Eye, EyeOff, BadgeCheck, 
  ChevronDown, LogIn, HelpCircle, ShieldCheck, Languages 
} from 'lucide-vue-next'

const email = ref('')
const password = ref('')
const position = ref('')
const remember = ref(false)
const showPassword = ref(false)

const errors = ref<{ email?: string; password?: string; position?: string; general?: string }>({})
const isLoading = ref(false)

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const submitForm = async () => {
  errors.value = {}
  isLoading.value = true

  try {
    const response = await axios.post('/login', {
      email: email.value,
      password: password.value,
      position: position.value,
      remember: remember.value,
    }, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      }
    });

    if (response.status === 200) {
      window.location.href = response.data.redirect || '/dashboard';
    }

  } catch (err: any) {
    if (err.response) {
      const status = err.response.status;
      const data = err.response.data;

      if (status === 422) {
        errors.value.email = data.errors?.email?.[0];
        errors.value.password = data.errors?.password?.[0];
        errors.value.position = data.errors?.position?.[0];
      } else if (status === 401) {
        errors.value.general = data.message || 'Invalid credentials. Please check your email and position.';
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
  <div class="font-display relative h-screen w-full flex items-center justify-center bg-cover bg-center bg-no-repeat overflow-hidden" 
       style="background-image: url('/images/ccislogo.png');">
    
    <div class="absolute inset-0 bg-[#221610]/80 backdrop-blur-sm"></div>

    <div class="relative z-10 w-full max-w-[95%] sm:max-w-md px-4 sm:px-6 py-4 sm:py-6 flex flex-col justify-center h-full max-h-225">
      
      <div class="flex flex-col items-center mb-6 sm:mb-6">
        <div class="w-20 h-20 sm:w-20 sm:h-20 mb-3 sm:mb-4 rounded-full bg-[#ec5b13]/10 flex items-center justify-center p-2 border border-[#ec5b13]/20">
          <img :src="'/images/logo-modified.png'" alt="CSUCCIS Logo" class="w-full h-full object-contain" />
        </div>
        <h1 class="text-2xl sm:text-2xl font-bold tracking-tight text-slate-100 text-center uppercase">CSUCCIS</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1 sm:mt-1.5 text-center font-medium">College of Computing and Information Sciences</p>
      </div>

      <div class="bg-[#221610]/40 border border-[#ec5b13]/10 backdrop-blur-md rounded-xl p-6 sm:p-7 shadow-2xl">
        
        <transition name="fade">
            <div v-if="errors.general" class="mb-4 p-2.5 sm:p-3 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center font-medium">
              {{ errors.general }}
            </div>
        </transition>

        <form @submit.prevent="submitForm" class="space-y-4">
          
          <div>
            <label class="block text-sm sm:text-sm font-medium text-slate-300 mb-1 ml-1">Email Address</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#ec5b13] transition-colors">
                <Mail :size="18" class="sm:w-4 sm:h-4" />
              </div>
              <input 
                v-model="email"
                type="email" 
                placeholder="name@university.edu"
                required
                :class="['block w-full pl-10 sm:pl-10 pr-4 py-2.5 bg-[#221610]/50 border rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all text-sm', 
                         errors.email ? 'border-red-500' : 'border-slate-700']"
              />
            </div>
            <p v-if="errors.email" class="text-red-500 text-xs mt-1 ml-1">{{ errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm sm:text-sm font-medium text-slate-300 mb-1 ml-1">Password</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#ec5b13] transition-colors">
                <Lock :size="18" class="sm:w-4 sm:h-4" />
              </div>
              <input 
                v-model="password"
                :type="showPassword ? 'text' : 'password'" 
                placeholder="••••••••"
                required
                :class="['block w-full pl-10 sm:pl-10 pr-10 py-2.5 bg-[#221610]/50 border rounded-lg text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all text-sm',
                         errors.password ? 'border-red-500' : 'border-slate-700']"
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300"
              >
                <EyeOff v-if="showPassword" :size="18" class="sm:w-4 sm:h-4" />
                <Eye v-else :size="18" class="sm:w-4 sm:h-4" />
              </button>
            </div>
            <p v-if="errors.password" class="text-red-500 text-xs mt-1 ml-1">{{ errors.password }}</p>
          </div>

          <div>
            <label class="block text-sm sm:text-sm font-medium text-slate-300 mb-1 ml-1">Position</label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#ec5b13] transition-colors">
                <BadgeCheck :size="18" class="sm:w-4 sm:h-4" />
              </div>
              <select 
                v-model="position"
                required
                :class="['block w-full pl-10 sm:pl-10 pr-10 py-2.5 bg-[#221610]/50 border rounded-lg text-slate-100 appearance-none focus:outline-none focus:ring-2 focus:ring-[#ec5b13]/50 focus:border-[#ec5b13] transition-all cursor-pointer text-sm',
                         errors.position ? 'border-red-500' : 'border-slate-700']"
              >
                <option disabled value="">Select your position</option>
                <option value="it_instructor">IT INSTRUCTOR</option>
                <option value="is_instructor">IS INSTRUCTOR</option>
                <option value="cs_instructor">CS INSTRUCTOR</option>
                <option value="lsg_officer">CCISLG OFFICER</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-500">
                <ChevronDown :size="18" class="sm:w-4 sm:h-4" />
              </div>
            </div>
            <p v-if="errors.position" class="text-red-500 text-xs mt-1 ml-1">{{ errors.position }}</p>
          </div>

          <div class="flex flex-row items-center justify-between w-full py-1">
            <div class="flex items-center">
              <input 
                v-model="remember"
                id="remember-me" 
                type="checkbox" 
                class="h-4 w-4 rounded border-slate-700 bg-[#221610]/50 text-[#ec5b13] focus:ring-[#ec5b13]/50"
              />
              <label for="remember-me" class="ml-2 block text-sm text-slate-400 mt-0.5">Remember me</label>
            </div>
            <a href="/forgot-password" class="text-sm font-medium text-[#ec5b13] hover:text-[#ec5b13]/80 transition-colors">Forgot password?</a>
          </div>

          <button 
            type="submit" 
            :disabled="isLoading"
            class="w-full bg-[#ec5b13] hover:bg-[#ec5b13]/90 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg shadow-[#ec5b13]/20 transform transition-all active:scale-[0.98] mt-2 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed text-sm sm:text-base"
          >
            <span v-if="!isLoading">Login to Portal</span>
            <span v-else>Processing...</span>
            <LogIn v-if="!isLoading" :size="18" class="sm:w-4 sm:h-4" />
          </button>

          <div class="text-center mt-4">
            <p class="text-slate-400 text-sm">
              Don't have an account? 
              <a href="/signup" class="text-[#ec5b13] font-semibold hover:underline decoration-2 underline-offset-4 ml-1">Sign Up</a>
            </p>
          </div>
        </form>
      </div>

      <footer class="mt-6 sm:mt-8 text-center">
        <p class="text-slate-500 text-[10px] sm:text-xs uppercase tracking-widest font-medium px-2">
          © 2026 CSU College of Computing and Information Sciences.<br class="block sm:hidden" /> All rights reserved.
        </p>
        <div class="flex justify-center gap-6 sm:gap-4 mt-4 sm:mt-3">
          <a href="#" class="text-slate-500 hover:text-[#ec5b13] transition-colors" title="Help"><HelpCircle :size="16" class="sm:w-4 sm:h-4" /></a>
          <a href="#" class="text-slate-500 hover:text-[#ec5b13] transition-colors" title="Policy"><ShieldCheck :size="16" class="sm:w-4 sm:h-4" /></a>
          <a href="#" class="text-slate-500 hover:text-[#ec5b13] transition-colors" title="Language"><Languages :size="16" class="sm:w-4 sm:h-4" /></a>
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