<script lang="ts">
export default { layout: null }
</script>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useTheme } from '../composable/usetheme.ts'
import AppSidebar from '../components/appsidebar.vue'
import AppNavbar from '../components/appnavbar.vue'
import EventCreateModal from '../modals/eventcreatemodal.vue'
import MonthYearSelector from '../components/monthyearselector.vue'
import UpcomingEvents from '../components/upcomingevents.vue'
import CalendarGrid from '../components/calendargrid.vue'

interface CalendarEvent {
    id: string | number
    title: string
    color?: string
    venue?: string
    description?: string
    start_time?: string  // Kept for your modal logic
    end_time?: string    // Kept for your modal logic
    startTime?: string   // ADDED: For multi-day ribbon logic
    endTime?: string     // ADDED: For multi-day ribbon logic
}

interface CalendarDay {
    date: number
    fullDate?: string    // ADDED: For multi-day date comparisons
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
    Venue: string
    start_time: string
    end_time?: string
    event_month?: number
    event_year?: number
}

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
}>()

const { theme, styles, surface, isDark, setUserType, initTheme } = useTheme()

const isSidebarOpen = ref(false)
const csrfToken = ref('')

// Calendar State
const today = new Date()
const currentMonth = ref(today.getMonth()) // 0-11
const currentYear = ref(today.getFullYear())

// Store events fetched from the database
const dbEvents = ref<DatabaseEvent[]>([])
const isLoading = ref(false)

// Modal state managers
const showCreateModal = ref(false)
const showEventDetailModal = ref(false)
const selectedDay = ref<CalendarDay | null>(null)

// NEW: Unified state for the modal to display (handles both Calendar clicks and Upcoming Events clicks)
const selectedEvent = ref<{title: string, venue: string, description: string, start_time: string} | null>(null)

const fetchEvents = async () => {
    isLoading.value = true
    try {
        const queryParams = new URLSearchParams({
            month: String(currentMonth.value + 1),
            year: String(currentYear.value)
        })

        const response = await fetch(`/api/events?${queryParams.toString()}`)
        
        if (response.ok) {
            const data = await response.json()
            dbEvents.value = data.events 
        }
    } catch (error) {
        console.error('Error fetching events:', error)
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
    
    // 1. Generate padding days (previous month)
    for (let i = startPadding - 1; i >= 0; i--) {
        const padDate = prevMonthLastDay - i
        const prevMonth = currentMonth.value === 0 ? 11 : currentMonth.value - 1
        const prevYear = currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value
        const fullDate = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(padDate).padStart(2, '0')}`
        
        days.push({ date: padDate, fullDate, isCurrentMonth: false, events: [] })
    }
    
    // 2. Generate current month days
    for (let i = 1; i <= totalDays; i++) {
        const fullDate = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`
        days.push({ date: i, fullDate, isCurrentMonth: true, events: [] })
    }
    
    // 3. Generate padding days (next month)
    let nextMonthDay = 1
    while (days.length < 42) {
        const nextMonth = currentMonth.value === 11 ? 0 : currentMonth.value + 1
        const nextYear = currentMonth.value === 11 ? currentYear.value + 1 : currentYear.value
        const fullDate = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(nextMonthDay).padStart(2, '0')}`
        
        days.push({ date: nextMonthDay++, fullDate, isCurrentMonth: false, events: [] })
    }
    
    // 4. Distribute events across the generated days
    dbEvents.value.forEach(event => {
        if (!event.start_time) return
        
        const startDate = new Date(event.start_time)
        // Default to start date if no end date exists
        const endDate = event.end_time ? new Date(event.end_time) : new Date(startDate)
        
        // Strip the time portion to ensure strict date-to-date comparison
        startDate.setHours(0, 0, 0, 0)
        endDate.setHours(23, 59, 59, 999)
        
        const calendarEvent: CalendarEvent = {
            id: event.event_id, 
            title: event.title,
            venue: event.Venue,    
            description: event.content,
            start_time: event.start_time,
            end_time: event.end_time,
            startTime: event.start_time, // Passed to grid for start boundary check
            endTime: event.end_time,     // Passed to grid for end boundary check
            color: theme.value.accent
        }

        days.forEach(day => {
            if (!day.fullDate) return
            
            const currentCellDate = new Date(day.fullDate)
            // Set cell date to noon to avoid unexpected timezone shifts
            currentCellDate.setHours(12, 0, 0, 0) 

            // If the cell falls within the start and end range, add the event
            if (currentCellDate >= startDate && currentCellDate <= endDate) {
                day.events.push(calendarEvent)
                day.isHighlight = true
            }
        })
    })
    
    return days
})

// Handles clicks from the Calendar Grid
const openEventDetail = (day: CalendarDay) => {
    selectedDay.value = day
    if (day.events && day.events.length > 0) {
        selectedEvent.value = {
            title: day.events[0].title,
            venue: day.events[0].venue || 'TBA',
            description: day.events[0].description || 'No description provided.',
            start_time: day.events[0].start_time || ''
        }
    } else {
        selectedEvent.value = null
    }
    showEventDetailModal.value = true
}

// NEW: Handles clicks from the Upcoming Events Sidebar
const openUpcomingEventDetail = async (eventId: number) => {
    // 1. Check if the event is already in the current month's calendar view
    const event = dbEvents.value.find(e => e.event_id === eventId)
    
    if (event) {
        selectedEvent.value = {
            title: event.title,
            venue: event.Venue,
            description: event.content,
            start_time: event.start_time
        }
        showEventDetailModal.value = true
    } else {
        // 2. If it's next month, it won't be in dbEvents. Fetch it from the upcoming list!
        try {
            const response = await fetch('/api/events/upcoming')
            const data = await response.json()
            const futureEvent = data.events?.find((e: any) => e.id === eventId || e.event_id === eventId)
            
            if (futureEvent) {
                selectedEvent.value = {
                    title: futureEvent.title,
                    venue: futureEvent.location || futureEvent.Venue || 'TBA', 
                    description: futureEvent.description || futureEvent.content || 'No description provided.',
                    start_time: futureEvent.start_time || futureEvent.created_at
                }
                showEventDetailModal.value = true
            }
        } catch (error) {
            console.error("Could not load future event details", error)
        }
    }
}

// Helper to format the date nicely for the modal header
const formatModalDate = (dateString?: string) => {
    if (!dateString) return 'Event Details'
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(() => {
    initTheme()
    if (props.user?.user_type) {
        setUserType(props.user.user_type)
    }
    
    const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
    if (tokenTag) {
        csrfToken.value = tokenTag.content
    }
    fetchEvents()
})
</script>

<template>
    <div
        class="fixed inset-0 w-full h-full overflow-hidden font-['Space_Grotesk'] flex transition-colors duration-300"
        :style="{ ...styles.pageBg, color: surface.textPrimary }"
    >
        <div
            v-if="isSidebarOpen"
            @click="isSidebarOpen = false"
            class="absolute inset-0 z-40 md:hidden backdrop-blur-sm transition-opacity"
            :style="{ backgroundColor: surface.overlayBg }"
        ></div>

        <AppSidebar
            :is-open="isSidebarOpen"
            :csrf-token="csrfToken"
            @close="isSidebarOpen = false"
        />

        <main class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
            <AppNavbar
                :user-name="user?.name"
                @toggle-sidebar="isSidebarOpen = true"
            />

            <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full no-scrollbar">
                <div class="max-w-7xl mx-auto pb-12">
                    
                    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                        <div class="flex flex-col gap-2">
                            <h3 class="text-2xl md:text-3xl font-bold tracking-tight" :style="styles.textPrimary">
                                Academic Calendar
                            </h3>
                            <p :style="styles.textSecondary" class="text-sm md:text-base max-w-2xl">
                                Manage and track upcoming university events, exams, and holidays.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <MonthYearSelector 
                                :theme="theme" 
                                :surface="surface" 
                                v-model:month="currentMonth"
                                v-model:year="currentYear"
                            />

                            <button 
                                @click="showCreateModal = true" 
                                class="flex items-center gap-2 px-4 h-10 rounded-lg font-semibold text-sm transition-all"
                                :style="styles.button"
                                @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '0.9'"
                                @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '1'"
                            >
                                <span class="material-symbols-outlined text-[20px]">add</span>
                                Post New Event
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col xl:flex-row gap-6 items-start">
                        <CalendarGrid 
                            :theme="theme" 
                            :surface="surface" 
                            :styles="styles"
                            :days="calendarDays"
                            @show-detail="openEventDetail"
                        />

                        <UpcomingEvents 
                            :theme="theme" 
                            :surface="surface" 
                            :styles="styles"
                            @show-detail="openUpcomingEventDetail" 
                        />
                    </div>

                </div>
            </div>
        </main>

        <Teleport to="body">
            <EventCreateModal 
            :show="showCreateModal"
            :theme="theme"
            :surface="surface"
            :styles="styles"
            @close="showCreateModal = false"
            @created="fetchEvents" />

            <div 
                v-if="showEventDetailModal" 
                class="fixed inset-0 backdrop-blur-sm z-101 flex items-center justify-center p-4 transition-opacity"
                :style="{ backgroundColor: surface.overlayBg }"
            >
                <div class="w-full max-w-md rounded-2xl shadow-2xl overflow-hidden" :style="styles.cardBg">
                    <div class="relative h-32 flex items-center justify-center" :style="{ backgroundColor: theme.accent }">
                        <span class="material-symbols-outlined text-white text-6xl opacity-30">event_note</span>
                        <button 
                            @click="showEventDetailModal = false" 
                            class="absolute top-4 right-4 size-8 rounded-full bg-black/20 text-white flex items-center justify-center hover:bg-black/40 transition-colors"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-8 -mt-10">
                        <div class="p-6 rounded-xl shadow-xl border" :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
                            
                            <div class="font-bold text-sm mb-1 uppercase tracking-wide" :style="{ color: theme.accent }">
                                {{ formatModalDate(selectedEvent?.start_time) }}
                            </div>

                            <h3 class="text-2xl font-bold mb-4" :style="styles.textPrimary">
                                {{ selectedEvent?.title || 'No Title Selected' }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined mt-0.5" :style="{ color: theme.accent }">location_on</span>
                                    <div>
                                        <p class="text-xs font-bold uppercase" :style="styles.textMuted">Venue</p>
                                        <p class="text-sm" :style="styles.textPrimary">{{ selectedEvent?.venue || 'TBA' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined mt-0.5" :style="{ color: theme.accent }">description</span>
                                    <div>
                                        <p class="text-xs font-bold uppercase" :style="styles.textMuted">Description</p>
                                        <p class="text-sm leading-relaxed" :style="styles.textSecondary">
                                            {{ selectedEvent?.description || 'No additional details provided.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex gap-2">
                                <button 
                                    class="flex-1 py-2.5 rounded-lg font-bold text-sm transition-opacity"
                                    :style="{ backgroundColor: theme.accent + '20', color: theme.accent }"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '0.8'"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '1'"
                                >
                                    Edit Event
                                </button>
                                <button 
                                    class="flex-1 py-2.5 rounded-lg border text-red-500 font-bold text-sm transition-colors"
                                    :style="{ borderColor: 'rgba(239, 68, 68, 0.2)' }"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'rgba(239, 68, 68, 0.1)'"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap');

.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>