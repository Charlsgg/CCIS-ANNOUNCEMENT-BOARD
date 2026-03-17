<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

const hoveredIndex = ref<number | null>(null)

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

const { theme, surface, styles, isDark } = useTheme()
const selectedIndex = ref(0)

watch(() => props.show, (newVal) => {
    if (newVal) {
        selectedIndex.value = 0
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = 'auto'
    }
})

const activeEvent = computed(() => props.events[selectedIndex.value] || null)

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
            <div v-if="show"
                class="fixed inset-0 z-100 flex items-center justify-center p-2 sm:p-4 md:p-6 transition-all duration-300 backdrop-blur-sm"
                :style="{ backgroundColor: surface.overlayBg }">
                <div class="absolute inset-0" @click="$emit('close')"></div>

                <div class="relative w-full max-w-4xl h-[85vh] md:h-162.5 rounded-xl shadow-2xl overflow-hidden flex flex-col border transition-all duration-300"
                    :style="[styles.cardBg, { borderColor: theme.accent + '20' }]">
                    <div class="relative h-40 md:h-48 w-full flex items-end overflow-hidden shrink-0 transition-colors duration-500"
                        :style="{ backgroundColor: isDark ? theme.accent + 'e6' : theme.accent }">
                        <div
                            class="absolute top-0 right-0 opacity-10 pointer-events-none translate-x-1/4 -translate-y-1/4">
                            <span
                                class="material-symbols-outlined text-[200px] md:text-[240px] text-white select-none">calendar_month</span>
                        </div>

                        <button @click="$emit('close')"
                            class="absolute top-3 right-3 md:top-4 md:right-4 z-20 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-black/20 hover:bg-black/40 text-white transition-colors">
                            <span class="material-symbols-outlined text-lg md:text-xl">close</span>
                        </button>

                        <div
                            class="relative z-10 p-4 md:p-6 flex items-center gap-4 w-full bg-linear-to-t from-black/50 to-transparent">
                            <div class="flex flex-col items-center justify-center p-2 rounded-xl shadow-lg min-w-16"
                                :style="styles.cardBg">
                                <span class="text-[10px] md:text-xs font-bold uppercase tracking-wider"
                                    :style="{ color: theme.accent }">
                                    {{ getMonth(activeEvent?.start_time) }}
                                </span>
                                <span class="text-xl md:text-2xl font-black" :style="styles.textPrimary">
                                    {{ getDay(activeEvent?.start_time) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <nav v-if="activeEvent?.category" class="flex gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 rounded bg-white/20 text-white text-[9px] md:text-[10px] font-bold uppercase tracking-widest truncate">
                                        {{ activeEvent.category }}
                                    </span>
                                </nav>
                                <h1
                                    class="text-white text-lg sm:text-xl md:text-2xl font-bold leading-tight drop-shadow-sm line-clamp-2">
                                    {{ activeEvent?.title }}
                                </h1>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-1 overflow-hidden flex-col md:flex-row">

                        <aside
                            class="w-full md:w-64 border-b md:border-b-0 md:border-r flex flex-col shrink-0 transition-colors duration-300"
                            :style="{ backgroundColor: surface.sidebarBg, borderColor: theme.accent + '15' }">
                            <div class="p-4 border-b hidden md:block" :style="{ borderColor: theme.accent + '10' }">
                                <h3 class="font-bold text-sm" :style="styles.textPrimary">Upcoming Events</h3>
                            </div>

                            <div
                                class="flex flex-row md:flex-col overflow-x-auto md:overflow-y-auto custom-scrollbar p-2 gap-2 md:gap-0 md:space-y-1 snap-x md:snap-none">
                                <div v-for="(event, index) in events" :key="index" @click="selectedIndex = index"
                                    class="shrink-0 w-60 md:w-auto p-3 rounded-lg cursor-pointer transition-all border-b-4 md:border-b-0 md:border-l-4 snap-start group"
                                    :style="selectedIndex === index ? {
                                        backgroundColor: theme.accent + '1a',
                                        borderColor: theme.accent
                                    } : {
                                        backgroundColor: 'transparent',
                                        borderColor: 'transparent'
                                    }">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-[10px] font-bold"
                                            :style="selectedIndex === index ? { color: theme.accent } : styles.textMuted">
                                            {{ formatTime(event.start_time) }}
                                        </span>
                                        <span v-if="selectedIndex === index"
                                            class="material-symbols-outlined text-xs hidden md:block"
                                            :style="{ color: theme.accent }">
                                            radio_button_checked
                                        </span>
                                    </div>
                                    <p class="font-semibold text-sm transition-colors truncate md:whitespace-normal leading-snug"
                                        :style="selectedIndex === index
                                            ? styles.textPrimary
                                            : { color: hoveredIndex === index ? theme.accent : (isDark ? '#cbd5e1' : '#334155') }"
                                        @mouseenter="hoveredIndex = index" @mouseleave="hoveredIndex = null">
                                        {{ event.title }}
                                    </p>
                                </div>
                            </div>
                        </aside>

                        <main class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 bg-transparent">

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 mb-6 md:mb-8">
                                <div v-for="item in [
                                    { label: 'Start Date', date: formatFullDate(activeEvent?.start_time), sub: formatTime(activeEvent?.start_time) , icon: 'event_upcoming' },
                                    { label: 'End Date', date: formatFullDate(activeEvent?.end_time || activeEvent?.start_time), sub: formatTime(activeEvent?.end_time) , icon: 'event_available' },
                                    { label: 'Venue', date: activeEvent?.venue, sub: activeEvent?.venueDetail, icon: 'location_on' }
                                ]" :key="item.label"
                                    class="p-3 md:p-4 rounded-xl border shadow-sm flex sm:block items-center sm:items-start gap-4 sm:gap-0 transition-shadow"
                                    :style="{ backgroundColor: surface.cardBg, borderColor: theme.accent + '1a' }">
                                    <div class="w-8 h-8 md:w-10 md:h-10 shrink-0 rounded-lg flex items-center justify-center sm:mb-2"
                                        :style="{ backgroundColor: theme.accent + '33' }">
                                        <span class="material-symbols-outlined text-[20px]"
                                            :style="{ color: theme.accent }">{{ item.icon }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-[10px] font-bold uppercase tracking-widest mb-0.5"
                                            :style="styles.textMuted">
                                            {{ item.label }}
                                        </h4>
                                        <p class="font-bold text-sm leading-tight truncate" :style="styles.textPrimary">
                                            {{ item.date }}</p>
                                        <p class="text-[11px] font-medium mt-0.5 truncate"
                                            :style="{ color: theme.accent }">{{ item.sub }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="max-w-2xl pb-4">
                                <h3 class="text-lg font-bold mb-3 md:mb-4 flex items-center gap-2"
                                    :style="styles.textPrimary">
                                    Description
                                </h3>
                                <div class="prose max-w-none space-y-4">
                                    <p class="text-sm md:text-base font-medium leading-relaxed italic border-l-4 pl-4 whitespace-pre-wrap wrap-break-word"
                                        :style="{ color: isDark ? '#cbd5e1' : '#334155', borderColor: theme.accent + '4d' }">
                                        {{ activeEvent?.description }}
                                    </p>

                                    <p v-if="activeEvent?.descriptionLong"
                                        class="leading-relaxed text-sm whitespace-pre-wrap wrap-break-word"
                                        :style="styles.textMuted">
                                        {{ activeEvent.descriptionLong }}
                                    </p>
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
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: v-bind('theme.accent + "44"');
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: v-bind('theme.accent');
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.prose p {
    color: inherit;
}
</style>