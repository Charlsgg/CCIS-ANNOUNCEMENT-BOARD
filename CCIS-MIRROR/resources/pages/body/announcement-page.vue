<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

import GeneralAnnouncements from '../components/generalannouncements.vue'
import UpcomingDeadlines from '../components/upcomingdeadlines.vue'
import AnnouncementFilters from '../components/announcementfilters.vue'

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
    csrfToken?: string;
}>()

const { isDark, setUserType, initTheme } = useTheme()

const activeTopic = ref<string | null>(null)
const announcements = ref<any[]>([])
const upcomingEvents = ref([])
const stats = ref({ cs: 0, it: 0, is: 0, lsg: 0, all: 0 })
const isLoading = ref(true)

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
</template>