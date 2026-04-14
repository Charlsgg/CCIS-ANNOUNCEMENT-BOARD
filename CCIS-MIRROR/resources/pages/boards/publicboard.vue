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
          <div class="mb-1 text-[10px] font-bold tracking-[0.2em] uppercase opacity-70 text-orange-500">
            {{ weatherCity }}
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-4xl font-light tracking-tighter">{{ weatherTemp }}°C</span>
            <span class="text-xs font-semibold tracking-widest uppercase opacity-60 text-orange-500">
              {{ weatherDesc }}
            </span>
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
            placeholder="Search Announcements..." type="text" />
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
              <img v-if="item.author_avatar" :src="item.author_avatar"
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
                {{ item.author_name }} • {{ item.date }}
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

    <button @click="toggleFullScreen"
      class="fixed bottom-6 left-6 z-50 flex items-center justify-center w-12 h-12 rounded-full bg-black/40 hover:bg-orange-500/20 border border-white/10 hover:border-orange-500 transition-all duration-300 group shadow-[0_0_15px_rgba(0,0,0,0.5)] backdrop-blur-md"
      title="Toggle Fullscreen">
      <span class="material-symbols-outlined text-white/70 group-hover:text-orange-500 transition-colors text-2xl">
        {{ isFullScreen ? 'fullscreen_exit' : 'fullscreen' }}
      </span>
    </button>

    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isModalOpen" class="fixed inset-0 z-100 flex items-center justify-center p-4 md:p-6">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" @click="closeModal"></div>

          <div v-if="selectedAnnouncement"
            class="relative w-full max-w-4xl rounded-3xl border border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.6)] backdrop-blur-2xl flex flex-col max-h-[90vh] overflow-hidden modal-scale"
            style="background: linear-gradient(145deg, rgba(24,24,27,0.85) 0%, rgba(9,9,11,0.95) 100%);">

            <div
              class="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] bg-orange-500/15 rounded-full blur-[100px] pointer-events-none mix-blend-screen">
            </div>
            <div
              class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] bg-blue-500/10 rounded-full blur-[100px] pointer-events-none mix-blend-screen">
            </div>

            <div class="flex items-center justify-between px-8 py-6 border-b border-white/5 relative z-10 bg-white/2">
              <div class="flex items-center gap-3">
                <div class="w-2 h-8 bg-orange-500 rounded-full shadow-[0_0_10px_rgba(249,115,22,0.5)]"></div>
                <h3 class="text-3xl font-light tracking-tight text-white">
                  Announcement <span class="text-orange-500 font-bold">Details</span>
                </h3>
              </div>
              <button @click="closeModal"
                class="text-white/40 hover:text-orange-500 hover:rotate-90 transition-all duration-300 rounded-full p-2 hover:bg-white/5 shrink-0">
                <span class="material-symbols-outlined block text-[24px]">close</span>
              </button>
            </div>

            <div class="flex-1 overflow-y-auto p-8 md:p-10 custom-scrollbar relative z-10 space-y-10">

              <h1 class="text-3xl md:text-5xl font-bold tracking-tighter leading-tight text-white wrap-break-word">
                {{ selectedAnnouncement.title }}
              </h1>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div
                  class="flex items-center gap-4 bg-white/3 hover:bg-white/6 transition-colors px-5 py-4 rounded-2xl border border-white/5 group/author relative overflow-hidden">
                  <div
                    class="absolute inset-0 bg-linear-to-r from-orange-500/10 to-transparent opacity-0 group-hover/author:opacity-100 transition-opacity">
                  </div>
                  <div
                    :class="['w-12 h-12 rounded-xl flex items-center justify-center font-bold shadow-lg text-white z-10 shrink-0 group-hover/author:scale-110 transition-transform', getPosBg(selectedAnnouncement.author_type)]">
                    <img v-if="selectedAnnouncement.author_avatar" :src="selectedAnnouncement.author_avatar"
                      class="w-full h-full rounded-xl object-cover" />
                    <span v-else class="text-xl">{{ selectedAnnouncement.author_name.substring(0, 1) }}</span>
                  </div>
                  <div class="flex flex-col z-10">
                    <span class="text-[10px] uppercase tracking-widest text-orange-500/70 font-bold mb-0.5">Posted
                      By</span>
                    <span class="text-white/90 font-medium">{{ selectedAnnouncement.author_name }}</span>
                  </div>
                </div>

                <div
                  class="flex items-center gap-4 bg-white/3 hover:bg-white/6 transition-colors px-5 py-4 rounded-2xl border border-white/5 group/details relative overflow-hidden">
                  <div
                    class="absolute inset-0 bg-linear-to-r from-blue-500/10 to-transparent opacity-0 group-hover/details:opacity-100 transition-opacity">
                  </div>
                  <div
                    class="bg-blue-500/10 p-3 rounded-xl shrink-0 group-hover/details:scale-110 transition-transform z-10">
                    <span class="material-symbols-outlined text-[24px] text-blue-400 block">info</span>
                  </div>
                  <div class="flex flex-col z-10 w-full">
                    <div class="flex justify-between items-center w-full mb-1">
                      <span class="text-[10px] uppercase tracking-widest text-blue-400/70 font-bold">Subject</span>
                      <span class="font-bold text-white/90 text-[10px] px-2 py-0.5 rounded bg-white/10">{{
                        selectedAnnouncement.topic }}</span>
                    </div>
                    <div class="w-full h-px bg-white/5 my-1.5"></div>
                    <div class="flex justify-between items-center w-full">
                      <span class="text-[10px] uppercase tracking-widest text-white/40 font-bold">Date</span>
                      <span class="font-medium text-white/60 text-xs">{{ selectedAnnouncement.full_date }}</span>
                    </div>
                  </div>
                </div>

              </div>

              <div class="bg-black/20 p-6 md:p-8 rounded-2xl border border-white/5">
                <h5 class="text-[11px] font-bold tracking-widest uppercase text-white/30 mb-4 flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-white/20"></span>
                  Message Content
                </h5>
                <div
                  class="prose prose-invert wrap-break-word max-w-none text-white/80 leading-relaxed text-[15px] prose-p:mb-4 prose-a:text-orange-400"
                  v-html="selectedAnnouncement.content"></div>
              </div>

              <div v-if="selectedAnnouncement.attachments?.length" class="space-y-4">
                <div class="flex items-center justify-center mb-6 mt-8">
                  <div class="h-px bg-linear-to-r from-transparent via-orange-500/20 to-transparent w-full"></div>
                </div>
                <h5 class="text-[11px] font-bold tracking-widest uppercase text-white/30 mb-4 flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-white/20"></span>
                  Attachments
                </h5>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div v-for="file in selectedAnnouncement.attachments" :key="file.id" @click="previewFile(file)"
                    class="group relative rounded-2xl overflow-hidden border border-white/5 bg-white/3 hover:bg-white/6 hover:border-orange-500/50 transition-all duration-300 cursor-pointer">

                    <img v-if="file.file_type.includes('image')" :src="file.file_path"
                      class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-700" />

                    <div v-else class="p-6 flex items-center gap-4 h-48 justify-center flex-col">
                      <span
                        class="material-symbols-outlined text-4xl text-orange-500 group-hover:scale-110 transition-transform duration-300">
                        {{ file.file_type.includes('pdf') ? 'picture_as_pdf' : 'description' }}
                      </span>
                      <p class="text-[10px] font-mono text-white/70 truncate w-full text-center px-4">{{
                        file.file_path.split('/').pop() }}</p>
                    </div>

                    <div
                      class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                      <span
                        class="bg-white text-black p-3 rounded-full material-symbols-outlined shadow-[0_0_20px_rgba(255,255,255,0.3)]">visibility</span>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <div
              class="px-8 py-5 border-t border-white/5 bg-black/40 flex justify-between items-center relative z-10 backdrop-blur-md">
              <button @click="handleLike(selectedAnnouncement)"
                class="flex items-center gap-2 transition-all duration-300 group/modal-like relative"
                :disabled="selectedAnnouncement.isCooldown || selectedAnnouncement.isProcessing" :class="[
                  selectedAnnouncement.isAnimating ? 'text-orange-500' : 'text-white/40 grayscale',
                  selectedAnnouncement.isCooldown && !selectedAnnouncement.isAnimating ? 'cursor-not-allowed opacity-70' : 'hover:text-orange-400'
                ]">
                <span class="material-symbols-outlined text-2xl transition-all duration-300"
                  :class="{ 'fill-icon scale-125 animate-pop': selectedAnnouncement.isAnimating }">
                  favorite
                </span>
                <span class="text-sm font-bold">{{ selectedAnnouncement.likes_count }}</span>
                <span v-if="selectedAnnouncement.isCooldown"
                  class="absolute -top-5 left-0 text-[10px] font-black text-orange-500 tracking-tighter animate-pulse w-max">
                  {{ selectedAnnouncement.cooldownTimer }} secs
                </span>
              </button>

              <button @click="closeModal"
                class="px-8 py-3 rounded-xl bg-white/5 hover:bg-orange-500 hover:shadow-[0_0_20px_rgba(249,115,22,0.4)] hover:text-black transition-all duration-300 text-sm font-bold tracking-wide group flex items-center gap-2 text-white">
                <span>Close</span>
                <span
                  class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </button>
            </div>

          </div>
        </div>
      </Transition>

      <Transition name="fade">
        <div v-if="activePreview"
          class="fixed inset-0 z-110 flex items-center justify-center bg-black/95 backdrop-blur-md p-4">

          <button @click="activePreview = null"
            class="absolute top-6 right-6 z-120 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all hover:rotate-90">
            <span class="material-symbols-outlined">close</span>
          </button>

          <div class="w-full h-full flex items-center justify-center">
            <img v-if="activePreview.file_type.includes('image')" :src="activePreview.file_path"
              class="max-w-full max-h-full object-contain animate-in zoom-in duration-300" />

            <iframe v-else-if="activePreview.file_type.includes('pdf')" :src="activePreview.file_path"
              class="w-full h-full md:w-[85%] md:h-[90%] rounded-2xl border border-white/10 bg-white shadow-[0_0_50px_rgba(0,0,0,0.5)]"
              frameborder="0"></iframe>

            <div v-else class="text-center bg-white/3 border border-white/10 p-12 rounded-3xl backdrop-blur-xl">
              <span class="material-symbols-outlined text-6xl text-orange-500 mb-4 block">draft</span>
              <p class="text-white/70 mb-8 font-light tracking-wide">Preview not available for this file type.</p>

              <a :href="activePreview.file_path" download target="_blank"
                class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 hover:shadow-[0_0_20px_rgba(249,115,22,0.4)] text-black px-8 py-3 rounded-xl font-bold transition-all duration-300">
                <span class="material-symbols-outlined text-[20px]">download</span>
                Download File
              </a>
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

// State
const announcements = ref([])
const searchQuery = ref('')
const currentTime = ref('')
const currentDate = ref('')
const currentDay = ref(new Date().getDate())
const currentMonthYear = ref('')
const isModalOpen = ref(false)
const selectedAnnouncement = ref(null)
const activePreview = ref(null)
const isFullScreen = ref(false) // Added fullscreen state

// Computed
const daysInMonth = computed(() => {
  const now = new Date(); return new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()
})
const firstDayOfMonth = computed(() => {
  const now = new Date(); return new Date(now.getFullYear(), now.getMonth(), 1).getDay()
})

const filteredAnnouncements = computed(() => {
  const q = searchQuery.value.toLowerCase()
  return announcements.value.filter(a =>
    a.title.toLowerCase().includes(q) ||
    (a.content && a.content.toLowerCase().includes(q)) ||
    (a.author_name && a.author_name.toLowerCase().includes(q))
  )
})

// Methods
const fetchAnnouncements = async () => {
  try {
    const response = await axios.get('board-data')
    announcements.value = response.data.announcements.map(a => ({
      ...a,
      isLiked: false,
      isProcessing: false,
      isCooldown: false,
      cooldownTimer: 0,
      isAnimating: false
    }))
  } catch (e) { console.error("Sync Error", e) }
}

const goToEvents = () => {
  window.location.href = '/announcements-events'
}

const goBack = () => {
  // No history checks, no double-click bugs. 
  // It simply forces them to the announcements board.
  window.location.href = '/announcements-board'
}

// Fullscreen Toggle logic
const toggleFullScreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch((err) => {
      console.error(`Error attempting to enable fullscreen: ${err.message}`)
    })
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen()
    }
  }
}

const handleLike = async (item) => {
  if (item.isProcessing || item.isCooldown) return

  const originalCount = item.likes_count
  item.isProcessing = true
  item.likes_count += 1
  item.isAnimating = true

  setTimeout(() => {
    item.isAnimating = false
  }, 500)

  startCooldown(item)

  try {
    const response = await axios.post(`/announcements/${item.id}/like`)
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

const getWeatherDescription = (code) => {
  const weatherCodes = {
    0: 'Clear Sky', 1: 'Mainly Clear', 2: 'Partly Cloudy', 3: 'Overcast',
    45: 'Fog', 48: 'Depositing Rime Fog', 51: 'Light Drizzle', 53: 'Moderate Drizzle',
    55: 'Dense Drizzle', 56: 'Light Freezing Drizzle', 57: 'Dense Freezing Drizzle',
    61: 'Slight Rain', 63: 'Moderate Rain', 65: 'Heavy Rain', 66: 'Light Freezing Rain',
    67: 'Heavy Freezing Rain', 71: 'Slight Snow Fall', 73: 'Moderate Snow Fall',
    75: 'Heavy Snow Fall', 77: 'Snow Grains', 80: 'Slight Rain Showers',
    81: 'Moderate Rain Showers', 82: 'Violent Rain Showers', 85: 'Slight Snow Showers',
    86: 'Heavy Snow Showers', 95: 'Thunderstorm', 96: 'Thunderstorm with Slight Hail',
    99: 'Thunderstorm with Heavy Hail'
  }
  return weatherCodes[code] || 'Unknown Conditions'
}
const fetchWeather = async () => {
  try {
    const url = "https://api.open-meteo.com/v1/forecast?latitude=8.9492&longitude=125.5436&current_weather=true&timezone=Asia%2FManila";
    const response = await fetch(url);

    if (!response.ok) {
      throw new Error(`Weather API is down! Status: ${response.status}`);
    }

    const data = await response.json();

    weatherCity.value = 'Butuan City';
    weatherTemp.value = Math.round(data.current_weather.temperature);
    weatherDesc.value = getWeatherDescription(data.current_weather.weathercode);

  } catch (e) {
    console.error("Weather Sync Error:", e.message);
    weatherTemp.value = '--';
    weatherDesc.value = 'Weather Unavailable';
  }
}

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

const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true })
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }).toUpperCase()
  currentMonthYear.value = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }).toUpperCase()
}

let clockTimer, fetchTimer, weatherTimer
onMounted(() => {
  fetchAnnouncements()
  updateClock()
  fetchWeather()
  
  // Listen for fullscreen changes (e.g. user hitting ESC key)
  document.addEventListener('fullscreenchange', () => {
    isFullScreen.value = !!document.fullscreenElement
  })

  clockTimer = setInterval(updateClock, 1000)
  fetchTimer = setInterval(fetchAnnouncements, 5000)
  weatherTimer = setInterval(fetchWeather, 1800000)
})

onUnmounted(() => {
  clearInterval(clockTimer)
  clearInterval(fetchTimer)
  clearInterval(weatherTimer)
  
  // Good practice to remove event listeners
  document.removeEventListener('fullscreenchange', () => {
    isFullScreen.value = !!document.fullscreenElement
  })
})
</script>