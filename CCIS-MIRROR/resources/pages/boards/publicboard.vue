<template>
  <div
    class="min-h-screen bg-black text-white p-4 md:p-8 font-sans selection:bg-orange-500/30 overflow-x-hidden relative">
    <div
      class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-orange-900/20 rounded-full blur-[120px] pointer-events-none">
    </div>
    <div
      class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-900/10 rounded-full blur-[120px] pointer-events-none">
    </div>

    <header class="flex flex-col md:flex-row justify-between items-start mb-16 gap-8 relative z-10">
      <div class="flex items-center gap-4 animate-in fade-in slide-in-from-left duration-700 group cursor-default">
        <div class="text-orange-500 group-hover:scale-110 transition-transform duration-500">
          <svg class="w-12 h-12 filter drop-shadow-[0_0_8px_rgba(249,115,22,0.4)]" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"
              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
          </svg>
        </div>
        <div>
          <div class="mb-1 text-[10px] font-bold tracking-[0.2em] uppercase opacity-70 text-orange-500">Butuan City
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-4xl font-light tracking-tighter">28°C</span>
            <span class="text-xs font-semibold tracking-widest uppercase opacity-60 text-orange-500">Partly
              Cloudy</span>
          </div>
        </div>
      </div>

      <div class="text-center md:absolute md:left-1/2 md:-translate-x-1/2 animate-in fade-in zoom-in duration-1000">
        <h1 class="text-7xl md:text-8xl font-light tracking-tighter leading-none glow-text font-mono">{{ currentTime }}
        </h1>
        <div class="uppercase tracking-[0.3em] text-sm font-medium mt-2 opacity-80 text-orange-500">
          {{ currentDate }}
        </div>
      </div>

      <div class="hidden lg:block animate-in fade-in slide-in-from-right duration-700">
        <div class="glass-card p-4 w-64 border-orange-500/10">
          <div class="flex justify-between items-center mb-4 text-orange-500">
            <h2 class="text-[10px] font-bold tracking-widest uppercase">{{ currentMonthYear }}</h2>
          </div>
          <div class="grid grid-cols-7 gap-1 text-center text-[9px] font-bold opacity-40 mb-2">
            <div>SU</div>
            <div>MO</div>
            <div>TU</div>
            <div>WE</div>
            <div>TH</div>
            <div>FR</div>
            <div>SA</div>
          </div>
          <div class="grid grid-cols-7 gap-1 text-center">
            <div v-for="empty in firstDayOfMonth" :key="'empty-' + empty" class="p-1"></div>
            <div v-for="d in daysInMonth" :key="d"
              :class="['p-1 text-[10px] transition-all duration-300',
                d === currentDay ? 'bg-orange-500 text-black rounded-full font-bold shadow-[0_0_15px_rgba(249,115,22,0.6)] scale-110' : 'opacity-60']">
              {{ d }}
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto relative z-10">
      <section class="flex flex-col md:flex-row break gap-4 mb-12 items-center">
        <button @click="goBack"
          class="flex items-center gap-2 px-6 py-3 rounded-full bg-white/5 hover:bg-orange-500/10 border border-white/10 hover:border-orange-500 transition-all duration-300 text-sm font-medium group shrink-0">
          <span
            class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
          <span>Back</span>
        </button>

        <div class="relative grow w-full">
          <input v-model="searchQuery"
            class="w-full glass-input rounded-xl px-12 py-3 text-lg hover:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all duration-300"
            placeholder="Search broadcasts..." type="text" />
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 opacity-40">search</span>
        </div>

        <button @click="goToEvents"
          class="flex items-center gap-2 px-6 py-3 rounded-full bg-white/5 hover:bg-orange-500/10 border border-white/10 hover:border-orange-500 transition-all duration-300 text-sm font-medium group shrink-0">
          <span class="material-symbols-outlined text-sm group-hover:scale-110 transition-transform">event</span>
          <span>Events</span>
        </button>
      </section>
      <TransitionGroup name="list" tag="div" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <article v-for="(item, index) in filteredAnnouncements" :key="item.id"
          class="glass-card p-6 flex flex-col h-full group relative overflow-hidden"
          :style="{ '--delay': `${index * 0.1}sec` }">

          <div class="flex items-center gap-4 mb-6 relative z-10">
            <div class="relative">
              <img v-if="item.author_avatar" :src="'/storage/' + item.author_avatar"
                class="w-12 h-12 rounded-lg object-cover shadow-lg border border-white/10 group-hover:border-orange-500/50 transition-all duration-500" />
              <div v-else
                :class="['w-12 h-12 rounded-lg flex items-center justify-center font-black text-xl italic transition-all duration-500 group-hover:rotate-3 shadow-lg', getPosBg(item.author_type)]">
                {{ item.author_name ? item.author_name.substring(0, 1).toUpperCase() : 'A' }}
              </div>
              <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-black"
                :class="getPosBg(item.author_type)"></div>
            </div>

            <div>
              <h3
                class="font-bold text-lg leading-tight group-hover:text-orange-400 transition-colors line-clamp-1 text-white">
                {{ item.title }}</h3>
              <p :class="['text-[10px] font-semibold tracking-wider uppercase mt-1', getTextColor(item.author_type)]">
                {{ formatPosition(item.author_type) }} • {{ item.date }}
              </p>
            </div>
          </div>

          <div class="text-sm opacity-70 leading-relaxed grow line-clamp-4 relative z-10" v-html="item.content"></div>

          <div class="mt-8 flex justify-between items-center relative z-10 border-t border-white/5 pt-4">
            <button @click="handleLike(item)"
              class="flex items-center gap-2 transition-all duration-300 group/like relative"
              :disabled="item.isCooldown || item.isProcessing" :class="[
                item.isAnimating ? 'text-orange-500' : 'text-white/40 grayscale',
                item.isCooldown && !item.isAnimating ? 'cursor-not-allowed opacity-70' : 'hover:text-orange-400'
              ]">
              <span class="material-symbols-outlined text-xl transition-all duration-300"
                :class="{ 'fill-icon scale-125 animate-pop': item.isAnimating }">
                favorite
              </span>
              <span class="text-xs font-bold">{{ item.likes_count }}</span>
              <span v-if="item.isCooldown"
                class="absolute -top-4 left-0 text-[8px] font-black text-orange-500 tracking-tighter animate-pulse">
                {{ item.cooldownTimer }} secs
              </span>
            </button>

            <button @click="openModal(item)"
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-500 hover:text-white transition-all flex items-center gap-2 group/btn relative z-10">
              Read More <span class="group-hover/btn:translate-x-2 transition-transform duration-300">→</span>
            </button>
          </div>
        </article>
      </TransitionGroup>
    </main>

    <Teleport to="body">
      <Transition name="fade">
        <div v-if="isModalOpen" class="fixed inset-0 z-100 flex items-center justify-center p-4 md:p-8">
          <div class="absolute inset-0 bg-black/90 backdrop-blur-xl" @click="closeModal"></div>

          <div v-if="selectedAnnouncement"
            class="glass-modal w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col relative animate-in zoom-in duration-300">
            <div class="p-6 border-b border-white/10 flex justify-between items-center bg-white/5">
              <div class="flex items-center gap-4">
                <div
                  :class="['w-10 h-10 rounded-lg flex items-center justify-center font-bold shadow-lg text-white', getPosBg(selectedAnnouncement.author_type)]">
                  <img v-if="selectedAnnouncement.author_avatar" :src="'/storage/' + selectedAnnouncement.author_avatar"
                    class="w-full h-full rounded-lg object-cover" />
                  <span v-else>{{ selectedAnnouncement.author_name.substring(0, 1) }}</span>
                </div>
                <div>
                  <h2 class="font-bold text-orange-500 uppercase tracking-widest text-[10px]">{{
                    selectedAnnouncement.topic }}</h2>
                  <p class="text-[10px] text-white/60">{{ selectedAnnouncement.author_name }} • {{
                    selectedAnnouncement.full_date }}</p>
                </div>
              </div>
              <button @click="closeModal"
                class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/10 transition-all group text-white">
                <span class="material-symbols-outlined group-hover:rotate-90 transition-transform">close</span>
              </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 md:p-10 custom-scrollbar">
              <h1 class="text-3xl md:text-5xl font-light tracking-tighter mb-8 leading-tight glow-text text-white">
                {{ selectedAnnouncement.title }}
              </h1>

              <div class="prose prose-invert break-all max-w-none text-white leading-relaxed text-lg mb-12"
                v-html="selectedAnnouncement.content"></div>

              <div v-if="selectedAnnouncement.attachments?.length" class="space-y-6">
                <div class="h-px bg-white/10 w-full"></div>
                <h3 class="text-[10px] font-bold uppercase tracking-[0.3em] text-orange-500/50">Attachments Gallery</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div v-for="file in selectedAnnouncement.attachments" :key="file.id" @click="previewFile(file)"
                    class="group relative rounded-xl overflow-hidden border border-white/10 bg-white/5 hover:border-orange-500/50 transition-all cursor-pointer">

                    <img v-if="file.file_type.includes('image')" :src="'/storage/' + file.file_path"
                      class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-700" />

                    <div v-else class="p-6 flex items-center gap-4 h-48 justify-center flex-col">
                      <span class="material-symbols-outlined text-4xl text-orange-500">
                        {{ file.file_type.includes('pdf') ? 'picture_as_pdf' : 'description' }}
                      </span>
                      <p class="text-[10px] font-mono text-white truncate w-full text-center px-4">{{
                        file.file_path.split('/').pop() }}</p>
                    </div>

                    <div
                      class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                      <span class="bg-white text-black p-3 rounded-full material-symbols-outlined">visibility</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-4 bg-white/5 border-t border-white/10 flex justify-between items-center px-8">
              <div class="flex items-center gap-6">
                <button @click="handleLike(selectedAnnouncement)"
                  class="flex items-center gap-2 transition-all duration-300 group/modal-like relative"
                  :disabled="selectedAnnouncement.isCooldown || selectedAnnouncement.isProcessing" :class="[
                    selectedAnnouncement.isAnimating ? 'text-orange-500' : 'text-white/40 grayscale',
                    selectedAnnouncement.isCooldown && !selectedAnnouncement.isAnimating ? 'cursor-not-allowed opacity-70' : 'hover:text-orange-400'
                  ]">
                  <span class="material-symbols-outlined text-xl transition-all duration-300"
                    :class="{ 'fill-icon scale-125 animate-pop': selectedAnnouncement.isAnimating }">
                    favorite
                  </span>
                  <span class="text-xs font-bold">{{ selectedAnnouncement.likes_count }}</span>
                  <span v-if="selectedAnnouncement.isCooldown"
                    class="absolute -top-4 left-0 text-[8px] font-black text-orange-500 tracking-tighter animate-pulse">
                    {{ selectedAnnouncement.cooldownTimer }} secs
                  </span>
                </button>
              </div>
              <button @click="closeModal"
                class="text-[10px] font-bold uppercase tracking-widest text-white hover:text-orange-500 transition-colors">Close</button>
            </div>
          </div>
        </div>
      </Transition>

      <Transition name="fade">
        <div v-if="activePreview"
          class="fixed inset-0 z-110 flex items-center justify-center bg-black/95 backdrop-blur-md p-4">
          <button @click="activePreview = null"
            class="absolute top-6 right-6 z-120 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
            <span class="material-symbols-outlined">close</span>
          </button>

          <div class="w-full h-full flex items-center justify-center">
            <img v-if="activePreview.file_type.includes('image')" :src="'/storage/' + activePreview.file_path"
              class="max-w-full max-h-full object-contain animate-in zoom-in duration-300" />

            <iframe v-else-if="activePreview.file_type.includes('pdf')" :src="'/storage/' + activePreview.file_path"
              class="w-full h-full md:w-[85%] md:h-[90%] rounded-lg border border-white/10 bg-white"
              frameborder="0"></iframe>

            <div v-else class="text-center">
              <span class="material-symbols-outlined text-6xl text-orange-500 mb-4">draft</span>
              <p class="text-white">Preview not available for this file type.</p>
              <a :href="'/storage/' + activePreview.file_path" download
                class="mt-4 inline-block bg-orange-500 text-black px-6 py-2 rounded-full font-bold">Download File</a>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
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
const isModalOpen = ref(false)
const selectedAnnouncement = ref(null)
const activePreview = ref(null)

const daysInMonth = computed(() => {
  const now = new Date(); return new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()
})
const firstDayOfMonth = computed(() => {
  const now = new Date(); return new Date(now.getFullYear(), now.getMonth(), 1).getDay()
})

const fetchAnnouncements = async () => {
  try {
    const response = await axios.get('/api/board-data')
    announcements.value = response.data.announcements.map(a => ({
      ...a,
      isLiked: false,
      isProcessing: false,
      isCooldown: false,
      cooldownTimer: 0,
      isAnimating: false // Add this new property
    }))
  } catch (e) { console.error("Sync Error", e) }
}

const goToEvents = () => {
  window.location.href = '/announcements-events'
}

const goBack = () => {
  window.history.back()
}
const handleLike = async (item) => {
  if (item.isProcessing || item.isCooldown) return

  const originalCount = item.likes_count

  // Optimistic UI Update
  item.isProcessing = true
  item.likes_count += 1

  // Trigger the quick visual pop
  item.isAnimating = true
  setTimeout(() => {
    item.isAnimating = false // Turns off the orange pop after half a second
  }, 500)

  // Start the 10-second lockdown
  startCooldown(item)

  try {
    const response = await axios.post(`/api/announcements/${item.id}/like`)
    item.likes_count = response.data.likes_count
  } catch (e) {
    item.likes_count = originalCount
    item.isCooldown = false
    item.cooldownTimer = 0
  } finally {
    item.isProcessing = false
  }
}

const startCooldown = (item) => {
  item.isCooldown = true
  item.cooldownTimer = 10

  const timer = setInterval(() => {
    item.cooldownTimer--
    if (item.cooldownTimer <= 0) {
      clearInterval(timer)
      item.isCooldown = false
    }
  }, 1000)
}


const filteredAnnouncements = computed(() => {
  const q = searchQuery.value.toLowerCase()
  return announcements.value.filter(a => a.title.toLowerCase().includes(q) || a.content?.toLowerCase().includes(q))
})

const openModal = (item) => {
  selectedAnnouncement.value = item
  isModalOpen.value = true
  document.body.style.overflow = 'hidden'
}

const closeModal = () => {
  isModalOpen.value = false
  activePreview.value = null
  document.body.style.overflow = 'auto'
}

const previewFile = (file) => {
  activePreview.value = file
}

const getTextColor = (t) => ({ 'it_instructor': 'text-blue-400', 'is_instructor': 'text-green-400', 'cs_instructor': 'text-red-400', 'lsg_officer': 'text-amber-400', 'superadmin': 'text-red-500' }[t] || 'text-orange-500')
const getPosBg = (t) => ({ 'it_instructor': 'bg-blue-600', 'is_instructor': 'bg-green-600', 'cs_instructor': 'bg-red-600', 'lsg_officer': 'bg-amber-600' }[t] || 'bg-orange-500')
const formatPosition = (pos) => pos ? pos.replace('_', ' ') : 'System'

const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true })
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }).toUpperCase()
  currentMonthYear.value = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }).toUpperCase()
}

let clockTimer, fetchTimer
onMounted(() => {
  fetchAnnouncements(); updateClock()
  clockTimer = setInterval(updateClock, 1000)
  fetchTimer = setInterval(fetchAnnouncements, 30000)
})
onUnmounted(() => { clearInterval(clockTimer); clearInterval(fetchTimer) })
</script>

<style scoped>
.glow-text {
  text-shadow: 0 0 30px rgba(249, 115, 22, 0.4);
}

.glass-card {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1.25rem;
  transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
}

.glass-card:hover {
  background: rgba(255, 255, 255, 0.07);
  border-color: rgba(249, 115, 22, 0.4);
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.glass-input {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  outline: none;
}

.glass-modal {
  background: rgba(10, 10, 10, 0.95);
  backdrop-filter: blur(40px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 2rem;
}

.fill-icon {
  font-variation-settings: 'FILL' 1;
}

@keyframes pop {
  0% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.4);
  }

  100% {
    transform: scale(1.25);
  }
}

.animate-pop {
  animation: pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

.list-enter-active {
  animation: slideIn 0.6s ease forwards;
  animation-delay: var(--delay);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(249, 115, 22, 0.3);
  border-radius: 20px;
}
</style>