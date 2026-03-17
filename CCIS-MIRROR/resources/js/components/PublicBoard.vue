<template>
  <div class="min-h-screen bg-black text-white p-8 font-sans selection:bg-orange-500/30">
    <header class="flex flex-col md:flex-row justify-between items-start mb-16 gap-8 relative z-10">
      
      <div class="flex items-center gap-4 animate-in fade-in slide-in-from-left duration-700">
        <div class="text-orange-500">
          <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
          </svg>
        </div>
        <div>
          <div class="mb-1 text-[10px] font-bold tracking-[0.2em] uppercase opacity-70 text-orange-500">Butuan City</div>
          <div class="flex items-baseline gap-2">
            <span class="text-4xl font-light">28°C</span>
            <span class="text-xs font-semibold tracking-widest uppercase opacity-60 text-orange-500">Partly Cloudy</span>
          </div>
        </div>
      </div>

      <div class="text-center md:absolute md:left-1/2 md:-translate-x-1/2 animate-in fade-in zoom-in duration-1000">
        <h1 class="text-8xl font-light tracking-tighter leading-none glow-text">{{ currentTime }}</h1>
        <div class="uppercase tracking-[0.3em] text-sm font-medium mt-2 opacity-80 text-orange-500">
          {{ currentDate }}
        </div>
      </div>

      <div class="hidden lg:block animate-in fade-in slide-in-from-right duration-700">
        <div class="glass-card p-4 w-64">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-[10px] font-bold tracking-widest uppercase text-orange-500">{{ currentMonthYear }}</h2>
          </div>
          <div class="grid grid-cols-7 gap-1 text-center text-[9px] font-bold opacity-40 mb-2">
            <div>SU</div><div>MO</div><div>TU</div><div>WE</div><div>TH</div><div>FR</div><div>SA</div>
          </div>
          <div class="grid grid-cols-7 gap-1 text-center">
             <div v-for="d in 31" :key="d" :class="['p-1 text-[10px]', d === currentDay ? 'bg-orange-500 rounded-full font-bold shadow-[0_0_10px_rgba(249,115,22,0.5)]' : 'opacity-60']">
               {{ d }}
             </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto relative z-10">
      <section class="flex flex-col md:flex-row gap-4 mb-12 items-center">
        <button @click="$router?.back()" class="flex items-center gap-2 px-6 py-3 rounded-full bg-white/5 hover:bg-orange-500/10 border border-white/10 hover:border-orange-500 transition-all duration-300 text-sm font-medium group shrink-0">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
          <span>Back</span>
        </button>
        
        <div class="relative grow w-full">
          <input 
            v-model="searchQuery"
            class="w-full glass-input rounded-xl px-12 py-3 text-lg hover:border-orange-500 transition-all duration-300" 
            placeholder="Search announcements..." 
            type="text"
          />
          <svg class="h-5 w-5 absolute left-4 top-1/2 -translate-y-1/2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
        </div>

        <button class="flex items-center gap-2 px-6 py-3 rounded-full bg-white/5 hover:bg-orange-500/10 border border-white/10 hover:border-orange-500 transition-all duration-300 text-sm font-medium group shrink-0">
          <span class="material-symbols-outlined text-lg">event</span>
          <span>Events</span>
        </button>
      </section>

      <TransitionGroup 
        name="list" 
        tag="div" 
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
      >
        <article v-for="(item, index) in filteredAnnouncements" :key="item.id" 
                 class="glass-card p-6 flex flex-col h-full group"
                 :style="{ '--delay': `${index * 0.1}s` }">
          
          <div class="flex items-center gap-4 mb-6">
            <div :class="['w-12 h-12 rounded-lg flex items-center justify-center font-black text-xl italic transition-all duration-500', getPosBg(item.author_position)]">
              {{ item.author_position ? item.author_position.substring(0,2).toUpperCase() : 'SY' }}
            </div>
            <div>
              <h3 class="font-bold text-lg leading-tight group-hover:text-orange-400 transition-colors">{{ item.title }}</h3>
              <p :class="['text-[10px] font-semibold tracking-wider uppercase mt-1', getTextColor(item.author_position)]">
                {{ formatPosition(item.author_position) }} • {{ item.time_ago }}
              </p>
            </div>
          </div>

          <div class="text-sm opacity-70 leading-relaxed grow ql-display line-clamp-4" v-html="item.content"></div>

          <button class="mt-6 text-[10px] font-bold uppercase tracking-[0.2em] text-orange-500 hover:text-white transition-all flex items-center gap-2">
            Read More <span class="group-hover:translate-x-2 transition-transform">→</span>
          </button>
        </article>
      </TransitionGroup>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const announcements = ref([])
const searchQuery = ref('')
const currentTime = ref('')
const currentDate = ref('')
const currentDay = ref(new Date().getDate())
const currentMonthYear = ref('')

const fetchAnnouncements = async () => {
  try {
    const response = await axios.get('/api/announcements')
    announcements.value = response.data
  } catch (e) { console.error("Sync Error", e) }
}

const filteredAnnouncements = computed(() => {
  return announcements.value.filter(a => 
    a.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (a.content && a.content.toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

const getTextColor = (pos) => {
  const colors = {
    'it_instructor': 'text-blue-400',
    'is_instructor': 'text-orange-500',
    'cs_instructor': 'text-purple-400',
    'ccislg_officer': 'text-yellow-400',
    'superadmin': 'text-red-500'
  }
  return colors[pos] || 'text-orange-500'
}

const getPosBg = (pos) => {
  const bgs = {
    'it_instructor': 'bg-blue-600',
    'is_instructor': 'bg-orange-600',
    'cs_instructor': 'bg-purple-600',
    'ccislg_officer': 'bg-yellow-600',
    'superadmin': 'bg-red-600'
  }
  return bgs[pos] || 'bg-orange-500'
}

const formatPosition = (pos) => pos ? pos.replace('_', ' ') : 'System'

const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false })
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }).toUpperCase()
  currentMonthYear.value = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }).toUpperCase()
}

let clockTimer;
onMounted(() => {
  fetchAnnouncements()
  updateClock()
  clockTimer = setInterval(updateClock, 1000)
  setInterval(fetchAnnouncements, 30000)
})
onUnmounted(() => clearInterval(clockTimer))
</script>

<style scoped>
/* Glassmorphism Styles */
.glow-text {
  text-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
}

.glass-card {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}

.glass-card:hover {
  background: rgba(255, 255, 255, 0.07);
  border: 1px solid rgba(249, 115, 22, 0.3);
  transform: translateY(-5px);
}

.glass-input {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  outline: none !important;
}

.glass-input:focus {
  border-color: #F97316 !important;
  background: rgba(255, 255, 255, 0.08);
}

/* Animations */
.list-enter-active {
  animation: slideIn 0.6s ease forwards;
  animation-delay: var(--delay);
}

@keyframes slideIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.line-clamp-4 {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>