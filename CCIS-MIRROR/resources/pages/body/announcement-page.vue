<script lang="ts">
export default { layout: null }
</script>

<script setup lang="ts">
import { ref, shallowRef, onMounted } from 'vue'
import { Megaphone } from 'lucide-vue-next'
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
const stats = ref({ 
    cs: 0, 
    it: 0, 
    is: 0, 
    lsg: 0,
    all: 0 
})
const csrfToken = ref('')
const isLoading = ref(true)

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
        
        // Construct URL with query parameter if topic exists
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
                    
                    <GeneralAnnouncements 
                        :announcements="announcements"
                        :is-loading="isLoading"
                    />

                   <aside class="w-full xl:w-80 shrink-0 flex flex-col gap-6 sticky top-0">
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
    </div>
</template>