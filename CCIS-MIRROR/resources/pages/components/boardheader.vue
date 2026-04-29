<template>
  <main
    class="w-full h-[85vh]  p-2 md:p-3 lg:p-4 max-w-400 mx-auto font-sans text-slate-900 overflow-hidden flex flex-col gap-6 box-border relative">

    <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-orange-400/20 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <button @click="isQrModalOpen = true" 
      class="fixed bottom-6 right-6 z-50 bg-orange-500 text-white p-4 rounded-full shadow-lg hover:bg-orange-600 transition-all hover:scale-110 flex items-center justify-center hover:shadow-orange-500/50">
      <span class="material-symbols-outlined text-3xl">qr_code_scanner</span>
    </button>

    <div class="grid grid-cols-12 gap-6 h-full min-h-0 relative z-10">

      <section
        class="col-span-12 lg:col-span-8 relative overflow-hidden rounded-xl  border-gray-300 hover:border-orange-500 bg-slate-900 h-full group">

        <img alt="Announcements Background"
          class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:scale-105 transition-transform duration-10000 ease-out"
          :src="activeAnnouncement ? getAnnouncementImage(activeAnnouncement) : fallbackImage" />

        <div class="absolute inset-0 bg-linear-to-t from-slate-950 via-slate-900/60 to-transparent"></div>

        <div class="relative h-full flex flex-col justify-between p-8 md:p-12">

          <div class="flex justify-between items-center shrink-0 w-full relative z-10">
            <span
              class="bg-orange-500 text-white px-5 py-2.5 text-[14px] font-bold tracking-widest uppercase rounded-sm flex items-center gap-3 shadow-lg shadow-orange-500/20">
              <span class="w-2.5 h-2.5 bg-white rounded-full pulse-dot"></span>
              Announcements
            </span>

            <div v-if="announcements.length > 0"
              class="flex gap-2
               backdrop-blur-md px-4 py-3 rounded-full border border-white/10 shrink-0 shadow-lg">
              <button v-for="(announcement, index) in announcements" :key="announcement.id"
                @click="setAnnouncementIndex(index)" class="h-2 rounded-full transition-all duration-500 shrink-0"
                :class="currentAnnouncementIndex === index ? 'w-8 bg-orange-500' : 'w-2 bg-white/30 hover:bg-white/60'"></button>
            </div>
          </div>

          <div class="flex flex-col justify-end mt-auto w-full relative z-10">
            <div v-if="activeAnnouncement" class="w-full">
              <transition name="slide-up" mode="out-in">
                <div :key="currentAnnouncementIndex" class="flex flex-col justify-end">
                  <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 max-w-4xl leading-[1.1] tracking-tight line-clamp-4 drop-shadow-xl">
                    {{ activeAnnouncement.title }}
                  </h1>
                  <p class="text-base md:text-lg lg:text-xl text-slate-300 max-w-3xl line-clamp-3 leading-relaxed">
                    {{ activeAnnouncement.content }}
                  </p>
                  <div class="mt-5 flex items-center gap-4">
                    <img v-if="activeAnnouncement.author_avatar" :src="activeAnnouncement.author_avatar"
                      class="w-8 h-8 rounded-full border border-white/20" alt="Author Avatar" />
                    <span class="text-sm font-medium text-slate-400">Posted by {{ activeAnnouncement.author_name }} • {{
                      activeAnnouncement.date }}</span>
                  </div>
                </div>
              </transition>
            </div>
            <div v-else class="w-full text-center text-slate-400 py-10">
              <p class="text-xl">No current announcements.</p>
            </div>
          </div>
        </div>
      </section>

      <div class="col-span-12 lg:col-span-4 flex flex-col gap-6 h-full min-h-0 overflow-y-auto no-scrollbar pb-2">

        <div
          class="glass-panel p-6 md:p-8 rounded-xl flex flex-col items-center justify-center text-center relative overflow-hidden shrink-0 border border-gray-300 hover:border-orange-500 transition-colors">
          <div class="absolute top-0 left-0 w-full h-1 bg-linear-to-r from-orange-400 to-orange-600"></div>
          <span class="text-[12px] font-bold tracking-[0.2em] text-slate-500 mb-2 uppercase">CURRENT TIME</span>
          <div
            class="text-6xl md:text-[64px] font-bold leading-none text-slate-900 tracking-tighter mb-2 font-mono flex items-baseline gap-2">
            {{ currentTime || '--:--' }}
            <span class="text-2xl text-orange-500 font-black">{{ amPm }}</span>
          </div>
          <div class="font-mono text-[14px] font-medium text-orange-600 uppercase tracking-wide">
            {{ currentDate }}
          </div>
        </div>

        <div class="grid grid-cols-2 grid-rows-2 gap-4 md:gap-6 flex-1 min-h-0">

          <div
            class="glass-panel p-4 md:p-5 rounded-xl flex flex-col justify-between border border-gray-300 hover:border-orange-500 transition-colors min-h-0 overflow-hidden">
            <div class="flex justify-between items-start mb-2">
              <span class="material-symbols-outlined text-orange-500 text-3xl">partly_cloudy_day</span>
              <span
                class="text-[10px] font-bold tracking-[0.08em] uppercase text-slate-400 text-right max-w-20 leading-tight">
                {{ weatherCity }}
              </span>
            </div>
            <div class="mt-auto">
              <div class="text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight">{{ weatherTemp }}°C</div>
              <div class="text-[12px] lg:text-[13px] text-slate-500 leading-tight mt-1 truncate">{{ weatherDesc }}</div>
            </div>
          </div>

          <div
            class="glass-panel p-4 md:p-5 rounded-xl flex flex-col justify-between border border-gray-300 hover:border-orange-500 transition-colors min-h-0 overflow-hidden relative">
            <div class="flex justify-between items-start mb-2 relative z-10">
              <span class="material-symbols-outlined text-blue-500 text-3xl">bolt</span>
              <span
                class="text-[10px] font-bold tracking-[0.08em] uppercase text-slate-400 text-right leading-tight">VIBE<br>CHECK</span>
            </div>
            <div class="relative z-10 mt-auto">
              <div class="text-[11px] font-semibold text-slate-600 mb-2 leading-tight"
                :class="{ 'text-orange-500': hasVoted }">
                {{ hasVoted ? 'Thanks!' : 'How are you?' }}
              </div>
              <div class="flex justify-between items-center"
                :class="{ 'opacity-50 pointer-events-none blur-[1px] transition-all': hasVoted }">
                <button v-for="emoji in sentimentOptions.slice(0, 5)" :key="emoji.id" @click="submitSentiment(emoji)"
                  class="text-xl lg:text-2xl hover:scale-125 hover:-translate-y-1 transition-all duration-300 grayscale hover:grayscale-0 focus:outline-none origin-bottom"
                  :title="emoji.label">
                  {{ emoji.icon }}
                </button>
              </div>
            </div>
          </div>

          <div
            class="glass-panel p-4 md:p-5 rounded-xl border border-gray-300 hover:border-orange-500 transition-colors flex flex-col min-h-0 overflow-hidden">
            <div class="flex items-center gap-2 mb-3 shrink-0">
              <span
                class="material-symbols-outlined text-orange-500 bg-orange-100 p-1 rounded-lg text-[14px]">event_upcoming</span>
              <h2 class="text-xs font-extrabold text-slate-900 tracking-tight">UP NEXT</h2>
            </div>

            <div class="flex flex-col gap-2 overflow-y-auto no-scrollbar grow pr-1">
              <div v-if="nextEvent"
                class="bg-slate-900 rounded-xl p-3 text-white relative overflow-hidden group shrink-0 shadow-md flex flex-col gap-2 border border-slate-800">
                <div
                  class="absolute inset-0 bg-linear-to-br from-orange-600/20 to-transparent opacity-50 group-hover:opacity-100 transition-opacity">
                </div>

                <div class="relative z-10 flex justify-between items-start">
                  <span
                    class="inline-block bg-orange-500 text-white text-[8px] font-bold tracking-widest uppercase px-1.5 py-0.5 rounded-sm">Featured</span>

                  <div class="flex items-center gap-1.5 text-center bg-black/30 px-1.5 py-1 rounded backdrop-blur-sm">
                    <div class="flex flex-col items-center">
                      <span class="text-xs font-black font-mono text-orange-400 leading-none">{{ countdownDays }}</span>
                      <span class="text-[7px] uppercase font-bold tracking-widest text-slate-400 mt-0.5">Days</span>
                    </div>
                    <span class="text-slate-600 text-[10px] font-bold leading-none mb-1.5">:</span>
                    <div class="flex flex-col items-center">
                      <span class="text-xs font-black font-mono text-white leading-none">{{ countdownHours }}</span>
                      <span class="text-[7px] uppercase font-bold tracking-widest text-slate-400 mt-0.5">Hrs</span>
                    </div>
                  </div>
                </div>

                <div class="relative z-10 flex flex-col">
                  <h3 class="text-xs font-bold leading-tight line-clamp-1 mb-1">{{ nextEvent.title }}</h3>
                  <div class="flex items-center gap-1 text-slate-400">
                    <span class="material-symbols-outlined text-[10px]">location_on</span>
                    <p class="text-[9px] uppercase tracking-wider font-semibold truncate">{{ nextEvent.venue }}</p>
                  </div>
                </div>
              </div>

              <div v-else
                class="flex flex-col items-center justify-center py-4 text-slate-400 shrink-0 border border-dashed border-slate-300 rounded-xl">
                <span class="material-symbols-outlined text-2xl mb-1 opacity-50">event_busy</span>
                <p class="text-[10px] font-medium text-center">No upcoming events.</p>
              </div>

              <div v-for="event in upcomingEvents.slice(1, 4)" :key="event.event_id"
                class=" hover:bg-white text-slate-800 p-2 rounded-lg flex items-center gap-2 shrink-0 group transition-all border border-gray-300 hover:border-orange-500 cursor-pointer"
                @click="goToEvents">
                <div
                  class=" text-center rounded p-1 min-w-9 group-hover:bg-orange-50 transition-colors border border-gray-300">
                  <div class="text-[8px] font-bold uppercase text-slate-500 group-hover:text-orange-500">{{ event.month
                    }}</div>
                  <div class="text-xs font-black">{{ event.day }}</div>
                </div>
                <div class="min-w-0">
                  <div class="text-[10px] font-bold truncate group-hover:text-orange-600 transition-colors">{{
                    event.title }}</div>
                  <div class="text-[9px] text-slate-500 truncate mt-0.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[9px]">map</span>
                    {{ event.venue }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="glass-panel p-4 md:p-5 rounded-xl flex flex-col justify-between border border-gray-300 hover:border-orange-500 transition-colors min-h-0 overflow-hidden relative">
            <div class="flex justify-between items-start mb-2 relative z-10 shrink-0">
              <span class="material-symbols-outlined text-orange-500 text-3xl">psychology</span>
              <span
                class="text-[10px] font-bold tracking-[0.08em] uppercase text-slate-400 text-right leading-tight">DAILY<br>TRIVIA</span>
            </div>

            <div class="relative z-10 mt-auto flex flex-col justify-end">
              <div class="text-[11px] font-semibold text-slate-600 mb-3 leading-tight text-center px-2">
                Take a quick break and test your knowledge!
              </div>
              <button @click="isTriviaModalOpen = true"
                class="w-full text-center px-2 py-2 rounded-lg bg-orange-500 text-white font-bold text-[10px] tracking-[0.08em] uppercase hover:bg-orange-600 transition-all flex justify-center items-center gap-1.5 active:scale-95">
                PLAY NOW
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <transition name="fade">
      <div v-if="isQrModalOpen" class="fixed inset-0 z-100 flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isQrModalOpen = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm flex flex-col overflow-hidden max-h-[90vh]">
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 backdrop-blur-md z-10 shrink-0">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-orange-500">qr_code_scanner</span>
              <h2 class="text-sm font-bold text-slate-800 tracking-widest uppercase">QR Code</h2>
            </div>
            <button @click="isQrModalOpen = false"
              class="text-slate-400 hover:text-slate-700 hover:bg-slate-200 p-1.5 rounded-full transition-colors flex items-center justify-center">
              <span class="material-symbols-outlined text-lg">close</span>
            </button>
          </div>

          <div class="p-8 flex flex-col items-center justify-center text-center">
            <h3 class="text-xl font-bold text-slate-900 mb-2">{{ activeQr.title }}</h3>
            <p class="text-sm text-slate-500 mb-6 px-4">{{ activeQr.description }}</p>

            <div class="w-56 h-56 bg-slate-50 rounded-2xl mb-8 flex items-center justify-center border-2 border-dashed border-slate-200 p-4">
              <img v-if="activeQr.image" :src="activeQr.image" class="w-full h-full object-contain" alt="QR Code" />
              <span v-else class="material-symbols-outlined text-6xl text-slate-300">qr_code</span>
            </div>

            <button @click="nextQrCode"
              class="w-full text-center px-4 py-3.5 rounded-xl bg-orange-500 text-white font-bold text-[13px] tracking-widest uppercase hover:bg-orange-600 transition-colors flex justify-center items-center gap-2 shadow-lg shadow-orange-500/20 active:scale-95">
              NEXT QR
              <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>

            <div class="flex gap-1.5 mt-5">
               <span v-for="(qr, index) in qrCodes" :key="qr.id" 
                 class="h-1.5 rounded-full transition-all duration-300"
                 :class="currentQrIndex === index ? 'w-4 bg-orange-500' : 'w-1.5 bg-slate-200'"></span>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <SentimentModal :is-open="isModalOpen" :sentiment="selectedSentiment" :average-vibe="mockAverageVibe"
      @close="isModalOpen = false" />

    <transition name="fade">
      <div v-if="isTriviaModalOpen" class="fixed inset-0 z-100 flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isTriviaModalOpen = false"></div>

        <div
          class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden max-h-[90vh]">
          <div
            class="flex items-center justify-between px-6 py-4 border-b border-slate-100  backdrop-blur-md z-10 shrink-0">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-orange-500">psychology</span>
              <h2 class="text-sm font-bold text-slate-800 tracking-widest uppercase">Daily Challenge</h2>
            </div>
            <button @click="isTriviaModalOpen = false"
              class="text-slate-400 hover:text-slate-700 hover:bg-slate-200 p-1.5 rounded-full transition-colors flex items-center justify-center">
              <span class="material-symbols-outlined text-lg">close</span>
            </button>
          </div>

          <div class="p-6 md:p-8 overflow-y-auto no-scrollbar flex flex-col flex-1">
            <div v-if="quizState === 'start'" class="grow flex flex-col justify-center items-center text-center py-4">
              <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-orange-500 text-5xl">lightbulb</span>
              </div>
              <h3 class="text-2xl font-bold text-slate-900 mb-2">Ready to Play?</h3>
              <p class="text-base text-slate-600 mb-8 leading-relaxed max-w-xs">
                Answer 10 multiple-choice questions. See how high you can score today!
              </p>
              <button @click="fetchQuiz" :disabled="quizLoading"
                class="w-full max-w-xs text-center px-6 py-4 rounded-xl border-[1.5px] border-orange-500 bg-orange-500 text-white font-bold text-[14px] tracking-widest uppercase hover:bg-orange-600 transition-colors shadow-lg shadow-orange-500/20 disabled:opacity-70 flex justify-center items-center gap-2">
                <span v-if="quizLoading" class="material-symbols-outlined text-base animate-spin">refresh</span>
                {{ quizLoading ? 'LOADING...' : 'START CHALLENGE' }}
              </button>
            </div>

            <div v-else-if="quizState === 'playing' || quizState === 'answered'" class="grow flex flex-col h-full">
              <div class="flex justify-between items-center mb-6 border-b border-slate-200 pb-3 shrink-0">
                <span class="text-[12px] font-bold text-slate-400 uppercase tracking-widest">Question {{
                  currentQuestionIndex + 1 }} of 10</span>
                <span
                  class="text-[12px] font-bold text-orange-600 bg-orange-100 px-3 py-1 rounded-full uppercase tracking-widest">Score:
                  {{ quizScore }}</span>
              </div>
              <p class="text-lg md:text-xl font-bold text-slate-800 mb-8 leading-relaxed"
                v-html="quizQuestions[currentQuestionIndex].question"></p>

              <div class="grid grid-cols-1 gap-3 mt-auto shrink-0">
                <button v-for="(answer, idx) in shuffledAnswers" :key="idx" @click="selectAnswer(answer)"
                  :disabled="quizState === 'answered'"
                  class="w-full text-left px-5 py-4 rounded-xl border-2 text-[14px] font-medium transition-all"
                  :class="getAnswerClass(answer)" v-html="answer"></button>
              </div>

              <transition name="fade">
                <button v-if="quizState === 'answered'" @click="nextQuestion"
                  class="mt-6 w-full bg-slate-900 text-white py-4 rounded-xl text-[13px] font-bold tracking-widest uppercase hover:bg-slate-800 transition-colors flex justify-center items-center gap-2 shadow-lg shrink-0">
                  {{ currentQuestionIndex === 9 ? 'SEE FINAL RESULTS' : 'NEXT QUESTION' }}
                  <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
              </transition>
            </div>

            <div v-else-if="quizState === 'completed'"
              class="grow flex flex-col justify-center items-center text-center py-6">
              <div class="relative mb-6">
                <div class="absolute inset-0 bg-orange-200 blur-2xl rounded-full opacity-50 animate-pulse"></div>
                <span class="material-symbols-outlined text-orange-500 text-7xl relative z-10">workspace_premium</span>
              </div>
              <h3 class="text-3xl font-black text-slate-900 mb-2">Challenge Complete</h3>
              <p class="text-base text-slate-500 mb-8 font-medium">You scored <strong
                  class="text-orange-600 text-xl mx-1">{{ quizScore }}</strong> out of 10</p>

              <div class="flex flex-col gap-3 w-full max-w-xs">
                <button @click="fetchQuiz"
                  class="w-full text-center px-4 py-3.5 rounded-xl border-[1.5px] border-slate-200 hover:border-orange-400 hover:bg-orange-50 text-[13px] font-bold tracking-widest uppercase transition-all text-slate-700 flex justify-center items-center gap-2 shadow-sm">
                  <span class="material-symbols-outlined text-base">replay</span>
                  PLAY AGAIN
                </button>
                <button @click="isTriviaModalOpen = false"
                  class="w-full text-center px-4 py-3.5 rounded-xl text-[13px] font-bold tracking-widest uppercase transition-all text-slate-400 hover:text-slate-600">
                  CLOSE TRIVIA
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </transition>

  </main>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import SentimentModal from '../modals/sentimentmodal.vue'

const isQrModalOpen = ref(false)
const currentQrIndex = ref(0)
const qrCodes = ref([
  { id: 1, title: 'Feedback Form', description: 'Tell us about your experience!', image: '/images/sample.png' },
  { id: 2, title: 'Printing Form', description: 'Scan To request for printing', image: '/images/sample.png' },
  { id: 3, title: 'Do Day Form', description: 'Scan to submit your Do Day form', image: '/images/sample.png' }
])
const activeQr = computed(() => qrCodes.value[currentQrIndex.value])

const nextQrCode = () => {
  currentQrIndex.value = (currentQrIndex.value + 1) % qrCodes.value.length
}

// Routing Mock
const goToEvents = () => {
  window.location.href = '/announcements-events'
}

// Time & Calendar State
const currentTime = ref('')
const amPm = ref('')
const currentDate = ref('')
const currentDay = ref(new Date().getDate())
const currentMonthYear = ref('')
const currentHour = ref(new Date().getHours())

// Weather State
const weatherCity = ref('Butuan City')
const weatherTemp = ref('--')
const weatherDesc = ref('Loading...')

// --- DYNAMIC DATA STATE ---
const announcements = ref([])
const upcomingEvents = ref([])
const nextEvent = ref(null)
const countdownDays = ref('00')
const countdownHours = ref('00')
const fallbackImage = '/images/ccislogo.png'

const currentAnnouncementIndex = ref(0)
const activeAnnouncement = computed(() => announcements.value[currentAnnouncementIndex.value] || null)

const getAnnouncementImage = (announcement) => {
  if (announcement.attachments && announcement.attachments.length > 0) {
    return announcement.attachments[0].file_path
  }
  return fallbackImage
}

const setAnnouncementIndex = (index) => {
  currentAnnouncementIndex.value = index
}

const rotateAnnouncements = () => {
  if (announcements.value.length > 0) {
    currentAnnouncementIndex.value = (currentAnnouncementIndex.value + 1) % announcements.value.length
  }
}

// Fetch Board Data from Backend
const fetchAnnouncements = async () => {
  try {
    const response = await axios.get('board-data')

    // Map Announcements
    announcements.value = response.data.announcements.map(a => ({
      ...a,
      isLiked: false,
      isProcessing: false,
      isCooldown: false,
      cooldownTimer: 0,
      isAnimating: false
    }))

    // Map Upcoming Events
    upcomingEvents.value = response.data.upcoming_events || []
    if (upcomingEvents.value.length > 0) {
      nextEvent.value = upcomingEvents.value[0]
      updateCountdown()
    }

  } catch (e) {
    console.error("Sync Error", e)
  }
}

// Trivia Logic
const isTriviaModalOpen = ref(false)
const quizQuestions = ref([])
const currentQuestionIndex = ref(0)
const quizScore = ref(0)
const quizState = ref('start')
const selectedAnswer = ref(null)
const shuffledAnswers = ref([])
const quizLoading = ref(false)
const quizError = ref('')

const fetchQuiz = async () => {
  quizLoading.value = true
  quizError.value = ''
  try {
    const response = await fetch('https://opentdb.com/api.php?amount=10&category=18&difficulty=easy&type=multiple')
    const data = await response.json()

    if (data.response_code === 0) {
      quizQuestions.value = data.results
      quizState.value = 'playing'
      currentQuestionIndex.value = 0
      quizScore.value = 0
      setupQuestion()
    } else {
      quizError.value = "Failed to load quiz. Try again!"
    }
  } catch (error) {
    console.error("Quiz Fetch Error:", error)
    quizError.value = "Network error. Check connection."
  } finally {
    quizLoading.value = false
  }
}

const setupQuestion = () => {
  const currentQ = quizQuestions.value[currentQuestionIndex.value]
  const answers = [...currentQ.incorrect_answers, currentQ.correct_answer]
  shuffledAnswers.value = answers.sort(() => Math.random() - 0.5)
  selectedAnswer.value = null
  quizState.value = 'playing'
}

const selectAnswer = (answer) => {
  selectedAnswer.value = answer
  quizState.value = 'answered'
  if (answer === quizQuestions.value[currentQuestionIndex.value].correct_answer) {
    quizScore.value++
  }
}

const nextQuestion = () => {
  if (currentQuestionIndex.value < quizQuestions.value.length - 1) {
    currentQuestionIndex.value++
    setupQuestion()
  } else {
    quizState.value = 'completed'
  }
}

const getAnswerClass = (answer) => {
  if (quizState.value !== 'answered') {
    return 'border-slate-200 text-slate-700 bg-white hover:border-orange-400 hover:bg-orange-50 hover:shadow-sm'
  }
  const isCorrect = answer === quizQuestions.value[currentQuestionIndex.value].correct_answer
  if (isCorrect) {
    return 'bg-green-50 border-green-500 text-green-700 font-bold shadow-sm'
  }
  if (answer === selectedAnswer.value) {
    return 'bg-red-50 border-red-400 text-red-700'
  }
  return 'opacity-50 border-slate-200 text-slate-400'
}

// --- SENTIMENT TRACKER STATE ---
const hasVoted = ref(false)
const selectedSentiment = ref(null)
const mockAverageVibe = ref('Productive')
const isModalOpen = ref(false)

const sentimentOptions = [
  { id: 1, icon: '😫', label: 'Stressed', message: 'Take a deep breath! Pause and hydrate.' },
  { id: 2, icon: '😴', label: 'Tired', message: 'Make sure to get some rest soon.' },
  { id: 3, icon: '🙂', label: 'Okay', message: 'Steady and solid! Keep up the good pace.' },
  { id: 4, icon: '😎', label: 'Productive', message: 'Awesome! Crush those goals!' },
  { id: 5, icon: '🤩', label: 'Great', message: 'Love the energy! Keep it going!' }
]

const submitSentiment = async (emoji) => {
  try {
    selectedSentiment.value = emoji
    hasVoted.value = true
    isModalOpen.value = true

    setTimeout(() => {
      hasVoted.value = false
      selectedSentiment.value = null
    }, 15000)

  } catch (error) {
    console.error("Failed to record vibe:", error)
  }
}

// Clock Logic
const updateClock = () => {
  const now = new Date()
  const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })
  const [timeText, period] = timeString.split(' ')
  currentTime.value = timeText
  amPm.value = period
  currentDate.value = now.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric', weekday: 'long' }).toUpperCase()
  currentMonthYear.value = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }).toUpperCase()
  currentDay.value = now.getDate()
  currentHour.value = now.getHours()
}

// Weather Logic
const getWeatherDescription = (code) => {
  const weatherCodes = {
    0: 'Clear Sky', 1: 'Mainly Clear', 2: 'Partly Cloudy', 3: 'Overcast',
    45: 'Fog', 48: 'Depositing Rime Fog', 51: 'Light Drizzle', 53: 'Moderate Drizzle',
    55: 'Dense Drizzle', 56: 'Light Freezing Drizzle', 57: 'Dense Freezing Drizzle',
    61: 'Slight Rain', 63: 'Moderate Rain', 65: 'Heavy Rain', 66: 'Light Freezing Rain',
    67: 'Heavy Freezing Rain', 71: 'Slight Snow Fall', 73: 'Moderate Snow Fall',
    75: 'Heavy Snow Fall', 77: 'Snow Grains', 80: 'Slight Rain Showers',
    81: 'Moderate Rain Showers', 82: 'Violent Rain Showers', 85: 'Slight Snow Showers',
    86: 'Heavy Snow Showers', 95: 'Thunderstorm', 96: 'Thunderstorm with Hail'
  }
  return weatherCodes[code] || 'Unknown Conditions'
}

const fetchWeather = async () => {
  try {
    const url = "https://api.open-meteo.com/v1/forecast?latitude=8.8222&longitude=125.1022&current_weather=true&timezone=Asia%2FManila";
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Weather API is down! Status: ${response.status}`);
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

const updateCountdown = () => {
  if (!nextEvent.value || !nextEvent.value.start_time) {
    countdownDays.value = '--'
    countdownHours.value = '--'
    return
  }
  const eventDate = new Date(nextEvent.value.start_time)
  const now = new Date()
  const diff = eventDate - now

  if (diff <= 0) {
    countdownDays.value = '00'
    countdownHours.value = '00'
    return
  }

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))

  countdownDays.value = String(days).padStart(2, '0')
  countdownHours.value = String(hours).padStart(2, '0')
}

// Timers
let clockTimer, weatherTimer, carouselTimer

onMounted(() => {
  updateClock()
  fetchWeather()
  fetchAnnouncements()

  clockTimer = setInterval(updateClock, 1000)
  weatherTimer = setInterval(fetchWeather, 1800000)
  carouselTimer = setInterval(rotateAnnouncements, 8000)
})

onUnmounted(() => {
  clearInterval(clockTimer)
  clearInterval(weatherTimer)
  clearInterval(carouselTimer)
})
</script>

<style scoped>
.glass-panel {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(32px);
  -webkit-backdrop-filter: blur(32px);
}

.material-symbols-outlined {
  font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
}

.pulse-dot {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Scrollbar Hiding CSS */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  /* IE and Edge */
  scrollbar-width: none;
  /* Firefox */
}

@keyframes pulse {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.3;
  }
}

/* Vue Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.5s ease;
}

.slide-up-enter-from {
  opacity: 0;
  transform: translateY(15px);
}

.slide-up-leave-to {
  opacity: 0;
  transform: translateY(-15px);
}
</style>