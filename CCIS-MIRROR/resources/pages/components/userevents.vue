<script setup lang="ts">
import { ref, computed, watch } from 'vue'

const props = defineProps<{
    isOpen: boolean // <-- NEW: Controls modal visibility
    theme: Record<string, any>
    surface: Record<string, any>
    styles: Record<string, any>
}>()

const emit = defineEmits<{
    (e: 'close'): void // <-- NEW: Emits when modal is closed
    (e: 'edit', event: any): void
    (e: 'deleted'): void
}>()

const myEvents = ref<any[]>([])
const isLoading = ref(true)
const searchQuery = ref('') 

// --- Delete Modal State ---
const isDeleteDialogOpen = ref(false)
const isDeleting = ref(false)
const eventToDelete = ref<any>(null)

const fetchMyEvents = async () => {
    isLoading.value = true
    try {
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

// Fetch events whenever the modal is opened
watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        fetchMyEvents()
    }
}, { immediate: true })

const filteredEvents = computed(() => {
    if (!searchQuery.value.trim()) {
        return myEvents.value
    }
    const query = searchQuery.value.toLowerCase()
    return myEvents.value.filter(event => {
        const titleMatch = event.title?.toLowerCase().includes(query)
        const contentMatch = (event.content || event.description)?.toLowerCase().includes(query)
        const venueMatch = (event.venue || event.Venue)?.toLowerCase().includes(query)
        return titleMatch || contentMatch || venueMatch
    })
})

const confirmDelete = (event: any) => {
    eventToDelete.value = event
    isDeleteDialogOpen.value = true
}

const executeDelete = async () => {
    if (!eventToDelete.value) return

    const eventId = eventToDelete.value.id || eventToDelete.value.event_id
    isDeleting.value = true

    try {
        const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
        const csrfToken = tokenTag ? tokenTag.content : ''

        const response = await fetch(`/api/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })

        if (response.ok) {
            await fetchMyEvents()
            emit('deleted')
            isDeleteDialogOpen.value = false 
        } else {
            const data = await response.json()
            alert(data.message || 'Failed to delete event.')
        }
    } catch (error) {
        console.error("Error deleting event:", error)
        alert("An error occurred while trying to delete the event.")
    } finally {
        isDeleting.value = false
    }
}

const formatDate = (dateStr: string) => {
    if (!dateStr) return 'TBA'
    const d = new Date(dateStr)
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

defineExpose({ refresh: fetchMyEvents })
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="isOpen" class="fixed inset-0 z-[9990] flex items-center justify-center backdrop-blur-sm p-4 sm:p-6"
                 :style="{ backgroundColor: surface.overlayBg }">
                
                <div class="relative w-full max-w-6xl max-h-[90vh] flex flex-col rounded-2xl shadow-2xl overflow-hidden border"
                     :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 border-b shrink-0" 
                         :style="{ borderColor: surface.borderSubtle }">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-2xl md:text-3xl font-bold tracking-tight" :style="styles.textPrimary">
                                    My Posted Events
                                </h3>
                                <button @click="emit('close')" class="sm:hidden p-2 rounded-full hover:opacity-70 transition-opacity" :style="styles.textSecondary">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <p :style="styles.textSecondary" class="text-sm md:text-base max-w-2xl">
                                Manage the events you've posted to the academic calendar. Click on an event to edit its details, or delete it if it's no longer relevant.
                            </p>
                        </div>

                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <div class="relative w-full sm:w-72 shrink-0">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[20px]"
                                      :style="styles.textMuted">
                                    search
                                </span>
                                <input 
                                    v-model="searchQuery" 
                                    type="text" 
                                    placeholder="Search your events..."
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border outline-none transition-colors text-sm"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: styles.textPrimary.color }"
                                    @focus="(e: Event) => (e.target as HTMLElement).style.borderColor = theme.accent"
                                    @blur="(e: Event) => (e.target as HTMLElement).style.borderColor = surface.borderSubtle"
                                >
                                <button v-if="searchQuery" @click="searchQuery = ''"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-0.5 hover:opacity-70 flex items-center justify-center transition-opacity">
                                    <span class="material-symbols-outlined text-[16px]" :style="styles.textSecondary">close</span>
                                </button>
                            </div>
                            
                            <button @click="emit('close')" class="hidden sm:flex p-2.5 rounded-xl border hover:opacity-70 transition-opacity"
                                    :style="{ borderColor: surface.borderSubtle, color: styles.textSecondary.color }" title="Close">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6" :style="{ backgroundColor: surface.pageBg }">
                        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
                            <span class="material-symbols-outlined animate-spin text-4xl mb-4" :style="{ color: theme.accent }">progress_activity</span>
                            <p class="font-medium" :style="styles.textSecondary">Loading your events...</p>
                        </div>

                        <div v-else-if="myEvents.length === 0" 
                             class="flex flex-col items-center justify-center py-20 px-4 rounded-2xl border-2 border-dashed transition-colors" 
                             :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.cardBg }">
                            <div class="p-5 rounded-full mb-5" :style="{ backgroundColor: `${theme.accent}10`, color: theme.accent }">
                                <span class="material-symbols-outlined text-5xl">event_busy</span>
                            </div>
                            <h4 class="text-xl font-bold mb-2" :style="styles.textPrimary">No events yet</h4>
                            <p class="text-base text-center max-w-md" :style="styles.textSecondary">
                                You haven't posted any events to the calendar. When you create one, it will appear here for you to manage.
                            </p>
                        </div>

                        <div v-else-if="filteredEvents.length === 0" 
                             class="flex flex-col items-center justify-center py-20 px-4 rounded-2xl border transition-colors" 
                             :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.cardBg }">
                            <div class="p-4 rounded-full mb-4" :style="{ backgroundColor: surface.hoverBg, color: styles.textSecondary.color }">
                                <span class="material-symbols-outlined text-4xl">search_off</span>
                            </div>
                            <h4 class="text-lg font-bold mb-1" :style="styles.textPrimary">No matches found</h4>
                            <p class="text-sm text-center" :style="styles.textSecondary">
                                We couldn't find any events matching "{{ searchQuery }}".
                            </p>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="event in filteredEvents" :key="event.id || event.event_id" 
                                 class="group relative p-6 rounded-2xl border transition-all duration-300 hover:-translate-y-1 flex flex-col h-full overflow-hidden"
                                 :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
                                
                                <div class="absolute top-0 left-0 w-full h-1.5 opacity-80 transition-opacity group-hover:opacity-100" :style="{ backgroundColor: theme.accent }"></div>
                                
                                <div class="flex justify-between items-start mb-5 pt-2">
                                    <h4 class="font-bold text-xl leading-tight line-clamp-2 pr-4" :style="styles.textPrimary">
                                        {{ event.title }}
                                    </h4>
                                    
                                    <div class="flex items-center gap-2 shrink-0 ml-4">
                                        <button @click.prevent.stop="$emit('edit', event)"
                                            class="p-1.5 flex items-center justify-center rounded-lg border transition-transform hover:scale-105"
                                            :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: theme.accent }"
                                            title="Edit">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </button>
                                        
                                        <button @click.prevent.stop="confirmDelete(event)"
                                            class="p-1.5 flex items-center justify-center rounded-lg border transition-colors hover:bg-red-500/10 text-red-500"
                                            :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }" 
                                            title="Delete">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-3 mb-6 mt-auto">
                                    <div class="flex items-center gap-3 text-sm font-medium" :style="styles.textSecondary">
                                        <div class="flex items-center justify-center size-8 rounded-full" :style="{ backgroundColor: surface.hoverBg, color: theme.accent }">
                                            <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                                        </div>
                                        {{ formatDate(event.start_time) }}
                                    </div>
                                    <div class="flex items-center gap-3 text-sm font-medium" :style="styles.textSecondary">
                                        <div class="flex items-center justify-center size-8 rounded-full" :style="{ backgroundColor: surface.hoverBg, color: theme.accent }">
                                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                                        </div>
                                        <span class="truncate">{{ event.venue || event.Venue || 'TBA' }}</span>
                                    </div>
                                </div>
                                
                                <div class="pt-5 border-t" :style="{ borderColor: surface.borderSubtle }">
                                    <p class="text-sm leading-relaxed line-clamp-2" :style="{ color: styles.textSecondary.color }">
                                        {{ event.content || event.description || 'No description provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <Teleport to="body">
        <Transition name="fade">
            <div v-if="isDeleteDialogOpen" class="fixed inset-0 z-[9999] flex items-center justify-center backdrop-blur-sm p-4"
                 :style="{ backgroundColor: surface.overlayBg }">
                <div class="max-w-md w-full rounded-2xl p-6 shadow-xl" 
                    :style="{ backgroundColor: surface.cardBg, border: `1px solid ${surface.borderSubtle}` }">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-red-500/10 rounded-full shrink-0 flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-500 text-[24px]">delete</span>
                        </div>
                        <h3 class="text-xl font-bold" :style="styles.textPrimary">Delete Event?</h3>
                    </div>
                    
                    <p class="mb-8" :style="styles.textSecondary">
                        Are you sure you want to delete <span class="font-bold">"{{ eventToDelete?.title }}"</span>? This action is permanent and cannot be undone.
                    </p>
                    
                    <div class="flex justify-end gap-3 flex-wrap">
                        <button @click="isDeleteDialogOpen = false" :disabled="isDeleting"
                            class="px-5 py-2.5 rounded-xl font-medium transition-colors hover:opacity-80 flex-1 sm:flex-none text-center border"
                            :style="{ backgroundColor: surface.inputBg, color: styles.textPrimary.color, borderColor: surface.borderSubtle }">
                            Cancel
                        </button>
                        <button @click="executeDelete" :disabled="isDeleting"
                            class="px-5 py-2.5 rounded-xl font-medium text-white bg-red-500 hover:bg-red-600 transition-colors flex items-center justify-center gap-2 flex-1 sm:flex-none">
                            <span v-if="isDeleting" class="size-4 border-2 border-white/30 border-t-white animate-spin rounded-full"></span>
                            {{ isDeleting ? 'Deleting...' : 'Delete Event' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>