<script setup lang="ts">
import { ref, computed, watch } from 'vue'

const props = defineProps<{
    show: boolean
    theme: Record<string, any>
    surface: Record<string, any>
    styles: Record<string, any>
    eventData?: Record<string, any> | null
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'created'): void 
    (e: 'updated'): void 
}>()

// 1. Define the reactive form state
const form = ref({
    title: '',
    month: new Date().getMonth() + 1, 
    day_range: '',
    year: new Date().getFullYear(),
    venue: '',
    description: ''
})

const isLoading = ref(false)
const errorMessage = ref('')

const isEditing = computed(() => !!props.eventData)

const resetForm = () => {
    form.value = {
        title: '',
        month: new Date().getMonth() + 1,
        day_range: '',
        year: new Date().getFullYear(),
        venue: '',
        description: ''
    }
    errorMessage.value = ''
}

watch(() => props.eventData, (newVal) => {
    if (newVal) {
        form.value = {
            title: newVal.title || '',
            month: newVal.event_month || new Date().getMonth() + 1, 
            day_range: newVal.day_range || '',
            year: newVal.event_year || new Date().getFullYear(),
            venue: newVal.venue || '', 
            description: newVal.content || ''
        }
    } else {
        resetForm()
    }
}, { immediate: true })

// 2. Handle the form submission
const submitEvent = async () => {
    isLoading.value = true
    errorMessage.value = ''

    try {
        const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
        const csrfToken = tokenTag ? tokenTag.content : ''

        const url = isEditing.value ? `/api/events/${props.eventData?.id}` : '/api/events'
        const method = isEditing.value ? 'PUT' : 'POST'

        const rawDayRange = form.value.day_range.toString().trim()
        let datesToProcess = [rawDayRange]

        // NEW LOGIC: If we are creating a new event and the input contains commas, 
        // split it into an array (e.g., "3, 5, 8" -> ["3", "5", "8"])
        if (!isEditing.value && rawDayRange.includes(',')) {
            datesToProcess = rawDayRange.split(',').map(d => d.trim()).filter(d => d !== '')
        }

        // Create an array of fetch promises so we can post them simultaneously
        const requests = datesToProcess.map(async (dateVal) => {
            const payload = {
                title: form.value.title,
                event_month: form.value.month,
                event_year: form.value.year,
                Venue: form.value.venue,     
                content: form.value.description,
                day_range: dateVal // Sends "3-10" once, or loops "3", "5", "8"
            }

            const response = await fetch(url, {
                method: method,
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
                    const errors = responseData.errors;
                    let errorMessages = [];
                    for (const field in errors) {
                        errorMessages.push(errors[field][0]);
                    }
                    // Add the date to the error so the user knows which one failed
                    throw new Error(`Error for day(s) '${dateVal}': ` + errorMessages.join(' '));
                } else {
                    throw new Error(`Error for day(s) '${dateVal}': ` + (responseData.message || 'Server error occurred.'));
                }
            }
            return responseData;
        })

        // Fire all requests simultaneously and wait for them to finish
        await Promise.all(requests)

        // 3. Success! Reset form, tell parent to refresh data, and close modal
        resetForm()
        if (isEditing.value) {
            emit('updated')
        } else {
            emit('created') 
        }
        emit('close')

    } catch (error: any) {
        console.error('Error saving event:', error)
        errorMessage.value = error.message || 'An error occurred while saving.'
    } finally {
        isLoading.value = false
    }
}

// Reset the form if the user clicks "Cancel" or the 'X'
const handleClose = () => {
    resetForm()
    emit('close')
}
</script>
<template>
    <Teleport to="body">
        <div 
            v-if="show" 
            class="fixed inset-0 backdrop-blur-sm z-100 flex items-center justify-center p-4 transition-opacity font-['Space_Grotesk']"
            :style="{ backgroundColor: surface.overlayBg }"
        >
            <div class="w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden" :style="styles.cardBg">
                
                <div class="p-6 border-b flex justify-between items-center" :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.hoverBg }">
                    <h3 class="text-xl font-bold" :style="styles.textPrimary">
                        {{ isEditing ? 'Edit Event' : 'Post New Event' }}
                    </h3>
                    <button 
                        @click="handleClose" 
                        class="size-8 rounded-full flex items-center justify-center transition-colors" 
                        :style="styles.textSecondary"
                        @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.borderSubtle"
                        @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'"
                    >
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
                            <input 
                                v-model="form.title"
                                required
                                class="w-full rounded-lg font-display outline-none p-2 border" 
                                placeholder="e.g., Science Night" type="text"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                            />
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Month</label>
                                <select 
                                    v-model="form.month"
                                    class="w-full rounded-lg font-display outline-none p-2 border appearance-none"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                >
                                    <option :value="1">January</option>
                                    <option :value="2">February</option>
                                    <option :value="3">March</option>
                                    <option :value="4">April</option>
                                    <option :value="5">May</option>
                                    <option :value="6">June</option>
                                    <option :value="7">July</option>
                                    <option :value="8">August</option>
                                    <option :value="9">September</option>
                                    <option :value="10">October</option>
                                    <option :value="11">November</option>
                                    <option :value="12">December</option>
                                </select>
                            </div>
                            <div class="space-y-1 col-span-2">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Day Range</label>
                                <input 
                                    v-model="form.day_range"
                                    required
                                    class="w-full rounded-lg font-display outline-none p-2 border" 
                                    placeholder="e.g., 3-10 or 15" type="text"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                />
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Year</label>
                                <input 
                                    v-model="form.year"
                                    required
                                    class="w-full rounded-lg font-display outline-none p-2 border" 
                                    type="number"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Venue</label>
                                <input 
                                    v-model="form.venue"
                                    required
                                    class="w-full rounded-lg font-display outline-none p-2 border" 
                                    placeholder="Room or Hall" type="text"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                />
                            </div>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Brief Description</label>
                            <textarea 
                                v-model="form.description"
                                required
                                class="w-full rounded-lg font-display outline-none p-3 border resize-none" 
                                placeholder="Describe the event..." rows="3"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                            ></textarea>
                        </div>
                    </div>
                    
                    <div class="p-6 flex gap-3 justify-end border-t" :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.hoverBg }">
                        <button 
                            type="button"
                            @click="handleClose" 
                            class="px-6 py-2 rounded-lg font-semibold text-sm transition-colors border" 
                            :style="{ borderColor: surface.borderSubtle, color: surface.textPrimary }"
                            @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.borderSubtle"
                            @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'"
                        >
                            Cancel
                        </button>
                        
                        <button 
                            type="submit"
                            :disabled="isLoading"
                            class="px-6 py-2 rounded-lg font-semibold text-sm transition-opacity flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :style="styles.button"
                            @mouseenter="(e: MouseEvent) => { if(!isLoading) (e.currentTarget as HTMLElement).style.opacity = '0.9' }"
                            @mouseleave="(e: MouseEvent) => { if(!isLoading) (e.currentTarget as HTMLElement).style.opacity = '1' }"
                        >
                            <span v-if="isLoading" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                            {{ isLoading ? (isEditing ? 'Updating...' : 'Publishing...') : (isEditing ? 'Update Event' : 'Publish Event') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </Teleport>
</template>