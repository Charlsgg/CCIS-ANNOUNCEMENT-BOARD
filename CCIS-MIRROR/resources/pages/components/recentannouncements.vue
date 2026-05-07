<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTheme } from '../composable/usetheme.ts'
import AnnouncementCard from './announcementcard.vue'
import FilePreviewModal from '../modals/filepreviewmodal.vue'

interface Attachment {
    id?: number | string
    attachment_id?: number | string
    file_type: string
    file_path: string
    url?: string
}

interface Announcement {
    id: number | string
    title: string
    content: string
    topic?: string
    date: string
    likes_count?: number
    attachments?: Attachment[]
    author_name?: string
    author_avatar?: string | null
}

const props = defineProps<{
    announcements: Announcement[]
}>()

const emit = defineEmits<{
    (e: 'delete', id: number | string): void
    (e: 'update', payload: {
        id: number | string,
        data: { title: string, content: string, topic: string },
        attachments: { newFiles: File[], deletedIds: (number | string)[] }
    }): void
}>()

const { theme, styles, surface } = useTheme()

// --- NEW: Search State ---
const searchQuery = ref('')

// --- UPDATED: Computed Filtering & Sorting ---
const filteredAndSortedAnnouncements = computed(() => {
    let result = props.announcements

    // 1. Filter based on search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim()
        result = result.filter(post => 
            post.title.toLowerCase().includes(query) ||
            post.content.toLowerCase().includes(query) ||
            (post.topic && post.topic.toLowerCase().includes(query)) ||
            (post.author_name && post.author_name.toLowerCase().includes(query))
        )
    }

    // 2. Sort the filtered results by date
    return [...result].sort((a, b) => {
        return new Date(b.date).getTime() - new Date(a.date).getTime()
    })
})

const getFileName = (path?: string | null) => {
    if (!path) return 'Download File'
    const base = path.split('/').pop() || 'Download File'
    return base.split('?')[0]
}

// --- View State Management ---
const activePreview = ref<Attachment | null>(null)

const openPreview = (file: Attachment) => {
    activePreview.value = file
}

const closePreview = () => {
    activePreview.value = null
}

// --- Edit Modal State Management ---
const isEditModalOpen = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)

const editForm = ref({
    id: '' as number | string,
    title: '',
    content: '',
    topic: ''
})

const existingAttachments = ref<Attachment[]>([])
const deletedAttachmentIds = ref<(number | string)[]>([])
const newAttachments = ref<File[]>([])

const openEditModal = (post: Announcement) => {
    editForm.value = {
        id: post.id,
        title: post.title,
        content: post.content,
        topic: post.topic || ''
    }

    existingAttachments.value = post.attachments ? [...post.attachments] : []
    deletedAttachmentIds.value = []
    newAttachments.value = []

    isEditModalOpen.value = true
}

const closeEditModal = () => {
    isEditModalOpen.value = false
    if (fileInputRef.value) fileInputRef.value.value = ''
}

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files) {
        newAttachments.value.push(...Array.from(target.files))
    }
    if (fileInputRef.value) fileInputRef.value.value = ''
}

const removeExistingAttachment = (id?: number | string) => {
    if (!id) return;
    if (!deletedAttachmentIds.value.includes(id)) {
        deletedAttachmentIds.value.push(id)
    }
}

const removeNewAttachment = (index: number) => {
    newAttachments.value.splice(index, 1)
}

const triggerFileInput = () => {
    fileInputRef.value?.click()
}

const submitEdit = () => {
    emit('update', {
        id: editForm.value.id,
        data: {
            title: editForm.value.title,
            content: editForm.value.content,
            topic: editForm.value.topic
        },
        attachments: {
            newFiles: [...newAttachments.value],
            deletedIds: [...deletedAttachmentIds.value]
        }
    })
    closeEditModal()
}
</script>

<template>
    <div class="flex flex-col gap-6 relative w-full">

        <!-- Header Actions: Search & View All -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 w-full">
            
            <!-- Restrained Width Search Bar -->
            <div class="relative w-full sm:max-w-md shadow-sm rounded-xl" :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-[20px]" :style="styles.textSecondary">search</span>
                </div>
                <input 
                    v-model="searchQuery" 
                    type="text" 
                    placeholder="Search announcements..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border focus:outline-none focus:ring-2 transition-all duration-300"
                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: styles.textPrimary.color, '--tw-ring-color': theme.accent }" 
                />
                <button 
                    v-if="searchQuery" 
                    @click="searchQuery = ''"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center hover:opacity-70 transition-opacity"
                    title="Clear search"
                >
                    <span class="material-symbols-outlined text-[18px]" :style="styles.textSecondary">close</span>
                </button>
            </div>

        </div>

        <!-- Announcement Loop -->
        <AnnouncementCard v-for="post in filteredAndSortedAnnouncements" :key="post.id" :post="post" :show-actions="true"
            @preview="openPreview" @edit="openEditModal" @delete="$emit('delete', $event)" />

        <Teleport to="body">
            <div v-if="isEditModalOpen"
                class="fixed inset-0 z-9999 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                <div class="w-full max-w-lg rounded-xl shadow-lg flex flex-col overflow-hidden border"
                    :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">

                    <div class="px-6 py-4 border-b flex justify-between items-center"
                        :style="{ borderColor: surface.borderSubtle }">
                        <h3 class="text-lg font-bold" :style="styles.textPrimary">Edit Announcement</h3>
                        <button @click="closeEditModal" class="p-1 rounded hover:bg-black/5"
                            :style="styles.textSecondary">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-5 overflow-y-auto max-h-[70vh]">
                        <div class="space-y-1">
                            <label class="text-sm font-medium" :style="styles.textPrimary">Title <span
                                    class="text-red-500">*</span></label>
                            <input v-model="editForm.title" type="text" required maxlength="255"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: styles.textPrimary.color }" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium" :style="styles.textPrimary">Subject</label>
                            <input v-model="editForm.topic" type="text" maxlength="255"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: styles.textPrimary.color }" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium" :style="styles.textPrimary">Content <span
                                    class="text-red-500">*</span></label>
                            <textarea v-model="editForm.content" required rows="4"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2 resize-y"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: styles.textPrimary.color }"></textarea>
                        </div>

                        <div class="space-y-3 pt-2 border-t" :style="{ borderColor: surface.borderSubtle }">
                            <label class="text-sm font-medium" :style="styles.textPrimary">Attachments</label>

                            <div v-for="attachment in existingAttachments.filter(a => !deletedAttachmentIds.includes(a.attachment_id!))"
                                :key="attachment.attachment_id!"
                                class="flex items-center justify-between p-2 rounded border"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }">
                                <span class="text-sm truncate mr-2" :style="styles.textPrimary">{{
                                    getFileName(attachment.file_path) }}</span>
                                <button type="button" @click="removeExistingAttachment(attachment.attachment_id)"
                                    class="text-red-500">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </div>

                            <div v-for="(file, index) in newAttachments" :key="index"
                                class="flex items-center justify-between p-2 rounded border"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }">
                                <span class="text-sm truncate mr-2" :style="styles.textPrimary">{{ file.name }}</span>
                                <button type="button" @click="removeNewAttachment(index)" class="text-red-500">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </div>

                            <input type="file" multiple ref="fileInputRef" @change="handleFileSelect" class="hidden" />
                            <button type="button" @click="triggerFileInput"
                                class="px-3 py-2 w-full text-sm rounded border border-dashed flex justify-center items-center gap-2 hover:opacity-80 transition-opacity"
                                :style="{ borderColor: surface.borderSubtle, color: theme.accent, backgroundColor: surface.inputBg }">
                                <span class="material-symbols-outlined text-sm">attach_file</span>
                                Add Files
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t flex justify-end gap-3"
                        :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.inputBg }">
                        <button @click="closeEditModal" type="button" class="px-4 py-2 text-sm font-medium"
                            :style="styles.textSecondary">Cancel</button>
                        <button @click="submitEdit" type="button" :disabled="!editForm.title || !editForm.content"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-50"
                            :style="{ backgroundColor: theme.accent }">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <FilePreviewModal :file="activePreview" @close="closePreview" />

    </div>
</template>

<style scoped>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>