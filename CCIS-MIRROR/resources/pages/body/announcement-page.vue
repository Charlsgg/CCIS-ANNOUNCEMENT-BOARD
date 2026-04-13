<script lang="ts">
export default { layout: null }
</script>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

import AppSidebar from '../components/appsidebar.vue'
import AppNavbar from '../components/appnavbar.vue'
import GeneralAnnouncements from '../components/generalannouncements.vue'
import UpcomingDeadlines from '../components/upcomingdeadlines.vue'
import AnnouncementFilters from '../components/announcementfilters.vue'

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
}>()

const { styles, surface, isDark, setUserType, initTheme } = useTheme()

const activeTopic = ref<string | null>(null)
const isSidebarOpen = ref(false)
const announcements = ref<any[]>([])
const upcomingEvents = ref([])
const stats = ref({ cs: 0, it: 0, is: 0, lsg: 0, all: 0 })
const csrfToken = ref('')
const isLoading = ref(true)

// --- ADDED FOR PREVIEW ---
const activePreview = ref<any>(null) 

onMounted(() => {
    initTheme()
    if (props.user?.user_type) {
        setUserType(props.user.user_type)
    }
    fetchBoardData()
    
    const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
    if (tokenTag) {
        csrfToken.value = tokenTag.content
    }
})

const fetchBoardData = async (topic: string | null = null) => {
    try {
        isLoading.value = true
        activeTopic.value = topic
        const baseUrl = '/api/board-data'
        const url = topic ? `${baseUrl}?topic=${topic}` : baseUrl
        const response = await fetch(url)
        
        if (response.ok) {
            const data = await response.json()
            announcements.value = data.announcements
            upcomingEvents.value = data.upcoming_events
            stats.value = data.stats 
        }
    } catch (error) {
        console.error('Error fetching board data:', error)
    } finally {
        isLoading.value = false
    }
}

const handleFilterChange = (role: string | null) => {
    fetchBoardData(role)
}
</script>

<template>
    <div
        class="fixed inset-0 w-full h-full overflow-hidden font-['Space_Grotesk'] flex transition-colors duration-300"
        :style="{ ...styles.pageBg, color: surface.textPrimary }"
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

            <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full custom-scrollbar">
                <div class="max-w-7xl mx-auto pb-12 flex flex-col xl:flex-row gap-8 items-start">
                    
                    <div class="w-full flex-1 order-2 xl:order-1">
                        <GeneralAnnouncements 
                            :announcements="announcements"
                            :is-loading="isLoading"
                            @preview="activePreview = $event"
                        />
                    </div>

                    <aside class="w-full xl:w-80 shrink-0 flex flex-col gap-6 order-1 xl:order-2 xl:sticky xl:top-0">
                        <UpcomingDeadlines 
                            :events="upcomingEvents"
                            :is-dark="isDark"
                        />
                        
                        <AnnouncementFilters 
                            :stats="stats"
                            @filter-change="handleFilterChange"
                        />
                    </aside>
                </div>
            </div>
        </main>

        <Transition name="fade">
            <div v-if="activePreview"
                class="fixed inset-0 z-[200] flex items-center justify-center bg-black/95 backdrop-blur-md p-4">
                <button @click="activePreview = null"
                    class="absolute top-6 right-6 z-[210] w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all hover:rotate-90">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <div class="w-full h-full flex items-center justify-center">
                    <img v-if="activePreview.file_type.includes('image')" 
                        :src="activePreview.file_path"
                        class="max-w-full max-h-full object-contain animate-in zoom-in duration-300" />

                    <iframe v-else-if="activePreview.file_type.includes('pdf')" 
                        :src="activePreview.file_path"
                        class="w-full h-full md:w-[85%] md:h-[90%] rounded-2xl bg-white"
                        frameborder="0"></iframe>

                    <div v-else class="text-center">
                        <p class="text-white/70 mb-4">Preview not available.</p>
                        <a :href="activePreview.file_path" download class="bg-orange-500 text-black px-6 py-2 rounded-lg">Download</a>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>