<template>
  <div class="min-h-screen bg-[#020202] text-slate-300 p-8 overflow-hidden font-sans selection:bg-indigo-500/30">
    
    <div class="pointer-events-none fixed inset-0 z-50 bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,118,0.06))] bg-size-[100%_4px,3px_100%]"></div>

    <header class="relative z-10 flex justify-between items-end mb-16 border-b border-white/5 pb-8">
      <div class="animate-in fade-in slide-in-from-left duration-1000">
        <div class="flex items-center gap-3">
          <div class="w-3 h-3 bg-indigo-500 rounded-full animate-ping"></div>
          <h1 class="text-white text-3xl font-extralight tracking-[0.3em] uppercase">System_Output</h1>
        </div>
        <p class="text-[10px] font-mono opacity-40 mt-2 tracking-widest">ENCRYPTED UP-LINK STABLE</p>
      </div>
      
      <div class="text-right font-mono animate-in fade-in slide-in-from-right duration-1000">
        <div class="text-4xl text-white font-light tracking-tighter">{{ currentTime }}</div>
        <div class="text-[10px] text-indigo-400/60 uppercase mt-1">{{ currentDate }}</div>
      </div>
    </header>

    <main class="relative z-10 max-w-6xl mx-auto">
      <TransitionGroup 
        name="list" 
        tag="div" 
        class="grid grid-cols-1 md:grid-cols-2 gap-10"
      >
        <div v-for="(item, index) in announcements" :key="item.id" 
             class="announcement-card group"
             :style="{ '--delay': `${index * 0.1}s` }">
          
          <div class="relative bg-linear-to-br from-[#111] to-[#080808] border border-white/10 rounded-xl p-8 transition-all duration-700 hover:border-indigo-500/50 hover:shadow-[0_0_30px_rgba(79,70,229,0.1)]">
            
            <div class="absolute -left-px top-10 h-12 w-0.75 shadow-[0_0_10px_currentColor]" 
                 :class="getTextColor(item.author_position)"></div>

            <div class="flex justify-between items-start mb-6">
              <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] mb-1" :class="getTextColor(item.author_position)">
                  {{ formatPosition(item.author_position) }}
                </span>
                <span class="text-[9px] opacity-30 font-mono italic">SECURE_LOG_{{ item.id }}</span>
              </div>
              <span class="text-[10px] opacity-40 font-mono tabular-nums">{{ item.time_ago }}</span>
            </div>

            <h3 class="text-2xl font-semibold text-white/90 mb-4 group-hover:text-white transition-colors">
              {{ item.title }}
            </h3>

            <div class="ql-display opacity-70 group-hover:opacity-100 transition-opacity duration-500" v-html="item.content"></div>

            <div class="mt-8 flex items-center justify-between opacity-20 group-hover:opacity-50 transition-all">
              <span class="text-[9px] font-bold tracking-[0.3em] uppercase">Data Packet Verified</span>
              <div class="h-px grow mx-4 bg-white/20"></div>
              <i class="fa-solid fa-microchip text-xs"></i>
            </div>
          </div>
        </div>
      </TransitionGroup>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const announcements = ref([])
const loading = ref(true)
const currentTime = ref('')
const currentDate = ref('')

const fetchAnnouncements = async () => {
    try {
        const response = await axios.get('/api/announcements')
        // Simple logic to trigger the TransitionGroup: 
        // If the data is actually different, Vue will handle the movement animations
        announcements.value = response.data
    } finally {
        loading.value = false
    }
}

const getTextColor = (pos) => {
    const colors = {
        'it_instructor': 'text-blue-400',
        'is_instructor': 'text-orange-400',
        'cs_instructor': 'text-purple-400',
        'ccislg_officer': 'text-yellow-400',
        'superadmin': 'text-red-500'
    }
    return colors[pos] || 'text-indigo-400'
}

const formatPosition = (pos) => pos ? pos.replace('_', ' ') : 'System'

const updateClock = () => {
    const now = new Date()
    currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false })
    currentDate.value = now.toLocaleDateString([], { weekday: 'long', month: 'short', day: 'numeric' }).toUpperCase()
}

let clockTimer;
onMounted(() => {
    fetchAnnouncements()
    updateClock()
    clockTimer = setInterval(updateClock, 1000)
    setInterval(fetchAnnouncements, 30000) // Auto-refresh for headless feel
})
onUnmounted(() => clearInterval(clockTimer))
</script>

<style scoped>
/* Staggered Entry Animation */
.announcement-card {
  animation: slideIn 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
  animation-delay: var(--delay);
  opacity: 0;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Vue List Transitions (for when items update) */
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
.list-move {
  transition: transform 0.6s ease;
}

/* Rich Text Styling */
.ql-display :deep(img) {
  border-radius: 8px;
  filter: saturate(1.2) brightness(0.9);
  transition: filter 0.5s;
}
.announcement-card:hover .ql-display :deep(img) {
  filter: saturate(1.5) brightness(1.1);
}
.ql-display :deep(blockquote) {
  border-left: 2px solid #6366f1;
  padding-left: 1rem;
  font-style: italic;
  margin: 1rem 0;
}
</style>