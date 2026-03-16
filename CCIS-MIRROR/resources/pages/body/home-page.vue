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
const newTopic = ref('') // Added topic ref
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
                // Include topic here if your backend supports it: topic: newTopic.value
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
            newTopic.value = ''
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
        :style="styles.pageBg"
    >
        <div
            v-if="isSidebarOpen"
            @click="isSidebarOpen = false"
            class="absolute inset-0 z-40 md:hidden backdrop-blur-sm transition-opacity"
            :style="{ backgroundColor: surface.overlayBg }"
        ></div>

        <AppSidebar
            :is-open="isSidebarOpen"
            :csrf-token="csrfToken"
            @close="isSidebarOpen = false"
        />

        <main class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
            <AppNavbar
                :user-name="user?.name"
                @toggle-sidebar="isSidebarOpen = true"
            />

            <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full">
                <div class="max-w-4xl mx-auto pb-12">
                    <div class="mb-6 md:mb-8 flex flex-col gap-2">
                        <h3
                            class="text-2xl md:text-3xl font-bold tracking-tight"
                            :style="styles.textPrimary"
                        >
                            Your Announcements 
                        </h3>
                        <p :style="styles.textSecondary" class="text-sm md:text-base max-w-2xl">
                            Create and see your announcements here.
                        </p>
                    </div>

                    <div 
                        class="mb-10 rounded-2xl shadow-sm overflow-hidden flex flex-col transition-colors p-4 md:p-6"
                        :style="styles.cardBg"
                    >
                        <form @submit.prevent="postAnnouncement" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label for="title" class="block text-sm font-bold" :style="styles.textPrimary">Title</label>
                                    <input 
                                        type="text" 
                                        id="title" 
                                        v-model="newTitle"
                                        placeholder="e.g., General Assembly 2024 Schedule" 
                                        class="theme-input w-full px-4 py-2.5 rounded-xl transition-colors"
                                    >
                                </div>
                                <div class="space-y-1.5">
                                    <label for="topic" class="block text-sm font-bold" :style="styles.textPrimary">Topic</label>
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

                            <div class="flex items-center justify-between pt-2">
                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors text-sm font-medium hover:opacity-80 border"
                                        :style="{ backgroundColor: surface.inputBg, color: surface.textSecondary, borderColor: surface.borderSubtle }"
                                    >
                                        <span class="material-symbols-outlined text-lg">image</span>
                                        Photo
                                    </button>
                                    <button 
                                        type="button" 
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
                                    class="theme-button flex items-center gap-2 px-6 py-2.5 rounded-lg font-bold transition-all text-sm disabled:opacity-50"
                                >
                                    <span class="material-symbols-outlined text-lg">send</span>
                                    {{ isPosting ? 'Posting...' : 'Post Announcement' }}
                                </button>
                            </div>
                        </form>
                    </div>

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
                            <div
                                class="h-16 w-16 md:h-20 md:w-20 rounded-xl border shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform shadow-inner"
                                :style="styles.cardIcon"
                            >
                                <component :is="post.icon" :size="32" />
                            </div>

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

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap');

/* --- Dynamic Theme CSS Bindings --- */
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
    /* Creates a subtle focus ring using the dynamic accent color + 33 (20% opacity hex) */
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
/* ----------------------------------- */

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.whitespace-pre-wrap { white-space: pre-wrap; }

/* Material Symbols Default config */
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>