<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useTheme } from '../composable/usetheme.ts'
import EventCreateModal from '../modals/eventcreatemodal.vue'
import EventDetailModal from '../modals/eventdetailmodal.vue'
import EventEditModal from '../modals/eventupdatemodal.vue' 
import MonthYearSelector from '../components/monthyearselector.vue'
import UpcomingEvents from '../components/upcomingevents.vue'
import CalendarGrid from '../components/calendargrid.vue'
import UserEvents from '../components/userevents.vue' 

const selectedEvents = ref<Array<{id?: string | number, title: string, venue: string, description: string, start_time: string, end_time?: string | null}>>([])

interface CalendarEvent {
    id: string | number
    title: string
    color?: string
    venue?: string
    description?: string
    start_time?: string  
    end_time?: string | null  
    startTime?: string   
    endTime?: string | null   
}

interface CalendarDay {
    date: number
    fullDate?: string    
    isCurrentMonth: boolean
    events: CalendarEvent[]
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
}

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
    csrfToken?: string 
}>()

const { theme, styles, surface, setUserType, initTheme } = useTheme()

const today = new Date()
const currentMonth = ref(today.getMonth()) 
const currentYear = ref(today.getFullYear())

const dbEvents = ref<DatabaseEvent[]>([])
const isLoading = ref(false)

// Modal States
const showCreateModal = ref(false)
const showEventDetailModal = ref(false)
const showEditModal = ref(false)
const showUserEventsModal = ref(false) // <-- NEW: State for the UserEvents modal
const eventToEdit = ref<any>(null)
const selectedDay = ref<CalendarDay | null>(null)

// Ref to call refresh on UserEvents component
const userEventsRef = ref<InstanceType<typeof UserEvents> | null>(null)

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

// Triggers refresh for both global calendar and user-specific events
const handleEventsUpdated = () => {
    fetchEvents()
    if (userEventsRef.value) {
        userEventsRef.value.refresh()
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
            venue: event.venue || event.Venue, 
            description: event.content,        
            start_time: event.start_time,
            end_time: event.end_time, 
            startTime: event.start_time, 
            endTime: event.end_time,  
            color: theme.value.accent
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

const openEventDetail = (day: CalendarDay) => {
    selectedDay.value = day
    if (day.events && day.events.length > 0) {
        selectedEvents.value = day.events.map(e => ({
            id: e.id,
            title: e.title,
            venue: e.venue || 'TBA',
            description: e.description || 'No description provided.',
            start_time: e.start_time || '',
            end_time: e.end_time || null
        }))
        showEventDetailModal.value = true
    } else {
        selectedEvents.value = []
    }
}

const openUpcomingEventDetail = async (eventId: number) => {
    const event = dbEvents.value.find(e => e.event_id === eventId)
    
    if (event) {
        selectedEvents.value = [{
            id: event.event_id,
            title: event.title,
            venue: event.venue || event.Venue || 'TBA',
            description: event.content,
            start_time: event.start_time,
            end_time: event.end_time || null
        }]
        showEventDetailModal.value = true
    } else {
        try {
            const response = await fetch('/api/events/upcoming')
            const data = await response.json()
            const futureEvent = data.events?.find((e: any) => e.event_id === eventId || e.id === eventId)
            
            if (futureEvent) {
                selectedEvents.value = [{
                    id: futureEvent.event_id || futureEvent.id,
                    title: futureEvent.title,
                    venue: futureEvent.venue || futureEvent.Venue || 'TBA', 
                    description: futureEvent.content || 'No description provided.',
                    start_time: futureEvent.start_time,
                    end_time: futureEvent.end_time || null
                }]
                showEventDetailModal.value = true
            }
        } catch (error) {
            console.error("Could not load future event details", error)
        }
    }
}

const openEditModal = (eventData: any) => {
    eventToEdit.value = eventData
    showEventDetailModal.value = false 
    showEditModal.value = true
}

onMounted(() => {
    initTheme()
    if (props.user?.user_type) {
        setUserType(props.user.user_type)
    }
    fetchEvents()
})
</script>

<template>
    <div class="max-w-7xl mx-auto pb-12 w-full min-w-0">
        
        <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4 w-full">
            <div class="flex flex-col gap-2">
                <h3 class="text-2xl md:text-3xl font-bold tracking-tight" :style="styles.textPrimary">
                    Academic Calendar
                </h3>
                <p :style="styles.textSecondary" class="text-sm md:text-base max-w-2xl">
                    Manage and track upcoming university events, exams, and holidays.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full xl:w-auto">
                <MonthYearSelector 
                    :theme="theme" 
                    :surface="surface" 
                    v-model:month="currentMonth"
                    v-model:year="currentYear"
                    class="w-full sm:w-auto"
                />

                <button 
                    @click="showUserEventsModal = true" 
                    class="flex items-center justify-center gap-2 px-4 h-10 w-full sm:w-auto rounded-lg font-semibold text-sm border transition-all"
                    :style="{ color: styles.textPrimary.color, backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }"
                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '0.8'"
                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '1'"
                >
                    <span class="material-symbols-outlined text-[20px]">event_list</span>
                    My Events
                </button>

                <button 
                    @click="showCreateModal = true" 
                    class="flex items-center justify-center gap-2 px-4 h-10 w-full sm:w-auto rounded-lg font-semibold text-sm transition-all"
                    :style="styles.button"
                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '0.9'"
                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '1'"
                >
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Post New Event
                </button>
            </div>
        </div>

        <div class="flex flex-col xl:flex-row gap-6 items-start w-full min-w-0">
            
            <div class="w-full xl:flex-1 min-w-0 max-w-full flex flex-col">
                <div class="w-full pb-4">
                    <CalendarGrid 
                        :theme="theme" 
                        :surface="surface" 
                        :styles="styles"
                        :days="calendarDays"
                        @show-detail="openEventDetail"
                    />
                </div>
            </div>
            
            <aside class="w-full xl:w-87.5 shrink-0 min-w-0">
                <UpcomingEvents 
                    :theme="theme" 
                    :surface="surface" 
                    :styles="styles"
                    @show-detail="openUpcomingEventDetail" 
                    class="w-full"
                />
            </aside>
        </div>
    </div>

    <Teleport to="body">
        
        <UserEvents 
            ref="userEventsRef"
            :isOpen="showUserEventsModal"
            :theme="theme"
            :surface="surface"
            :styles="styles"
            @close="showUserEventsModal = false"
            @edit="openEditModal"
            @deleted="handleEventsUpdated" 
        />

        <EventCreateModal 
            :show="showCreateModal"
            :theme="theme"
            :surface="surface"
            :styles="styles"
            @close="showCreateModal = false"
            @created="handleEventsUpdated" 
        />

        <EventDetailModal 
            :show="showEventDetailModal"
            :theme="theme"
            :surface="surface"
            :styles="styles"
            :events="selectedEvents" 
            @close="showEventDetailModal = false"
        />

        <EventEditModal
            :show="showEditModal"
            :theme="theme"
            :surface="surface"
            :styles="styles"
            :eventData="eventToEdit"
            @close="showEditModal = false; eventToEdit = null"
            @updated="handleEventsUpdated"
        />
    </Teleport>
</template>