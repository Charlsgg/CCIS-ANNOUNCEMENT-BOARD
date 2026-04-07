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
      <div class="flex items-center gap-4 animate-in fade-in slide-in-from-left duration-700">
        <div class="text-orange-500 hover:scale-110 transition-transform duration-500">
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
            <div v-for="empty in firstDayOfCurrentMonth" :key="'empty-' + empty" class="p-1"></div>
            <div v-for="d in daysInCurrentMonth" :key="d"
              :class="['p-1 text-[10px] transition-all duration-300',
                d === currentLiveDay ? 'bg-orange-500 text-black rounded-full font-bold shadow-[0_0_15px_rgba(249,115,22,0.6)] scale-110' : 'opacity-60']">
              {{ d }}
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto relative z-10 flex flex-col h-full">
      <section class="flex flex-col md:flex-row gap-4 mb-8 items-center">
        <button @click="goBack"
          class="flex items-center gap-2 px-6 py-3 rounded-full bg-white/5 hover:bg-orange-500/10 border border-white/10 hover:border-orange-500 transition-all duration-300 text-sm font-medium whitespace-nowrap group">
          <span
            class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
          <span>Back</span>
        </button>

        <div class="relative grow w-full z-50">
          <input
            class="w-full glass-input rounded-xl px-12 py-3 text-lg hover:border-orange-500 focus:border-orange-500 transition-all duration-300"
            placeholder="Search events..." type="text" v-model="searchQuery" @focus="isSearchFocused = true"
            @blur="handleSearchBlur" />
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 opacity-40">search</span>

          <div v-if="searchQuery && isSearchFocused"
            class="absolute top-full left-0 w-full mt-2 glass-card overflow-hidden max-h-60 overflow-y-auto custom-scrollbar shadow-[0_10px_40px_rgba(0,0,0,0.8)] border border-orange-500/30">
            <ul v-if="filteredDbEvents.length > 0">
              <li v-for="event in filteredDbEvents" :key="event.event_id" @mousedown="openSearchedEvent(event)"
                class="px-5 py-3 border-b border-white/5 hover:bg-orange-500/20 cursor-pointer transition-colors flex flex-col gap-1 last:border-0">
                <span class="font-bold text-orange-500">{{ event.title }}</span>
                <span class="text-xs opacity-70">
                  {{ formatTime(event.start_time) }} <span v-if="event.venue || event.Venue">• {{ event.venue ||
                    event.Venue }}</span>
                </span>
              </li>
            </ul>
            <div v-else class="px-5 py-4 text-center opacity-50 text-sm italic">
              No events found matching "{{ searchQuery }}"
            </div>
          </div>
        </div>
      </section>

      <div class="flex-1 flex flex-col mt-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
          <h2 class="text-4xl font-light">Academic <span class="text-orange-500 font-bold">Calendar</span></h2>
          <div class="flex space-x-4">
            <div class="glass-card px-4 py-2 flex items-center space-x-2">
              <span class="text-orange-500 text-xs font-bold uppercase">Month</span>
              <select v-model="currentMonth"
                class="bg-transparent border-none text-white text-sm focus:ring-0 cursor-pointer outline-none w-24">
                <option v-for="(month, index) in months" :key="index" :value="index" class="bg-zinc-900 text-white">{{
                  month }}</option>
              </select>
            </div>
            <div class="glass-card px-4 py-2 flex items-center space-x-2">
              <span class="text-orange-500 text-xs font-bold uppercase">Year</span>
              <select v-model="currentYear"
                class="bg-transparent border-none text-white text-sm focus:ring-0 cursor-pointer outline-none w-20">
                <option v-for="year in years" :key="year" :value="year" class="bg-zinc-900 text-white">{{ year }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="flex-1 glass-card overflow-hidden flex flex-col min-h-150">
          <div class="grid grid-cols-7 bg-white/5 border-b border-white/10">
            <div v-for="day in ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']" :key="day"
              class="py-4 text-center text-orange-500 text-xs md:text-sm font-black tracking-widest">
              {{ day }}
            </div>
          </div>

          <div class="flex-1 grid grid-cols-7 overflow-y-auto custom-scrollbar">
            <div v-for="(day, index) in processedDays" :key="index" @click="openEventDetail(day)" :class="[
              'border-b border-r border-white/5 p-2 md:p-4 min-h-30 transition-colors cursor-pointer group flex flex-col overflow-hidden',
              !day.isCurrentMonth ? 'opacity-30' : 'hover:bg-white/5',
              day.isHighlight ? 'bg-orange-500/5 border-orange-500/30' : ''
            ]">

              <span :class="[
                'text-lg md:text-2xl font-light inline-block w-8 h-8 md:w-10 md:h-10 text-center leading-8 md:leading-10 rounded-full transition-all mb-2',
                isToday(day.fullDate) ? 'bg-orange-500 text-black font-bold shadow-[0_0_15px_rgba(249,115,22,0.5)]' : 'group-hover:text-orange-400',
                day.isHighlight && !isToday(day.fullDate) ? 'text-orange-500 font-bold border-b-2 border-orange-500 rounded-none h-auto leading-normal' : ''
              ]">
                {{ day.date }}
              </span>

              <div class="flex-1 flex flex-col gap-1 w-full relative">
                <div v-for="(event, idx) in day.slottedEvents" :key="event ? event.id : `empty-${day.date}-${idx}`"
                  class="min-h-7 md:min-h-8 py-0.5 flex flex-col justify-center text-[9px] md:text-[10px] font-bold uppercase transition-all leading-tight"
                  :class="event ? getEventClasses(event, day) : 'opacity-0 pointer-events-none h-7 md:h-8'"
                  :style="event ? getEventStyle(event) : {}">

                  <template v-if="event && isEventStart(event, day)">
                    <span class="truncate px-1.5 drop-shadow-md">{{ event.title }}</span>

                    <div v-if="event.startTime"
                      class="px-1.5 opacity-80 font-medium normal-case tracking-wider text-[7px] md:text-[8px] truncate mt-0.5">
                      {{ formatTime(event.startTime) }}
                      <span v-if="event.endTime"> - {{ formatTime(event.endTime) }}</span>
                    </div>
                  </template>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </main>

    <Teleport to="body">
      <PublicEventDetailModal v-if="showEventDetailModal" :show="showEventDetailModal" :theme="theme" :surface="surface"
        :styles="styles" :events="selectedEvents" @close="showEventDetailModal = false" />
    </Teleport>
  </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'

// Import the modal and theme just like the parent component
import PublicEventDetailModal from '../modals/publiceventdetailmodal.vue' 
import { useTheme } from '../composable/usetheme.ts'       

// ----- Types -----
interface CalendarEvent {
  id: string | number
  title: string
  color?: string
  venue?: string
  description?: string
  startTime?: string
  endTime?: string | null
}

interface CalendarDay {
  date: number
  fullDate?: string
  isCurrentMonth: boolean
  events: CalendarEvent[]
  slottedEvents?: (CalendarEvent | null)[]
  isHighlight?: boolean
}

interface DatabaseEvent {
  event_id: number
  user_id?: number
  board_id?: number
  title: string
  content: string
  venue?: string
  Venue?: string
  start_time: string
  end_time?: string | null
  event_month?: number
  event_year?: number
  color?: string
}

// ----- Modal & Theme Setup -----
const { theme, styles, surface, initTheme } = useTheme()

const showEventDetailModal = ref(false)
const selectedEvents = ref<Array<{ title: string, venue: string, description: string, start_time: string, end_time?: string | null }>>([])

// ----- State: Weather -----
const weatherCity = ref('Butuan City')
const weatherTemp = ref<number | string>('--')
const weatherDesc = ref('Loading...')
let weatherTimer: ReturnType<typeof setInterval>

// ----- State: Live Header -----
const currentTime = ref('')
const currentDate = ref('')
const currentLiveDay = ref(new Date().getDate())
const currentMonthYear = ref('')
let clockTimer: ReturnType<typeof setInterval>

const daysInCurrentMonth = computed(() => {
  const now = new Date(); return new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()
})
const firstDayOfCurrentMonth = computed(() => {
  const now = new Date(); return new Date(now.getFullYear(), now.getMonth(), 1).getDay()
})

// ----- State: Calendar Controller -----
const today = new Date()
const currentMonth = ref(today.getMonth())
const currentYear = ref(today.getFullYear())
const searchQuery = ref('')
const dbEvents = ref<DatabaseEvent[]>([])
const isLoading = ref(false)

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
const years = computed(() => {
  const current = new Date().getFullYear()
  return Array.from({ length: 10 }, (_, i) => current - 2 + i)
})

// ----- Logic: Weather API -----
const getWeatherDescription = (code: number): string => {
  const weatherCodes: Record<number, string> = {
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
    const response = await fetch(url, {
      method: 'GET',
      credentials: 'omit' // Safely bypass CORS
    });

    if (!response.ok) throw new Error(`Weather API is down! Status: ${response.status}`);

    const data = await response.json();
    weatherCity.value = 'Butuan City';
    weatherTemp.value = Math.round(data.current_weather.temperature);
    weatherDesc.value = getWeatherDescription(data.current_weather.weathercode);
  } catch (e: any) {
    console.error("Weather Sync Error:", e.message);
    weatherTemp.value = '--';
    weatherDesc.value = 'Weather Unavailable';
  }
}

// ----- Logic: Search Filter & Dropdown -----
const filteredDbEvents = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  if (!query) return dbEvents.value

  return dbEvents.value.filter(event =>
    event.title.toLowerCase().includes(query)
  )
})

const isSearchFocused = ref(false)

const handleSearchBlur = () => {
  setTimeout(() => {
    isSearchFocused.value = false
  }, 150)
}

const openSearchedEvent = (event: DatabaseEvent) => {
  selectedEvents.value = [{
    title: event.title,
    venue: event.venue || event.Venue || 'TBA',
    description: event.content || 'No description provided.',
    start_time: event.start_time,
    end_time: event.end_time || null
  }]

  showEventDetailModal.value = true
  searchQuery.value = '' 
  isSearchFocused.value = false 
}

// ----- Integration Logic: Formatting & Tracks -----
const formatTime = (timeStr?: string | null): string => {
  if (!timeStr) return ''
  try {
    const date = new Date(timeStr)
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  } catch (e) {
    return timeStr
  }
}

const isSameDay = (dateStr1?: string | null, dateStr2?: string | null): boolean => {
  if (!dateStr1 || !dateStr2) return false
  const d1 = new Date(dateStr1)
  const d2 = new Date(dateStr2)
  return d1.getFullYear() === d2.getFullYear() &&
    d1.getMonth() === d2.getMonth() &&
    d1.getDate() === d2.getDate()
}

const isEventStart = (event: CalendarEvent, day: CalendarDay): boolean => {
  return !day.fullDate || !event.startTime || isSameDay(event.startTime, day.fullDate)
}

const getEventClasses = (event: CalendarEvent, day: CalendarDay): string[] => {
  if (!day.fullDate) return ['rounded', 'border-l-2']

  const isStart = isEventStart(event, day)
  const isEnd = !event.endTime || isSameDay(event.endTime, day.fullDate)
  const classes = ['relative', 'z-10']

  if (isStart) {
    classes.push('rounded-l', 'border-l-2')
  } else {
    classes.push('-ml-2', 'md:-ml-4', 'pl-2', 'md:pl-4', 'border-l-0', 'rounded-l-none')
  }

  if (isEnd) {
    classes.push('rounded-r')
  } else {
    classes.push('-mr-2', 'md:-mr-4', 'pr-2', 'md:pr-4', 'rounded-r-none')
  }

  return classes
}

const getEventStyle = (event: CalendarEvent): Record<string, string> => {
  const isBlue = event.color === 'blue'
  const colorHex = isBlue ? '#3b82f6' : '#f97316'
  return {
    backgroundColor: `${colorHex}30`,
    color: 'white',
    borderLeftColor: colorHex
  }
}

// ----- Logic: API Fetch Integration -----
const fetchEvents = async () => {
  isLoading.value = true
  try {
    const queryParams = new URLSearchParams({
      month: String(currentMonth.value + 1),
      year: String(currentYear.value)
    })

    const response = await fetch(`/api/events?${queryParams.toString()}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const contentType = response.headers.get("content-type");

    if (contentType && contentType.includes("text/html")) {
      const htmlText = await response.text();
      console.error("ERROR: LARAVEL RETURNED HTML");
      dbEvents.value = [];
      return;
    }

    if (!response.ok) return;

    const data = await response.json()
    dbEvents.value = data.events || []

  } catch (error) {
    console.error('Network/Parsing Error:', error)
  } finally {
    isLoading.value = false
  }
}

watch([currentMonth, currentYear], () => {
  fetchEvents()
})

const calendarDays = computed(() => {
  const days: CalendarDay[] = []
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)

  const startPadding = firstDay.getDay()
  const totalDays = lastDay.getDate()
  const prevMonthLastDay = new Date(currentYear.value, currentMonth.value, 0).getDate()

  // Previous month overflow
  for (let i = startPadding - 1; i >= 0; i--) {
    const padDate = prevMonthLastDay - i
    const prevMonth = currentMonth.value === 0 ? 11 : currentMonth.value - 1
    const prevYear = currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value
    const fullDate = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(padDate).padStart(2, '0')}`
    days.push({ date: padDate, fullDate, isCurrentMonth: false, events: [] })
  }

  // Current month
  for (let i = 1; i <= totalDays; i++) {
    const fullDate = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`
    days.push({ date: i, fullDate, isCurrentMonth: true, events: [] })
  }

  // Next month overflow
  let nextMonthDay = 1
  while (days.length < 42) {
    const nextMonth = currentMonth.value === 11 ? 0 : currentMonth.value + 1
    const nextYear = currentMonth.value === 11 ? currentYear.value + 1 : currentYear.value
    const fullDate = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(nextMonthDay).padStart(2, '0')}`
    days.push({ date: nextMonthDay++, fullDate, isCurrentMonth: false, events: [] })
  }

  // Map events USING ALL dbEvents
  dbEvents.value.forEach(event => {
    if (!event.start_time) return

    const startDate = new Date(event.start_time)
    const endDate = event.end_time ? new Date(event.end_time) : new Date(startDate)

    startDate.setHours(0, 0, 0, 0)
    endDate.setHours(23, 59, 59, 999)

    const calendarEvent: CalendarEvent = {
      id: event.event_id,
      title: event.title,
      color: event.color,
      venue: event.venue || event.Venue,
      description: event.content,
      startTime: event.start_time,
      endTime: event.end_time
    }

    days.forEach(day => {
      if (!day.fullDate) return
      const currentCellDate = new Date(day.fullDate)
      currentCellDate.setHours(12, 0, 0, 0)

      if (currentCellDate >= startDate && currentCellDate <= endDate) {
        day.events.push(calendarEvent)
        day.isHighlight = true
      }
    })
  })

  return days
})

// ----- Apply Track Allocation Logic -----
const processedDays = computed(() => {
  const activeTracks: (string | number | null)[] = []
  const result: CalendarDay[] = []

  for (const day of calendarDays.value) {
    if (!day.fullDate || !day.events || !day.events.length) {
      activeTracks.fill(null)
      result.push({ ...day, slottedEvents: [] })
      continue
    }

    const currentEventIds = new Set(day.events.map(e => e.id))

    for (let i = 0; i < activeTracks.length; i++) {
      if (activeTracks[i] !== null && !currentEventIds.has(activeTracks[i]!)) {
        activeTracks[i] = null
      }
    }

    const slottedEvents: (CalendarEvent | null)[] = []
    let maxTrack = -1
    const unplacedEvents: CalendarEvent[] = []

    for (const event of day.events) {
      const trackIdx = activeTracks.indexOf(event.id)
      if (trackIdx !== -1) {
        slottedEvents[trackIdx] = event
        maxTrack = Math.max(maxTrack, trackIdx)
      } else {
        unplacedEvents.push(event)
      }
    }

    for (const event of unplacedEvents) {
      let trackIdx = 0
      while (activeTracks[trackIdx] !== null && activeTracks[trackIdx] !== undefined) {
        trackIdx++
      }
      activeTracks[trackIdx] = event.id
      slottedEvents[trackIdx] = event
      maxTrack = Math.max(maxTrack, trackIdx)
    }

    const finalEvents = []
    for (let i = 0; i <= maxTrack; i++) {
      finalEvents.push(slottedEvents[i] || null)
    }

    result.push({ ...day, slottedEvents: finalEvents })
  }

  return result
})

const goBack = () => {
  window.history.back()
}

const isToday = (dateString?: string) => {
  if (!dateString) return false
  const d = new Date()
  const todayStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
  return dateString === todayStr
}

// ----- Modal Activation Logic -----
const openEventDetail = (day: CalendarDay) => {
  if (day.events && day.events.length > 0) {
    selectedEvents.value = day.events.map(e => ({
      title: e.title,
      venue: e.venue || 'TBA',
      description: e.description || 'No description provided.',
      start_time: e.startTime || '',
      end_time: e.endTime || null
    }))
    showEventDetailModal.value = true
  } else {
    selectedEvents.value = []
  }
}

const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true })
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }).toUpperCase()
  currentMonthYear.value = now.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }).toUpperCase()
}

onMounted(() => {
  if (initTheme) initTheme() 
  updateClock()
  fetchEvents()
  fetchWeather() // <-- Kick off weather fetch on load
  
  clockTimer = setInterval(updateClock, 1000)
  weatherTimer = setInterval(fetchWeather, 1800000) // <-- Update weather every 30 minutes
})

onUnmounted(() => {
  clearInterval(clockTimer)
  clearInterval(weatherTimer) // <-- Cleanup the timer to prevent memory leaks
})
</script>