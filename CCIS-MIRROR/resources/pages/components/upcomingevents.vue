<script setup lang="ts">
import { ref, onMounted } from 'vue'

defineProps<{
    theme: Record<string, any>
    surface: Record<string, any>
    styles: Record<string, any>
}>()

defineEmits<{
    (e: 'show-detail', eventId: number): void
}>()

// Define the shape of your Event data based on your Laravel model
interface AppEvent {
    id: number;
    title: string;
    description: string;
    event_month: string;
    event_year: string;
    location?: string;
    created_at?: string;
}

const events = ref<AppEvent[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

const fetchUpcomingEvents = async () => {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
        const response = await fetch('/api/events/upcoming')
        const data = await response.json()
        
        if (data.status === 'success') {
            events.value = data.events
        } else {
            errorMessage.value = 'Failed to load events.'
        }
    } catch (error) {
        console.error('Error fetching events:', error)
        errorMessage.value = 'Could not connect to the server.'
    } finally {
        isLoading.value = false
    }
}

onMounted(() => {
    fetchUpcomingEvents()
})

// Quick helper to format the 'posted' date if you have timestamps
const formatDate = (dateString?: string) => {
    if (!dateString) return 'Recently'
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}
</script>

<template>
    <aside 
        class="w-full xl:w-80 shrink-0 rounded-2xl overflow-hidden shadow-lg shadow-black/5 flex flex-col" 
        :style="styles.cardBg"
    >
        <div class="p-6 border-b" :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.hoverBg }">
            <h3 class="text-lg font-bold" :style="styles.textPrimary">Upcoming Events</h3>
            <p class="text-xs" :style="styles.textMuted">Chronological feed of announcements</p>
        </div>
        
        <div class="p-4 space-y-4">
            
            <div v-if="isLoading" class="text-center p-4 text-sm" :style="styles.textMuted">
                Loading events...
            </div>

            <div v-else-if="errorMessage" class="text-center p-4 text-sm text-red-500">
                {{ errorMessage }}
            </div>

            <div v-else-if="events.length === 0" class="text-center p-4 text-sm" :style="styles.textMuted">
                No upcoming events found.
            </div>

            <div 
                v-else
                v-for="event in events"
                :key="event.id"
                class="p-4 rounded-xl transition-all cursor-pointer border"
                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }"
                @click="$emit('show-detail', event.id)"
                @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.borderColor = surface.borderStrong"
                @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.borderColor = surface.borderSubtle"
            >
                <div class="flex justify-between items-start mb-2">
                    <span 
                        class="px-2 py-1 text-[10px] font-bold rounded uppercase"
                        :style="{ backgroundColor: theme.accent + '20', color: theme.accent }"
                    >
                        {{ event.event_month }} {{ event.event_year }}
                    </span>
                    <span class="text-[10px]" :style="styles.textMuted">
                        Posted: {{ formatDate(event.created_at) }}
                    </span>
                </div>
                
                <h4 class="font-bold text-sm mb-1 leading-snug" :style="styles.textPrimary">
                    {{ event.title }}
                </h4>
                
                <div class="flex items-center gap-1 text-[11px] mb-2" :style="styles.textMuted">
                    <span class="material-symbols-outlined text-[14px]">location_on</span>
                    {{ event.location || 'TBA' }}
                </div>
                
                <p class="text-xs line-clamp-2" :style="styles.textSecondary">
                    {{ event.description || 'No description provided.' }}
                </p>
            </div>
            
        </div>
    </aside>
</template>