<script lang="ts">
export default { layout: null }
</script>

<script setup lang="ts">
import { ref, shallowRef, onMounted } from 'vue'
import { Megaphone, Send } from 'lucide-vue-next'
import { useTheme } from '../composable/usetheme.ts'
import AppSidebar from '../components/appsidebar.vue'
import AppNavbar from '../components/appnavbar.vue'

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
}>()

const { theme, styles, surface, isDark, setUserType, initTheme } = useTheme()

const isSidebarOpen = ref(false)
const newTitle = ref('')
const newContent = ref('')
const announcements = ref<any[]>([])
const csrfToken = ref('')
const isPosting = ref(false)


onMounted(() => {
    initTheme()
    if (props.user?.user_type) {
        setUserType(props.user.user_type)
    }
    fetchAnnouncements()
    const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
    if (tokenTag) {
        csrfToken.value = tokenTag.content
    }
})


const fetchAnnouncements = async () => {
    try {
        const response = await fetch('/api/announcements')
        if (response.ok) {
            const data = await response.json()
            announcements.value = data.map((post: any) => ({
                id: post.announcement_id,
                title: post.title,
                content: post.content,
                date: new Date(post.created_at).toLocaleDateString(undefined, {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                }),
                icon: shallowRef(Megaphone),
            }))
        }
    } catch (error) {
        console.error('Error fetching announcements:', error)
    }
}

const postAnnouncement = async () => {
    if (!newTitle.value.trim() || !newContent.value.trim()) {
        alert('Please enter both a title and a message.')
        return
    }

    isPosting.value = true

    try {
        const response = await fetch('/api/announcements', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
            },
            body: JSON.stringify({
                title: newTitle.value,
                content: newContent.value,
                board_id: 1,
            }),
        })

        if (response.ok) {
            const savedPost = await response.json()
            announcements.value.unshift({
                id: savedPost.announcement_id,
                title: savedPost.title,
                content: savedPost.content,
                date: 'Just now',
                icon: shallowRef(Megaphone),
            })
            newTitle.value = ''
            newContent.value = ''
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
        class="fixed inset-0 w-full h-full overflow-hidden font-['Space_Grotesk'] flex transition-colors duration-300"
        :style="{ ...styles.pageBg, color: surface.textPrimary }"
    >
        <!-- Mobile Overlay -->
        <div
            v-if="isSidebarOpen"
            @click="isSidebarOpen = false"
            class="absolute inset-0 z-40 md:hidden backdrop-blur-sm transition-opacity"
            :style="{ backgroundColor: surface.overlayBg }"
        ></div>

        <!-- Sidebar -->
        <AppSidebar
            :is-open="isSidebarOpen"
            :csrf-token="csrfToken"
            @close="isSidebarOpen = false"
        />

        <!-- Main -->
        <main class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
            <!-- Navbar -->
            <AppNavbar
                :user-name="user?.name"
                @toggle-sidebar="isSidebarOpen = true"
            />

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full">
                <div class="max-w-250 mx-auto pb-12">
                    <!-- Page Heading -->
                    <div class="mb-6 md:mb-8 flex flex-col gap-2">
                        <h3
                            class="text-2xl md:text-3xl font-bold tracking-tight"
                            :style="styles.textPrimary"
                        >
                            {{ theme.announcementHeading }}
                        </h3>
                        <p :style="styles.textSecondary" class="text-sm md:text-base max-w-2xl">
                            {{ theme.announcementSubheading }}
                        </p>
                    </div>

                    <!-- Composer -->
                    <div
                        class="mb-10 rounded-2xl shadow-lg shadow-black/10 overflow-hidden flex flex-col transition-colors p-4 md:p-6"
                        :style="styles.cardBg"
                    >
                        <input
                            v-model="newTitle"
                            type="text"
                            placeholder="Announcement Title"
                            class="w-full bg-transparent border-none focus:ring-0 text-xl md:text-2xl font-bold px-0 pb-4 outline-none mb-4"
                            :style="{ ...styles.input, color: surface.textPrimary }"
                            :class="isDark ? 'placeholder-slate-600' : 'placeholder-slate-400'"
                        />

                        <textarea
                            v-model="newContent"
                            placeholder="What's the latest update?"
                            class="w-full min-h-32 bg-transparent border-none focus:ring-0 text-base outline-none resize-none"
                            :style="{ color: surface.textPrimary }"
                            :class="isDark ? 'placeholder-slate-600' : 'placeholder-slate-400'"
                        ></textarea>

                        <div
                            class="mt-6 flex flex-col sm:flex-row items-end justify-end gap-4 pt-4"
                            :style="styles.composerBorder"
                        >
                            <button
                                @click="postAnnouncement"
                                :disabled="isPosting"
                                class="w-full sm:w-auto px-8 py-2.5 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :style="styles.button"
                                @mouseenter="(e: MouseEvent) => {
                                    if (!isPosting) (e.currentTarget as HTMLElement).style.opacity = '0.9'
                                }"
                                @mouseleave="(e: MouseEvent) => {
                                    (e.currentTarget as HTMLElement).style.opacity = '1'
                                }"
                            >
                                <span v-if="isPosting">Posting...</span>
                                <span v-else>Post Announcement</span>
                                <Send :size="16" />
                            </button>
                        </div>
                    </div>

                    <!-- Announcements List -->
                    <div class="flex flex-col gap-6">
                        <div
                            v-if="announcements.length === 0"
                            class="text-center py-12 italic"
                            :style="styles.textMuted"
                        >
                            No announcements posted yet.
                        </div>

                        <div
                            v-for="post in announcements"
                            :key="post.id"
                            class="p-5 md:p-6 rounded-2xl transition-all flex flex-col sm:flex-row gap-5 md:gap-6 group"
                            :style="{
                                backgroundColor: surface.cardBg,
                                border: styles.cardBorder,
                            }"
                            @mouseenter="(e: MouseEvent) => {
                                (e.currentTarget as HTMLElement).style.border = styles.cardBorderHover
                            }"
                            @mouseleave="(e: MouseEvent) => {
                                (e.currentTarget as HTMLElement).style.border = styles.cardBorder
                            }"
                        >
                            <!-- Icon -->
                            <div
                                class="h-16 w-16 md:h-20 md:w-20 rounded-xl border shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform shadow-inner"
                                :style="styles.cardIcon"
                            >
                                <component :is="post.icon" :size="32" />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-1 pb-2"
                                    :style="styles.cardDivider"
                                >
                                    <h5
                                        class="text-base md:text-xl font-bold truncate"
                                        :style="styles.textPrimary"
                                    >
                                        {{ post.title }}
                                    </h5>
                                    <span class="text-xs font-medium shrink-0" :style="styles.textMuted">
                                        {{ post.date }}
                                    </span>
                                </div>

                                <div
                                    class="text-sm mb-4 leading-relaxed whitespace-pre-wrap"
                                    :style="styles.textSecondary"
                                    v-html="post.content"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.whitespace-pre-wrap { white-space: pre-wrap; }
</style>