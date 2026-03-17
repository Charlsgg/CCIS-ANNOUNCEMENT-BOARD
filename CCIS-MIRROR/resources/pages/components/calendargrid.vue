<script setup lang="ts">
import { computed } from 'vue'

export interface CalendarEvent {
    id: string | number
    title: string
    color?: string
    startTime?: string 
    endTime?: string
}

export interface CalendarDay {
    date: number
    fullDate?: string
    isCurrentMonth: boolean
    events: CalendarEvent[]
    isHighlight?: boolean 
}

const props = defineProps<{
    theme: Record<string, any>
    surface: Record<string, any>
    styles: Record<string, any>
    days: CalendarDay[] 
}>()

defineEmits<{
    (e: 'show-detail', day: CalendarDay): void
}>()

const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

// 1. Utility: Format Date/Time
const formatTime = (timeStr?: string): string => {
    if (!timeStr) return ''
    try {
        const date = new Date(timeStr)
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    } catch (e) {
        return timeStr 
    }
}

const isSameDay = (dateStr1?: string, dateStr2?: string): boolean => {
    if (!dateStr1 || !dateStr2) return false
    const d1 = new Date(dateStr1)
    const d2 = new Date(dateStr2)
    return d1.getFullYear() === d2.getFullYear() && 
           d1.getMonth() === d2.getMonth() && 
           d1.getDate() === d2.getDate()
}

// 2. Logic: Event Rendering Helpers
const isEventStart = (event: CalendarEvent, day: CalendarDay): boolean => {
    return !day.fullDate || !event.startTime || isSameDay(event.startTime, day.fullDate)
}

const getEventClasses = (event: CalendarEvent, day: CalendarDay): string[] => {
    if (!day.fullDate) return ['rounded', 'border-l-2']

    const isStart = isEventStart(event, day)
    const isEnd = !event.endTime || isSameDay(event.endTime, day.fullDate)

    const classes = ['relative', 'z-10']

    // Left side boundary styling
    if (isStart) {
        classes.push('rounded-l', 'border-l-2')
    } else {
        classes.push('-ml-[9px]', 'pl-[9px]', 'border-l-0', 'rounded-l-none')
    }

    // Right side boundary styling
    if (isEnd) {
        classes.push('rounded-r')
    } else {
        classes.push('-mr-[9px]', 'pr-[9px]', 'rounded-r-none')
    }

    return classes
}

const getEventStyle = (event: CalendarEvent): Record<string, string> => {
    const activeColor = event.color || props.theme.accent
    return {
        backgroundColor: `${activeColor}20`, 
        color: activeColor,
        borderLeftColor: activeColor
    }
}

// 3. Logic: Track Slotting Algorithm
// This ensures multi-day events stay on the same vertical Y-axis
const processedDays = computed(() => {
    const activeTracks: (string | number | null)[] = []
    const result = []

    for (const day of props.days) {
        if (!day.fullDate || !day.events || !day.events.length) {
            // Free all tracks if there are no events today
            activeTracks.fill(null)
            result.push({ ...day, slottedEvents: [] })
            continue
        }

        const currentEventIds = new Set(day.events.map(e => e.id))

        // Free up tracks for events that have ended
        for (let i = 0; i < activeTracks.length; i++) {
            if (activeTracks[i] !== null && !currentEventIds.has(activeTracks[i]!)) {
                activeTracks[i] = null
            }
        }

        const slottedEvents: (CalendarEvent | null)[] = []
        let maxTrack = -1

        // Maintain tracks for continuing multi-day events
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

        // Assign lowest available tracks to new events
        for (const event of unplacedEvents) {
            let trackIdx = 0
            while (activeTracks[trackIdx] !== null && activeTracks[trackIdx] !== undefined) {
                trackIdx++
            }
            activeTracks[trackIdx] = event.id
            slottedEvents[trackIdx] = event
            maxTrack = Math.max(maxTrack, trackIdx)
        }

        // Fill empty gaps with nulls to render visual spacers
        const finalEvents = []
        for (let i = 0; i <= maxTrack; i++) {
            finalEvents.push(slottedEvents[i] || null)
        }

        result.push({ ...day, slottedEvents: finalEvents })
    }

    return result
})
</script>

<template>
    <section 
        class="flex-1 w-full rounded-2xl overflow-hidden shadow-lg shadow-black/5" 
        :style="styles.cardBg"
    >
        <div class="grid grid-cols-7 gap-px" :style="{ backgroundColor: surface.borderSubtle }">
            
            <div 
                v-for="day in daysOfWeek" 
                :key="day" 
                class="p-4 text-center font-bold text-xs uppercase tracking-wider"
                :style="{ backgroundColor: surface.hoverBg, color: theme.accent }"
            >
                {{ day }}
            </div>

            <div 
                v-for="(day, index) in processedDays" 
                :key="'day-' + index" 
                @click="$emit('show-detail', day)" 
                class="min-h-30 p-2 cursor-pointer transition-colors relative"
                :class="[
                    !day.isCurrentMonth ? 'opacity-40' : '',
                    day.isHighlight || day.events.length ? 'border-t-2' : ''
                ]"
                :style="{ 
                    backgroundColor: surface.cardBg, 
                    color: !day.isCurrentMonth ? surface.textMuted : 'inherit',
                    borderTopColor: (day.isHighlight || day.events.length) ? theme.accent : 'transparent'
                }"
                @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.cardBg"
            >
                <span :class="{ 'font-bold': day.isHighlight || day.events.length }">
                    {{ day.date }}
                </span>
                
                <div 
                    v-for="(event, idx) in day.slottedEvents"
                    :key="event ? event.id : `empty-${day.date}-${idx}`"
                    class="mt-2 p-1.5 text-[10px] leading-tight font-semibold mb-1 flex flex-col gap-0.5"
                    :class="event ? getEventClasses(event, day) : ['opacity-0', 'pointer-events-none']"
                    :style="event ? getEventStyle(event) : {}"
                >
                    <template v-if="event">
                        <template v-if="isEventStart(event, day)">
                            <span class="truncate">{{ event.title }}</span>
                            
                            <div v-if="event.startTime" class="opacity-80 font-normal text-[9px] truncate">
                                {{ formatTime(event.startTime) }} 
                                <span v-if="event.endTime"> - {{ formatTime(event.endTime) }}</span>
                            </div>
                        </template>
                        
                        <template v-else>
                            <span class="opacity-0 truncate">{{ event.title }}</span>
                            <div v-if="event.startTime" class="opacity-0 font-normal text-[9px] truncate">Time</div>
                        </template>
                    </template>
                    
                    <template v-else>
                        <span>&nbsp;</span>
                    </template>
                </div>

            </div>
        </div>
    </section>
</template>