<script setup lang="ts">
import { ref } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

interface Attachment {
    attachment_id: number | string
    file_type: string
    file_path: string
    url?: string
}

interface Announcement {
    id: number | string
    title: string
    content: string
    topic: string
    date: string
    likes_count: number
    attachments?: Attachment[]
    author_name?: string
    author_avatar?: string | null
}

defineProps<{
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

/**
 * FIXED URL CLEANER
 * Syncs with Laravel Storage::disk('s3')->url() output and manual relative paths.
 */
const getFileUrl = (path?: string | null) => {
    // 1. Failsafe for null/ghost values
    if (!path || path === 'undefined' || path === 'null') {
        return 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs='
    }

    // 2. CRITICAL FIX: Since your JSON already shows a full URL:
    // "https://hahocarxbknajzqjacuk.supabase.co/storage/v1/object/public/..."
    // Just return it exactly as it is. Do NOT add prefixes.
    if (path.startsWith('http')) {
        return path
    }

    // 3. Fallback for old records that might still be relative paths
    const PROJECT_ID = 'hahocarxbknajzqjacuk'
    const BUCKET = 'announcements'
    
    // Remove any leading slashes or redundant bucket names from the relative path
    let cleanPath = path.replace(/^announcements\//, '').replace(/^\/+/, '')
    
    return `https://${PROJECT_ID}.supabase.co/storage/v1/object/public/${BUCKET}/${cleanPath}`
}

const isImage = (type: string | null) => {
    if (!type) return false
    return type.startsWith('image/') || /\.(jpg|jpeg|png|webp|gif)$/i.test(type)
}

const isPdf = (type: string | null) => {
    if (!type) return false
    return type === 'application/pdf' || /\.(pdf)$/i.test(type)
}

const getFileName = (path?: string | null) => {
    if (!path) return 'Download File'
    const base = path.split('/').pop() || 'Download File'
    return base.split('?')[0] // Strip query strings
}

const getDefaultAvatar = (name?: string) => {
    return `https://ui-avatars.com/api/?background=random&color=fff&name=${encodeURIComponent(name || 'User')}`
}

// --- View State Management ---
const expandedPosts = ref<Set<number | string>>(new Set())
const activePreview = ref<Attachment | null>(null)

const toggleAttachments = (postId: number | string) => {
    if (expandedPosts.value.has(postId)) {
        expandedPosts.value.delete(postId)
    } else {
        expandedPosts.value.add(postId)
    }
}

const openPreview = (file: Attachment) => {
    activePreview.value = file
    document.body.style.overflow = 'hidden'
}

const closePreview = () => {
    activePreview.value = null
    document.body.style.overflow = 'auto'
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

const removeExistingAttachment = (id: number | string) => {
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
    <div class="flex flex-col gap-6 relative">
        <div v-for="post in announcements" :key="post.id"
            class="rounded-xl p-5 space-y-4 shadow-sm hover:shadow-md transition-shadow relative"
            :style="styles.cardBg">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img :alt="post.author_name || 'Author'" class="size-10 rounded-full object-cover border"
                        :style="{ borderColor: surface.borderSubtle }"
                        :src="post.author_avatar ? getFileUrl(post.author_avatar) : getDefaultAvatar(post.author_name)"
                        @error="(e) => (e.target as HTMLImageElement).src = getDefaultAvatar(post.author_name)" />
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide" :style="styles.textPrimary">
                            {{ post.title }}
                        </p>
                        <p class="text-xs" :style="styles.textSecondary">
                            <span v-if="post.author_name" class="font-medium">{{ post.author_name }} &bull; </span>
                            Posted {{ post.date }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 relative z-50">
                    <button type="button" @click.prevent.stop="openEditModal(post)"
                        class="p-2 flex items-center justify-center rounded-lg transition-all cursor-pointer shadow-sm border hover:scale-105"
                        :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: theme.accent }"
                        title="Edit">
                        <span class="material-symbols-outlined text-lg">edit</span>
                    </button>

                    <button type="button" @click.prevent.stop="$emit('delete', post.id)"
                        class="p-2 flex items-center justify-center rounded-lg transition-all cursor-pointer shadow-sm border hover:scale-105 text-red-500 hover:bg-red-50/10"
                        :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }" title="Delete">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </div>
            </div>

            <p class="leading-relaxed break-all whitespace-pre-wrap text-sm md:text-base" :style="styles.textPrimary"
                v-html="post.content"></p>

            <div v-if="post.attachments && post.attachments.length > 0" class="mt-4 relative z-10">
                <button @click="toggleAttachments(post.id)"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border transition-colors"
                    :style="{
                        backgroundColor: surface.inputBg,
                        borderColor: surface.borderSubtle,
                        color: surface.textPrimary
                    }">
                    <span :style="{ color: theme.accent }" class="material-symbols-outlined text-sm">attach_file</span>
                    {{ expandedPosts.has(post.id) ? 'Hide Attachments' : `View Attachments (${post.attachments.length})` }}
                </button>

                <div v-show="expandedPosts.has(post.id)" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                    <template v-for="attachment in post.attachments" :key="attachment.attachment_id">
                        <div v-if="isImage(attachment.file_type)"
                            class="h-44 w-full rounded-lg bg-cover bg-center border cursor-pointer hover:opacity-90 transition-opacity relative group"
                            :style="{
                                backgroundImage: `url('${getFileUrl(attachment.file_path)}')`,
                                borderColor: surface.borderSubtle
                            }" @click="openPreview(attachment)">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-lg">
                                <span class="bg-white/20 backdrop-blur-sm text-white p-2 rounded-full material-symbols-outlined">visibility</span>
                            </div>
                        </div>

                        <div v-else
                            class="rounded-lg p-3 flex items-center justify-between border cursor-pointer hover:opacity-80 transition-opacity"
                            :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }"
                            @click="openPreview(attachment)" title="Preview File">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <span :style="{ color: theme.accent }" class="material-symbols-outlined text-sm">
                                    {{ isPdf(attachment.file_type) ? 'picture_as_pdf' : 'description' }}
                                </span>
                                <span class="text-xs font-medium truncate" :style="styles.textPrimary">
                                    {{ getFileName(attachment.file_path) }}
                                </span>
                            </div>
                            <span :style="{ color: theme.accent }" class="material-symbols-outlined text-sm">visibility</span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pt-2 flex gap-4 relative z-10" :style="{ borderTop: `1px solid ${surface.borderSubtle}` }">
                <button class="flex items-center gap-1.5 text-xs hover:text-red-500 transition-colors"
                    :style="styles.textSecondary">
                    <span class="material-symbols-outlined text-sm">favorite</span>
                    {{ post.likes_count }}
                </button>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="isEditModalOpen" class="fixed inset-0 z-100 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                <div class="w-full max-w-lg rounded-xl shadow-lg flex flex-col overflow-hidden border"
                    :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">

                    <div class="px-6 py-4 border-b flex justify-between items-center"
                        :style="{ borderColor: surface.borderSubtle }">
                        <h3 class="text-lg font-bold" :style="styles.textPrimary">Edit Announcement</h3>
                        <button @click="closeEditModal" class="p-1 rounded hover:bg-black/5" :style="styles.textSecondary">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="p-6 space-y-5 overflow-y-auto max-h-[70vh]">
                        <div class="space-y-1">
                            <label class="text-sm font-medium" :style="styles.textPrimary">Title <span class="text-red-500">*</span></label>
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
                            <label class="text-sm font-medium" :style="styles.textPrimary">Content <span class="text-red-500">*</span></label>
                            <textarea v-model="editForm.content" required rows="4"
                                class="w-full px-3 py-2 rounded-lg border focus:outline-none focus:ring-2 resize-y"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: styles.textPrimary.color }"></textarea>
                        </div>

                        <div class="space-y-3 pt-2 border-t" :style="{ borderColor: surface.borderSubtle }">
                            <label class="text-sm font-medium" :style="styles.textPrimary">Attachments</label>

                            <div v-for="attachment in existingAttachments.filter(a => !deletedAttachmentIds.includes(a.attachment_id))"
                                :key="attachment.attachment_id"
                                class="flex items-center justify-between p-2 rounded border"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }">
                                <span class="text-sm truncate mr-2" :style="styles.textPrimary">{{ getFileName(attachment.file_path) }}</span>
                                <button type="button" @click="removeExistingAttachment(attachment.attachment_id)" class="text-red-500">
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

                    <div class="px-6 py-4 border-t flex justify-end gap-3" :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.inputBg }">
                        <button @click="closeEditModal" type="button" class="px-4 py-2 text-sm font-medium" :style="styles.textSecondary">Cancel</button>
                        <button @click="submitEdit" type="button" :disabled="!editForm.title || !editForm.content"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-white disabled:opacity-50"
                            :style="{ backgroundColor: theme.accent }">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <Transition name="fade">
                <div v-if="activePreview"
                    class="fixed inset-0 z-110 flex items-center justify-center bg-black/95 backdrop-blur-md p-4"
                    @click.self="closePreview">
                    <button @click="closePreview"
                        class="absolute top-6 right-6 z-120 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
                        <span class="material-symbols-outlined">close</span>
                    </button>

                    <div class="w-full h-full flex items-center justify-center pointer-events-none">
                        <img v-if="isImage(activePreview.file_type)" :src="getFileUrl(activePreview.file_path)"
                            class="max-w-full max-h-full object-contain pointer-events-auto" />

                        <iframe v-else-if="isPdf(activePreview.file_type)" :src="getFileUrl(activePreview.file_path)"
                            class="w-full h-full md:w-[85%] md:h-[90%] rounded-lg border border-white/10 bg-white pointer-events-auto"
                            frameborder="0"></iframe>

                        <div v-else class="text-center pointer-events-auto">
                            <span class="material-symbols-outlined text-6xl text-orange-500 mb-4">draft</span>
                            <p class="text-white font-medium mb-6">Preview not available.</p>
                            <a :href="getFileUrl(activePreview.file_path)" download target="_blank"
                                class="inline-block bg-orange-500 hover:bg-orange-400 text-black px-6 py-2 rounded-full font-bold transition-colors">
                                Download File
                            </a>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>