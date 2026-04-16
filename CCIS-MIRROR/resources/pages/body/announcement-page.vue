<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

import GeneralAnnouncements from '../components/generalannouncements.vue'
import UpcomingDeadlines from '../components/upcomingdeadlines.vue'
import AnnouncementFilters from '../components/announcementfilters.vue'

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
}>()

const { isDark, setUserType, initTheme } = useTheme()

const activeTopic = ref<string | null>(null)
const announcements = ref<any[]>([])
const upcomingEvents = ref([])
const stats = ref({ cs: 0, it: 0, is: 0, lsg: 0, all: 0 })
const isLoading = ref(true)

// --- ADDED FOR PREVIEW ---
const activePreview = ref<any>(null) 

onMounted(() => {
    initTheme()
    if (props.user?.user_type) {
        setUserType(props.user.user_type)
    }
    fetchBoardData()
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
    <div class="max-w-7xl mx-auto pb-12 flex flex-col xl:flex-row gap-8 items-start w-full min-w-0">
        
        <div class="w-full flex-1 order-2 xl:order-1 min-w-0">
            <GeneralAnnouncements 
                :announcements="announcements"
                :is-loading="isLoading"
                @preview="activePreview = $event"
            />
        </div>

        <aside class="w-full xl:w-80 shrink-0 flex flex-col gap-6 order-1 xl:order-2 xl:sticky xl:top-0 min-w-0">
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

    <Teleport to="body">
        <Transition name="fade">
            <div v-if="activePreview"
                class="fixed inset-0 z-200 flex items-center justify-center bg-black/95 backdrop-blur-md p-4">
                <button @click="activePreview = null"
                    class="absolute top-6 right-6 z-210 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all hover:rotate-90">
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
    </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>