<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
    show: boolean
    theme: Record<string, any>
    surface: Record<string, any>
    styles: Record<string, any>
    eventData: Record<string, any> | null
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'updated'): void
}>()

const form = ref({
    title: '',
    month: new Date().getMonth() + 1,
    day_range: '',
    year: new Date().getFullYear(),
    venue: '',
    time: '',
    end_time: '',
    description: ''
})

const isLoading = ref(false)
const errorMessage = ref('')

const extractTime = (dateStr?: string | null) => {
    if (!dateStr) return ''
    if (/^\d{2}:\d{2}(:\d{2})?$/.test(dateStr)) return dateStr.substring(0, 5)
    try {
        const d = new Date(dateStr)
        if (isNaN(d.getTime())) return ''
        return d.toTimeString().substring(0, 5)
    } catch (e) {
        return ''
    }
}

watch(() => props.eventData, (newVal) => {
    if (newVal) {
        form.value = {
            title: newVal.title || '',
            month: newVal.event_month || new Date().getMonth() + 1,
            day_range: newVal.day_range || (newVal.start_time ? new Date(newVal.start_time).getDate().toString() : ''),
            year: newVal.event_year || new Date().getFullYear(),
            venue: newVal.venue || newVal.Venue || '',
            time: newVal.time || extractTime(newVal.start_time) || '', 
            end_time: newVal.end_time_input || extractTime(newVal.end_time) || '', 
            description: newVal.content || newVal.description || ''
        }
    }
}, { immediate: true })

const submitEvent = async () => {
    if (!props.eventData?.id && !props.eventData?.event_id) return
    
    isLoading.value = true
    errorMessage.value = ''

    try {
        const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
        const csrfToken = tokenTag ? tokenTag.content : ''
        
        const eventId = props.eventData.id || props.eventData.event_id
        const url = `/api/events/${eventId}`

        let startDay = form.value.day_range.toString().trim()
        let endDay = null

        if (startDay.includes('-')) {
            const parts = startDay.split('-')
            startDay = parts[0].trim()
            endDay = parts[1].trim()
        }

        // REMOVED 'day_range' from the payload to prevent the backend from trying to save it.
        const payload = {
            title: form.value.title,
            event_month: form.value.month,
            event_year: form.value.year,
            Venue: form.value.venue,
            content: form.value.description,
            time: form.value.time, 
            end_time: form.value.end_time || null,
            start_day: startDay, 
            end_day: endDay 
        }

        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })

        const responseData = await response.json()

        if (!response.ok) {
            if (response.status === 422) {
                const errors = responseData.errors
                let errorMessages = []
                for (const field in errors) {
                    errorMessages.push(errors[field][0])
                }
                throw new Error(errorMessages.join(' '))
            } else {
                throw new Error(responseData.message || 'Server error occurred.')
            }
        }

        emit('updated')
        emit('close')

    } catch (error: any) {
        console.error('Error saving event:', error)
        errorMessage.value = error.message || 'An error occurred while updating.'
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show"
            class="fixed inset-0 backdrop-blur-sm z-100 flex items-center justify-center p-4 transition-opacity font-['Space_Grotesk']"
            :style="{ backgroundColor: surface.overlayBg }">
            <div class="w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden" :style="styles.cardBg">

                <div class="p-6 border-b flex justify-between items-center"
                    :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.hoverBg }">
                    <h3 class="text-xl font-bold" :style="styles.textPrimary">
                        Edit Event
                    </h3>
                    <button @click="$emit('close')"
                        class="size-8 rounded-full flex items-center justify-center transition-colors"
                        :style="styles.textSecondary"
                        @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.borderSubtle"
                        @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form @submit.prevent="submitEvent">
                    <div class="p-6 space-y-4" :style="styles.textPrimary">
                        <div v-if="errorMessage" class="p-3 rounded-lg text-sm bg-red-500/10 text-red-500 border border-red-500/20">
                            {{ errorMessage }}
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Event Title</label>
                            <input v-model="form.title" required
                                class="w-full rounded-lg font-display outline-none p-2 border" type="text"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Month</label>
                                <select v-model="form.month"
                                    class="w-full rounded-lg font-display outline-none p-2 border appearance-none"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }">
                                    <option v-for="m in 12" :key="m" :value="m">{{ new Date(0, m - 1).toLocaleString('default', { month: 'long' }) }}</option>
                                </select>
                            </div>
                            <div class="space-y-1 col-span-2">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Day</label>
                                <input v-model="form.day_range" required
                                    class="w-full rounded-lg font-display outline-none p-2 border" type="text"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Year</label>
                                <input v-model="form.year" required
                                    class="w-full rounded-lg font-display outline-none p-2 border" type="number"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Venue</label>
                                <input v-model="form.venue" required
                                    class="w-full rounded-lg font-display outline-none p-2 border" type="text"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Start Time</label>
                                <input v-model="form.time" required
                                    class="w-full rounded-lg font-display outline-none p-2 border" type="time"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">End Time <span class="text-[9px] opacity-70 normal-case">(Optional)</span></label>
                                <input v-model="form.end_time"
                                    class="w-full rounded-lg font-display outline-none p-2 border" type="time"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Brief Description</label>
                            <textarea v-model="form.description" required
                                class="w-full rounded-lg font-display outline-none p-3 border resize-none" rows="3"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"></textarea>
                        </div>
                    </div>

                    <div class="p-6 flex gap-3 justify-end border-t"
                        :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.hoverBg }">
                        <button type="button" @click="$emit('close')"
                            class="px-6 py-2 rounded-lg font-semibold text-sm transition-colors border"
                            :style="{ borderColor: surface.borderSubtle, color: surface.textPrimary }">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isLoading"
                            class="px-6 py-2 rounded-lg font-semibold text-sm transition-opacity flex items-center gap-2 disabled:opacity-50"
                            :style="styles.button">
                            <span v-if="isLoading" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                            {{ isLoading ? 'Updating...' : 'Update Event' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </Teleport>
</template>