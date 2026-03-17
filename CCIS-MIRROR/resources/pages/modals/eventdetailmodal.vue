<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

interface CalendarEvent {
    title: string
    venue: string
    venueDetail?: string
    description: string
    descriptionLong?: string
    start_time: string
    end_time?: string
    category?: string
    attendees?: number
}

const props = defineProps<{
    show: boolean
    events: CalendarEvent[]
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

// Use the theme composable
const { theme, surface, styles, isDark } = useTheme()

const selectedIndex = ref(0)

// Handle scroll lock and reset selection
watch(() => props.show, (newVal) => {
    if (newVal) {
        selectedIndex.value = 0
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = 'auto'
    }
})

const activeEvent = computed(() => props.events[selectedIndex.value] || null)

// Date Formatting Helpers
const getMonth = (dateStr?: string) => {
    if (!dateStr) return '---'
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short' })
}

const getDay = (dateStr?: string) => {
    if (!dateStr) return '--'
    return new Date(dateStr).getDate()
}

const formatFullDate = (dateStr?: string) => {
    if (!dateStr) return 'TBA'
    return new Date(dateStr).toLocaleDateString('en-US', { 
        month: 'short', day: 'numeric', year: 'numeric' 
    })
}

const formatTime = (dateStr?: string) => {
    if (!dateStr) return '--:--'
    return new Date(dateStr).toLocaleTimeString('en-US', { 
        hour: '2-digit', minute: '2-digit' 
    })
}
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div 
                v-if="show" 
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 transition-colors duration-300"
                :style="{ backgroundColor: surface.overlayBg }"
            >
                <div class="absolute inset-0" @click="$emit('close')"></div>

                <div 
                    class="relative rounded-2xl shadow-2xl flex flex-col w-full max-w-5xl h-[85vh] md:h-[750px] overflow-hidden transition-all duration-300 border"
                    :style="styles.cardBg"
                >
                    <button 
                        @click="$emit('close')"
                        class="absolute top-4 right-4 text-white/70 hover:text-white transition-opacity z-30"
                    >
                        <span class="material-symbols-outlined text-3xl">close</span>
                    </button>

                    <section 
                        class="relative h-48 md:h-64 flex items-end p-6 md:p-10 overflow-hidden shrink-0 transition-colors duration-500"
                        :style="{ backgroundColor: isDark ? '#111' : theme.accent }"
                    >
                        <span 
                            class="material-symbols-outlined absolute -top-10 -right-10 text-[220px] rotate-12 pointer-events-none opacity-10"
                            :style="{ color: isDark ? theme.accent : '#fff' }"
                        >
                            calendar_today
                        </span>
                        
                        <div class="relative z-10 w-full">
                            <div class="flex items-center gap-4 md:gap-6">
                                <div class="bg-white rounded-xl p-2 md:p-4 flex flex-col items-center justify-center min-w-[60px] md:min-w-[80px] shadow-lg">
                                    <span class="text-xs font-bold uppercase" :style="{ color: theme.accent }">
                                        {{ getMonth(activeEvent?.start_time) }}
                                    </span>
                                    <span class="text-slate-900 text-2xl md:text-3xl font-black leading-none">
                                        {{ getDay(activeEvent?.start_time) }}
                                    </span>
                                </div>
                                <h1 class="text-2xl md:text-5xl font-extrabold text-white leading-tight tracking-tight line-clamp-2 drop-shadow-sm">
                                    {{ activeEvent?.title }}
                                </h1>
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-1 overflow-hidden flex-col md:flex-row">
                        <aside 
                            class="w-full md:w-1/3 flex flex-col transition-colors duration-300 border-r"
                            :style="{ backgroundColor: surface.sidebarBg, borderColor: surface.borderSubtle }"
                        >
                            <div class="p-4 md:p-6 border-b hidden md:block" :style="{ borderColor: surface.borderSubtle }">
                                <h2 class="text-lg font-bold flex items-center gap-2" :style="styles.textPrimary">
                                    <span class="material-symbols-outlined" :style="styles.iconColor">event_note</span>
                                    Schedule
                                </h2>
                                <p class="text-xs mt-1" :style="styles.textMuted">{{ events.length }} events found</p>
                            </div>

                            <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 space-y-3">
                                <div 
                                    v-for="(event, index) in events" 
                                    :key="index"
                                    @click="selectedIndex = index"
                                    class="p-4 rounded-xl cursor-pointer transition-all border group"
                                    :style="selectedIndex === index ? { 
                                        backgroundColor: theme.accent + '15', 
                                        borderColor: theme.accent,
                                        borderLeftWidth: '4px'
                                    } : {
                                        backgroundColor: surface.cardBg,
                                        borderColor: surface.borderSubtle
                                    }"
                                >
                                    <div class="flex justify-between items-start mb-1">
                                        <span 
                                            class="text-[10px] font-semibold uppercase tracking-wider"
                                            :style="selectedIndex === index ? styles.iconColor : styles.textMuted"
                                        >
                                            {{ selectedIndex === index ? 'Active' : formatFullDate(event.start_time) }}
                                        </span>
                                        <span class="text-[10px]" :style="styles.textMuted">
                                            {{ formatTime(event.start_time) }}
                                        </span>
                                    </div>
                                    <h3 
                                        class="text-sm font-bold leading-tight transition-colors"
                                        :style="selectedIndex === index ? styles.textPrimary : styles.textSecondary"
                                    >
                                        {{ event.title }}
                                    </h3>
                                    <p class="text-xs mt-2 flex items-center gap-1" :style="styles.textMuted">
                                        <span class="material-symbols-outlined text-xs">location_on</span>
                                        {{ event.venue }}
                                    </p>
                                </div>
                            </div>
                        </aside>

                        <main class="flex-1 flex flex-col overflow-hidden bg-transparent">
                            <div class="flex-1 overflow-y-auto p-6 md:p-10 custom-scrollbar">
                                
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 md:gap-8 mb-10">
                                    <div v-for="item in [
                                        { label: 'Start', val: formatFullDate(activeEvent?.start_time), sub: formatTime(activeEvent?.start_time), icon: 'calendar_today' },
                                        { label: 'End', val: formatFullDate(activeEvent?.end_time || activeEvent?.start_time), sub: formatTime(activeEvent?.end_time), icon: 'event_available' },
                                        { label: 'Venue', val: activeEvent?.venue, sub: activeEvent?.venueDetail , icon: 'location_on' }
                                    ]" :key="item.label" class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" :style="styles.iconBg">
                                            <span class="material-symbols-outlined">{{ item.icon }}</span>
                                        </div>
                                        <div>
                                            <h4 class="text-[10px] font-bold uppercase tracking-widest mb-1" :style="styles.textMuted">
                                                {{ item.label }}
                                            </h4>
                                            <p class="font-bold text-sm" :style="styles.textPrimary">{{ item.val }}</p>
                                            <p class="text-xs" :style="styles.textSecondary">{{ item.sub }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <h4 class="text-xs font-bold uppercase tracking-widest flex items-center gap-2" :style="styles.textMuted">
                                        <span class="material-symbols-outlined text-base">subject</span>
                                        Description
                                    </h4>
                                    <div class="prose max-w-none">
                                        <p class="leading-relaxed text-lg font-medium" :style="styles.textSecondary">
                                            {{ activeEvent?.description }}
                                        </p>
                                        <p v-if="activeEvent?.descriptionLong" class="leading-relaxed mt-4" :style="styles.textMuted">
                                            {{ activeEvent.descriptionLong }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                           
                        </main>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Scrollbar custom colors based on theme */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: v-bind('theme.accent + "40"');
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: v-bind('theme.accent');
}

/* Animations */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* Ensure typography adapts to current theme colors */
.prose p {
    color: inherit;
}
</style>