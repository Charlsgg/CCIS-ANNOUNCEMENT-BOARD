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
    author_avatar: string | null
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

// --- UPDATED Helpers ---

/**
 * FIX: Backend already provides the full Supabase URL.
 * We simply return the path as is.
 */
const getFileUrl = (path?: string | null) => {
    if (!path) return '#'
    return path // No more '/storage/' prefix!
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
    // Extracts the filename from the end of the Supabase URL
    return path.split('/').pop()?.split('?')[0] || 'Download File'
}

const defaultAvatar = `data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2394a3b8'><path d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/></svg>`
</script>

<template>
    <section class="flex-1 min-w-0 flex flex-col gap-6">
        <article v-for="post in announcements" :key="post.id"
            class="rounded-xl p-6 space-y-4 shadow-sm border transition-all min-w-0 w-full overflow-hidden"
            :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
            
            <div class="flex items-center gap-3">
                <img class="size-10 rounded-full object-cover border" :style="{ borderColor: surface.borderSubtle }"
                    :src="post.author_avatar ? getFileUrl(post.author_avatar) : defaultAvatar" />
                <div>
                    <h3 class="text-base font-bold leading-tight" :style="styles.textPrimary">{{ post.title }}</h3>
                    <p class="text-xs" :style="styles.textSecondary">{{ post.author_name }} • {{ post.date }}</p>
                </div>
            </div>

            <div class="text-sm leading-relaxed break-all whitespace-pre-wrap overflow-hidden"
                :style="styles.textPrimary" v-html="post.content"></div>

            <div v-if="post.attachments.length > 0" class="mt-4">
                <button @click="toggleAttachments(post.id)"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border transition-colors"
                    :style="{
                        backgroundColor: surface.inputBg,
                        borderColor: surface.borderSubtle,
                        ...styles.textPrimary
                    }">
                    <span :style="{ color: theme.accent }" class="material-symbols-outlined text-sm">attach_file</span>
                    {{ expandedPosts.has(post.id) ? 'Hide Attachments' : `View Attachments (${post.attachments.length})` }}
                </button>

                <div v-show="expandedPosts.has(post.id)" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                    <template v-for="file in post.attachments" :key="file.id">

                        <div v-if="isImage(file.file_type)"
                            class="h-44 w-full rounded-lg bg-cover bg-center border cursor-pointer hover:opacity-90 transition-opacity relative group"
                            title="Preview Image" :style="{
                                backgroundImage: `url('${getFileUrl(file.file_path)}')`,
                                borderColor: surface.borderSubtle
                            }" @click="openPreview(file)">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-lg">
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
                                <span class="text-xs font-medium truncate" :style="styles.textPrimary">
                                    {{ getFileName(file.file_path) }}
                                </span>
                            </div>
                            <span :style="{ color: theme.accent }" class="material-symbols-outlined text-sm">visibility</span>
                        </div>
                    </template>
                </div>
            </div>
        </article>
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