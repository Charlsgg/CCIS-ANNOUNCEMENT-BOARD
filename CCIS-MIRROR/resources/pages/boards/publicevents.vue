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

    <main class="max-w-6xl mx-auto relative z-10 flex flex-col h-full">
      <section class="flex flex-col md:flex-row gap-4 mb-8 items-center relative">
        <button @click="goBack"
          class="flex items-center gap-2 px-6 py-3 rounded-full bg-white hover:bg-orange-50 border border-gray-200 hover:border-orange-500 transition-all duration-300 text-sm font-medium whitespace-nowrap group shadow-sm text-gray-700">
          <span
            class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span>
          <span>Back</span>
        </button>

        <div class="relative w-full z-100">
          <div class="relative flex items-center">
            <span class="material-symbols-outlined absolute left-4 text-gray-400 pointer-events-none">search</span>
            <input
              class="w-full bg-white border border-gray-200 rounded-xl px-12 py-3 text-lg text-gray-800 hover:border-orange-500/50 focus:border-orange-500 focus:bg-white focus:ring-1 focus:ring-orange-500/50 transition-all duration-300 outline-none shadow-sm"
              placeholder="Search events..." type="text" v-model="searchQuery" @focus="isSearchFocused = true"
              @blur="handleSearchBlur" />
          </div>

          <transition enter-active-class="transition duration-200 ease-out" enter-from-class="translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100" leave-to-class="translate-y-1 opacity-0">
            <div v-if="searchQuery && isSearchFocused"
              class="absolute top-full left-0 w-full mt-2 bg-white/90 overflow-hidden max-h-100 overflow-y-auto custom-scrollbar shadow-2xl border border-gray-200 rounded-xl z-110 backdrop-blur-xl">
              <ul v-if="filteredDbEvents.length > 0">
                <li v-for="event in filteredDbEvents" :key="event.event_id" @mousedown="openSearchedEvent(event)"
                  class="px-5 py-4 border-b border-gray-100 hover:bg-orange-50 cursor-pointer transition-all flex flex-col gap-1 last:border-0 group">
                  <span class="font-bold text-orange-500 group-hover:text-orange-600 transition-colors">{{ event.title
                    }}</span>
                  <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                    <span>{{ new Date(event.start_time).toLocaleDateString() }}</span>
                    <span v-if="event.venue || event.Venue" class="flex items-center gap-1">
                      <span class="opacity-40">•</span>
                      <span class="material-symbols-outlined text-[14px]">location_on</span>
                      {{ event.venue || event.Venue }}
                    </span>
                  </div>
                </li>
              </ul>
              <div v-else class="px-5 py-8 text-center text-gray-500 text-sm italic">
                No events found matching "{{ searchQuery }}"
              </div>
            </div>
          </transition>
        </div>
      </section>

      <div class="flex-1 flex flex-col mt-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
          <h2 class="text-4xl font-light text-gray-900">Academic <span class="text-orange-500 font-bold">Calendar</span></h2>
          <div class="flex space-x-4">
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl px-4 py-2 flex items-center space-x-2">
              <span class="text-orange-500 text-xs font-bold uppercase">Month</span>
              <select v-model="currentMonth"
                class="bg-transparent border-none text-gray-800 text-sm focus:ring-0 cursor-pointer outline-none w-24 appearance-none">
                <option v-for="(month, index) in months" :key="index" :value="index" class="bg-white text-gray-900">{{
                  month }}</option>
              </select>
            </div>
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl px-4 py-2 flex items-center space-x-2">
              <span class="text-orange-500 text-xs font-bold uppercase">Year</span>
              <select v-model="currentYear"
                class="bg-transparent border-none text-gray-800 text-sm focus:ring-0 cursor-pointer outline-none w-20 appearance-none">
                <option v-for="year in years" :key="year" :value="year" class="bg-white text-gray-900">{{ year }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="flex-1 bg-white border border-gray-200 shadow-sm rounded-2xl overflow-hidden flex flex-col min-h-37.5">
          <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
            <div v-for="day in ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']" :key="day"
              class="py-4 text-center text-orange-500 text-xs md:text-sm font-black tracking-widest">
              {{ day }}
            </div>
          </div>

          <div class="flex-1 grid grid-cols-7 overflow-y-auto custom-scrollbar bg-white">
            <div v-for="(day, index) in processedDays" :key="index" @click="openEventDetail(day)" :class="[
              'border-b border-r border-gray-100 p-2 md:p-4 min-h-30 transition-colors cursor-pointer group flex flex-col overflow-hidden',
              !day.isCurrentMonth ? 'opacity-40 bg-gray-50' : 'hover:bg-gray-50',
              day.isHighlight ? 'bg-orange-50/50' : ''
            ]">

              <span :class="[
                'text-lg md:text-2xl font-light inline-block w-8 h-8 md:w-10 md:h-10 text-center leading-8 md:leading-10 rounded-full transition-all mb-2',
                isToday(day.fullDate) ? 'bg-orange-500 text-white font-bold shadow-[0_0_15px_rgba(249,115,22,0.4)]' : 'text-gray-700 group-hover:text-orange-500',
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
                    <span class="truncate px-1.5 drop-shadow-sm">{{ event.title }}</span>

                    <div v-if="event.startTime"
                      class="px-1.5 opacity-90 font-medium normal-case tracking-wider text-[7px] md:text-[8px] truncate mt-0.5">
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

    <button @click="toggleFullScreen"
      class="fixed bottom-6 left-6 z-50 flex items-center justify-center w-12 h-12 rounded-full bg-white hover:bg-gray-50 border border-gray-200 hover:border-orange-500 transition-all duration-300 group shadow-md"
      title="Toggle Fullscreen">
      <span class="material-symbols-outlined text-gray-600 group-hover:text-orange-500 transition-colors text-2xl">
        {{ isFullScreen ? 'fullscreen_exit' : 'fullscreen' }}
      </span>
    </button>

    <Teleport to="body">
      <PublicEventDetailModal v-if="showEventDetailModal" :show="showEventDetailModal" :theme="theme" :surface="surface"
        :styles="styles" :events="selectedEvents" @close="showEventDetailModal = false" />
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

// Import components and composables
import AppHeader from '../components/boardheader.vue' 
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

// ----- Fullscreen Logic -----
const isFullScreen = ref(false)

const handleFullscreenChange = () => {
  isFullScreen.value = !!document.fullscreenElement
}

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
    backgroundColor: `${colorHex}15`, // Lightened the background tint slightly for white theme
    color: colorHex, // Make the text color the primary color for contrast on white
    borderLeftColor: colorHex
  }
}

// ----- Logic: API Fetch Integration -----
const fetchMonthEvents = async (month: number, year: number) => {
  const queryParams = new URLSearchParams({
    month: String(month),
    year: String(year)
  })

  const response = await fetch(`/api/events?${queryParams.toString()}`, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })

  const contentType = response.headers.get("content-type")
  if (contentType && contentType.includes("text/html")) {
    console.error("ERROR: LARAVEL RETURNED HTML")
    return []
  }

  if (!response.ok) return []

  const data = await response.json()
  return data.events || []
}

const fetchEvents = async () => {
  isLoading.value = true
  try {
    const prevDate = new Date(currentYear.value, currentMonth.value - 1, 1)
    const nextDate = new Date(currentYear.value, currentMonth.value + 1, 1)

    const [prevEvents, currentEvents, nextEvents] = await Promise.all([
      fetchMonthEvents(prevDate.getMonth() + 1, prevDate.getFullYear()),
      fetchMonthEvents(currentMonth.value + 1, currentYear.value),
      fetchMonthEvents(nextDate.getMonth() + 1, nextDate.getFullYear())
    ])

    const combinedEvents = [...prevEvents, ...currentEvents, ...nextEvents]
    const uniqueEvents = Array.from(new Map(combinedEvents.map(e => [e.event_id, e])).values())

    dbEvents.value = uniqueEvents

  } catch (error) {
    console.error('Network/Parsing Error:', error)
  } finally {
    isLoading.value = false
  }
}

const calendarDays = computed(() => {
  const days: CalendarDay[] = []
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)

  const startPadding = firstDay.getDay()
  const totalDays = lastDay.getDate()
  const prevMonthLastDay = new Date(currentYear.value, currentMonth.value, 0).getDate()

  for (let i = startPadding - 1; i >= 0; i--) {
    const padDate = prevMonthLastDay - i
    const prevMonth = currentMonth.value === 0 ? 11 : currentMonth.value - 1
    const prevYear = currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value
    const fullDate = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(padDate).padStart(2, '0')}`
    days.push({ date: padDate, fullDate, isCurrentMonth: false, events: [] })
  }

  for (let i = 1; i <= totalDays; i++) {
    const fullDate = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`
    days.push({ date: i, fullDate, isCurrentMonth: true, events: [] })
  }

  let nextMonthDay = 1
  while (days.length < 42) {
    const nextMonth = currentMonth.value === 11 ? 0 : currentMonth.value + 1
    const nextYear = currentMonth.value === 11 ? currentYear.value + 1 : currentYear.value
    const fullDate = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(nextMonthDay).padStart(2, '0')}`
    days.push({ date: nextMonthDay++, fullDate, isCurrentMonth: false, events: [] })
  }

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

onMounted(() => {
  if (initTheme) initTheme()
  fetchEvents()
  // Uses named function reference properly for memory cleanup
  document.addEventListener('fullscreenchange', handleFullscreenChange) 
})

onUnmounted(() => {
  // Pass the exact same named function to properly remove the listener
  document.removeEventListener('fullscreenchange', handleFullscreenChange)
})

</script>