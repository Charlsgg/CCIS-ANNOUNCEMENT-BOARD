<script setup lang="ts">
import { ref } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

interface Attachment {
    id: number | string
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
    author_avatar: string | null // This will be the full Supabase URL
    likes_count: number
    attachments: Attachment[]
}

defineProps<{
    announcements: Announcement[]
    isLoading: boolean
}>()

const { theme, styles, surface } = useTheme()

// Track which posts have their attachments expanded
const expandedPosts = ref<Set<number | string>>(new Set())

// Track the active file being previewed
const activePreview = ref<Attachment | null>(null)

const toggleAttachments = (postId: number | string) => {
    if (expandedPosts.value.has(postId)) {
        expandedPosts.value.delete(postId)
    } else {
        expandedPosts.value.add(postId)
    }
}

// Open and close modal functions
const openPreview = (file: Attachment) => {
    activePreview.value = file
    document.body.style.overflow = 'hidden'
}

const closePreview = () => {
    activePreview.value = null
    document.body.style.overflow = 'auto'
}

// --- Helpers ---

/**
 * Since your backend provides the full URL, we just return it.
 * We add a fallback to handle nulls gracefully.
 */
const getFileUrl = (path?: string | null) => {
    return path || '#'
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
    return path.split('/').pop()?.split('?')[0] || 'Download File'
}

// Standard placeholder for users without an avatar
const defaultAvatar = `https://ui-avatars.com/api/?background=random&color=fff&name=User`

</script>

<template>
    <section class="flex-1 min-w-0 flex flex-col gap-6">
        <div v-if="isLoading" class="animate-pulse space-y-6">
            <div v-for="i in 3" :key="i" class="h-48 rounded-xl" :style="{ backgroundColor: surface.cardBg }"></div>
        </div>

        <article v-for="post in announcements" :key="post.id"
            class="rounded-xl p-6 space-y-4 shadow-sm border transition-all min-w-0 w-full overflow-hidden"
            :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
            
            <div class="flex items-center gap-3">
                <div class="relative">
                    <img 
                        class="size-10 rounded-full object-cover border" 
                        :style="{ borderColor: surface.borderSubtle }"
                        :src="post.author_avatar ? getFileUrl(post.author_avatar) : defaultAvatar" 
                        @error="(e) => (e.target as HTMLImageElement).src = defaultAvatar"
                    />
                </div>
                <div>
                    <h3 class="text-base font-bold leading-tight" :style="styles.textPrimary">{{ post.title }}</h3>
                    <p class="text-xs font-medium" :style="styles.textSecondary">
                        {{ post.author_name }} 
                        <span class="mx-1 opacity-50">•</span> 
                        {{ post.date }}
                    </p>
                </div>
            </div>

            <div class="text-sm leading-relaxed break-words whitespace-pre-wrap overflow-hidden"
                :style="styles.textPrimary" v-html="post.content"></div>

            <div v-if="post.attachments.length > 0" class="mt-4">
                <button @click="toggleAttachments(post.id)"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-lg border transition-all active:scale-95"
                    :style="{
                        backgroundColor: surface.inputBg,
                        borderColor: surface.borderSubtle,
                        color: surface.textPrimary
                    }">
                    <span :style="{ color: theme.accent }" class="material-symbols-outlined text-sm">attach_file</span>
                    {{ expandedPosts.has(post.id) ? 'Hide Attachments' : `View Attachments (${post.attachments.length})` }}
                </button>

                <div v-show="expandedPosts.has(post.id)" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                    <template v-for="file in post.attachments" :key="file.id">

                        <div v-if="isImage(file.file_type)"
                            class="h-44 w-full rounded-lg bg-cover bg-center border cursor-pointer hover:opacity-90 transition-opacity relative group overflow-hidden"
                            title="Preview Image" :style="{
                                backgroundImage: `url('${getFileUrl(file.file_path)}')`,
                                borderColor: surface.borderSubtle
                            }" @click="openPreview(file)">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <span class="bg-white/20 backdrop-blur-sm text-white p-2 rounded-full material-symbols-outlined">visibility</span>
                            </div>
                        </div>

                        <div v-else
                            class="rounded-lg p-3 flex items-center justify-between border cursor-pointer hover:opacity-80 transition-opacity"
                            :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }"
                            @click="openPreview(file)" title="Preview File">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <span :style="{ color: theme.accent }" class="material-symbols-outlined text-sm">
                                    {{ isPdf(file.file_type) ? 'picture_as_pdf' : 'description' }}
                                </span>
                                <span class="text-xs font-medium truncate max-w-[150px]" :style="styles.textPrimary">
                                    {{ getFileName(file.file_path) }}
                                </span>
                            </div>
                            <span :style="{ color: theme.accent }" class="material-symbols-outlined text-sm">visibility</span>
                        </div>
                    </template>
                </div>
            </div>
        </article>

        <div v-if="!isLoading && announcements.length === 0" class="text-center py-20 rounded-xl border-2 border-dashed" :style="{ borderColor: surface.borderSubtle }">
            <span class="material-symbols-outlined text-4xl mb-2 opacity-30">campaign</span>
            <p :style="styles.textSecondary">No announcements found.</p>
        </div>
    </section>

    <Teleport to="body">
        <Transition name="fade">
            <div v-if="activePreview"
                class="fixed inset-0 z-[150] flex items-center justify-center bg-black/95 backdrop-blur-md p-4"
                @click.self="closePreview">
                
                <button @click="closePreview"
                    class="absolute top-6 right-6 z-[160] w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <div class="w-full h-full flex items-center justify-center pointer-events-none">
                    <img v-if="isImage(activePreview.file_type)" :src="getFileUrl(activePreview.file_path)"
                        class="max-w-full max-h-full object-contain pointer-events-auto shadow-2xl" />

                    <iframe v-else-if="isPdf(activePreview.file_type)" :src="getFileUrl(activePreview.file_path)"
                        class="w-full h-full md:w-[85%] md:h-[90%] rounded-lg border border-white/10 bg-white pointer-events-auto"
                        frameborder="0"></iframe>

                    <div v-else class="text-center pointer-events-auto">
                        <span class="material-symbols-outlined text-6xl text-orange-500 mb-4">draft</span>
                        <p class="text-white font-medium mb-6">Preview not available for this file type.</p>
                        <a :href="getFileUrl(activePreview.file_path)" download
                            target="_blank"
                            class="inline-block bg-orange-500 hover:bg-orange-400 text-black px-6 py-2 rounded-full font-bold transition-colors">
                            Download File
                        </a>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>