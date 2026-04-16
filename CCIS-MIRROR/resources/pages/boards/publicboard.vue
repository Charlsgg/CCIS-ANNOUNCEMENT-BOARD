<template>
  <div
    class="min-h-screen bg-gray-50 text-gray-900 p-4 md:p-8 font-sans selection:bg-orange-500/30 overflow-x-hidden relative">
    
    <div
      class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-orange-400/20 rounded-full blur-[120px] pointer-events-none">
    </div>
    <div
      class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-400/20 rounded-full blur-[120px] pointer-events-none">
    </div>

    <AppHeader />

    <main class="max-w-6xl mx-auto relative z-10">

      <section class="flex flex-col lg:flex-row flex-wrap gap-4 mb-12 items-center w-full">
        <button @click="goBack"
          class="flex items-center gap-2 px-6 py-3 rounded-full bg-white hover:bg-orange-50 border border-gray-200 hover:border-orange-500 transition-all duration-300 text-sm font-medium text-gray-700 group shrink-0 w-full lg:w-auto justify-center shadow-sm">
          <span
            class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
          <span>Back</span>
        </button>
        <div class="relative grow lg:grow-0 lg:min-w-37.5">
            <select v-model="selectedAuthor"
              class="w-full bg-white border border-gray-200 rounded-xl pl-4 pr-10 py-3.5 text-sm text-gray-800 hover:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all duration-300 appearance-none cursor-pointer outline-none shadow-sm">
              <option value="all" class="bg-white text-gray-900">All Authors</option>
              <option v-for="author in uniqueAuthors" :key="author" :value="author" class="bg-white text-gray-900">
                {{ author }}
              </option>
            </select>
            <span
              class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm">expand_more</span>
          </div>
        <div class="relative grow w-full lg:w-auto min-w-50">
          <input v-model="searchQuery"
            class="w-full bg-white border border-gray-200 rounded-xl px-12 py-3 text-lg text-gray-800 hover:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all duration-300 outline-none shadow-sm"
            placeholder="Search Announcements..." type="text" />
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">search</span>
        </div>

        <div class="flex gap-2 w-full lg:w-auto">
          <div class="relative grow lg:grow-0 lg:min-w-40">
            <select v-model="selectedDepartment"
              class="w-full bg-white border border-gray-200 rounded-xl pl-4 pr-10 py-3.5 text-sm text-gray-800 hover:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-all duration-300 appearance-none cursor-pointer outline-none shadow-sm">
              <option value="all" class="bg-white text-gray-900">All Departments</option>
              <option value="it" class="bg-white text-gray-900">IT Department</option>
              <option value="is" class="bg-white text-gray-900">IS Department</option>
              <option value="cs" class="bg-white text-gray-900">CS Department</option>
            </select>
            <span
              class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm">expand_more</span>
          </div>
        </div>

        <button @click="goToEvents"
          class="flex items-center gap-2 px-6 py-3 rounded-full bg-white hover:bg-orange-50 border border-gray-200 hover:border-orange-500 transition-all duration-300 text-sm font-medium text-gray-700 group shrink-0 w-full lg:w-auto justify-center shadow-sm">
          <span class="material-symbols-outlined text-sm group-hover:scale-110 transition-transform text-orange-500">event</span>
          <span>Events</span>
        </button>
      </section>

      <TransitionGroup name="list" tag="div" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <article v-for="(item, index) in filteredAnnouncements" :key="item.id"
          class="bg-white border border-gray-200 shadow-sm rounded-2xl p-6 flex flex-col h-full group relative overflow-hidden transition-all hover:shadow-md"
          :style="{ '--delay': `${index * 0.1}sec` }">

          <div class="flex items-center gap-4 mb-6 relative z-10">
            <div class="relative">
              <img v-if="item.author_avatar" :src="item.author_avatar"
                class="w-12 h-12 rounded-lg object-cover shadow-sm border border-gray-200 group-hover:border-orange-500/50 transition-all duration-500" />
              <div v-else
                :class="['w-12 h-12 rounded-lg flex items-center justify-center font-black text-xl italic text-white transition-all duration-500 group-hover:rotate-3 shadow-sm', getPosBg(item.author_type)]">
                {{ item.author_name ? item.author_name.substring(0, 1).toUpperCase() : 'A' }}
              </div>
              <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white"
                :class="getPosBg(item.author_type)"></div>
            </div>

            <div>
              <h3
                class="font-bold text-lg leading-tight group-hover:text-orange-500 transition-colors line-clamp-1 text-gray-900">
                {{ item.title }}</h3>
              <p :class="['text-[10px] font-semibold tracking-wider uppercase mt-1', getTextColor(item.author_type)]">
                {{ item.author_name }} • {{ item.date }}
              </p>
            </div>
          </div>

          <div class="text-sm text-gray-600 leading-relaxed grow line-clamp-4 relative z-10" v-html="item.content"></div>

          <div class="mt-8 flex justify-between items-center relative z-10 border-t border-gray-100 pt-4">
            <button @click="handleLike(item)"
              class="flex items-center gap-2 transition-all duration-300 group/like relative"
              :disabled="item.isCooldown || item.isProcessing" :class="[
                item.isAnimating ? 'text-orange-500' : 'text-gray-400 grayscale',
                item.isCooldown && !item.isAnimating ? 'cursor-not-allowed opacity-70' : 'hover:text-orange-500'
              ]">
              <span class="material-symbols-outlined text-xl transition-all duration-300"
                :class="{ 'fill-icon scale-125 animate-pop text-orange-500': item.isAnimating }">
                favorite
              </span>
              <span class="text-xs font-bold text-gray-700 group-hover/like:text-orange-500">{{ item.likes_count }}</span>
              <span v-if="item.isCooldown"
                class="absolute -top-4 left-0 text-[8px] font-black text-orange-500 tracking-tighter animate-pulse">
                {{ item.cooldownTimer }} secs
              </span>
            </button>

            <button @click="openModal(item)"
              class="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-500 hover:text-orange-600 transition-all flex items-center gap-2 group/btn relative z-10">
              Read More <span class="group-hover/btn:translate-x-2 transition-transform duration-300">→</span>
            </button>
          </div>
        </article>
      </TransitionGroup>

      <div v-if="filteredAnnouncements.length === 0" class="text-center py-20 text-gray-400">
        <span class="material-symbols-outlined text-5xl mb-4 block">search_off</span>
        <p>No announcements match your search or filters.</p>
      </div>
    </main>

    <button @click="toggleFullScreen"
      class="fixed bottom-6 left-6 z-50 flex items-center justify-center w-12 h-12 rounded-full bg-white hover:bg-gray-50 border border-gray-200 hover:border-orange-500 transition-all duration-300 group shadow-md"
      title="Toggle Fullscreen">
      <span class="material-symbols-outlined text-gray-600 group-hover:text-orange-500 transition-colors text-2xl">
        {{ isFullScreen ? 'fullscreen_exit' : 'fullscreen' }}
      </span>
    </button>

    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="isModalOpen" class="fixed inset-0 z-100 flex items-center justify-center p-4 md:p-6">
          <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

          <div v-if="selectedAnnouncement"
            class="relative w-full max-w-4xl rounded-3xl border border-gray-200 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden modal-scale bg-white">

            <div
              class="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] bg-orange-200/40 rounded-full blur-[100px] pointer-events-none">
            </div>
            <div
              class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] bg-blue-200/40 rounded-full blur-[100px] pointer-events-none">
            </div>

            <div class="flex items-center justify-between px-8 py-6 border-b border-gray-100 relative z-10 bg-white/80 backdrop-blur-sm">
              <div class="flex items-center gap-3">
                <div class="w-2 h-8 bg-orange-500 rounded-full shadow-[0_0_10px_rgba(249,115,22,0.3)]"></div>
                <h3 class="text-3xl font-light tracking-tight text-gray-900">
                  Announcement <span class="text-orange-500 font-bold">Details</span>
                </h3>
              </div>
              <button @click="closeModal"
                class="text-gray-400 hover:text-orange-500 hover:rotate-90 transition-all duration-300 rounded-full p-2 hover:bg-gray-100 shrink-0">
                <span class="material-symbols-outlined block text-[24px]">close</span>
              </button>
            </div>

            <div class="flex-1 overflow-y-auto p-8 md:p-10 custom-scrollbar relative z-10 space-y-10">

              <h1 class="text-3xl md:text-5xl font-bold tracking-tighter leading-tight text-gray-900 wrap-break-word">
                {{ selectedAnnouncement.title }}
              </h1>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div
                  class="flex items-center gap-4 bg-gray-50 hover:bg-gray-100 transition-colors px-5 py-4 rounded-2xl border border-gray-100 group/author relative overflow-hidden">
                  <div
                    class="absolute inset-0 bg-linear-to-r from-orange-500/5 to-transparent opacity-0 group-hover/author:opacity-100 transition-opacity">
                  </div>
                  <div
                    :class="['w-12 h-12 rounded-xl flex items-center justify-center font-bold shadow-sm text-white z-10 shrink-0 group-hover/author:scale-110 transition-transform', getPosBg(selectedAnnouncement.author_type)]">
                    <img v-if="selectedAnnouncement.author_avatar" :src="selectedAnnouncement.author_avatar"
                      class="w-full h-full rounded-xl object-cover" />
                    <span v-else class="text-xl">{{ selectedAnnouncement.author_name.substring(0, 1) }}</span>
                  </div>
                  <div class="flex flex-col z-10">
                    <span class="text-[10px] uppercase tracking-widest text-orange-500 font-bold mb-0.5">Posted
                      By</span>
                    <span class="text-gray-900 font-medium">{{ selectedAnnouncement.author_name }}</span>
                  </div>
                </div>

                <div
                  class="flex items-center gap-4 bg-gray-50 hover:bg-gray-100 transition-colors px-5 py-4 rounded-2xl border border-gray-100 group/details relative overflow-hidden">
                  <div
                    class="absolute inset-0 bg-linear-to-r from-blue-500/5 to-transparent opacity-0 group-hover/details:opacity-100 transition-opacity">
                  </div>
                  <div
                    class="bg-blue-100 p-3 rounded-xl shrink-0 group-hover/details:scale-110 transition-transform z-10">
                    <span class="material-symbols-outlined text-[24px] text-blue-600 block">info</span>
                  </div>
                  <div class="flex flex-col z-10 w-full">
                    <div class="flex justify-between items-center w-full mb-1">
                      <span class="text-[10px] uppercase tracking-widest text-blue-600 font-bold">Subject</span>
                      <span class="font-bold text-gray-800 text-[10px] px-2 py-0.5 rounded bg-white border border-gray-200">{{
                        selectedAnnouncement.topic }}</span>
                    </div>
                    <div class="w-full h-px bg-gray-200 my-1.5"></div>
                    <div class="flex justify-between items-center w-full">
                      <span class="text-[10px] uppercase tracking-widest text-gray-500 font-bold">Date</span>
                      <span class="font-medium text-gray-700 text-xs">{{ selectedAnnouncement.full_date }}</span>
                    </div>
                  </div>
                </div>

              </div>

              <div class="bg-gray-50 p-6 md:p-8 rounded-2xl border border-gray-200">
                <h5 class="text-[11px] font-bold tracking-widest uppercase text-gray-500 mb-4 flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                  Message Content
                </h5>
                <div
                  class="prose wrap-break-word max-w-none text-gray-800 leading-relaxed text-[15px] prose-p:mb-4 prose-a:text-orange-500"
                  v-html="selectedAnnouncement.content"></div>
              </div>

              <div v-if="selectedAnnouncement.attachments?.length" class="space-y-4">
                <div class="flex items-center justify-center mb-6 mt-8">
                  <div class="h-px bg-linear-to-r from-transparent via-orange-500/30 to-transparent w-full"></div>
                </div>
                <h5 class="text-[11px] font-bold tracking-widest uppercase text-gray-500 mb-4 flex items-center gap-2">
                  <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                  Attachments
                </h5>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div v-for="file in selectedAnnouncement.attachments" :key="file.id" @click="previewFile(file)"
                    class="group relative rounded-2xl overflow-hidden border border-gray-200 bg-white hover:border-orange-500 transition-all duration-300 cursor-pointer shadow-sm">

                    <img v-if="file.file_type.includes('image')" :src="file.file_path"
                      class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-700" />

                    <div v-else class="p-6 flex items-center gap-4 h-48 justify-center flex-col bg-gray-50">
                      <span
                        class="material-symbols-outlined text-4xl text-orange-500 group-hover:scale-110 transition-transform duration-300">
                        {{ file.file_type.includes('pdf') ? 'picture_as_pdf' : 'description' }}
                      </span>
                      <p class="text-[10px] font-mono text-gray-600 truncate w-full text-center px-4">{{
                        file.file_path.split('/').pop() }}</p>
                    </div>

                    <div
                      class="absolute inset-0 bg-white/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                      <span
                        class="bg-white text-gray-900 border border-gray-200 p-3 rounded-full material-symbols-outlined shadow-md">visibility</span>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <div
              class="px-8 py-5 border-t border-gray-200 bg-gray-50 flex justify-between items-center relative z-10 backdrop-blur-md">
              <button @click="handleLike(selectedAnnouncement)"
                class="flex items-center gap-2 transition-all duration-300 group/modal-like relative"
                :disabled="selectedAnnouncement.isCooldown || selectedAnnouncement.isProcessing" :class="[
                  selectedAnnouncement.isAnimating ? 'text-orange-500' : 'text-gray-400 grayscale',
                  selectedAnnouncement.isCooldown && !selectedAnnouncement.isAnimating ? 'cursor-not-allowed opacity-70' : 'hover:text-orange-500'
                ]">
                <span class="material-symbols-outlined text-2xl transition-all duration-300"
                  :class="{ 'fill-icon scale-125 animate-pop text-orange-500': selectedAnnouncement.isAnimating }">
                  favorite
                </span>
                <span class="text-sm font-bold text-gray-800 group-hover/modal-like:text-orange-500">{{ selectedAnnouncement.likes_count }}</span>
                <span v-if="selectedAnnouncement.isCooldown"
                  class="absolute -top-5 left-0 text-[10px] font-black text-orange-500 tracking-tighter animate-pulse w-max">
                  {{ selectedAnnouncement.cooldownTimer }} secs
                </span>
              </button>

              <button @click="closeModal"
                class="px-8 py-3 rounded-xl bg-white border border-gray-200 hover:bg-orange-500 hover:border-orange-500 hover:shadow-[0_0_15px_rgba(249,115,22,0.3)] hover:text-white transition-all duration-300 text-sm font-bold tracking-wide group flex items-center gap-2 text-gray-700">
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
          class="fixed inset-0 z-[110] flex items-center justify-center bg-white/95 backdrop-blur-md p-4">

          <button @click="activePreview = null"
            class="absolute top-6 right-6 z-[120] w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 transition-all hover:rotate-90 border border-gray-200 shadow-sm">
            <span class="material-symbols-outlined">close</span>
          </button>

          <div class="w-full h-full flex items-center justify-center">
            <img v-if="activePreview.file_type.includes('image')" :src="activePreview.file_path"
              class="max-w-full max-h-full object-contain animate-in zoom-in duration-300 shadow-xl border border-gray-200 rounded-lg" />

            <iframe v-else-if="activePreview.file_type.includes('pdf')" :src="activePreview.file_path"
              class="w-full h-full md:w-[85%] md:h-[90%] rounded-2xl border border-gray-200 bg-white shadow-2xl"
              frameborder="0"></iframe>

            <div v-else class="text-center bg-white border border-gray-200 p-12 rounded-3xl backdrop-blur-xl shadow-xl">
              <span class="material-symbols-outlined text-6xl text-orange-500 mb-4 block">draft</span>
              <p class="text-gray-600 mb-8 font-light tracking-wide">Preview not available for this file type.</p>

              <a :href="activePreview.file_path" download target="_blank"
                class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 hover:shadow-[0_0_15px_rgba(249,115,22,0.3)] text-white px-8 py-3 rounded-xl font-bold transition-all duration-300">
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
import AppHeader from '../components/boardheader.vue' 

// State
const announcements = ref([])
const searchQuery = ref('')
const selectedAuthor = ref('all')       
const selectedDepartment = ref('all')   
const isModalOpen = ref(false)
const selectedAnnouncement = ref(null)
const activePreview = ref(null)
const isFullScreen = ref(false)

// Extract Unique Authors for the dropdown
const uniqueAuthors = computed(() => {
  const authors = announcements.value.map(a => a.author_name).filter(Boolean);
  return [...new Set(authors)].sort((a, b) => a.localeCompare(b));
})

// Filter Logic
const filteredAnnouncements = computed(() => {
  const q = searchQuery.value.toLowerCase()
  const author = selectedAuthor.value
  const dept = selectedDepartment.value

  return announcements.value.filter(a => {
    const matchesSearch =
      a.title.toLowerCase().includes(q) ||
      (a.content && a.content.toLowerCase().includes(q)) ||
      (a.author_name && a.author_name.toLowerCase().includes(q));

    let matchesAuthor = true;
    if (author !== 'all') {
      matchesAuthor = a.author_name === author;
    }

    let matchesDept = true;
    if (dept !== 'all') {
      matchesDept = a.author_type && a.author_type.startsWith(dept + '_');
    }

    return matchesSearch && matchesAuthor && matchesDept;
  })
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
  window.location.href = '/announcements-board'
}

// Fullscreen Logic
const toggleFullScreen = async () => {
  try {
    if (!document.fullscreenElement) {
      await document.documentElement.requestFullscreen()
    } else {
      if (document.exitFullscreen) {
        await document.exitFullscreen()
      }
    }
  } catch (err) {
    console.error(`Error attempting to toggle fullscreen: ${err.message}`)
  }
}

const handleFullscreenChange = () => {
  isFullScreen.value = !!document.fullscreenElement
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

const getTextColor = (t) => ({ 'it_instructor': 'text-blue-600', 'is_instructor': 'text-green-600', 'cs_instructor': 'text-red-600', 'lsg_officer': 'text-amber-600', 'superadmin': 'text-red-600' }[t] || 'text-orange-500')
const getPosBg = (t) => ({ 'it_instructor': 'bg-blue-600', 'is_instructor': 'bg-green-600', 'cs_instructor': 'bg-red-600', 'lsg_officer': 'bg-amber-600' }[t] || 'bg-orange-500')

let fetchTimer

onMounted(() => {
  fetchAnnouncements()
  document.addEventListener('fullscreenchange', handleFullscreenChange)
  fetchTimer = setInterval(fetchAnnouncements, 30000)
})

onUnmounted(() => {
  clearInterval(fetchTimer)
  document.removeEventListener('fullscreenchange', handleFullscreenChange)
})
</script>