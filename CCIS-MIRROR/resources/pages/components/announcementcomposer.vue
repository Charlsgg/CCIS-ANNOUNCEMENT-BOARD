<script setup lang="ts">
import { ref } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

const props = defineProps<{
    csrfToken: string
}>()

// Emit an event to the parent when a post is successfully created
const emit = defineEmits(['post-created'])

const { theme, styles, surface } = useTheme()

// --- State Variables ---
const newTitle = ref('')
const newTopic = ref('')
const newContent = ref('')
const isPosting = ref(false)

// --- File Upload States ---
const selectedFiles = ref<File[]>([])
const fileInput = ref<HTMLInputElement | null>(null)

// --- Helper Functions ---
const triggerFileInput = (acceptType: string) => {
    if (fileInput.value) {
        fileInput.value.accept = acceptType
        fileInput.value.click()
    }
}

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files) {
        selectedFiles.value = [...selectedFiles.value, ...Array.from(target.files)]
        target.value = ''
    }
}

const removeFile = (index: number) => {
    selectedFiles.value.splice(index, 1)
}

// --- API Call ---
const postAnnouncement = async () => {
    if (!newTitle.value.trim() || !newContent.value.trim()) {
        alert('Please enter both a title and a message.')
        return
    }

    isPosting.value = true

    const formData = new FormData()
    formData.append('title', newTitle.value)
    formData.append('content', newContent.value)
    formData.append('board_id', '1') 
    formData.append('topic', newTopic.value || 'General')

    selectedFiles.value.forEach((file) => {
        formData.append('attachments[]', file)
    })

    try {
        const response = await fetch('/api/announcements', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            body: formData,
        })

        if (response.ok) {
            const savedPost = await response.json()
            
            // Send the new post data up to the parent component
            emit('post-created', savedPost)
            
            // Reset form fields
            newTitle.value = ''
            newTopic.value = ''
            newContent.value = ''
            selectedFiles.value = []
        } else {
            const errorData = await response.json()
            alert(errorData.message || 'Failed to save announcement.')
        }
    } catch (error) {
        console.error('Error posting announcement:', error)
        alert('A network error occurred.')
    } finally {
        isPosting.value = false
    }
}
</script>

<template>
    <div 
        class="mb-10 rounded-2xl shadow-sm overflow-hidden flex flex-col transition-colors p-4 md:p-6"
        :style="styles.cardBg"
    >
        <form @submit.prevent="postAnnouncement" class="space-y-4">
            <input 
                type="file" 
                ref="fileInput" 
                multiple 
                class="hidden" 
                @change="handleFileSelect"
            >

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="title" class="block text-sm font-bold" :style="styles.textPrimary">Title</label>
                    <input 
                        type="text" 
                        id="title" 
                        v-model="newTitle"
                        placeholder="e.g., General Assembly 2026 Schedule" 
                        class="theme-input w-full px-4 py-2.5 rounded-xl transition-colors"
                    >
                </div>
                <div class="space-y-1.5">
                    <label for="topic" class="block text-sm font-bold" :style="styles.textPrimary">Subject</label>
                    <input 
                        type="text" 
                        id="topic" 
                        v-model="newTopic"
                        placeholder="e.g., Academic, Event, Urgent" 
                        class="theme-input w-full px-4 py-2.5 rounded-xl transition-colors"
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="message" class="block text-sm font-bold" :style="styles.textPrimary">Message Body</label>
                <textarea 
                    id="message" 
                    v-model="newContent"
                    rows="4" 
                    placeholder="Enter the detailed announcement content here..." 
                    class="theme-input w-full px-4 py-3 rounded-xl transition-colors resize-none"
                ></textarea>
            </div>

            <div v-if="selectedFiles.length > 0" class="flex flex-wrap gap-2 pt-1">
                <div 
                    v-for="(file, index) in selectedFiles" 
                    :key="index" 
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm"
                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: surface.textPrimary }"
                >
                    <span class="material-symbols-outlined text-[16px]">draft</span>
                    <span class="truncate max-w-37.5 md:max-w-50">{{ file.name }}</span>
                    <button 
                        type="button" 
                        @click.prevent="removeFile(index)" 
                        class="hover:text-red-500 ml-1 transition-colors"
                    >
                        <span class="material-symbols-outlined text-[16px] block">close</span>
                    </button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between pt-2">
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="triggerFileInput('image/*')"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors text-sm font-medium hover:opacity-80 border"
                        :style="{ backgroundColor: surface.inputBg, color: surface.textSecondary, borderColor: surface.borderSubtle }"
                    >
                        <span class="material-symbols-outlined text-lg">image</span>
                        Photo
                    </button>
                    <button 
                        type="button" 
                        @click="triggerFileInput('*/*')"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors text-sm font-medium hover:opacity-80 border"
                        :style="{ backgroundColor: surface.inputBg, color: surface.textSecondary, borderColor: surface.borderSubtle }"
                    >
                        <span class="material-symbols-outlined text-lg">attach_file</span>
                        File
                    </button>
                </div>
                
                <button 
                    type="submit" 
                    :disabled="isPosting"
                    class="theme-button w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg font-bold transition-all text-sm disabled:opacity-50"
                >
                    <span class="material-symbols-outlined text-lg">send</span>
                    {{ isPosting ? 'Posting...' : 'Post Announcement' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Scoped theme classes specifically for the composer */
.theme-input {
    background-color: transparent;
    border: 1px solid v-bind('surface.borderSubtle');
    color: v-bind('surface.textPrimary');
}

.theme-input::placeholder {
    color: v-bind('surface.textMuted');
}

.theme-input:focus {
    outline: none;
    border-color: v-bind('theme.accent');
    box-shadow: 0 0 0 3px v-bind('theme.accent + "33"'); 
}

.theme-button {
    background-color: v-bind('theme.accent');
    color: white;
    box-shadow: 0 4px 12px v-bind('theme.accent + "33"');
}

.theme-button:hover:not(:disabled) {
    background-color: v-bind('theme.accent + "dd"');
}

.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>