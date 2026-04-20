<template>
  <header class="mb-16 relative z-10 w-full">

    <div class="flex flex-col md:flex-row justify-between items-start gap-8 w-full">

      <div class="flex flex-col gap-6 animate-in fade-in slide-in-from-left duration-700 w-full md:w-64 relative z-10">

        <div class="flex items-center gap-4 group cursor-default">
          <div class="text-orange-500 group-hover:scale-110 transition-transform duration-500">
            <span class="material-symbols-outlined text-5xl filter drop-shadow-[0_0_8px_rgba(249,115,22,0.3)]">
              partly_cloudy_day
            </span>
          </div>
          <div>
            <div class="mb-1 text-[10px] font-bold tracking-[0.2em] uppercase text-orange-500">
              {{ weatherCity }}
            </div>
            <div class="flex items-baseline gap-2">
              <span class="text-4xl font-light tracking-tighter text-gray-800">{{ weatherTemp }}°C</span>
              <span class="text-xs font-semibold tracking-widest uppercase opacity-80 text-orange-500">
                {{ weatherDesc }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="nextEvent"
          class="hidden md:block bg-white shadow-sm border border-gray-200 rounded-2xl p-4 backdrop-blur-md relative overflow-hidden group cursor-pointer hover:shadow-md transition-all hover:border-orange-300"
          @click="goToEvents">
          <div
            class="absolute -right-4 -top-4 w-16 h-16 bg-orange-500/10 rounded-full transition-transform group-hover:scale-150 duration-500">
          </div>

          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-orange-500">
              <span class="material-symbols-outlined text-sm animate-pulse">event_upcoming</span>
              <span class="text-[9px] font-bold tracking-widest uppercase text-gray-500">Up Next</span>
            </div>
          </div>

          <h3 class="text-sm font-bold text-gray-800 leading-tight mb-1 line-clamp-1">{{ nextEvent.title }}</h3>
          <p class="text-[10px] text-gray-500 font-medium mb-3 uppercase tracking-wider">{{ nextEvent.venue }}</p>

          <div class="flex gap-2">
            <div
              class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-xl text-center grow border border-orange-100 shadow-inner">
              <span class="block text-xl font-black leading-none mb-0.5">{{ countdownDays }}</span>
              <span class="block text-[8px] uppercase tracking-widest font-bold opacity-80">Days</span>
            </div>
            <div
              class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-xl text-center grow border border-orange-100 shadow-inner">
              <span class="block text-xl font-black leading-none mb-0.5">{{ countdownHours }}</span>
              <span class="block text-[8px] uppercase tracking-widest font-bold opacity-80">Hours</span>
            </div>
          </div>
        </div>

        <div
          class="bg-white shadow-sm border border-gray-200 rounded-2xl p-4 backdrop-blur-md relative overflow-hidden">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-orange-500">
              <span class="material-symbols-outlined text-sm">newspaper</span>
              <span class="text-[9px] font-bold tracking-widest uppercase text-gray-500">PH Top News</span>
            </div>
          </div>

          <div class="flex flex-col gap-3">
            <div v-if="newsHeadlines.length === 0" class="text-xs text-gray-400 italic">
              {{ newsStatusMessage }}
            </div>
            <a v-for="(article, index) in newsHeadlines" :key="index" :href="article.url" target="_blank"
              class="group block border-b border-gray-100 last:border-0 pb-2 last:pb-0">
              <h3
                class="text-xs font-medium text-gray-800 leading-tight line-clamp-2 group-hover:text-orange-500 transition-colors">
                {{ article.title }}
              </h3>
            </a>
          </div>
        </div>

      </div>

      <div
        class="text-center md:absolute md:left-1/2 md:-translate-x-1/2 animate-in fade-in zoom-in duration-1000 flex flex-col items-center">
        <span
          class="bg-orange-100 text-orange-600 px-4 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase mb-4 shadow-sm border border-orange-200">
          {{ dynamicGreeting }}
        </span>

        <h1
          class="text-7xl md:text-8xl font-light tracking-tighter leading-none text-gray-900 font-mono drop-shadow-sm">
          {{ currentTime }}
        </h1>
        <div class="uppercase tracking-[0.3em] text-sm font-medium mt-2 text-orange-500">
          {{ currentDate }}
        </div>
      </div>

      <div class="hidden lg:flex flex-col gap-4 animate-in fade-in slide-in-from-right duration-700 w-64 relative z-10">

        <div class="bg-white shadow-sm border border-gray-200 rounded-2xl p-4 backdrop-blur-md">
          <div class="flex justify-between items-center mb-4 text-orange-500">
            <h2 class="text-[10px] font-bold tracking-widest uppercase">{{ currentMonthYear }}</h2>
          </div>
          <div class="grid grid-cols-7 gap-1 text-center text-[9px] font-bold text-gray-400 mb-2">
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
                d === currentDay ? 'bg-orange-500 text-white rounded-full font-bold shadow-[0_0_10px_rgba(249,115,22,0.4)] scale-110' : 'text-gray-600']">
              {{ d }}
            </div>
          </div>
        </div>

        <div
          class="bg-white shadow-sm border border-gray-200 rounded-2xl p-4 backdrop-blur-md relative overflow-hidden">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2 text-orange-500">
              <span class="material-symbols-outlined text-sm">mood</span>
              <span class="text-[9px] font-bold tracking-widest uppercase text-gray-500">Campus Vibe Check</span>
            </div>
          </div>

          <div v-if="!hasVoted" class="flex flex-col gap-3 transition-all duration-500">
            <p class="text-xs text-gray-600 font-medium text-center">How are you feeling today?</p>
            <div class="flex justify-between items-center px-1">
              <button v-for="emoji in sentimentOptions" :key="emoji.id" @click="submitSentiment(emoji)"
                class="text-2xl hover:scale-125 hover:-translate-y-1 transition-all duration-300 grayscale hover:grayscale-0 focus:outline-none"
                :title="emoji.label">
                {{ emoji.icon }}
              </button>
            </div>
          </div>

          <div v-else
            class="flex flex-col items-center justify-center gap-2 py-1 animate-in fade-in zoom-in duration-500">
            <span class="text-3xl">{{ selectedSentiment.icon }}</span>
            <p class="text-xs text-gray-600 text-center font-medium leading-relaxed">
              Vibe recorded! <br />
              <span class="text-[10px] text-gray-400 font-normal">Most students are feeling <strong>"{{ mockAverageVibe
                  }}"</strong> today.</span>
            </p>
          </div>
        </div>

      </div>
    </div>

    <div
      class="mt-12 w-full flex items-center bg-white border border-gray-200 shadow-sm rounded-full overflow-hidden h-10 relative z-10">
      <div class="bg-orange-500 text-white h-full px-6 flex items-center justify-center z-10 shrink-0 shadow-md">
        <span class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
          <span class="material-symbols-outlined text-sm animate-pulse">campaign</span>
          Updates
        </span>
      </div>
      <div class="grow overflow-hidden relative h-full flex items-center">
        <div class="whitespace-nowrap animate-marquee text-xs font-medium text-gray-600 tracking-wide">
          <span class="mx-4 text-orange-500">•</span> Welcome to the centralized announcement board MIRACIS!
          <span class="mx-4 text-orange-500">•</span> Stay tuned for the latest news, events, and updates from the
          College of Computer and Information Sciences.
          <span class="mx-4 text-orange-500">•</span> Don't forget to check the Events tab for upcoming department
          activities.
          <span class="mx-4 text-orange-500">•</span> interact with the announcements to view details and attachments.
          <span class="mx-4 text-orange-500">•</span> For any inquiries or support, visit us at the CCIS Network
          Administration Office.
        </div>
      </div>
    </div>

  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

// Routing Mock
const goToEvents = () => {
  window.location.href = '/announcements-events'
}

// Time & Calendar State
const currentTime = ref('')
const currentDate = ref('')
const currentDay = ref(new Date().getDate())
const currentMonthYear = ref('')
const currentHour = ref(new Date().getHours())

// Weather State
const weatherCity = ref('Butuan City')
const weatherTemp = ref('--')
const weatherDesc = ref('Loading...')

// Upcoming Events State
const upcomingEvents = ref([])
const nextEvent = ref(null)
const countdownDays = ref('04')
const countdownHours = ref('12')



// News State
const newsHeadlines = ref([])
const newsStatusMessage = ref('Fetching latest news...')

// Sentiment Tracker State
const hasVoted = ref(false)
const selectedSentiment = ref(null)
const mockAverageVibe = ref('Productive')

const sentimentOptions = [
  { id: 1, icon: '😫', label: 'Stressed' },
  { id: 2, icon: '😴', label: 'Tired' },
  { id: 3, icon: '🙂', label: 'Okay' },
  { id: 4, icon: '😎', label: 'Productive' },
  { id: 5, icon: '🤩', label: 'Great' }
]

const submitSentiment = async (emoji) => {
  try {
    // 1. Send the data to the Laravel backend
    // Remove the '/api' prefix
    const response = await axios.post('/sentiment', {
      sentiment: emoji.label
    })

    // 2. Update UI states
    selectedSentiment.value = emoji
    hasVoted.value = true

    // 3. Replace the mock text with real database data!
    mockAverageVibe.value = response.data.most_common_vibe

    // 4. Optional: Reset the UI after 10 seconds to match the backend cooldown
    setTimeout(() => {
      hasVoted.value = false
      selectedSentiment.value = null
    }, 10000)

  } catch (error) {
    if (error.response && error.response.status === 429) {
      alert("Hold on! You're voting too fast. Wait 10 seconds.")
    } else {
      console.error("Failed to record vibe:", error)
    }
  }
}

// Computed properties
const daysInMonth = computed(() => {
  const now = new Date(); return new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()
})
const firstDayOfMonth = computed(() => {
  const now = new Date(); return new Date(now.getFullYear(), now.getMonth(), 1).getDay()
})

const dynamicGreeting = computed(() => {
  if (currentHour.value < 12) return "Good Morning, Campus!"
  if (currentHour.value < 18) return "Good Afternoon, Students!"
  return "Good Evening, Students!"
})

// Clock Logic
const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true })
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }).toUpperCase()
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
    86: 'Heavy Snow Showers', 95: 'Thunderstorm', 96: 'Thunderstorm with Slight Hail',
    99: 'Thunderstorm with Heavy Hail'
  }
  return weatherCodes[code] || 'Unknown Conditions'
}

const fetchWeather = async () => {
  try {
    const url = "https://api.open-meteo.com/v1/forecast?latitude=8.9492&longitude=125.5436&current_weather=true&timezone=Asia%2FManila";
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

// GNEWS API Logic
const fetchPHNews = async () => {
  try {
    const API_KEY = 'a402c195e9fc98081c12f68c383c2354';
    const url = `https://gnews.io/api/v4/top-headlines?category=general&country=ph&apikey=${API_KEY}`;

    const response = await fetch(url);

    if (response.status === 401 || response.status === 403) {
      newsStatusMessage.value = "Missing or Invalid API Key.";
      throw new Error("Unauthorized: Check your GNews API key.");
    }
    if (!response.ok) throw new Error(`News API error: ${response.status}`);

    const data = await response.json();
    if (data.articles && data.articles.length > 0) {
      newsHeadlines.value = data.articles.slice(0, 3).map(article => ({
        title: article.title,
        url: article.url
      }));
    } else {
      newsStatusMessage.value = "No news found right now.";
    }
  } catch (e) {
    console.error("News Sync Error:", e.message);
    if (newsHeadlines.value.length === 0) {
      newsStatusMessage.value = "API Error. Check console.";
    }
  }
}



// Upcoming Events Logic
const fetchUpcomingEvents = async () => {
  try {
    const response = await axios.get('events/upcoming')
    upcomingEvents.value = response.data.events || []
    if (upcomingEvents.value.length > 0) {
      nextEvent.value = upcomingEvents.value[0]
      updateCountdown()
    }
  } catch (e) { console.error("Events Error", e) }
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
let clockTimer, weatherTimer, newsTimer

onMounted(() => {
  updateClock()
  fetchWeather()
  fetchPHNews()
  fetchUpcomingEvents()

  clockTimer = setInterval(updateClock, 1000)
  weatherTimer = setInterval(fetchWeather, 1800000) // Update weather every 30 mins
  newsTimer = setInterval(fetchPHNews, 3600000) // Refresh news every hour
})

onUnmounted(() => {
  clearInterval(clockTimer)
  clearInterval(weatherTimer)
  clearInterval(newsTimer)
})
</script>

<style scoped>
@keyframes marquee {
  0% {
    transform: translateX(100%);
  }

  100% {
    transform: translateX(-100%);
  }
}

.animate-marquee {
  display: inline-block;
  animation: marquee 25s linear infinite;
}

.animate-marquee:hover {
  animation-play-state: paused;
}
</style>