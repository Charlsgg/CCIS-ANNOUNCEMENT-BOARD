<script setup lang="ts">
import { ref, shallowRef, onMounted, computed } from 'vue' 
import { Megaphone, Trash2 } from 'lucide-vue-next'
import { useTheme } from '../composable/usetheme.ts'
import AnnouncementComposer from '../components/announcementcomposer.vue'
import RecentAnnouncements from '../components/recentannouncements.vue'
import axios from 'axios'

// --- Types ---
interface Attachment {
    id?: number | string
    attachment_id: number | string // <-- Removed the ? to make it required
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
    icon?: any
}

const props = defineProps<{
    csrfToken?: string;
    user?: { name: string; email: string; user_type: string; profile?: { profile_picture: string } }
}>()

const { theme, styles, surface, setUserType, initTheme } = useTheme()

// --- State Variables ---
const announcements = ref<Announcement[]>([])
const csrfToken = ref('')
const showAll = ref(false) 
const isLoading = ref(false)

// --- Delete State ---
const isDeleteDialogOpen = ref(false)
const announcementToDelete = ref<number | string | null>(null)
const isDeleting = ref(false)

// --- Edit State ---
const isUpdating = ref(false)

// --- Computed ---
const displayedAnnouncements = computed(() => {
    return showAll.value ? announcements.value : announcements.value.slice(0, 3)
})

// --- Lifecycle ---
onMounted(() => {
    initTheme()
    
    // Set Axios defaults
    const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
    if (tokenTag) {
        csrfToken.value = tokenTag.content
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.value
    }
    
    if (props.user?.user_type) {
        setUserType(props.user.user_type)
    }
    fetchAnnouncements()
})

// --- API Calls & Handlers ---
const fetchAnnouncements = async () => {
    isLoading.value = true
    try {
        const response = await axios.get('/my-announcements')
        
        announcements.value = response.data.announcements.map((post: any) => ({
            ...post,
            icon: shallowRef(Megaphone),
        }))
    } catch (error) {
        console.error('Error fetching announcements:', error)
    } finally {
        isLoading.value = false
    }
}

const handleNewAnnouncement = async () => {
    await fetchAnnouncements();
    if (announcements.value.length > 0) {
        showAll.value = false; 
    }
}

// --- Delete Handlers ---
const confirmDelete = (id: number | string) => {
    announcementToDelete.value = id
    isDeleteDialogOpen.value = true
}

const deleteAnnouncement = async () => {
    if (!announcementToDelete.value) return
    
    isDeleting.value = true
    try {
        await axios.delete(`/my-announcements/${announcementToDelete.value}`)
        announcements.value = announcements.value.filter(a => a.id !== announcementToDelete.value)
        isDeleteDialogOpen.value = false
    } catch (error) {
        console.error('Error deleting announcement:', error)
    } finally {
        isDeleting.value = false
        announcementToDelete.value = null
    }
}

// --- Edit Handlers ---
const handleUpdate = async (payload: { 
    id: number | string, 
    data: { title: string, content: string, topic: string },
    attachments: { newFiles: File[], deletedIds: (number | string)[] }
}) => {
    isUpdating.value = true
    
    try {
        const formData = new FormData()
        
        formData.append('title', payload.data.title)
        formData.append('content', payload.data.content)
        if (payload.data.topic) {
            formData.append('topic', payload.data.topic)
        }

        if (payload.attachments && payload.attachments.newFiles.length > 0) {
            payload.attachments.newFiles.forEach((file) => {
                formData.append('newFiles[]', file)
            })
        }

        if (payload.attachments && payload.attachments.deletedIds.length > 0) {
            payload.attachments.deletedIds.forEach((id) => {
                formData.append('deletedIds[]', String(id))
            })
        }

        const response = await axios.post(`/my-announcements/${payload.id}`, formData)

        if (response.data.announcement) {
            const index = announcements.value.findIndex(a => a.id === payload.id)
            if (index !== -1) {
                announcements.value[index] = {
                    ...response.data.announcement,
                    icon: shallowRef(Megaphone)
                }
            }
        } else {
            await fetchAnnouncements()
        }
    } catch (error) {
        console.error('Error updating announcement:', error)
    } finally {
        isUpdating.value = false
    }
}
</script>

<template>
    <div class="max-w-6xl mx-2 sm:mx-4 md:mx-6 lg:mx-auto pb-12 w-full min-w-0 px-4 sm:px-6">

        <div class="mb-6 md:mb-8 flex flex-col gap-2">
            <div class="flex items-center gap-3">
                <h3 class="text-2xl md:text-3xl font-bold tracking-tight" :style="styles.textPrimary">
                    Your Announcements
                </h3>
                <div v-if="isLoading" class="size-4 border-2 border-t-transparent animate-spin rounded-full" :style="{ borderColor: theme.accent }"></div>
            </div>
            <p :style="styles.textSecondary" class="text-sm md:text-base max-w-2xl">
                Create and manage your posts here.
            </p>
        </div>

        <AnnouncementComposer :csrf-token="csrfToken" @post-created="handleNewAnnouncement" />

        <div class="mt-12 mb-4 flex items-center justify-between gap-4">
            <h4 class="font-bold text-lg truncate" :style="styles.textPrimary">
                {{ showAll ? 'All My Announcements' : 'Recent Announcements' }}
                <span v-if="isUpdating" class="ml-2 text-xs font-normal opacity-70 animate-pulse">Updating...</span>
            </h4>

            <button v-if="announcements.length > 3" @click="showAll = !showAll"
                class="text-sm font-semibold hover:underline transition-all shrink-0"
                :style="{ color: theme.accent }">
                {{ showAll ? 'Show Less' : `View All (${announcements.length})` }}
            </button>
        </div>

        <RecentAnnouncements 
            :announcements="displayedAnnouncements" 
            @refresh="fetchAnnouncements"
            @delete="confirmDelete" 
            @update="handleUpdate"
        />

        <div v-if="announcements.length === 0 && !isLoading"
            class="text-center py-20 border-2 border-dashed rounded-2xl mt-4"
            :style="{ borderColor: surface.borderSubtle }">
            <p :style="styles.textSecondary">You haven't posted anything yet.</p>
        </div>

    </div>

    <Teleport to="body">
        <div v-if="isDeleteDialogOpen" class="fixed inset-0 z-9999 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="max-w-md w-full rounded-2xl p-6 shadow-xl" 
                :style="{ backgroundColor: surface.cardBg, border: `1px solid ${surface.borderSubtle}` }">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-full shrink-0">
                        <Trash2 class="w-6 h-6 text-red-500" />
                    </div>
                    <h3 class="text-xl font-bold" :style="styles.textPrimary">Delete Post?</h3>
                </div>
                
                <p class="mb-8" :style="styles.textSecondary">
                    Are you sure you want to delete this announcement? This action is permanent and cannot be undone.
                </p>
                
                <div class="flex justify-end gap-3 flex-wrap">
                    <button @click="isDeleteDialogOpen = false" :disabled="isDeleting"
                        class="px-5 py-2.5 rounded-xl font-medium transition-colors hover:opacity-80 flex-1 sm:flex-none text-center"
                        :style="{ backgroundColor: surface.inputBg, color: styles.textPrimary.color }">
                        Cancel
                    </button>
                    <button @click="deleteAnnouncement" :disabled="isDeleting"
                        class="px-5 py-2.5 rounded-xl font-medium text-white bg-red-500 hover:bg-red-600 transition-colors flex items-center justify-center gap-2 flex-1 sm:flex-none">
                        <span v-if="isDeleting" class="size-4 border-2 border-white/30 border-t-white animate-spin rounded-full"></span>
                        {{ isDeleting ? 'Deleting...' : 'Delete Post' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>