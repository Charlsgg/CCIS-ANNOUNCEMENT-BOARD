<script setup lang="ts">
import { ref, onMounted } from 'vue'

const props = defineProps<{
    theme: Record<string, any>
    surface: Record<string, any>
    styles: Record<string, any>
}>()

const emit = defineEmits<{
    (e: 'edit', event: any): void
}>()

const myEvents = ref<any[]>([])
const isLoading = ref(true)

const fetchMyEvents = async () => {
    isLoading.value = true
    try {
        // Change this endpoint if your Laravel route for getting user's events is different.
        // e.g., /api/user/events or /api/events?my_events=true
        const response = await fetch('/api/user/events', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        
        if (response.ok) {
            const data = await response.json()
            myEvents.value = data.events || data || []
        }
    } catch (error) {
        console.error("Failed to fetch user events", error)
    } finally {
        isLoading.value = false
    }
}

const formatDate = (dateStr: string) => {
    if (!dateStr) return 'TBA'
    const d = new Date(dateStr)
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(() => {
    fetchMyEvents()
})

// Expose refresh method to parent so it can be called when a new event is created/updated
defineExpose({ refresh: fetchMyEvents })
</script>

<template>
    <div class="w-full mt-12 pt-8 border-t" :style="{ borderColor: surface.borderSubtle }">
        <h3 class="text-2xl font-bold tracking-tight mb-6" :style="styles.textPrimary">
            My Posted Events
        </h3>

        <div v-if="isLoading" class="flex justify-center p-8">
            <span class="material-symbols-outlined animate-spin text-3xl" :style="{ color: theme.accent }">progress_activity</span>
        </div>

        <div v-else-if="myEvents.length === 0" class="text-center p-8 rounded-xl border border-dashed" :style="{ borderColor: surface.borderSubtle, color: styles.textSecondary.color }">
            You haven't posted any events yet.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="event in myEvents" :key="event.id || event.event_id" 
                 class="p-5 rounded-xl border flex flex-col gap-3 transition-shadow hover:shadow-md"
                 :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
                
                <div class="flex justify-between items-start">
                    <h4 class="font-bold text-lg truncate" :style="styles.textPrimary">{{ event.title }}</h4>
                    <button @click="$emit('edit', event)" class="p-1.5 rounded-md transition-colors text-sm flex items-center gap-1"
                            :style="{ backgroundColor: `${theme.accent}15`, color: theme.accent }">
                        <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                    </button>
                </div>
                
                <div class="text-sm space-y-1" :style="styles.textSecondary">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                        {{ formatDate(event.start_time) }}
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">location_on</span>
                        {{ event.venue || event.Venue || 'TBA' }}
                    </div>
                </div>
                
                <p class="text-sm mt-2 line-clamp-2" :style="styles.textPrimary">
                    {{ event.content || event.description }}
                </p>
            </div>
        </div>
    </div>
</template>