<script setup lang="ts">
import { ref } from 'vue'
import { useTheme } from '../composable/usetheme.ts'
import AnnouncementCard from './announcementcard.vue'
import FilePreviewModal from '../modals/filepreviewmodal.vue'

const emit = defineEmits<{
    preview: [file: any] 
}>()

interface Attachment {
    id?: number | string     // <-- Added the '?'
    attachment_id?: number | string // Added just to be safe/consistent!
    file_path: string
    file_type: string
}

interface Announcement {
    id: number | string
    title: string
    content: string
    topic: string
    date: string
    author_name: string
    author_avatar: string | null
    likes_count: number
    attachments: Attachment[]
}

defineProps<{
    announcements: Announcement[]
    isLoading: boolean
}>()

const { theme, styles, surface } = useTheme()

// --- View State Management ---
const activePreview = ref<Attachment | null>(null)

const openPreview = (file: Attachment) => {
    activePreview.value = file
    // Emitting just in case the parent still needs to know, 
    // though the local modal will handle the visual preview.
    emit('preview', file) 
}

const closePreview = () => {
    activePreview.value = null
}
</script>

<template>
    <section class="flex-1 min-w-0 flex flex-col gap-6">
        
        <div v-if="isLoading" class="space-y-6">
            <div v-for="i in 3" :key="i" class="h-64 rounded-xl animate-pulse"
                :style="{ backgroundColor: surface.cardBg, border: `1px solid ${surface.borderSubtle}` }">
            </div>
        </div>

        <template v-else>
            <AnnouncementCard 
                v-for="post in announcements" 
                :key="post.id"
                :post="post"
                :show-actions="false"
                @preview="openPreview"
            />

            <div v-if="announcements.length === 0" class="text-center py-24 rounded-xl border-2 border-dashed"
                :style="{ borderColor: surface.borderSubtle }">
                <span class="material-symbols-outlined text-5xl mb-3 opacity-20"
                    :style="{ color: theme.accent }">campaign</span>
                <p class="font-bold" :style="styles.textSecondary">No announcements available at this time.</p>
            </div>
        </template>
    </section>

    <FilePreviewModal 
        :file="activePreview" 
        @close="closePreview" 
    />
</template>

<style scoped>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
}
</style>