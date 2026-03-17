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
import SearchBar from '../components/searchbar.vue'
import UpcomingEvents from '../components/upcomingevents.vue'
import CalendarGrid from '../components/calendargrid.vue'
interface CalendarEvent {
    id: string | number
    title: string
    color?: string
    venue?: string
    description?: string
    start_time?: string // Added to read from DB
}

interface CalendarDay {
    date: number
    isCurrentMonth: boolean
    events: CalendarEvent[]
    isHighlight?: boolean
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

// NEW: Store events fetched from the database
const dbEvents = ref<DatabaseEvent[]>([])
const isLoading = ref(false)

// Modal state managers
const showCreateModal = ref(false)
const showEventDetailModal = ref(false)
const selectedDay = ref<CalendarDay | null>(null)

// NEW: Function to fetch events from Laravel
const fetchEvents = async () => {
    isLoading.value = true
    try {
        // We add +1 to month because JavaScript months are 0-11, but your DB uses 1-12
        const queryParams = new URLSearchParams({
            month: String(currentMonth.value + 1),
            year: String(currentYear.value)
        })

        // Replace with your actual Laravel route
        // Inside your fetchEvents() function:
        const response = await fetch(`/api/events?${queryParams.toString()}`)
        
        if (response.ok) {
            const data = await response.json()
            dbEvents.value = data.events // Save the DB events to our ref
        }
    } catch (error) {
        console.error('Error fetching events:', error)
    } finally {
        isLoading.value = false
    }
}

// NEW: Automatically refetch events when the user changes the month or year dropdowns!
watch([currentMonth, currentYear], () => {
    fetchEvents()
})
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
const calendarDays = computed(() => {
    const days: CalendarDay[] = []
    const firstDay = new Date(currentYear.value, currentMonth.value, 1)
    const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
    
    const startPadding = firstDay.getDay()
    const totalDays = lastDay.getDate()
    const prevMonthLastDay = new Date(currentYear.value, currentMonth.value, 0).getDate()
    
    // Previous month padding
    for (let i = startPadding - 1; i >= 0; i--) {
        days.push({ date: prevMonthLastDay - i, isCurrentMonth: false, events: [] })
    }
    
    // Current month days
    for (let i = 1; i <= totalDays; i++) {
        // NEW: Filter the database events to find ones that match this specific day
        const dayEvents = dbEvents.value.filter(event => {
            if (!event.start_time) return false;
            const eventDate = new Date(event.start_time);
            
            // Check if the event's date matches the current calendar square
            return eventDate.getDate() === i &&
                   eventDate.getMonth() === currentMonth.value &&
                   eventDate.getFullYear() === currentYear.value;
        }).map(event => {
           
            return {
                id: event.event_id, 
                title: event.title,
                venue: event.Venue,    
                description: event.content,
                start_time: event.start_time,
                color: theme.value.accent
                
                
            }
        });

        days.push({
            date: i,
            isCurrentMonth: true,
            events: dayEvents,
            isHighlight: dayEvents.length > 0 // Highlight the day if it has events
        })
    }
    
    // Next month padding
    let nextMonthDay = 1
    while (days.length < 42) {
        days.push({ date: nextMonthDay++, isCurrentMonth: false, events: [] })
    }
    
    return days
})

const openEventDetail = (day: CalendarDay) => {
    selectedDay.value = day
    showEventDetailModal.value = true
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
                            <SearchBar :surface="surface" />

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
                            @show-detail="showEventDetailModal = true"
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
            />

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
                            
                            <div class="font-bold text-sm mb-1" :style="{ color: theme.accent }">
                                {{ selectedDay ? `${currentMonth + 1}/${selectedDay.date}/${currentYear}` : 'Event Details' }}
                            </div>
                            <h3 class="text-2xl font-bold mb-4" :style="styles.textPrimary">
                                {{ selectedDay?.events?.[0]?.title || 'Selected Event' }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined mt-0.5" :style="{ color: theme.accent }">location_on</span>
                                    <div>
                                        <p class="text-xs font-bold uppercase" :style="styles.textMuted">Venue</p>
                                        <p class="text-sm" :style="styles.textPrimary">Main Campus Building, All Lecture Halls</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined mt-0.5" :style="{ color: theme.accent }">description</span>
                                    <div>
                                        <p class="text-xs font-bold uppercase" :style="styles.textMuted">Description</p>
                                        <p class="text-sm leading-relaxed" :style="styles.textSecondary">Mandatory evaluation period for all CCIS students. Please refer to your specific department for detailed schedules per subject. Bring valid school ID and permit.</p>
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