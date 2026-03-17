<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
    show: boolean
    event: any
    theme: Record<string, any>
    surface: Record<string, any>
    styles: Record<string, any>
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'updated'): void // Tell parent to refresh
    (e: 'deleted'): void // Tell parent to refresh
}>()

const isEditing = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

// Editable form state
const form = ref({
    title: '',
    venue: '',
    description: ''
})

// Populate form when modal opens or event changes
watch(() => props.event, (newEvent) => {
    if (newEvent) {
        form.value = {
            title: newEvent.title || '',
            venue: newEvent.venue || '',
            description: newEvent.description || ''
        }
    }
}, { immediate: true })

const close = () => {
    isEditing.value = false
    errorMessage.value = ''
    emit('close')
}

// UPDATE (Edit) Logic
const submitUpdate = async () => {
    if (!props.event?.id) return

    isLoading.value = true
    errorMessage.value = ''

    try {
        const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
        const csrfToken = tokenTag ? tokenTag.content : ''

        // Prepare payload matching Laravel validation
        const payload = {
            title: form.value.title,
            Venue: form.value.venue,
            content: form.value.description
        }

        const response = await fetch(`/api/events/${props.event.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })

        if (!response.ok) throw new Error('Failed to update event.')

        emit('updated')
        isEditing.value = false // Return to view mode
    } catch (error: any) {
        errorMessage.value = error.message || 'Error updating event.'
    } finally {
        isLoading.value = false
    }
}

// DELETE Logic
const confirmDelete = async () => {
    if (!props.event?.id) return
    if (!confirm('Are you sure you want to delete this event?')) return

    isLoading.value = true
    errorMessage.value = ''

    try {
        const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
        const csrfToken = tokenTag ? tokenTag.content : ''

        const response = await fetch(`/api/events/${props.event.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })

        if (!response.ok) throw new Error('Failed to delete event.')

        emit('deleted')
        close() // Close the modal entirely
    } catch (error: any) {
        errorMessage.value = error.message || 'Error deleting event.'
    } finally {
        isLoading.value = false
    }
}

const formatModalDate = (dateString?: string) => {
    if (!dateString) return 'Event Details'
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 backdrop-blur-sm z-101 flex items-center justify-center p-4 transition-opacity font-['Space_Grotesk']"
        :style="{ backgroundColor: surface.overlayBg }"
    >
        <div class="w-full max-w-md rounded-2xl shadow-2xl overflow-hidden" :style="styles.cardBg">
            
            <div class="relative h-32 flex items-center justify-center transition-colors" :style="{ backgroundColor: isEditing ? surface.borderStrong : theme.accent }">
                <span class="material-symbols-outlined text-white text-6xl opacity-30">
                    {{ isEditing ? 'edit_square' : 'event_note' }}
                </span>
                <button 
                    @click="close" 
                    class="absolute top-4 right-4 size-8 rounded-full bg-black/20 text-white flex items-center justify-center hover:bg-black/40 transition-colors"
                >
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-8 -mt-10">
                <div class="p-6 rounded-xl shadow-xl border" :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
                    
                    <div v-if="errorMessage" class="mb-4 p-3 rounded-lg text-sm bg-red-500/10 text-red-500 border border-red-500/20">
                        {{ errorMessage }}
                    </div>

                    <div class="font-bold text-sm mb-1 uppercase tracking-wide flex justify-between items-center" :style="{ color: theme.accent }">
                        {{ formatModalDate(event?.start_time) }}
                        <span v-if="isEditing" class="text-[10px] bg-red-500 text-white px-2 py-0.5 rounded">EDITING</span>
                    </div>

                    <div v-if="!isEditing">
                        <h3 class="text-2xl font-bold mb-4" :style="styles.textPrimary">
                            {{ event?.title || 'No Title Selected' }}
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined mt-0.5" :style="{ color: theme.accent }">location_on</span>
                                <div>
                                    <p class="text-xs font-bold uppercase" :style="styles.textMuted">Venue</p>
                                    <p class="text-sm" :style="styles.textPrimary">{{ event?.venue || 'TBA' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined mt-0.5" :style="{ color: theme.accent }">description</span>
                                <div>
                                    <p class="text-xs font-bold uppercase" :style="styles.textMuted">Description</p>
                                    <p class="text-sm leading-relaxed" :style="styles.textSecondary">
                                        {{ event?.description || 'No additional details provided.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex gap-2">
                            <button 
                                @click="isEditing = true"
                                class="flex-1 py-2.5 rounded-lg font-bold text-sm transition-opacity flex justify-center items-center gap-2"
                                :style="{ backgroundColor: theme.accent + '20', color: theme.accent }"
                                @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '0.8'"
                                @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '1'"
                            >
                                <span class="material-symbols-outlined text-[18px]">edit</span> Edit
                            </button>
                            <button 
                                @click="confirmDelete"
                                :disabled="isLoading"
                                class="flex-1 py-2.5 rounded-lg border text-red-500 font-bold text-sm transition-colors flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :style="{ borderColor: 'rgba(239, 68, 68, 0.2)' }"
                                @mouseenter="(e: MouseEvent) => { if(!isLoading) (e.currentTarget as HTMLElement).style.backgroundColor = 'rgba(239, 68, 68, 0.1)' }"
                                @mouseleave="(e: MouseEvent) => { if(!isLoading) (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent' }"
                            >
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                {{ isLoading ? '...' : 'Delete' }}
                            </button>
                        </div>
                    </div>

                    <form v-else @submit.prevent="submitUpdate" class="space-y-4">
                        <div>
                            <label class="text-xs font-bold uppercase mb-1 block" :style="styles.textMuted">Title</label>
                            <input v-model="form.title" required class="w-full rounded border p-2 text-sm outline-none" :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }">
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase mb-1 block" :style="styles.textMuted">Venue</label>
                            <input v-model="form.venue" required class="w-full rounded border p-2 text-sm outline-none" :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }">
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase mb-1 block" :style="styles.textMuted">Description</label>
                            <textarea v-model="form.description" required rows="3" class="w-full rounded border p-2 text-sm outline-none resize-none" :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"></textarea>
                        </div>
                        
                        <div class="mt-6 flex gap-2">
                            <button 
                                type="button"
                                @click="isEditing = false"
                                class="flex-1 py-2.5 rounded-lg font-bold text-sm border transition-colors"
                                :style="{ borderColor: surface.borderSubtle, color: surface.textPrimary }"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                :disabled="isLoading"
                                class="flex-1 py-2.5 rounded-lg font-bold text-sm transition-opacity flex justify-center items-center gap-2 disabled:opacity-50"
                                :style="styles.button"
                            >
                                <span v-if="isLoading" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                                Save Changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</template>