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

        <div class="hidden md:block bg-white shadow-sm border border-gray-200 rounded-2xl p-4 backdrop-blur-md relative overflow-hidden group cursor-pointer hover:shadow-md transition-all hover:border-orange-300" @click="goToEvents">
          <div class="absolute -right-4 -top-4 w-16 h-16 bg-orange-500/10 rounded-full transition-transform group-hover:scale-150 duration-500"></div>
          
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-orange-500">
              <span class="material-symbols-outlined text-sm animate-pulse">event_upcoming</span>
              <span class="text-[9px] font-bold tracking-widest uppercase text-gray-500">Up Next</span>
            </div>
          </div>
          
          <h3 class="text-sm font-bold text-gray-800 leading-tight mb-1 line-clamp-1">Midterm Examinations</h3>
          <p class="text-[10px] text-gray-500 font-medium mb-3 uppercase tracking-wider">Campus-wide Event</p>
          
          <div class="flex gap-2">
            <div class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-xl text-center grow border border-orange-100 shadow-inner">
              <span class="block text-xl font-black leading-none mb-0.5">{{ countdownDays }}</span>
              <span class="block text-[8px] uppercase tracking-widest font-bold opacity-80">Days</span>
            </div>
            <div class="bg-orange-50 text-orange-600 px-3 py-1.5 rounded-xl text-center grow border border-orange-100 shadow-inner">
              <span class="block text-xl font-black leading-none mb-0.5">{{ countdownHours }}</span>
              <span class="block text-[8px] uppercase tracking-widest font-bold opacity-80">Hours</span>
            </div>
          </div>
        </div>

      </div>

      <div class="text-center md:absolute md:left-1/2 md:-translate-x-1/2 animate-in fade-in zoom-in duration-1000 flex flex-col items-center">
        <span class="bg-orange-100 text-orange-600 px-4 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase mb-4 shadow-sm border border-orange-200">
          {{ dynamicGreeting }}
        </span>
        
        <h1 class="text-7xl md:text-8xl font-light tracking-tighter leading-none text-gray-900 font-mono drop-shadow-sm">
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
            <div>SU</div><div>MO</div><div>TU</div><div>WE</div><div>TH</div><div>FR</div><div>SA</div>
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

        <div class="bg-linear-to-br from-gray-900 to-gray-800 rounded-2xl p-4 shadow-md border border-gray-700 relative overflow-hidden group cursor-pointer">
          <div class="absolute top-0 right-0 w-16 h-16 bg-orange-500/10 rounded-bl-full transition-transform group-hover:scale-150"></div>
          <div class="flex items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-orange-500 text-sm">lightbulb</span>
            <span class="text-[9px] font-bold tracking-widest uppercase text-gray-400">Daily Spark</span>
          </div>
          <p class="text-xs text-gray-200 leading-relaxed font-light italic">
            "{{ dailyFact }}"
          </p>
        </div>

      </div>
    </div>

    <div class="mt-12 w-full flex items-center bg-white border border-gray-200 shadow-sm rounded-full overflow-hidden h-10 relative z-10">
      <div class="bg-orange-500 text-white h-full px-6 flex items-center justify-center z-10 shrink-0 shadow-md">
        <span class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
          <span class="material-symbols-outlined text-sm animate-pulse">campaign</span>
          Updates
        </span>
      </div>
      <div class="grow overflow-hidden relative h-full flex items-center">
        <div class="whitespace-nowrap animate-marquee text-xs font-medium text-gray-600 tracking-wide">
          <span class="mx-4 text-orange-500">•</span> Welcome to the centralized announcement board MIRACIS! 
          <span class="mx-4 text-orange-500">•</span> Stay tuned for the latest news, events, and updates from the College of Computer and Information Sciences.
          <span class="mx-4 text-orange-500">•</span> Don't forget to check the Events tab for upcoming department activities.
          <span class="mx-4 text-orange-500">•</span> interact with the announcements to view details and attachments.
          <span class="mx-4 text-orange-500">•</span> For any inquiries or support, visit us at the CCIS Network Administration Office.
        </div>
      </div>
    </div>

  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

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

// Event Countdown State (Mock data - set to 4 days from now)
const countdownDays = ref('04')
const countdownHours = ref('12')

// Hook Data State
const dailyFact = ref('')
const factsArray = [
  "The first computer bug was an actual moth found in a relay.",
  "Over 3.4 billion fake emails are sent every single day.",
  "The first 1GB hard drive weighed over 500 pounds.",
  "Programming languages were originally written in 1s and 0s.",
  "A single Google query uses 1,000 computers in 0.2 seconds to fetch an answer."
]

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

// Select a random fact for the day
const setDailyFact = () => {
  const randomIndex = Math.floor(Math.random() * factsArray.length)
  dailyFact.value = factsArray[randomIndex]
}

// Timers
let clockTimer, weatherTimer

onMounted(() => {
  updateClock()
  fetchWeather()
  setDailyFact()
  
  clockTimer = setInterval(updateClock, 1000)
  weatherTimer = setInterval(fetchWeather, 1800000) // Update weather every 30 mins
})

onUnmounted(() => {
  clearInterval(clockTimer)
  clearInterval(weatherTimer)
})
</script>

<style scoped>
@keyframes marquee {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}
.animate-marquee {
  display: inline-block;
  animation: marquee 25s linear infinite;
}
.animate-marquee:hover {
  animation-play-state: paused;
}
</style>